# 📊 Recap Plus Lokasi Report (Plugin Edition)

> ⚠️ **Disclaimer**  
> JANGAN langsung pasang DI SLiMS Operasional (tes di PC/SLiMS lain). Gunakan dengan risiko Anda sendiri.

## 🎨 Cuplikan Layar (Mock‑up)

![image](https://github.com/user-attachments/assets/401b2951-39b5-45fa-a160-84d840036264)

---

# Plugin **Rekap plus Lokasi** untuk SLiMS

> **Versi 2025‑05‑07** — membutuhkan SLiMS ≥ 9.4. (Bulian)

Plugin ini memperluas modul *reporting* SLiMS dengan **kolom rekap dinamis, filter lokasi, serta filter rentang tanggal yang fleksibel**. Cocok untuk pertanyaan seperti:

* *“Berapa judul dan eksemplar yang **diakuisisi** pada Maret 2025 di Perpustakaan Fakultas Hukum?”*
* *“Berapa koleksi yang rusak atau hilang selama tahun 2024?”*
* *“Tampilkan rekap klasifikasi **tahun berjalan** untuk semua lokasi.”*

---

## ✨ Fitur Utama

| Kategori                   | Penjelasan                                                                                                                                      |
| -------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------- |
| **Rekap dinamis**          | Membaca seluruh entri di `mst_item_status` (mis. Rusak, Hilang, Booked) dan otomatis membuat header serta totalnya.                             |
| **Kolom On‑Loan**          | Menghitung peminjaman aktif (`loan.is_lent = 1` dan `is_return = 0`) di dalam rentang tanggal yang sama.                                        |
| **Filter lokasi**          | Membatasi laporan pada satu lokasi perpustakaan atau menampilkan semuanya.                                                                      |
| **Filter rentang tanggal** | *Date‑picker* **Start + End** mendukung periode bulanan, tahunan, YTD, ataupun kustom.                                                          |
| **Nilai baku cerdas**      | *Start Date* = tanggal `item.input_date` tertua (atau `0000‑00‑00` jika kosong) → menjamin eksemplar hasil impor tanpa tanggal tetap terhitung. |
| **Rekap berdasarkan**      | Klasifikasi (000‑900 + kode non‑DDC), GMD, Tipe Koleksi, atau Bahasa.                                                                           |
| **Cetak & Ekspor**         | Tombol *Print Current Page* dan ekspor XLS (memakai `xlsoutput.php` bawaan).                                                                    |
| **Tanpa modifikasi core**  | Seluruh logika berada di folder plugin—upgrade SLiMS tetap aman.                                                                                |

---

## 🔍 Mengapa memakai `item.input_date` dan *fallback* `0000‑00‑00`?

* **Tanggal akuisisi riil.** `item.input_date` dicatat saat eksemplar fisik (barcode) ditambahkan—lebih relevan untuk laporan **perolehan** dibanding `biblio.input_date` (entri katalog).
* **Data hasil migrasi/impor.** Banyak perpustakaan melakukan *bulk insert* ke tabel `item` via SQL/CSV. Jika kolom `input_date` tidak diisi, MySQL menyimpannya sebagai `NULL` atau `0000‑00‑00`. Tanpa *fallback* ini, eksemplar tersebut akan hilang dari laporan yang difilter tanggal!
* **Logika baku.** Plugin menjalankan `SELECT MIN(DATE(input_date)) FROM item`. Jika hasilnya `NULL` (tabel kosong) → *Start Date* default = `0000‑00‑00`, sehingga **tidak ada batas bawah** dan seluruh data tetap terlihat.

---

## 📑 Definisi Kolom & Logika Filter Tanggal

| Kolom                                      | Isi yang ditampilkan                                                                                                                                                                                          | Dasar SQL (ikut filter **Start–End**)                  |
| ------------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------ |
| **\[Rekap Berdasarkan]**                   | Label dimensi pengelompokan (kode Klasifikasi, nama GMD, nama Tipe Koleksi, atau kode Bahasa).                                                                                                                | –                                                      |
| **Judul**                                  | `COUNT(DISTINCT biblio_id)` — jumlah *judul unik* yang memiliki **≥1** eksemplar dengan `DATE(item.input_date)` berada di antara *Start* dan *End*.                                                           | `... WHERE DATE(i.input_date) BETWEEN :start AND :end` |
| **Eksemplar**                              | `COUNT(item_id)` — total barcode yang **diakuisisi** selama periode tersebut.                                                                                                                                 | klausa tanggal yang sama                               |
| **Setiap Status**<br>(Rusak, Hilang, dst.) | Eksemplar yang **saat ini** berstatus tersebut **dan** tanggal *akuisisi*‑nya berada dalam periode. Buku dibeli 2023 lalu baru ditandai *Rusak* pada 2025 **tidak** terhitung jika Anda membuat laporan 2025. | tambah `AND i.item_status_id = :status`                |
| **On Loan**                                | Eksemplar yang *sedang* dipinjam (`is_lent = 1 ∧ is_return = 0`) **dan** `DATE(loan.loan_date)` berada di periode. Peminjaman yang dimulai sebelum *Start* tidak dihitung walau belum kembali.                | `... WHERE DATE(l.loan_date) BETWEEN :start AND :end`  |

### Contoh: Maret 2025

* **Start = 2025‑03‑01**, **End = 2025‑03‑31**

  * **Judul** menghitung setiap judul baru yang memperoleh ≥1 eksemplar pada Maret.
  * **Eksemplar** menampilkan total barcode yang ditambahkan pada Maret.
  * **Hilang** menghitung eksemplar yang **diakuisisi** Maret dan kini berstatus *Hilang*.
  * **On Loan** menghitung peminjaman **dibuka** Maret 2025 dan masih belum dikembalikan saat laporan dibuat.

> **Butuh menghitung status berdasarkan *kapan* status berubah (bukan kapan diakuisisi)?**  Ganti `DATE(i.input_date)` dengan `DATE(i.last_update)` (atau tabel log terpisah) di tiga query terkait eksemplar. Logika lain tetap sama.

---

## 🚀 Instalasi 
https://github.com/adeism/belajarslims/blob/main/belajar-pasang-plugin.md

---

## 📊 Panduan Pemakaian

1. **Pilih periode**
   Isikan *Start/End date* di bagian atas. Tombol *Bulan Ini* & *Tahun Ini* tersedia sebagai pintasan.
2. **Pilih “Rekap Berdasarkan”**
   Klasifikasi, GMD, Tipe Koleksi, atau Bahasa.
3. **Pilih lokasi** (opsional)
   *Semua Lokasi* adalah bawaan.
4. **Apply Filter** → laporan tampil dalam iframe.
5. **Cetak / Ekspor** sesuai kebutuhan.

> Contoh periode “Jan–Mar 2024”: **Start = 2024‑01‑01**, **End = 2024‑03‑31**.

---

## ⚡️ Tips Performa  (opsional)

* `item.input_date`, `loan.loan_date`, dan `item.item_status_id` sudah ter‑index di instalasi SLiMS standar.
* Untuk perpustakaan sangat besar (>1 juta baris) tambahkan misalnya:

  ```sql
  ALTER TABLE item  ADD INDEX idx_input_date (input_date);
  ALTER TABLE loan  ADD INDEX idx_loan_date  (loan_date);
  ```
* Pertimbangkan menyimpan cache laporan setiap malam jika hanya perlu update harian.

---

## 🗒 Riwayat Perubahan

| Tanggal    | Versi | Catatan                                                                                   |
| ---------- | ----- | ----------------------------------------------------------------------------------------- |
| 2025‑05‑07 | 1.0.0 | Rilis publik perdana: kolom status dinamis, filter lokasi, *date‑picker* rentang tanggal. |

---

© May 2025 Ade Ismail Siregar  
