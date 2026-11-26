# Panduan Kerja Tim (3 Orang), Git Workflow, & Setup Lokal
## Proyek LokaKos (Laravel 10 + PHP 8.3 + MySQL 8.4)

> Versi Dokumen: **3.0** (Sinkron dengan **24 tabel domain / 20 migrasi** `lokakos_db`, `chillerlan/php-qrcode`, 6 akun demo `DemoUserSeeder`, dan **45 test otomatis** Laravel).

---

## 1. Pembagian Tanggung Jawab Tim

| Anggota | Peran | Dokumen Waterfall | Modul Laravel 10 |
| :--- | :--- | :--- | :--- |
| **Anggota 1** | Lead & Modul Admin | Analisis Kebutuhan, Use Case Diagram, ERD & Kamus Data (13 Tabel Domain), PRD, Review Laporan Akhir | Setup Laravel 10, Breeze Auth, Middleware Role, Policy Multi-Mitra (`BoardingHousePolicy`, `BookingPolicy`), 13 Migrasi & Seeder, App Shell Sidebar Espresso + Toggle Tema, Dashboard Admin, Verifikasi Mitra, Moderasi & Unpublish Kos, Master Area (termasuk edit inline) & Fasilitas, Monitoring & Laporan Admin PDF |
| **Anggota 2** | Modul Mitra Pemilik Kos | Activity Diagram (4 alur), Class Diagram, Design Brief, Wireframe Panel Mitra | Registrasi & Profil Mitra (rekening & KTP), Onboarding Mitra Baru (`0` kos) & Dashboard Operasional 4 Metrik, CRUD Kos + Multi-Upload Foto + Peta Leaflet (Haversine), CRUD & Edit Penuh Tipe Kamar (deposit, listrik, kapasitas, foto kamar) + Kontrol Stok Cepat `-`/`+`, Verifikasi Bukti Transfer Transaksional, Laporan Pemasukan Mitra PDF |
| **Anggota 3** | Modul Publik, Penyewa, & QA | Sequence Diagram (3 alur), Wireframe Publik/Penyewa, Rencana & Eksekusi Skenario Uji Black-Box (`docs/TEST_PLAN.md`) | Beranda Rivora x Roomora, Katalog Split-View + Filter UMY + Mobile Drawer, Detail Kos + Peta Leaflet + Simpan Kos Favorit (`saved_boarding_houses`) + WhatsApp, Dashboard Penyewa (Pelacak 4 Tahap & Favorit), Form Pemesanan & Unggah Bukti Transfer, Invoice PDF, Rating & Ulasan, Pengujian Black-Box & UI Light/Dark |

---

## 2. Alur Git Branching (Minim Konflik)

1. **`main`**: Branch stabil yang selalu siap demo. Jangan commit fitur setengah jadi langsung ke `main`.
2. **Branch fitur per anggota:**
   - Anggota 1: `feat/admin-auth`
   - Anggota 2: `feat/mitra-module`
   - Anggota 3: `feat/public-penyewa`
3. **Aturan kerja harian:**
   - Sebelum mulai koding setiap hari, tarik pembaruan terbaru: `git pull origin main`.
   - Jangan mengubah struktur file migration lama yang sudah di-merge ke `main`. Jika butuh kolom atau tabel baru, buat file migration tambahan (seperti contoh `000012_add_mamikos_details_to_room_types_table.php` dan `000013_create_saved_boarding_houses_table.php`).
   - Pastikan `php artisan test` lulus 100% sebelum membuat Pull Request ke `main`.
4. **Sinkronisasi dua folder mata kuliah (`WEBPROG` dan `SIM`):**
   - Kedua folder (`d:\Tugas-Kuliah\WEBPROG` dan `d:\Tugas-Kuliah\SIM`) menyimpan proyek Laravel 10, hasil build frontend, dan dokumen Waterfall terbaru secara identik serta terhubung ke database MySQL `lokakos_db`.
   - Disarankan menjadikan satu remote GitHub/GitLab yang sama agar setelah push dari `WEBPROG`, cukup jalankan `git pull` di folder `SIM`.

---

## 3. Panduan Setup Lokal (Laragon di Windows)

### Prasyarat
- PHP **8.3.x** (Laragon)
- Composer **2.x**
- Node.js **20+** & npm
- MySQL **8.x** (jalankan via tombol **Start All** di Laragon)

### Langkah Menjalankan Aplikasi
1. Buka Laragon lalu klik **Start All** (pastikan layanan MySQL aktif di port `3306`).
2. Buat database MySQL bernama `lokakos_db` (lewat HeidiSQL/phpMyAdmin atau terminal MySQL: `CREATE DATABASE lokakos_db;`).
3. Pastikan file `.env` sudah mengarah ke database tersebut:
   ```env
   APP_NAME="LokaKos"
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=lokakos_db
   DB_USERNAME=root
   DB_PASSWORD=
   ```
4. Jalankan seluruh migrasi database (4 tabel bawaan Laravel + 20 migrasi domain LokaKos = 24 tabel domain), seeder data kawasan UMY & demo, serta symlink storage:
   ```bash
   php artisan migrate --seed
   php artisan storage:link
   ```
5. Jalankan build frontend dan verifikasi suite test otomatis:
   ```bash
   npm run build
   php artisan test
   ```
6. Jalankan server lokal dan buka `http://127.0.0.1:8000`:
   ```bash
   php artisan serve
   ```

---

## 4. Akun Demo & Data Seeder (Khusus Pengujian Lokal)

Setelah menjalankan `php artisan migrate --seed`, `DatabaseSeeder` mengisi:
- **8 Area Kawasan UMY** (`AreaSeeder`): Ngebel, Brajan, Geblagan, Tamantirto, Rukeman, Kasihan, Ambarketawang, Ringroad Barat.
- **16 Master Fasilitas** (`FacilitySeeder`): kombinasi fasilitas umum kos (`kos`) dan fasilitas dalam kamar (`kamar`).
- **7 Properti Kos Demo** (`DemoBoardingHouseSeeder`): 6 kos berstatus `published` dan 1 kos berstatus `pending_review` (untuk menguji antrean moderasi Admin), seluruhnya dimiliki oleh `mitra1@lokakos.test`.
- **6 Akun Uji Lokal** (`DemoUserSeeder`, password seluruh akun: `password`):

| Peran | Email | Nama & Status | Kegunaan Uji |
| :--- | :--- | :--- | :--- |
| **Admin** | `admin@lokakos.test` | Admin LokaKos | Verifikasi mitra, moderasi & unpublish kos, master data, monitoring, PDF |
| **Mitra 1 (Aktif)** | `mitra1@lokakos.test` | Pengelola Kos Demo 1 (`approved`, 7 kos) | Dashboard operasional, kelola/edit kamar, kontrol stok `-`/`+`, verifikasi bayar |
| **Mitra 2 (Pending)** | `mitra2@lokakos.test` | Pengelola Kos Demo 2 (`pending`, 0 kos) | Antrean verifikasi Mitra di Admin & tampilan menunggu persetujuan |
| **Mitra 3 (Baru)** | `mitra3@lokakos.test` | Pengelola Kos Demo 3 (`approved`, 0 kos) | Tampilan onboarding Mitra baru & uji blokir HTTP 403 ke kos/kamar milik Mitra 1 |
| **Penyewa 1** | `mahasiswa@lokakos.test` | Mahasiswa Uji UMY (`penyewa`) | Simpan kos favorit, booking kamar, unggah bukti transfer, invoice PDF, ulasan |
| **Penyewa 2** | `penyewa@lokakos.test` | Penyewa Demo UMY (`penyewa`) | Uji transaksi dan isolasi riwayat pesanan antar penyewa |

> Catatan: Seluruh akun demo, data kos contoh, dan foto berlabel `Ilustrasi demo` hanya digunakan untuk pengujian fungsional lokal dan demo akademik, bukan statistik produksi nyata.
