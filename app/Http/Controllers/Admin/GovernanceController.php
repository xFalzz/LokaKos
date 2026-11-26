<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\BoardingHouse;
use App\Models\Booking;
use App\Models\ListingReport;
use App\Services\ActivityLogger;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GovernanceController extends Controller
{
    public function reportsIndex(Request $request): View
    {
        $status = $request->query('status');

        $reports = ListingReport::with(['boardingHouse.mitra', 'reporter', 'handler'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.reports.index', [
            'reports' => $reports,
            'currentStatus' => $status,
        ]);
    }

    public function handleReport(Request $request, ListingReport $report): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:resolved,dismissed'],
            'admin_note' => ['nullable', 'string', 'max:500'],
            'unpublish_kos' => ['nullable', 'boolean'],
        ]);

        $report->load('boardingHouse');

        $report->update([
            'status' => $validated['status'],
            'admin_note' => $validated['admin_note'] ?? null,
            'handled_by' => $request->user()->id,
            'handled_at' => now(),
        ]);

        if ($request->boolean('unpublish_kos') && $report->boardingHouse) {
            $report->boardingHouse->update([
                'status' => BoardingHouse::STATUS_DRAFT,
                'rejection_note' => $validated['admin_note'] ?: 'Ditarik sementara berdasarkan laporan ketidaksesuaian data.',
            ]);

            NotificationService::notify(
                $report->boardingHouse->mitra_id,
                'kos_unpublished',
                'Kos Ditarik Sementara ke Draf',
                $report->boardingHouse->name . ' ditarik sementara karena laporan ketidaksesuaian. Silakan periksa dan perbarui data.',
                route('mitra.kos.edit', $report->boardingHouse)
            );
        }

        ActivityLogger::log(
            'report.' . $validated['status'],
            'Admin menindaklanjuti laporan #' . $report->id . ' pada ' . ($report->boardingHouse?->name ?? 'kos') . '.',
            $report
        );

        return back()->with('status', 'Laporan ketidaksesuaian berhasil ditindaklanjuti.');
    }

    public function auditIndex(Request $request): View
    {
        $actionFilter = trim((string) $request->query('action', ''));

        $logs = ActivityLog::with('user')
            ->when($actionFilter !== '', fn ($q) => $q->where('action', 'like', "%{$actionFilter}%"))
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.audit.index', [
            'logs' => $logs,
            'actionFilter' => $actionFilter,
        ]);
    }

    public function toggleFieldVerified(BoardingHouse $boardingHouse): RedirectResponse
    {
        $next = ! $boardingHouse->is_field_verified;

        $boardingHouse->update([
            'is_field_verified' => $next,
            'field_verified_at' => $next ? now() : null,
        ]);

        ActivityLogger::log(
            $next ? 'kos.field_verified' : 'kos.field_unverified',
            ($next ? 'Menandai ' : 'Mencabut tanda ') . 'Terverifikasi Lapangan pada ' . $boardingHouse->name . '.',
            $boardingHouse
        );

        NotificationService::notify(
            $boardingHouse->mitra_id,
            'kos_field_verified',
            $next ? 'Lencana Terverifikasi Lapangan Aktif' : 'Lencana Terverifikasi Lapangan Dicabut',
            $boardingHouse->name . ($next ? ' kini menampilkan lencana Terverifikasi Lapangan.' : ' tidak lagi menampilkan lencana Terverifikasi Lapangan.'),
            route('mitra.kos.edit', $boardingHouse)
        );

        return back()->with('status', 'Status verifikasi lapangan untuk ' . $boardingHouse->name . ' diperbarui.');
    }

    public function exportCsv(): StreamedResponse
    {
        $bookings = Booking::with(['user', 'roomType.boardingHouse.area', 'roomType.boardingHouse.mitra'])
            ->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_COMPLETED])
            ->latest()
            ->get();

        $filename = 'laporan-transaksi-platform-lokakos-' . now()->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($bookings) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Kode Booking', 'Tanggal', 'Penyewa', 'Mitra', 'Properti Kos', 'Area UMY', 'Tipe Kamar', 'Durasi (Bulan)', 'Kode Promo', 'Diskon', 'Total Tagihan', 'Status']);

            foreach ($bookings as $b) {
                fputcsv($out, [
                    $b->booking_code,
                    $b->created_at->format('Y-m-d'),
                    $b->user?->name ?? '-',
                    $b->roomType?->boardingHouse?->mitra?->name ?? '-',
                    $b->roomType?->boardingHouse?->name ?? '-',
                    $b->roomType?->boardingHouse?->area?->name ?? '-',
                    $b->roomType?->name ?? '-',
                    $b->duration_months,
                    $b->promo_code ?? '-',
                    $b->discount_amount,
                    $b->total_price,
                    $b->status,
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
