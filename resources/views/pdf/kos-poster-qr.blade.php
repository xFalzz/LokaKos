<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Poster QR {{ $kos->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1C1917; background: #F6F5F2; margin: 0; padding: 26px; }
        .frame { border: 3px solid #3D312C; background: #FFFFFF; padding: 28px; text-align: center; }
        .top-badge { display: inline-block; background: #3D312C; color: #FAF7F2; padding: 6px 18px; font-size: 12px; font-weight: bold; letter-spacing: 1px; }
        h1 { font-size: 28px; color: #3D312C; margin: 18px 0 6px; }
        .subtitle { font-size: 14px; color: #57534E; margin-bottom: 18px; }
        .price-band { background: #F3ECE0; border: 1px solid #E3DCD2; padding: 14px; margin: 16px auto; }
        .price-label { font-size: 11px; color: #57534E; text-transform: uppercase; letter-spacing: 1px; }
        .price-val { font-size: 22px; font-weight: bold; color: #3D312C; margin-top: 4px; }
        .qr-wrap { margin: 22px auto; }
        .qr-wrap img { width: 230px; height: 230px; border: 2px solid #3D312C; padding: 10px; background: #FFFFFF; }
        .cta { font-size: 15px; font-weight: bold; color: #3D312C; margin-top: 8px; }
        .info-box { margin-top: 18px; border-top: 1px solid #E3DCD2; padding-top: 14px; font-size: 12px; color: #57534E; }
        .url { font-size: 10px; color: #7A5820; margin-top: 8px; }
    </style>
</head>
<body>
    @php
        $minPrice = $kos->roomTypes->min('price_monthly');
        $avail = $kos->roomTypes->sum('available_rooms');
    @endphp
    <div class="frame">
        <div class="top-badge">LOKAKOS UMY · INFO KAMAR & BOOKING ONLINE</div>
        <h1>{{ $kos->name }}</h1>
        <div class="subtitle">
            Kos {{ ucfirst($kos->gender_type) }} {{ $kos->is_syariah ? '· Syariah ' : '' }}· Area {{ $kos->area?->name }}
            @if ($kos->distance_to_umy_km !== null)
                · {{ number_format($kos->distance_to_umy_km, 2, ',', '.') }} km dari UMY
            @endif
        </div>

        <div class="price-band">
            <div class="price-label">Tarif Mulai Dari</div>
            <div class="price-val">
                {{ $minPrice ? 'Rp ' . number_format($minPrice, 0, ',', '.') . ' / bulan' : 'Lihat detail di halaman kos' }}
            </div>
            <div style="font-size: 12px; color: #244A36; margin-top: 4px;">
                {{ $avail > 0 ? 'Saat cetak: ' . $avail . ' kamar tersedia' : 'Pindai QR untuk cek ketersediaan terbaru atau daftar tunggu' }}
            </div>
        </div>

        <div class="qr-wrap">
            <img src="{{ $qrDataUri }}" alt="QR Code {{ $kos->name }}">
            <div class="cta">Pindai QR Code Ini untuk Foto, Harga, & Booking Kamar</div>
        </div>

        <div class="info-box">
            <strong>Alamat:</strong> {{ $kos->address }}<br>
            <strong>Kontak Pengelola:</strong> {{ $kos->resolvedContactName() }}
            @if ($kos->resolvedDisplayWhatsapp())
                · WhatsApp {{ $kos->resolvedDisplayWhatsapp() }}
            @endif
            @if ($kos->contact_hours)
                ({{ $kos->contact_hours }})
            @endif
            <div class="url">{{ $publicUrl }}</div>
        </div>
    </div>
</body>
</html>
