# DESIGN.md
## Sistem Desain Visual & Antarmuka: LokaKos (Edisi Rivora x Roomora Warm Boutique)
### Platform Pemesanan Kos Multi-Mitra Kawasan Universitas Muhammadiyah Yogyakarta

> Nama Brand: **LokaKos** (LokaKos UMY)
> Stack UI: Laravel 10 Blade + Tailwind CSS + Alpine.js + Leaflet.js
> Dial Liveliness: `Dial: ENERGY 2 / RHYTHM 3 / MOTION 2`
> Arahan Pemilik Proyek: Memadukan kehangatan butik *Rivora* (kanvas alabaster hangat, champagne gold, blok espresso taupe membulat, kartu kamar frameless 2 kolom) dengan ketegasan arsitektural *Roomora* (tipografi display besar `Instrument Sans`, bar pencarian kapsul tumpang tindih hero, daftar baris bergaris tipis `1px`, dan grid hairline 2x2), lengkap dengan toggle Light/Dark Mode yang lolos WCAG AA.

---

## 1. Design Read & Identitas Produk

`Reading this as: Platform direktori dan pemesanan kos multi-mitra kawasan UMY dengan bahasa visual warm boutique hospitality (adaptasi Rivora & Roomora), dial ENERGY 2 / RHYTHM 3 / MOTION 2.`

### Perpaduan Ciri Khas (Rivora + Roomora -> LokaKos)
- **Dari Rivora:** Kanvas hangat `#F6F5F2`, aksen Champagne Gold `#CAA05C`, permukaan krem-emas tipis `#F3ECE0`, blok Espresso Taupe `#3D312C` ber-radius besar (`22px-28px`) dengan margin sisi tipis (`mx-3 sm:mx-5`), font body `Golos Text`, serta kartu kamar frameless (foto `rounded-[18px]` bersih diikuti baris metadata kiri + harga kanan dan judul besar di bawahnya).
- **Dari Roomora:** Tipografi display `Instrument Sans` berskala besar dan tegas, Hero full-bleed membulat (`rounded-[28px]`), bilah pencarian kapsul melayang tumpang tindih tepi bawah Hero (`-mt-10 sm:-mt-14`), daftar area berbaris penuh dengan garis pemisah `1px`, serta grid fitur hairline 2x2.
- **Kepatuhan Hard Gate Antislop:** Nol testimoni fiktif, nol angka statistik karangan, nol link mati, seluruh tombol Champagne Gold memakai teks Espresso gelap (`#1C1917`, rasio `7.24:1` PASS WCAG AAA, bukan teks putih di atas emas yang gagal kontras), dan foto demo diberi penanda jujur sebagai ilustrasi contoh sampai Mitra mengunggah foto asli.

---

## 2. Liveliness Dials & Levers

| Dial | Nilai | Penerapan Konkret |
| :--- | :---: | :--- |
| **ENERGY** | **2 (Balanced Boutique)** | Hero editorial penuh foto arsitektur hangat dan judul display besar, sementara katalog dan panel Mitra/Admin tetap tenang dan mudah dipindai. |
| **RHYTHM** | **3 (Varied Composition)** | Setiap bagian beranda punya bentuk visual berbeda: (1) Framed Photo Hero + Floating Capsule Search Bar, (2) 2-Column Frameless Room Showcase, (3) Champagne-Tinted Framed Area List dengan garis `1px`, (4) Split Espresso Callout + Hairline 2x2 Grid, (5) Framed Espresso Footer. |
| **MOTION** | **2 (Purposeful)** | Transisi `200ms cubic-bezier(0.22, 1, 0.36, 1)` pada toggle tema Light/Dark, hover gambar kartu kos (`scale 1.03`), sorotan baris area, buka/tutup drawer filter & menu mobile. Tanpa animasi loop tanpa henti. |

---

## 3. Palet Warna & Bukti Verifikasi WCAG AA

Batas palet: **2 warna inti** (Champagne Gold, Espresso Taupe) + **1 warna status/aksen fungsional** (Sage untuk Syariah/Tersedia) di atas netral alabaster/charcoal hangat.

### Mode Terang (Light Mode)
| Token CSS | Hex | Peran | Rasio Kontras (WCAG AA) |
| :--- | :---: | :--- | :--- |
| `--bg-canvas` | `#F6F5F2` | Kanvas utama (Warm Alabaster) | Dasar |
| `--bg-surface` | `#FFFFFF` | Permukaan panel pencarian, input, kartu panel | Dasar |
| `--bg-tint` | `#F3ECE0` | Permukaan krem-emas lembut ala Rivora | Seksi framed |
| `--bg-espresso` | `#3D312C` | Blok gelap & Footer framed ala Rivora | Teks `#FAF7F2` = `11.74:1` (PASS) |
| `--border-subtle` | `#E3DCD2` | Garis pemisah tipis `1px` | Struktural |
| `--border-control` | `#877E75` | Garis tepi input & kontrol | `3.98:1` di `#FFFFFF` (PASS >= 3:1) |
| `--text-primary` | `#1C1917` | Heading & teks utama | `16.04:1` di `#F6F5F2` (PASS) |
| `--text-secondary` | `#57534E` | Paragraf & metadata | `7.00:1` di `#F6F5F2`, `6.50:1` di `#F3ECE0` (PASS) |
| `--brand-gold` | `#CAA05C` | CTA primer pill & highlight | Teks `#1C1917` = `7.24:1` (PASS) |
| `--brand-gold-hover` | `#D6B070` | Hover CTA primer | Teks `#1C1917` = `8.58:1` (PASS) |
| `--brand-gold-text` | `#7A5820` | Tautan teks bronze pada latar terang | `5.94:1` di `#F6F5F2` (PASS) |
| `--brand-sage-bg` / `ink` | `#E8F0EB` / `#244A36` | Badge Syariah & Tersedia | `8.58:1` (PASS) |
| `--status-danger` | `#9E2A25` | Error & status ditolak (dengan teks + ikon) | `6.85:1` di `#F6F5F2` (PASS) |

### Mode Gelap (Dark Mode)
| Token CSS | Hex | Peran | Rasio Kontras (WCAG AA) |
| :--- | :---: | :--- | :--- |
| `--bg-canvas` | `#151210` | Kanvas utama (Warm Charcoal) | Dasar |
| `--bg-surface` | `#1F1B18` | Permukaan kartu & bar pencarian | Dasar |
| `--bg-tint` | `#241E1A` | Permukaan seksi framed hangat | Seksi framed |
| `--bg-espresso` | `#29211D` | Blok espresso & footer framed | Teks `#F5F1EB` = `13.4:1` (PASS) |
| `--border-subtle` | `#3A322C` | Garis pemisah tipis `1px` | Struktural |
| `--border-control` | `#8C8177` | Garis tepi input & kontrol | `4.50:1` di `#1F1B18` (PASS >= 3:1) |
| `--text-primary` | `#F5F1EB` | Heading & teks utama | `16.58:1` di `#151210` (PASS) |
| `--text-secondary` | `#C7BDB3` | Paragraf & metadata | `9.25:1` di `#1F1B18` (PASS) |
| `--brand-gold` | `#D5AE6D` | CTA primer pill | Teks `#151210` = `8.97:1` (PASS) |
| `--brand-gold-text` | `#E2BD7E` | Tautan teks emas hangat | `9.62:1` di `#1F1B18` (PASS) |
| `--brand-sage-bg` / `ink` | `#1D3227` / `#A4D6B8` | Badge Syariah & Tersedia | `8.38:1` (PASS) |
| `--status-danger` | `#F28E86` | Error & status ditolak | `7.32:1` di `#1F1B18` (PASS) |

---

## 4. Tipografi & Bentuk

- **Heading & Display:** `Instrument Sans` (`500`, `600`, `700`), letter-spacing `-0.025em` pada H1/H2.
- **Body, Form, Tabel, Harga:** `Golos Text` (`400`, `500`, `600`, `700`), `tabular-nums` pada nominal rupiah dan jarak km.
- **Skala Radius Bertingkat (R-11):**
  - Badge fungsional: `8px`
  - Input form & select di halaman dalam: `12px`
  - Kartu kos / panel foto: `18px`
  - Framed section, Hero, & Footer: `24px` (mobile) / `28px` (desktop)
  - Tombol CTA primer/sekunder & floating search bar desktop: `9999px` (pill elegan khas Rivora/Roomora, dibedakan dari input/kartu agar tidak serba pill).

---

## 5. Matriks Alasan Desain (R-31)
- **Palet Alabaster + Champagne Gold + Espresso Taupe:** Diadaptasi langsung dari referensi *Rivora* milik pemilik proyek sambil menyesuaikan warna teks tombol emas ke Espresso gelap agar 100% lolos WCAG AA.
- **Tipografi `Instrument Sans` + `Golos Text`:** Menyatukan heading arsitektural *Roomora* dan teks isi *Rivora* untuk tampilan kontemporer yang tetap ramah dibaca di layar ponsel maupun desktop.
- **Kartu Frameless 2 Kolom (Beranda) + Split-View Filter (Katalog):** Beranda menonjolkan fotografi dan keterbacaan harga cepat ala *Rivora*, sementara katalog menjaga kecepatan penyaringan multi-kriteria untuk mahasiswa UMY.
- **Foto Demo Arsitektur Berlabel Jujur:** Menghadirkan kualitas visual hospitality nyata sesuai referensi sekaligus jujur ditandai sebagai ilustrasi contoh hingga Mitra mengunggah foto lapangan.
