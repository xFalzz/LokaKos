<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Keuangan Mitra</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1C1917; }
        h1 { font-size: 18px; color: #3D312C; margin-bottom: 4px; }
        h2 { font-size: 13px; color: #3D312C; margin-top: 18px; margin-bottom: 6px; }
        .muted { color: #57534E; margin-bottom: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #DDD3C4; padding: 6px 8px; text-align: left; }
        th { background: #F3ECE0; }
        .summary { margin-top: 16px; border: 1px solid #3D312C; padding: 10px 14px; background: #FAF8F4; }
    </style>
</head>
<body>
    <h1>LokaKos · Laporan Keuangan & Operasional Mitra</h1>
    <div class="muted">
        Mitra: {{ $mitra->mitraProfile?->business_name ?? $mitra->name }} |
        Periode: {{ $from ?: 'Awal' }} s.d. {{ $to ?: now()->format('Y-m-d') }}
    </div>

    <h2>1. Pemasukan Sewa Terkonfirmasi</h2>
    <table>
        <thead>
            <tr>
                <th>Kode Booking</th>
                <th>Penyewa</th>
                <th>Kos & Kamar</th>
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
                    <td>{{ $b->roomType?->boardingHouse?->name }} ({{ $b->roomType?->name }})</td>
                    <td>{{ $b->duration_months }} bln</td>
                    <td>{{ $b->status }}</td>
                    <td>Rp {{ number_format($b->total_price, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="6">Belum ada transaksi terkonfirmasi pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>2. Pengeluaran Operasional Kos</h2>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Properti</th>
                <th>Kategori</th>
                <th>Keterangan</th>
                <th>Nominal</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($expenses ?? [] as $exp)
                <tr>
                    <td>{{ $exp->expense_date->format('d M Y') }}</td>
                    <td>{{ $exp->boardingHouse?->name ?? 'Umum' }}</td>
                    <td>{{ str_replace('_', ' ', ucfirst($exp->category)) }}</td>
                    <td>{{ $exp->description }}</td>
                    <td>Rp {{ number_format($exp->amount, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="5">Belum ada catatan pengeluaran operasional pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        <p style="margin: 2px 0;"><strong>Total Pemasukan Kotor:</strong> Rp {{ number_format($totalIncome, 0, ',', '.') }}</p>
        <p style="margin: 2px 0;"><strong>Total Pengeluaran Operasional:</strong> Rp {{ number_format($totalExpenses ?? 0, 0, ',', '.') }}</p>
        <p style="margin: 6px 0 0; font-size: 13px;"><strong>Estimasi Pemasukan Bersih: Rp {{ number_format($netIncome ?? $totalIncome, 0, ',', '.') }}</strong></p>
    </div>
</body>
</html>
