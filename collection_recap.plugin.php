<?php
/**
 * Plugin Name: Rekap plus Lokasi
 * Plugin URI: https://github.com/adeism
 * Description: Menampilkan laporan rekapitulasi koleksi dengan tambahan filter lokasi dan kolom status eksemplar.
 * Version: 1.0.0
 * Author: Ade Ismail Siregar
 * Author URI: https://github.com/adeism
 */

// Mencegah akses langsung
defined('INDEX_AUTH') or die('Direct access is not allowed!');

// Mendapatkan instance Plugins
$plugin = \SLiMS\Plugins::getInstance();

// Mendaftarkan menu plugin di bawah modul Reporting
$plugin->registerMenu('reporting', __('Rekap plus Lokasi'), __DIR__ . '/index.php');
