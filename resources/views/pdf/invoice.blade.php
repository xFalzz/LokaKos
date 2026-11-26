<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $booking->booking_code }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1C1917; line-height: 1.5; }
        .header { border-bottom: 2px solid #3D312C; padding-bottom: 12px; margin-bottom: 18px; }
        .brand { font-size: 20px; font-weight: bold; color: #3D312C; }
        .muted { color: #57534E; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 14px; }
        th, td { border: 1px solid #DDD3C4; padding: 8px; text-align: left; }
        th { background: #F3ECE0; }
        .total { font-size: 14px; font-weight: bold; }
        .qr-box { margin-top: 20px; padding: 12px; border: 1px solid #DDD3C4; background: #FAF8F4; }
        .qr-table { border: none; margin: 0; }
        .qr-table td { border: none; padding: 0; vertical-align: middle; }
    </style>
</head>
<body>
    <div class="header">
        <div class="brand">LokaKos · Bukti Pemesanan Kamar</div>
        <div class="muted">Kawasan Universitas Muhammadiyah Yogyakarta (Tamantirto, Kasihan, Bantul)</div>
    </div>

    <p><strong>Kode Booking:</strong> {{ $booking->booking_code }}</p>
    <p><strong>Status:</strong> {{ strtoupper($booking->status) }}</p>
    <p><strong>Penyewa:</strong> {{ $booking->user?->name }} ({{ $booking->user?->email }})</p>
    <p><strong>Properti Kos:</strong> {{ $booking->roomType?->boardingHouse?->name }} ({{ $booking->roomType?->boardingHouse?->area?->name }})</p>
    <p><strong>Alamat Kos:</strong> {{ $booking->roomType?->boardingHouse?->address }}</p>
    <p><strong>Pengelola / Mitra:</strong> {{ $booking->roomType?->boardingHouse?->mitra?->mitraProfile?->business_name }}</p>

    <table>
        <thead>
            <tr>
                <th>Tipe Kamar</th>
                <th>Periode Sewa</th>
                <th>Durasi</th>
                <th>Total Tagihan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    {{ $booking->roomType?->name }}
                    @if ($booking->promo_code)
                        <br><span class="muted">Promo {{ $booking->promo_code }} (Potongan Rp {{ number_format($booking->discount_amount, 0, ',', '.') }})</span>
                    @endif
                </td>
                <td>{{ $booking->check_in_date->format('d M Y') }} s.d. {{ $booking->leaseEndDate()->format('d M Y') }}</td>
                <td>{{ $booking->duration_months }} Bulan</td>
                <td class="total">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    @if ($booking->payment)
        <p style="margin-top: 16px;">
            <strong>Pembayaran Diverifikasi:</strong> {{ $booking->payment->sender_bank }} a.n. {{ $booking->payment->sender_name }}
            ({{ $booking->payment->verified_at?->format('d M Y H:i') }} WIB)
        </p>
    @endif

    @if (! empty($qrDataUri))
        <div class="qr-box">
            <table class="qr-table">
                <tr>
                    <td style="width: 110px;">
                        <img src="{{ $qrDataUri }}" alt="QR Verifikasi Invoice" style="width: 96px; height: 96px;">
                    </td>
                    <td style="padding-left: 12px;">
                        <strong>QR Verifikasi Keaslian Bukti Booking</strong><br>
                        <span class="muted">Pindai QR ini saat check-in untuk memvalidasi keaslian status pembayaran resmi di sistem LokaKos.</span><br>
                        <span class="muted" style="font-size: 9px;">{{ $verificationUrl ?? '' }}</span>
                    </td>
                </tr>
            </table>
        </div>
    @endif

    <p class="muted" style="margin-top: 20px;">
        Tunjukkan dokumen ini kepada pengelola kos saat melakukan check-in. Dicetak pada {{ now()->format('d M Y H:i') }} WIB.
    </p>
</body>
</html>
