# Product Requirements Document (PRD)
## LokaKos: Sistem Informasi Pemesanan Kos Berbasis Web dengan Multi-Mitra Pemilik Kos
### Studi Kasus: Kawasan Universitas Muhammadiyah Yogyakarta (UMY)

| Informasi Dokumen | Keterangan |
| :--- | :--- |
| Nama Produk | **LokaKos** (LokaKos UMY) |
| Versi Dokumen | **3.0** (Lengkap 24 Tabel Domain / 20 Migrasi & 45 Test Otomatis Lulus) |
| Metode Pengembangan | Waterfall |
| Stack Teknologi | Laravel 10.50, PHP 8.3, MySQL 8.4, Blade, Tailwind CSS, Alpine.js, Leaflet.js, DomPDF |
| Tim Pengembang | 3 Orang (Anggota 1: Lead/Admin, Anggota 2: Modul Mitra, Anggota 3: Modul Publik/Penyewa/QA) |

---

## 1. Latar Belakang & Tujuan Produk

### 1.1 Pernyataan Masalah
Mahasiswa Universitas Muhammadiyah Yogyakarta (terutama mahasiswa baru dari luar daerah) kesulitan mencari kos yang sesuai kebutuhan di sekitar Kampus Terpadu UMY (Tamantirto, Kasihan, Bantul). Informasi kos masih tersebar di spanduk jalan, grup media sosial, dan chat pribadi sehingga:
- Ketersediaan kamar tidak dapat diketahui secara pasti sebelum menghubungi pemilik satu per satu.
- Perbandingan jarak ke kampus, area sekitar (Ngebel, Brajan, Geblagan, Tamantirto, Rukeman, Kasihan, Ambarketawang, Ringroad Barat), jenis kos (Putra/Putri/Campur), aturan Syariah, kebijakan listrik, dan uang jaminan (deposit) memakan waktu lama.
- Pemilik kos lokal (Mitra) mencatat pemesanan dan mengecek bukti transfer secara manual di chat sehingga rawan tercecer, sulit memperbarui sisa kamar per tipe, dan menyulitkan rekap pemasukan.

### 1.2 Tujuan Produk
Membangun sistem informasi berbasis web multi-mitra (**LokaKos**) yang mempertemukan mahasiswa pencari kos dengan pemilik kos terverifikasi di kawasan UMY dalam satu alur terstruktur: pencarian berbasis filter lokal dan jarak peta, penyimpanan kos favorit, pemesanan kamar berbasis stok dengan detail kamar lengkap, pembayaran transfer manual terverifikasi, cetak invoice PDF, hingga ulasan penghuni.

---

## 2. Persona Pengguna

### Persona 1: Mahasiswa Pencari Kos (Penyewa)
- **Profil:** Mahasiswa UMY (S1/vokasi) yang mencari tempat tinggal bulanan, semesteran, atau tahunan dekat kampus.
- **Kebutuhan:** Mencari kos berdasarkan area dusun sekitar UMY, mengetahui estimasi jarak ke kampus, memeriksa rincian kamar (kapasitas, listrik, deposit, foto kamar), menyimpan kos incaran, menghubungi pemilik lewat WhatsApp, memesan kamar tersedia, mengunggah bukti transfer, memantau progres 4 tahap pesanan, dan mengunduh bukti booking resmi saat check-in.

### Persona 2: Pemilik / Pengelola Kos (Mitra)
- **Profil:** Pemilik atau pengelola 1 sampai beberapa properti kos di sekitar Tamantirto dan Kasihan.
- **Kebutuhan:** Mendaftarkan properti kos secara mandiri dengan panduan awal saat baru bergabung (`0` kos), mengelola properti dan tipe kamar miliknya sendiri secara terisolasi dari mitra lain, mengedit rincian kamar dan foto per tipe kamar, memperbarui stok kamar secara cepat (`-`/`+`), memverifikasi bukti transfer masuk ke rekeningnya, serta mencetak rekap pemasukan PDF.

### Persona 3: Pengelola Platform (Administrator)
- **Profil:** Admin pengelola layanan LokaKos.
- **Kebutuhan:** Memverifikasi identitas calon Mitra sebelum diizinkan menayangkan kos, memoderasi data kos sebelum tayang (termasuk menarik sementara kos tayang ke status `draft` bila perlu), mengelola master data Area UMY dan Fasilitas, memantau seluruh transaksi dan pengguna, memoderasi ulasan, serta mencetak laporan transaksi platform.

---

## 3. Ruang Lingkup Produk

### 3.1 Dalam Ruang Lingkup (Implementasi Terverifikasi)
1. **Autentikasi, Otorisasi Multi-Role, & Isolasi Mitra:** Registrasi Penyewa, registrasi Calon Mitra (dengan berkas KTP & rekening), login terpadu menuju `/dashboard`, Middleware pembatas role (`RoleMiddleware`), serta Laravel Policy (`BoardingHousePolicy`, `BookingPolicy`) yang mengunci seluruh properti, tipe kamar, dan pesanan agar hanya dapat diakses pemilik sahnya.
2. **Back-Office Administrator:** Dashboard ringkasan platform + badge antrean di sidebar Espresso, Verifikasi Mitra (`pending`/`approved`/`rejected`), Moderasi Kos (`pending_review`/`published`/`rejected` serta aksi **Tarik Sementara ke Draf** / `unpublish`), CRUD Master Area (termasuk edit inline) & Fasilitas, Monitoring Pengguna (aktif/nonaktifkan) & Transaksi lintas mitra, Moderasi Ulasan, dan Laporan Transaksi PDF.
3. **Workspace Multi-Mitra Pemilik Kos:** Mode onboarding bersih untuk Mitra baru (`0` kos), dashboard operasional 4 metrik untuk Mitra aktif, CRUD properti kos (multi-foto, kontak pengelola per kos berupa nama kontak/WA/telepon/jam survei/template pesan WA kustom, pin koordinat peta Leaflet.js dengan perhitungan jarak Haversine ke UMY), CRUD & Edit penuh tipe kamar (`deposit_amount`, `electricity_included`, `max_occupancy`, `room_image_path`, fasilitas kamar) + penyesuaian cepat stok `-1`/`+1`, verifikasi bukti transfer transaksional, konfirmasi check-in, dan Laporan Pemasukan Mitra PDF.
4. **Katalog Publik & Transaksi Penyewa:** Beranda bergaya Rivora x Roomora, katalog split-view dengan filter kawasan UMY + drawer mobile, halaman detail kos + peta + tombol **Simpan Kos** (`saved_boarding_houses`) + kontak pengelola (`Tanya via WhatsApp`, `Tanya Tipe Ini via WA`, dan telepon langsung dengan pesan otomatis berbeda dan menarik per properti/kamar), pemesanan kamar (durasi 1/3/6/12 bulan), unggah bukti transfer manual, tracker progres 4 tahap di dashboard Penyewa, unduh Invoice PDF, dan rating/ulasan untuk transaksi selesai.
5. **Pengalaman Antarmuka:** Responsif (mobile `375px`, tablet, desktop `1440px`), toggle mode Light & Dark bertema kalem hangat sesuai `DESIGN.md`, dengan App Shell sidebar Espresso untuk area login.

### 3.2 Di Luar Ruang Lingkup (Out of Scope)
- Integrasi payment gateway otomatis (Midtrans/Xendit) dan pencairan dana otomatis/escrow.
- Chat real-time berbasis WebSocket di dalam aplikasi (komunikasi cepat menggunakan tautan WhatsApp Mitra pada halaman detail kos).
- Aplikasi mobile native (Android/iOS).

---

## 4. User Stories & Acceptance Criteria

### A. Pengunjung & Penyewa
- **US-01 (Pencarian & Filter):** Sebagai mahasiswa, saya ingin memfilter kos berdasarkan Area sekitar UMY, tipe Putra/Putri/Campur, kategori Syariah, fasilitas, dan harga agar cepat menemukan kamar yang cocok.
  - *Kriteria Terima:* Hanya kos berstatus `published` yang tampil; hasil dapat diurutkan dari jarak terdekat ke UMY atau harga terendah; jika hasil kosong, tampil pesan penjelasan dan tombol `Reset Filter`.
- **US-02 (Pemesanan Kamar):** Sebagai penyewa login, saya ingin memilih tipe kamar yang masih tersedia beserta rinciannya (kapasitas, listrik, deposit), tanggal masuk, dan durasi sewa (1/3/6/12 bulan) untuk membuat pesanan.
  - *Kriteria Terima:* Tipe kamar dengan `available_rooms = 0` tidak dapat dipesan; sistem menghasilkan `booking_code` unik, menghitung `total_price`, dan menampilkan instruksi transfer ke rekening Mitra tujuan.
- **US-03 (Unggah Bukti Transfer):** Sebagai penyewa, saya ingin mengunggah foto/PDF bukti transfer beserta nama bank dan nama pengirim agar Mitra dapat memverifikasi pembayaran saya.
  - *Kriteria Terima:* Hanya menerima JPG/JPEG/PNG/PDF maksimal 2 MB; status pesanan otomatis berubah dari `pending_payment` ke `waiting_verification`.
- **US-04 (Invoice & Ulasan):** Sebagai penyewa yang pembayarannya disetujui, saya ingin mengunduh Invoice PDF dan memberi ulasan setelah check-in.
  - *Kriteria Terima:* Invoice PDF hanya tersedia untuk status `confirmed` dan `completed`; form ulasan hanya muncul pada status `completed` dan dibatasi 1 ulasan per booking.
- **US-10 (Simpan Kos Favorit & Kontak WhatsApp Personal):** Sebagai penyewa, saya ingin menyimpan kos incaran dan menghubungi pemilik/penjaga tiap kos lewat WhatsApp atau telepon dengan pesan pembuka otomatis yang relevan tanpa perlu mengetik ulang.
  - *Kriteria Terima:* Tombol `Simpan Kos` menyimpan/menghapus baris unik pada `saved_boarding_houses` dan tampil di `/penyewa/favorit` serta dashboard Penyewa; tombol `Tanya via WhatsApp` dan `Tanya Tipe Ini via WA` mengarah ke nomor WA khusus kos (`contact_whatsapp` dengan fallback `mitra.phone`) beserta pesan pembuka otomatis yang berbeda-beda sesuai pengelola, area, dan tipe kamar.

### B. Mitra Pemilik Kos
- **US-05 (Registrasi & Onboarding Mitra Baru):** Sebagai pemilik kos baru, saya ingin mendaftar akun Mitra dengan identitas KTP dan rekening, lalu setelah disetujui langsung dipandu menambahkan kos pertama tanpa menu operasional yang membingungkan.
  - *Kriteria Terima:* Sebelum `approved`, Mitra melihat status verifikasi (atau alasan penolakan); setelah `approved` dengan `0` kos, dashboard dan sidebar memfokuskan aksi **Tambah Kos Pertama** dan **Profil & Rekening Bank**.
- **US-06 (Kelola Kos, Edit Kamar Lengkap, & Isolasi Data):** Sebagai Mitra terverifikasi, saya ingin menambah/mengubah properti kos, pin peta, serta mengedit rincian tiap tipe kamar dan menyesuaikan stok secara cepat tanpa dapat melihat atau mengubah properti milik Mitra lain.
  - *Kriteria Terima:* Koordinat pin otomatis menghitung `distance_to_umy_km` terhadap UMY (`-7.8113, 110.3216`); form edit kamar mendukung harga, kapasitas, listrik, deposit, foto kamar, fasilitas kamar, dan penyesuaian cepat stok `-1`/`+1`; akses ke kos/kamar milik Mitra lain ditolak dengan HTTP 403.
- **US-07 (Verifikasi Pembayaran):** Sebagai Mitra, saya ingin melihat bukti transfer penyewa lalu menyetujui atau menolaknya disertai alasan.
  - *Kriteria Terima:* Persetujuan berjalan dalam `DB::transaction` dengan `lockForUpdate()`, mengurangi `available_rooms` sebanyak 1 (gagal aman bila stok 0), mengubah payment menjadi `verified` dan booking menjadi `confirmed`. Penolakan wajib disertai `rejection_note` dan tidak mengubah stok.

### C. Administrator
- **US-08 (Verifikasi Mitra, Moderasi, & Tarik Sementara Kos):** Sebagai Admin, saya ingin memverifikasi akun Mitra, memoderasi listing kos baru, serta menarik sementara kos tayang ke `draft` jika ditemukan pelanggaran data.
  - *Kriteria Terima:* Admin dapat menyetujui/menolak Mitra dan kos (dengan catatan alasan), serta menarik kos `published` kembali ke `draft` sehingga langsung tersembunyi dari katalog publik.
- **US-09 (Master Data, Monitoring, & Laporan):** Sebagai Admin, saya ingin mengelola daftar Area UMY (tambah/edit inline/hapus), daftar Fasilitas, status aktif pengguna, moderasi ulasan, dan mencetak laporan transaksi platform PDF.
  - *Kriteria Terima:* Perubahan master Area/Fasilitas langsung muncul di form Mitra dan filter katalog publik; laporan PDF dapat difilter berdasarkan rentang tanggal.

---

## 5. Aturan Bisnis Kunci (Business Rules)

1. **Perhitungan Jarak Otomatis:** Jarak ke kampus dihitung dengan rumus Haversine dalam satuan kilometer (2 angka desimal) dari koordinat kos ke titik referensi Kampus Terpadu UMY (`latitude: -7.8113, longitude: 110.3216`).
2. **Perhitungan Harga Paket Sewa & Rincian Kamar:** Jika Mitra mengisi tarif paket khusus (`price_3_months`, `price_6_months`, `price_yearly`), sistem memakai tarif paket tersebut; jika kosong, sistem mengalikan `price_monthly * duration_months`. Informasi uang jaminan (`deposit_amount`), status listrik (`electricity_included`), dan kapasitas penghuni (`max_occupancy`) ditampilkan transparan pada detail kos dan form kamar.
3. **Integritas Stok Kamar:** Pengurangan stok otomatis dilakukan tepat saat Mitra menyetujui bukti transfer di dalam transaksi database terkunci (`DB::transaction` + `lockForUpdate`). Penyesuaian manual tombol `-1`/`+1` juga dikunci pada rentang `0 <= available_rooms <= total_rooms`.
4. **Isolasi Multi-Mitra:** Setiap endpoint lihat/ubah/hapus/stok/verifikasi milik Mitra divalidasi dengan Laravel Policy untuk memastikan `boarding_house.mitra_id === auth()->id()`.
5. **Keunikan Favorit & Ulasan:** Tabel `saved_boarding_houses` memiliki constraint unik `(user_id, boarding_house_id)`, dan tabel `reviews` memiliki constraint unik pada `booking_id` yang hanya menerima ulasan dari pemilik booking berstatus `completed`.

---

## 6. Peta Rute & Arsitektur Informasi (Sitemap Aktual `routes/web.php`)

| Area | Prefix / URL | Halaman & Endpoint Utama |
| :--- | :--- | :--- |
| Publik | `/` | Beranda (`/`), Katalog Kos (`/kos`), Detail Kos (`/kos/{slug}`), Informasi Gabung Mitra (`/gabung-mitra`) |
| Autentikasi & Umum | `/` | Login (`/login`), Registrasi Penyewa (`/register`), Registrasi Mitra (`/register/mitra`), Dashboard Terpadu (`/dashboard`), Pengaturan Akun (`/profile`) |
| Penyewa | `/penyewa` | Riwayat Pesanan (`/penyewa/pesanan`), Detail & Upload Bukti (`/penyewa/pesanan/{booking}`), Unduh Invoice PDF (`/penyewa/pesanan/{booking}/invoice`), Kirim Ulasan (`/penyewa/pesanan/{booking}/review`), Kos Favorit (`/penyewa/favorit`, `POST /penyewa/favorit/{boardingHouse}`) |
| Mitra | `/mitra` | Profil Usaha & Rekening (`/mitra/profil`), Daftar & CRUD Kos (`/mitra/kos`, `/mitra/kos/tambah`, `/mitra/kos/{boardingHouse}/edit`), CRUD & Edit Kamar (`/mitra/kos/{boardingHouse}/kamar`, `/mitra/kos/{boardingHouse}/kamar/{roomType}/edit`, `PATCH .../stock`), Daftar & Verifikasi Pesanan (`/mitra/pesanan`), Laporan PDF (`/mitra/laporan/pdf`) |
| Admin | `/admin` | Verifikasi Mitra (`/admin/mitra`), Moderasi & Unpublish Kos (`/admin/kos`), Master Area & Fasilitas (`/admin/master`), Monitoring Pengguna, Transaksi & Ulasan (`/admin/monitoring`), Laporan PDF (`/admin/laporan/pdf`) |

---

## 7. Kriteria Keberhasilan Proyek (Definition of Done)

1. Seluruh 28 kebutuhan fungsional (`FR-01` sampai `FR-28`) berjalan sesuai skenario di `docs/TEST_PLAN.md` tanpa error 500 atau console error.
2. Seluruh skenario negatif keamanan (akses lintas role dan manipulasi ID kos/kamar/survei/Q&A/pesanan lintas mitra) tertolak dengan HTTP 403.
3. Antarmuka di desktop (`1440px`), tablet, dan ponsel (`375px`) bekerja penuh pada Light Mode maupun Dark Mode serta lolos Delivery Gate antislop.
4. Dokumen Waterfall (Tahap 1 sampai 4) sinkron 100% dengan skema **24 tabel domain** (**20 migrasi**) database MySQL `lokakos_db` dan **45 test otomatis** (`php artisan test`, 253 assertions) yang seluruhnya lulus.
