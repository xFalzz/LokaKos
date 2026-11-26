<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Transaksi Platform LokaKos</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1C1917; }
        h1 { font-size: 18px; color: #3D312C; margin-bottom: 4px; }
        .muted { color: #57534E; margin-bottom: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #DDD3C4; padding: 6px 8px; text-align: left; }
        th { background: #F3ECE0; }
    </style>
</head>
<body>
    <h1>LokaKos · Laporan Transaksi Platform (Kawasan UMY)</h1>
    <div class="muted">
        Periode: {{ $from ?: 'Awal' }} s.d. {{ $to ?: now()->format('Y-m-d') }} | Dicetak {{ now()->format('d M Y H:i') }} WIB
    </div>

    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Penyewa</th>
                <th>Mitra & Kos</th>
                <th>Durasi</th>
                <th>Status</th>
                <th>Nominal</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($bookings as $b)
                <tr>
                    <td>{{ $b->booking_code }}</td>
                    <td>{{ $b->user?->name }}</td>
                    <td>{{ $b->roomType?->boardingHouse?->name }} ({{ $b->roomType?->boardingHouse?->mitra?->name }})</td>
                    <td>{{ $b->duration_months }} bln</td>
                    <td>{{ $b->status }}</td>
                    <td>Rp {{ number_format($b->total_price, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="6">Belum ada transaksi tercatat pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    <p style="margin-top: 14px; font-size: 13px;">
        <strong>Total Nilai Transaksi Terkonfirmasi/Selesai: Rp {{ number_format($confirmedTotal, 0, ',', '.') }}</strong>
    </p>
</body>
</html>
