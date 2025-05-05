# Recap Plus Lokasi Report (Plugin Edition)

## Overview

This SLiMS plugin extends the built-in reporting module to provide:

- **Dynamic recap columns** for every entry in `mst_item_status` (e.g. Rusak, Hilang, Booked)
- An **“On Loan”** column that automatically counts items currently lent out and not yet returned.  
- A **Location filter** to restrict the report to one or all library locations.

## Features

- **Automatic status discovery**: Reads all item statuses from `mst_item_status` and builds headers and totals dynamically.
- **On-Loan tracking**: Counts active loans (not returned) per group.
- **Flexible grouping**: Recap by Classification, GMD, Collection Type, or Language.
- **Location-based filtering**: Select a specific location or view all.
- **Export & Print**: Print current view or export to XLS for offline analysis.

## Installation

1. **Copy plugin files**  
   - Extract or copy the entire plugin folder (e.g. `rekap-plus-lokasi/`) into your SLiMS installation’s `plugins/` directory:  

2. **Enable the plugin**  
   - Log in to your SLiMS admin panel.  
   - Navigate to **System → Plugins**.  
   - Locate **Rekap Plus Lokasi** in the list and click **Enable** 

3. **Use the report**  
   - Once enabled, the “Rekap Plus Lokasi” menu will appear under **Reporting**.  
   - Follow the **Usage** instructions to filter by location, group by status, print, or export.



With these steps, your plugin will be installed and ready to use in any SLiMS 9.x (“Bulian”) installation.


## Usage

1. Navigate to **Reporting → Rekap Plus Lokasi** in your SLiMS admin panel.  
2. Use the **Recap By** dropdown to choose grouping (e.g. Classification, GMD, Collection Type, Language).  
3. Use the **Location** dropdown to select either _All Locations_ or a specific branch.  
4. Click **Apply Filter** to reload the report iframe with your settings.  
5. To print, click **Print Current Page**; to download, click **Export to spreadsheet format**.

## Disclaimer

This SLiMS plugin is **experimental** and provided “as is.”  
Use at your own risk—no warranties, express or implied.  
The author (Ade Ismail Siregar) is not liable for any damage or data loss.  


© May 2025 Ade Ismail Siregar  
