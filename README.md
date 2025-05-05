# 📊 Recap Plus Lokasi Report (Plugin Edition)

![Plugin Screenshot](https://github.com/user-attachments/assets/7e6894e3-2f26-4b87-8f92-561efa4f2a18)

## 🔍 Overview

Plugin ini memperluas modul reporting SLiMS untuk menyediakan:

- 🔄 **Dynamic recap columns**  
  Kolom rekap otomatis untuk setiap status di `mst_item_status` (misalnya: Rusak, Hilang).

- 📚 **On-Loan tracking**  
  Kolom **“On Loan”** yang menghitung item yang sedang dipinjam dan belum kembali.

- 📍 **Location filter**  
  Filter lokasi untuk menampilkan laporan satu cabang atau _All Locations_.

## ✨ Fitur Utama

- ⚙️ **Automatic status discovery**  
  Membaca semua status di `mst_item_status` dan membuat header + total secara dinamis.

- 📈 **On-Loan tracking**  
  Menghitung jumlah pinjaman aktif (belum dikembalikan) per grup.

- 🗂️ **Flexible grouping**  
  Rekap berdasarkan:
  - Clas­si­fi­ca­tion  
  - GMD  
  - Collection Type  
  - Language

- 📍 **Location-based filtering**  
  Pilih salah satu cabang atau tampilkan semua lokasi.

- 🖨️ **Export & Print**  
  - 🖨️ Cetak tampilan saat ini  
  - 📥 Export ke XLS untuk analisis offline

## 🛠️ Installation

1. 📂 **Copy plugin files**  
   Salin seluruh folder plugin (`rekap-plus-lokasi/`) ke `plugins/` SLiMS Anda.

2. ✅ **Enable the plugin**  
   - Login ke admin panel  
   - **System → Plugins**  
   - Aktifkan **Rekap Plus Lokasi**

3. 🔍 **Use the report**  
   - Menu “Rekap Plus Lokasi” muncul di bawah **Reporting**

## 🚀 Usage

1. Akses **Reporting → Rekap Plus Lokasi**  
2. Pilih **Recap By** (🗂️ Classification, GMD, Collection Type, Language)  
3. Pilih **Location** (📍 All Locations atau cabang tertentu)  
4. Klik **Apply Filter** untuk memuat ulang laporan  
5. 🖨️ **Print Current Page** atau 📥 **Export to spreadsheet**

---

> ⚠️ **Disclaimer**  
> Plugin ini **eksperimental** dan diberikan “as is” tanpa jaminan apa pun.  
> Gunakan dengan risiko Anda sendiri.

© May 2025 Ade Ismail Siregar  
