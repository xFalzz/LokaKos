<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Surat Perjanjian Sewa {{ $booking->booking_code }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11.5px; color: #1C1917; line-height: 1.55; }
        .header { text-align: center; border-bottom: 2px solid #3D312C; padding-bottom: 10px; margin-bottom: 16px; }
        .title { font-size: 16px; font-weight: bold; color: #3D312C; text-transform: uppercase; }
        .muted { color: #57534E; font-size: 10.5px; }
        h3 { font-size: 12.5px; color: #3D312C; margin: 14px 0 6px; border-bottom: 1px solid #DDD3C4; padding-bottom: 3px; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        td { padding: 4px 6px; vertical-align: top; }
        .sig-table { margin-top: 30px; }
        .sig-table td { width: 50%; text-align: center; }
        .sig-space { height: 64px; }
    </style>
</head>
<body>
    @php
        $kos = $booking->roomType?->boardingHouse;
        $room = $booking->roomType;
        $mitraProfile = $kos?->mitra?->mitraProfile;
    @endphp

    <div class="header">
        <div class="title">Surat Perjanjian Sewa Kamar Kos</div>
        <div class="muted">Referensi Booking LokaKos: {{ $booking->booking_code }} · Tanggal Cetak: {{ now()->format('d M Y') }}</div>
    </div>

    <p>Yang bertanda tangan di bawah ini menyepakati perjanjian sewa kamar kos dengan rincian para pihak dan ketentuan sebagai berikut:</p>

    <h3>1. Identitas Para Pihak</h3>
    <table>
        <tr>
            <td style="width: 32%;"><strong>Pihak Pertama (Pengelola)</strong></td>
            <td>: {{ $mitraProfile?->business_name ?? $kos?->mitra?->name }} ({{ $kos?->resolvedContactName() }})</td>
        </tr>
        <tr>
            <td><strong>Kontak Pengelola</strong></td>
            <td>: {{ $kos?->resolvedDisplayWhatsapp() ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>Pihak Kedua (Penyewa)</strong></td>
            <td>: {{ $booking->user?->name }} ({{ $booking->user?->email }})</td>
        </tr>
        <tr>
            <td><strong>Nomor Telepon Penyewa</strong></td>
            <td>: {{ $booking->user?->phone ?? '-' }}</td>
        </tr>
    </table>

    <h3>2. Objek Sewa, Periode, & Biaya</h3>
    <table>
        <tr>
            <td style="width: 32%;"><strong>Nama & Alamat Kos</strong></td>
            <td>: {{ $kos?->name }}, {{ $kos?->address }} ({{ $kos?->area?->name }})</td>
        </tr>
        <tr>
            <td><strong>Tipe Kamar & Kapasitas</strong></td>
            <td>: {{ $room?->name }} (Maks {{ $room?->max_occupancy ?? 1 }} orang, {{ ($room?->electricity_included ?? false) ? 'Termasuk listrik' : 'Listrik token mandiri' }})</td>
        </tr>
        <tr>
            <td><strong>Masa Sewa</strong></td>
            <td>: {{ $booking->check_in_date->format('d M Y') }} s.d. {{ $booking->leaseEndDate()->format('d M Y') }} ({{ $booking->duration_months }} bulan)</td>
        </tr>
        <tr>
            <td><strong>Total Biaya Sewa Lunas</strong></td>
            <td>: Rp {{ number_format($booking->total_price, 0, ',', '.') }} @if ($booking->promo_code) (Kode Promo: {{ $booking->promo_code }}) @endif</td>
        </tr>
        <tr>
            <td><strong>Uang Jaminan (Deposit)</strong></td>
            <td>: Rp {{ number_format($room?->deposit_amount ?? 0, 0, ',', '.') }} (dikembalikan saat keluar jika kamar dalam kondisi baik)</td>
        </tr>
    </table>

    <h3>3. Tata Tertib & Peraturan Penghuni</h3>
    @if ($kos?->rules)
        <ul>
            @foreach (preg_split('/\r\n|\r|\n/', trim($kos->rules)) as $rule)
                @if (trim($rule) !== '')
                    <li>{{ trim($rule) }}</li>
                @endif
            @endforeach
        </ul>
    @else
        <p>Penyewa wajib menjaga ketertiban, kebersihan fasilitas bersama, keamanan kamar, serta mematuhi peraturan lingkungan RT/RW setempat.</p>
    @endif

    <p style="margin-top: 10px;">
        Perjanjian ini dibuat sebagai pegangan kedua belah pihak selama masa sewa berlangsung dan berlaku sejak tanggal check-in.
    </p>

    <table class="sig-table">
        <tr>
            <td>
                <strong>Pihak Pertama (Pengelola Kos)</strong>
                <div class="sig-space"></div>
                <div>( {{ $kos?->resolvedContactName() ?? 'Pengelola' }} )</div>
            </td>
            <td>
                <strong>Pihak Kedua (Penyewa)</strong>
                <div class="sig-space"></div>
                <div>( {{ $booking->user?->name ?? 'Penyewa' }} )</div>
            </td>
        </tr>
    </table>
</body>
</html>
