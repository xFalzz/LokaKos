# Design Brief: LokaKos
## Arahan Desain untuk Tim Pengembang (3 Orang)

> Versi Dokumen: **2.0** (Disinkronkan dengan `DESIGN.md` edisi Rivora x Roomora Warm Boutique dan App Shell Workspace Sidebar Espresso).

Dokumen ini menjadi ringkasan praktis arah visual, batasan inspirasi, nada bahasa, dan status aset untuk tim agar seluruh halaman yang dikerjakan Anggota 1 (Admin), Anggota 2 (Mitra), dan Anggota 3 (Publik/Penyewa) terlihat seperti satu produk utuh dan bebas dari pola desain generik (AI slop).

---

## 1. Ringkasan Proyek
- **Nama Produk:** LokaKos (LokaKos UMY)
- **Fokus Wilayah:** Kawasan Universitas Muhammadiyah Yogyakarta (Tamantirto, Ngebel, Brajan, Geblagan, Rukeman, Kasihan, Ambarketawang, Ringroad Barat).
- **Tujuan Desain:** Membantu mahasiswa menemukan, membandingkan, menyimpan, dan memesan kos tanpa kebingungan, serta membantu pemilik kos lokal mengelola kamar dan memverifikasi bukti transfer manual secara cepat di ponsel maupun laptop.

---

## 2. Analisis Referensi & Batas Anti-Kloning

Pemilik proyek memberikan referensi visual **Rivora**, **Roomora**, **Katalog Split-View**, serta referensi fungsional skala **Mamikos/Papikos**. Berikut batasan jelas mengenai elemen yang **diadaptasi** dan elemen yang **tidak boleh ditiru**:

| Referensi | Elemen yang Diadaptasi (Inspirasi) | Elemen yang Ditolak (Anti-Kloning & Anti-Slop) |
| :--- | :--- | :--- |
| **Rivora** (Hospitality) | Kanvas hangat Alabaster (`#F6F5F2`), aksen Champagne Gold (`#CAA05C`), blok Espresso Taupe (`#3D312C`) membulat, body font `Golos Text`, kartu kamar frameless 2 kolom. | Tidak memakai teks putih di atas tombol emas (diubah ke teks Espresso gelap `#1C1917` dengan kontras 7,24:1 agar lolos WCAG AA); tidak memakai foto resort mewah yang tidak relevan. |
| **Roomora** (Booking) | Tipografi display `Instrument Sans` tegas, Hero foto membulat, bilah pencarian kapsul melayang di tepi bawah Hero, daftar area berbaris dengan garis `1px`, grid fitur hairline 2x2. | Tidak meniru animasi parallax berlebihan, angka statistik promosi fiktif, atau testimoni karangan. |
| **Katalog Split-View & Mamikos/Papikos** | Panel filter kiri tetap terlihat di desktop (ubah menjadi drawer di mobile), detail kamar lengkap (kapasitas, listrik, deposit, foto kamar), tombol simpan kos favorit, kontrol stok cepat `-`/`+`, dan workspace sidebar per peran. | Tidak memakai palet biru-hitam dingin milik referensi gambar; tidak menumpuk banner promosi atau pop-up mengganggu. |

---

## 3. Nada Bahasa & Panduan Copywriting

- **Gaya Bahasa:** Bahasa Indonesia yang ramah, langsung pada fakta, dan mudah dipahami baik oleh mahasiswa usia 18-23 tahun maupun pemilik kos lokal usia 40-60 tahun.
- **Contoh Pilihan Kata yang Benar:**
  - Gunakan: *"Cari kos terverifikasi di sekitar Kampus Terpadu UMY."*
  - Hindari: *"Temukan pengalaman revolusioner dan seamless dalam mencari hunian impian generasi berikutnya."*
  - Gunakan CTA spesifik: `Cari Kos`, `Ajukan Pemesanan`, `Simpan Kos`, `Tanya via WhatsApp`, `Unggah Bukti Transfer`, `Tambah Properti Kos`, `Edit Kamar`, `Tarik Sementara (Draf)`, `Setujui Pembayaran`, `Unduh Invoice PDF`.
  - Hindari CTA generik: `Mulai Sekarang`, `Pelajari Lebih Lanjut`, `Jelajahi`.
- **Aturan Tanda Baca:** Tidak memakai tanda pisah panjang / em dash di teks antarmuka; gunakan koma, titik, titik dua, atau titik tengah (`·`).
- **Tanpa Klaim Fiktif:** Jangan menulis angka "500+ kos terdaftar" atau ulasan bohongan. Tampilkan jumlah riil dari database.

---

## 4. Inventaris Aset & Status Media

| Aset | Status Saat Ini | Perlakuan di UI |
| :--- | :--- | :--- |
| **Logo LokaKos** | Wordmark teks resmi | Wordmark **LokaKos UMY** dengan font `Instrument Sans` SemiBold/Bold (tanpa ikon vektor buatan). |
| **Foto Kos & Kamar** | 4 foto arsitektur demo + dukungan unggah asli Mitra | Tersedia 4 foto ilustrasi di `public/images/demo/` (`hero-kos-umy.jpg`, `room-boutique-ac.jpg`, `room-minimal-warm.jpg`, `room-studio-balcony.jpg`) yang diberi label jujur `Ilustrasi demo`. Saat Mitra mengunggah foto properti (`boarding_images`) atau foto tipe kamar (`room_types.room_image_path`), foto asli otomatis ditampilkan. |
| **Avatar Pengguna** | Tidak mewajibkan foto profil | Nama lengkap, email, dan badge peran ditampilkan rapi pada footer sidebar Espresso. |
| **Peta Lokasi** | Siap pakai | Peta interaktif Leaflet.js + OpenStreetMap dengan marker titik Kampus Terpadu UMY (`-7.8113, 110.3216`) dan titik lokasi kos. |
| **Ikon Antarmuka** | Siap pakai | Ikon SVG fungsional ringkas (toggle tema Light/Dark, status tayang, menu drawer mobile, tombol hapus/tutup). Tanpa emoji dekoratif di UI. |

---

## 5. Daftar Deliverable Layar per Anggota Tim

Seluruh halaman memakai token CSS terpusat di `resources/css/app.css` dan layout bersama (`resources/views/layouts/public.blade.php`, `app.blade.php`, `navigation.blade.php`, `guest.blade.php`):

1. **Komponen & Shell Bersama (Anggota 1):**
   - Layout publik Rivora x Roomora + navbar + toggle tema Light/Dark + footer Espresso framed.
   - App Shell Workspace (`/dashboard` dan semua halaman login): sidebar Espresso Taupe (`w-64`) di desktop + slide-over drawer di mobile/tablet, top utility bar (`Situs Publik` + toggle tema), serta badge antrean live untuk Admin dan Mitra.
   - Halaman Auth (`login`, `register`, `register-mitra`, `forgot-password`) dengan pesan validasi Bahasa Indonesia (`lang/id/`).
2. **Halaman Admin (Anggota 1):** Dashboard Platform, Verifikasi Mitra (`/admin/mitra`), Moderasi & Unpublish Kos (`/admin/kos`), Master Area (termasuk edit inline) & Fasilitas (`/admin/master`), Monitoring Pengguna/Transaksi/Ulasan (`/admin/monitoring`), Template Laporan Admin PDF.
3. **Halaman Mitra (Anggota 2):** Dashboard Onboarding (`0` kos) & Operasional Aktif (4 kartu metrik, antrean bayar, rincian properti + kontrol stok cepat `-`/`+`), Profil Usaha & Rekening (`/mitra/profil`), CRUD Kos + Pin Peta Leaflet (`/mitra/kos`), CRUD & Edit Penuh Tipe Kamar (`/mitra/kos/{id}/kamar` & `edit`), Verifikasi Pesanan (`/mitra/pesanan`), Template Laporan Mitra PDF.
4. **Halaman Publik & Penyewa (Anggota 3):** Beranda, Katalog Split-View (Desktop + Drawer Mobile), Detail Kos + Peta + Simpan Kos + WhatsApp + Panel Pemesanan, Dashboard Penyewa (pelacak progres 4 tahap + ringkasan kos favorit), Daftar Kos Tersimpan (`/penyewa/favorit`), Riwayat & Detail Pesanan + Unggah Bukti + Ulasan (`/penyewa/pesanan`), Template Invoice PDF.

---

## 6. Daftar Periksa Sebelum Merge UI (Ringkas)

Sebelum anggota tim melakukan Pull Request halaman Blade ke branch `main`, pastikan:
1. Halaman diuji di mode **Light** dan **Dark** (tidak ada teks pudar atau kontras < 4.5:1 di kedua mode).
2. Halaman diuji di lebar **375px (HP)**, **768px (Tablet)**, dan **1440px (Desktop)** tanpa geser menyamping (horizontal overflow).
3. Tombol dan input dapat dicapai dengan tombol `Tab`, diaktifkan dengan `Enter`/`Space`, dan drawer ditutup dengan `Escape`.
4. Seluruh tombol dan tautan benar-benar menuju halaman/aksi nyata.
5. Empty state (termasuk onboarding Mitra `0` kos dan daftar favorit kosong) serta error state tersedia di seluruh halaman daftar dan formulir.
