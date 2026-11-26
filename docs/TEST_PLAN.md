# Rencana Pengujian & Skenario Uji Black-Box (Tahap 4 Waterfall)
## LokaKos: Sistem Informasi Pemesanan Kos Multi-Mitra (Kawasan UMY)

> Versi Dokumen: **3.1** (Diperbarui dengan 35 Kasus Uji Fungsional + 4 Checklist UI dan status verifikasi otomatis `php artisan test`: **46 passed, 265 assertions** pada 22 November 2026).

Dokumen ini menjadi acuan **Anggota 3 (QA)** bersama tim saat melaksanakan pengujian fungsional (Black-Box Testing), pengujian keamanan akses multi-mitra, dan pengujian antarmuka responsif (Light/Dark Mode).

---

## 1. Tujuan & Lingkungan Pengujian
- **Metode Uji:** Black-Box Testing (menguji input, proses, validasi, dan output dari sisi pengguna) dilengkapi suite pengujian otomatis `php artisan test` (SQLite `:memory:`) dan klik langsung di browser pada database MySQL `lokakos_db`.
- **Lingkungan Uji:** Laravel 10.50.4, PHP 8.3.30, MySQL 8.4.3 (Laragon), browser desktop (`1440x900`) dan simulasi mobile (`375x812`).
- **Kriteria Lulus:** Seluruh kasus uji berstatus `PASS` tanpa HTTP 500, tanpa console error JavaScript, dan tanpa kebocoran akses antar role atau antar mitra.

---

## 2. Daftar Skenario Uji Black-Box (29 Kasus Uji)

### A. Modul Autentikasi, Role, & Keamanan Multi-Mitra

| ID | Ref | Skenario & Input Uji | Hasil yang Diharapkan | Tipe | Status |
| :--- | :--- | :--- | :--- | :--- | :---: |
| **TC-01** | FR-01 | Pengunjung mengisi form Registrasi Penyewa (`/register`) dengan data valid | Akun tersimpan dengan `role = penyewa`, otomatis login, diarahkan ke `/dashboard` | Positif | `[x] PASS` |
| **TC-02** | FR-01 | Pengunjung mendaftar dengan email yang sudah terdaftar | Sistem menolak dan menampilkan pesan validasi email sudah digunakan | Negatif | `[x] PASS` |
| **TC-03** | FR-01 | Calon Mitra mengisi form Registrasi Mitra (`/register/mitra`) lengkap (nama usaha, rekening, unggah foto KTP JPG < 2 MB) | Akun tersimpan dengan `role = mitra` dan `mitra_profiles.verification_status = pending`; belum dapat menayangkan kos | Positif | `[x] PASS` |
| **TC-04** | FR-02 | Login dengan akun `admin`, `mitra`, dan `penyewa` secara bergantian | Seluruh peran diarahkan ke `/dashboard` dan `DashboardController` merender workspace sesuai peran masing-masing | Positif | `[x] PASS` |
| **TC-05** | FR-04 | Pengguna login sebagai `penyewa` atau `mitra` lalu mengakses langsung URL `/admin/mitra` atau `/admin/kos` | `RoleMiddleware` menolak akses dengan respons HTTP 403 Forbidden | Negatif | `[x] PASS` |
| **TC-06** | FR-04 | Mitra A (`mitra3@lokakos.test`) mengakses `/mitra/kos/1/edit`, `/mitra/kos/1/kamar/1/edit`, `PATCH .../stock`, atau verifikasi pesanan milik Mitra B | Laravel Policy (`BoardingHousePolicy` & `BookingPolicy`) menolak seluruh permintaan dengan HTTP 403 Forbidden | Negatif | `[x] PASS` |

### B. Modul Administrator (Verifikasi, Moderasi, Unpublish, Master Data)

| ID | Ref | Skenario & Input Uji | Hasil yang Diharapkan | Tipe | Status |
| :--- | :--- | :--- | :--- | :--- | :---: |
| **TC-07** | FR-19 | Admin menyetujui pendaftaran Mitra berstatus `pending` di `/admin/mitra` | Status berubah menjadi `approved`, `verified_by` dan `verified_at` terisi, Mitra dapat mengakses menu Tambah Kos | Positif | `[x] PASS` |
| **TC-08** | FR-19 | Admin menolak pendaftaran Mitra dengan mengisi catatan penolakan | Status berubah menjadi `rejected`, catatan tersimpan dan tampil pada halaman profil/dashboard Mitra | Positif | `[x] PASS` |
| **TC-09** | FR-20 | Mitra membuat kos baru (`pending_review`), lalu Pengunjung mencarinya di `/kos` sebelum disetujui Admin | Kos tidak tampil pada katalog publik maupun URL detail publik | Negatif | `[x] PASS` |
| **TC-10** | FR-20 | Admin menyetujui kos berstatus `pending_review` menjadi `published` di `/admin/kos` | Kos langsung tampil pada katalog publik dan hasil pencarian | Positif | `[x] PASS` |
| **TC-11** | FR-21, FR-22 | Admin menambah Area baru dan Fasilitas baru di `/admin/master` | Data tersimpan di `areas`/`facilities` dan langsung muncul sebagai pilihan di form Mitra dan filter katalog | Positif | `[x] PASS` |
| **TC-12** | FR-24 | Admin memilih rentang tanggal dan menekan tombol Unduh Laporan Transaksi PDF (`/admin/laporan/pdf`) | Browser mengunduh file PDF berisi rekap transaksi terkonfirmasi sesuai rentang tanggal | Positif | `[x] PASS` |
| **TC-28** | FR-20, FR-21 | Admin menekan **Tarik Sementara (Draf)** (`unpublish`) pada kos tayang di `/admin/kos`, lalu mengedit nama/deskripsi Area secara inline di `/admin/master` | Status kos kembali ke `draft` dan hilang dari katalog publik; perubahan Area tersimpan ke tabel `areas` | Positif | `[x] PASS` |

### C. Modul Mitra Pemilik Kos (Onboarding, Kos, Edit Kamar, Stok, & Verifikasi)

| ID | Ref | Skenario & Input Uji | Hasil yang Diharapkan | Tipe | Status |
| :--- | :--- | :--- | :--- | :--- | :---: |
| **TC-13** | FR-14 | Mitra menambah kos dengan pin peta pada koordinat Ngebel (`-7.8145, 110.3190`) | Sistem menghitung dan menyimpan `distance_to_umy_km` otomatis berdasarkan rumus Haversine ke koordinat UMY | Positif | `[x] PASS` |
| **TC-14** | FR-15 | Mitra menambahkan tipe kamar dengan `total_rooms = 4` dan `available_rooms = 6` (melebihi total) | Validasi menolak input dan menampilkan pesan bahwa kamar tersedia tidak boleh melebihi total kamar | Negatif | `[x] PASS` |
| **TC-15** | FR-17, NFR-02 | Mitra menyetujui bukti pembayaran valid pada pesanan untuk tipe kamar berstok `3` | Status payment = `verified`, booking = `confirmed`, `available_rooms` berkurang menjadi `2` secara transaksional | Positif | `[x] PASS` |
| **TC-16** | FR-17, NFR-02 | Mitra mencoba menyetujui bukti pembayaran ketika `available_rooms` kamar tersebut sudah `0` | Transaksi dibatalkan (`rollback`), stok tidak menjadi negatif, dan muncul peringatan stok habis | Negatif | `[x] PASS` |
| **TC-17** | FR-17 | Mitra menolak bukti pembayaran yang buram dan mengisi `rejection_note` | Status payment & booking = `rejected`, alasan tampil di sisi Penyewa, dan stok kamar tidak berkurang | Positif | `[x] PASS` |
| **TC-18** | FR-18 | Mitra menandai pesanan `confirmed` menjadi `completed` (check-in) lalu mencetak Laporan Pemasukan PDF (`/mitra/laporan/pdf`) | Status booking = `completed`, fitur ulasan penyewa terbuka, dan PDF pemasukan khusus milik Mitra terunduh | Positif | `[x] PASS` |
| **TC-25** | FR-13 | Mitra `approved` yang baru memiliki `0` kos (`mitra3@lokakos.test`) membuka `/dashboard` | Sidebar hanya menampilkan menu relevan (`Tambah Kos Pertama`, `Profil & Rekening Bank`) dan layar utama menampilkan panduan onboarding | Positif | `[x] PASS` |
| **TC-26** | FR-15 | Mitra mengedit tipe kamar (`/mitra/kos/{id}/kamar/{roomId}/edit`) mengisi `deposit_amount`, `electricity_included`, `max_occupancy`, dan unggah foto kamar, lalu menekan kontrol stok cepat `-1`/`+1` | Seluruh kolom baru tersimpan pada tabel `room_types`, foto tersimpan di storage, dan stok berubah aman dalam batas `0..total_rooms` | Positif | `[x] PASS` |

### D. Modul Publik & Penyewa (Katalog, Favorit, Booking, Transfer, Ulasan)

| ID | Ref | Skenario & Input Uji | Hasil yang Diharapkan | Tipe | Status |
| :--- | :--- | :--- | :--- | :--- | :---: |
| **TC-19** | FR-06 | Pengunjung memfilter katalog: Area = `Ngebel`, Tipe = `Putri`, checkbox `Syariah` aktif, urutkan `Terdekat ke UMY` | Hanya kos `published` yang memenuhi seluruh kriteria yang tampil, berurutan naik berdasarkan `distance_to_umy_km` | Positif | `[x] PASS` |
| **TC-20** | FR-08 | Penyewa mencoba memesan tipe kamar yang `available_rooms = 0` (via UI maupun manipulasi POST) | Tombol pesan nonaktif di UI dan server menolak permintaan POST dengan pesan stok habis | Negatif | `[x] PASS` |
| **TC-21** | FR-08, FR-09 | Penyewa memilih tipe kamar tersedia, tanggal masuk valid, durasi `6 bulan`, lalu submit ke `POST /penyewa/pesanan` | Pesanan tersimpan dengan kode unik, `total_price` dihitung benar, dan halaman menampilkan nomor rekening Mitra tujuan | Positif | `[x] PASS` |
| **TC-22** | FR-10 | Penyewa mengunggah file `.exe` atau file gambar 5 MB pada form bukti transfer | Validasi menolak file dengan pesan format (JPG/PNG/PDF) dan batas ukuran (2 MB) yang jelas | Negatif | `[x] PASS` |
| **TC-23** | FR-10, FR-11 | Penyewa mengunggah bukti transfer valid (PNG < 2 MB), lalu setelah disetujui Mitra menekan `Unduh Invoice PDF` | Status naik ke `waiting_verification`; setelah `confirmed`, file Invoice PDF dengan kode booking berhasil terunduh | Positif | `[x] PASS` |
| **TC-24** | FR-12 | Penyewa mencoba mengirim ulasan sebelum status `completed`, lalu mengirim ulasan setelah `completed`, lalu mencoba mengirim ulasan kedua | Percobaan sebelum `completed` ditolak; ulasan pertama tersimpan dan muncul di detail kos; ulasan kedua ditolak | Negatif & Positif | `[x] PASS` |
| **TC-27** | FR-07, FR-25 | Penyewa menekan **Simpan Kos** pada `/kos/{slug}`, mengecek `/penyewa/favorit` dan dashboard, menekan ulang untuk menghapus, serta mengecek tautan **Tanya via WhatsApp** | Baris tersimpan unik di `saved_boarding_houses` lalu terhapus saat toggle kedua; tautan WhatsApp berisi nomor pemilik kos | Positif | `[x] PASS` |
| **TC-29** | FR-07, FR-14 | Mitra mengisi kontak khusus kos (`contact_name`, `contact_whatsapp`, `contact_phone`, `contact_hours`, `whatsapp_message_template`) di edit kos lalu membandingkan link WA pada 3 kos berbeda dan tombol **Tanya Tipe Ini via WA** | Nomor WA ternormalisasi ke `62...`, tiap kos menghasilkan pesan otomatis berbeda dan personal, template kustom mendukung token `{pengelola}`/`{nama_kos}`, dan tombol per-kamar menyertakan nama & harga kamar | Positif | `[x] PASS` |
| **TC-30** | FR-26 | Pengunjung membuka Peta Katalog UMY (`/kos`), memilih 2 kos untuk `/kos/bandingkan`, serta memeriksa estimasi waktu jalan kaki/motor & kalkulator biaya awal + patungan | Peta memplot seluruh kos tayang + UMY, tabel perbandingan menampilkan 2 kos berdampingan, dan simulasi biaya awal/patungan menghitung akurat | Positif | `[x] PASS` |
| **TC-31** | FR-26, FR-27 | Penyewa mengajukan survei lokasi, bertanya di Q&A publik, dan mendaftar tunggu kamar penuh (`available_rooms = 0`); Mitra lain mencoba mengubah -> ditolak 403; Mitra pemilik menyetujui survei, menjawab Q&A, dan menaikkan stok kamar menjadi 1 | Survei & Q&A terisolasi per mitra, waitlist berubah `notified` dan notifikasi in-app otomatis terkirim ke penyewa | Positif & Negatif | `[x] PASS` |
| **TC-32** | FR-26 | Penyewa memesan 6 bulan memakai kode promo `MABAUMY26` + unggah KTM, membayar & dikonfirmasi Mitra, lalu mengunduh Invoice PDF ber-QR, membuka URL verifikasi QR, mengunduh Surat Perjanjian PDF, melapor keluhan kamar, mengajukan perpanjangan sewa, dan perintah `lokakos:expire-bookings` dijalankan pada booking lewat 24 jam | Diskon Rp 150.000 terpotong, QR verifikasi valid, PDF kontrak & invoice terunduh, keluhan tertangani, dan booking kadaluarsa dibatalkan otomatis | Positif | `[x] PASS` |
| **TC-33** | FR-27 | Mitra menambahkan nomor kamar fisik (`B-09`), membuat promo `HEMAT10`, mencatat pengeluaran operasional di Buku Kas, mengunduh Poster QR PDF, dan mengekspor CSV | Seluruh data tersimpan ke `room_units`, `promos`, `mitra_expenses`; Poster QR PDF dan CSV berhasil terunduh | Positif | `[x] PASS` |
| **TC-34** | FR-28 | Pengunjung mengirim laporan ketidaksesuaian listing; Admin memberi/mencabut Verifikasi Lapangan, menangani laporan + tarik kos ke Draf, melihat Audit Log, ekspor CSV platform, dan menandai notifikasi dibaca | Laporan teratasi, kos kembali ke `draft`, `activity_logs` mencatat `report.resolved`, CSV platform dan Pusat Notifikasi berjalan tanpa error | Positif | `[x] PASS` |
| **TC-35** | FR-14 | Mitra menempelkan link Google Maps (`maps.app.goo.gl`, URL penuh `!3d/!4d`, koordinat DMS) di form kos lalu menekan **Terapkan ke Peta** dan menyimpan kos | Pin Leaflet langsung berpindah akurat ke koordinat Google Maps, `distance_to_umy_km` terhitung otomatis, dan tombol **Buka di Google Maps** tampil di detail kos publik | Positif | `[x] PASS` |

---

## 3. Checklist Pengujian UI, Responsif, & Tema (Anti-Slop Gate)

| ID | Pengujian | Kriteria Lulus | Status |
| :--- | :--- | :--- | :---: |
| **UI-01** | Toggle Light / Dark Mode | Kedua mode bekerja di seluruh halaman publik, Penyewa, Mitra, dan Admin; tidak ada teks pudar atau komponen rusak saat beralih tema. | `[x] PASS` |
| **UI-02** | Responsif Mobile (`375px`) & Desktop (`1440px`) | Tidak ada horizontal scroll (`scrollWidth === innerWidth`), sidebar workspace dan filter katalog berubah menjadi drawer di mobile, semua tombol memiliki area sentuh memadai. | `[x] PASS` |
| **UI-03** | Navigasi Keyboard | Seluruh tombol, input, dan dropdown dapat diakses dengan `Tab`/`Enter`/`Space`, drawer ditutup dengan `Escape`, focus ring terlihat jelas. | `[x] PASS` |
| **UI-04** | Empty, Loading, & Error State | Filter tanpa hasil, daftar favorit kosong, serta Mitra `0` kos menampilkan empty state / onboarding terarah; kesalahan form menampilkan pesan spesifik di bawah field terkait. | `[x] PASS` |
