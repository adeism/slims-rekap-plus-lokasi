<?php
/**
 * Recap plus Location Report (Plugin Edition)
 * Author: Ade Ismail Siregar
 * Author URI: https://github.com/adeism
 */

if (!defined('INDEX_AUTH')) {
    define('INDEX_AUTH', '1');
}

// -- BASIC SECURITY ---------------------------------------------------------
require LIB . 'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-reporting');
require SB . 'admin/default/session.inc.php';
require SB . 'admin/default/session_check.inc.php';

if (!utility::havePrivilege('reporting', 'r')) {
    die('<div class="errorBox">' . __('You don\'t have enough privileges to access this area!') . '</div>');
}

// -- DEPENDENCIES -----------------------------------------------------------
require SIMBIO . 'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO . 'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO . 'simbio_GUI/form_maker/simbio_form_element.inc.php';
require SIMBIO . 'simbio_DB/datagrid/simbio_dbgrid.inc.php';
require MDLBS  . 'reporting/report_dbgrid.inc.php';

// -- HELPER -----------------------------------------------------------------
function currentUrlQuery(array $add = []): string
{
    $skip = ['reportView', 'applyFilter'];
    $keep = [];
    foreach ($_GET as $k => $v) {
        if (in_array($k, $skip, true) || is_array($v)) continue;
        $keep[$k] = $v;
    }
    return http_build_query(array_merge($keep, $add));
}

$page_title = __('Rekap plus Lokasi');
$reportView = isset($_GET['reportView']);

// ---------------------------------------------------------------------------
// 1.  LOAD ALL ITEM STATUSES (for header + counting) -------------------------
// ---------------------------------------------------------------------------
$statusRows = [];
$status_q = $dbs->query('SELECT item_status_id, item_status_name FROM mst_item_status ORDER BY item_status_id');
while ($status_q && ($s = $status_q->fetch_row())) {
    $statusRows[] = $s; // [id, name]
}
$statusCount = count($statusRows);

// ---------------------------------------------------------------------------
// 2.  FILTER FORM ------------------------------------------------------------
// ---------------------------------------------------------------------------
if (!$reportView):
    $plugKey = isset($_GET['p']) ? 'p' : (isset($_GET['id']) ? 'id' : '');
    $plugVal = $plugKey ? htmlspecialchars($_GET[$plugKey]) : '';
    $modVal  = htmlspecialchars($_GET['mod'] ?? 'reporting');
?>
<div class="menuBox">
  <div class="menuBoxInner reportIcon">
    <div class="per_title"><h2><?= $page_title ?></h2></div>
    <div class="infoBox"><?= __('Report Filter') ?></div>

    <div class="sub_section">
      <form class="form-inline" method="get" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" target="reportView">
        <div class="form-group mr-2">
          <label for="recapBy" class="mr-2"><?= __('Recap By') ?></label>
          <?php
            echo simbio_form_element::selectList(
              'recapBy',
              [
                ['', __('Classification')],
                ['gmd', __('GMD')],
                ['collType', __('Collection Type')],
                ['language', __('Language')],
              ],
              ($_GET['recapBy'] ?? ''),
              'class="form-control" id="recapBy"'
            );
          ?>
        </div>
        <div class="form-group mr-2">
          <label for="location_id" class="mr-2"><?= __('Location') ?></label>
          <?php
            $loc_q = $dbs->query('SELECT location_id, location_name FROM mst_location ORDER BY location_name');
            $opts = [[0, __('ALL Locations')]];
            while ($loc_q && $r = $loc_q->fetch_row()) $opts[] = [$r[0], $r[1]];
            echo simbio_form_element::selectList('location_id', $opts, ($_GET['location_id'] ?? '0'), 'class="form-control" id="location_id"');
          ?>
        </div>
        <input type="submit" name="applyFilter" value="<?= __('Apply Filter') ?>" class="btn btn-primary">
        <input type="hidden" name="reportView" value="true">
        <input type="hidden" name="mod" value="<?= $modVal ?>">
        <?php if ($plugKey): ?><input type="hidden" name="<?= $plugKey ?>" value="<?= $plugVal ?>"><?php endif; ?>
      </form>
    </div>
  </div>
</div>
<iframe name="reportView" id="reportView" src="<?= htmlspecialchars($_SERVER['PHP_SELF']).'?reportView=true&'.currentUrlQuery(); ?>" style="width:100%;height:550px" frameborder="0"></iframe>
<?php
// ---------------------------------------------------------------------------
// 3.  REPORT VIEW ------------------------------------------------------------
else:
  ob_start();

  // -- 3.1 LOCATION FILTER --------------------------------------------------
  $locId  = isset($_GET['location_id']) && $_GET['location_id'] !== '0' ? $dbs->escape_string($_GET['location_id']) : '';
  $locSQL = $locId ? " AND i.location_id = '$locId'" : '';
  $locCap = __('ALL Locations');
  if ($locId) {
      $capQ = $dbs->query("SELECT location_name FROM mst_location WHERE location_id = '$locId'");
      if ($capQ && $capQ->num_rows) $locCap = $capQ->fetch_row()[0];
  }

  // -- 3.2 PREPARE HEADER ---------------------------------------------------
  $baseHeaders = [__('Title'), __('Items')];
  foreach ($statusRows as $st) { $baseHeaders[] = $st[1]; }
  $baseHeaders[] = __('On Loan');

  $rowClass = 'alterCellPrinted';
  $rows     = [];
  $recapby  = __('Classification');

  // first row for XLS header (Recap col handled separately)
  $rows[] = array_merge([$recapby], $baseHeaders);

  // HTML table header
  $output = '<table class="s-table table table-sm table-bordered mb-0"><tr>';
  $output.= '<th id="recap-header">'.$recapby.'</th>';
  foreach ($baseHeaders as $h) { $output .= '<th>'.$h.'</th>'; }
  $output.= '</tr>';

  // -- 3.3 DEFINE GROUP QUERY BUILDER --------------------------------------
  $type   = trim($_GET['recapBy'] ?? '');
  $groups = [];
  switch ($type) {
      case 'gmd':
          $recapby = __('GMD');
          $gq = $dbs->query('SELECT gmd_id, gmd_name FROM mst_gmd ORDER BY gmd_name');
          while ($gq && ($g = $gq->fetch_row())) { $groups[] = [$g[0], $g[1], 'b.gmd_id = '.(int)$g[0]]; }
          break;
      case 'language':
          $recapby = __('Language');
          $lq = $dbs->query('SELECT language_id, language_name FROM mst_language ORDER BY language_name');
          while ($lq && ($l = $lq->fetch_row())) { $groups[] = [$l[0], $l[1], "b.language_id = '".$dbs->escape_string($l[0])."'"]; }
          break;
      case 'collType':
          $recapby = __('Collection Type');
          $cq = $dbs->query('SELECT coll_type_id, coll_type_name FROM mst_coll_type ORDER BY coll_type_name');
          while ($cq && ($c = $cq->fetch_row())) { $groups[] = [$c[0], $c[1], 'i.coll_type_id = '.(int)$c[0]]; }
          break;
      default: // Classification 000‑900 etc.
          for ($i=0;$i<=9;$i++) {
              $groups[] = [$i, $i.'00', "TRIM(b.classification) LIKE '$i%'"]; // ID not used further
          }
          // non‑numeric classes
          $ncq = $dbs->query("SELECT DISTINCT classification FROM biblio WHERE classification REGEXP '^[^0-9]' ORDER BY classification");
          while ($ncq && ($n=$ncq->fetch_row())) { $groups[] = [$n[0], $n[0], "b.classification = '".$dbs->escape_string($n[0])."'"]; }
  }

  // -- 3.4 LOOP GROUPS & BUILD ROWS ----------------------------------------
  foreach ($groups as $gIdx => $ginfo) {
      [$gid, $glabel, $whereGroup] = $ginfo;
      $rowClass = $gIdx % 2 ? 'alterCellPrinted2' : 'alterCellPrinted';

      // Title count
      $title_q = $dbs->query('SELECT COUNT(DISTINCT b.biblio_id) FROM biblio b JOIN item i ON b.biblio_id=i.biblio_id WHERE '.$whereGroup.$locSQL);
      $title_c = $title_q ? $title_q->fetch_row()[0] : 0;

      // Item count (all statuses)
      $item_q  = $dbs->query('SELECT COUNT(i.item_id) FROM item i JOIN biblio b ON i.biblio_id=b.biblio_id WHERE '.$whereGroup.$locSQL);
      $item_c  = $item_q ? $item_q->fetch_row()[0] : 0;

      // Status counts
      $statusCounts = [];
      foreach ($statusRows as $st) {
          $st_q = $dbs->query('SELECT COUNT(i.item_id) FROM item i JOIN biblio b ON i.biblio_id=b.biblio_id WHERE '.$whereGroup.$locSQL.' AND i.item_status_id = \''.$dbs->escape_string($st[0]).'\'' );
          $statusCounts[] = $st_q ? $st_q->fetch_row()[0] : 0;
      }

      // On‑loan count (loan not returned)
      $loan_q = $dbs->query('SELECT COUNT(l.item_code) FROM loan l JOIN item i ON l.item_code = i.item_code JOIN biblio b ON i.biblio_id=b.biblio_id WHERE l.is_lent = 1 AND l.is_return = 0 AND '.$whereGroup.$locSQL);
      $loan_c = $loan_q ? $loan_q->fetch_row()[0] : 0;

      // assemble row array and HTML
      $rowArr  = array_merge([$glabel, $title_c, $item_c], $statusCounts, [$loan_c]);
      $rows[]  = $rowArr;

      $output .= '<tr><td class="'.$rowClass.'">'.$glabel.'</td>';
      foreach (array_slice($rowArr, 1) as $idx => $val) {
          $output .= '<td class="'.$rowClass.' text-center">'.$val.'</td>';
      }
      $output .= '</tr>';
  }

  $output .= '</table>';

  // -- 3.5 ACTION BAR -------------------------------------------------------
  $xlsUrl = (defined('MWB') ? MWB : SWB.'admin/modules/') . 'reporting/xlsoutput.php';
  echo '<div class="mb-2">'.__('Title and Collection Recap by').' <strong>'.htmlspecialchars($recapby).'</strong>';
  if ($locId) echo ' '.__('at Location').': <strong>'.htmlspecialchars($locCap).'</strong>';
  echo ' <a href="#" class="s-btn btn btn-default printReport" onclick="window.print()">'.__('Print Current Page').'</a>';
  echo ' <a href="'.$xlsUrl.'" class="s-btn btn btn-default" target="_blank">'.__('Export to spreadsheet format').'</a></div>';

  echo '<script>jQuery("#recap-header").text("'.addslashes($recapby).'");</script>';
  echo $output;

  // -- 3.6 XLS SESSION PREP -------------------------------------------------
  $_SESSION['xlsdata'] = $rows;
  $_SESSION['tblout']  = 'recap_'.strtolower(str_replace(' ','_',$recapby)).($locId?('_loc_'.$locId):'');

  $content = ob_get_clean();
  require SB.'admin/'.$sysconf['admin_template']['dir'].'/printed_page_tpl.php';
endif; ?>
