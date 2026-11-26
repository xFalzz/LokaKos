<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BoardingHouse;
use App\Services\ActivityLogger;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminKosController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status', '');

        $query = BoardingHouse::with(['area', 'mitra.mitraProfile', 'roomTypes'])->latest();

        if (in_array($status, ['draft', 'pending_review', 'published', 'rejected'], true)) {
            $query->where('status', $status);
        }

        return view('admin.kos.index', [
            'boardingHouses' => $query->paginate(12)->withQueryString(),
            'statusFilter' => $status,
        ]);
    }

    public function approve(BoardingHouse $boardingHouse): RedirectResponse
    {
        $boardingHouse->update([
            'status' => BoardingHouse::STATUS_PUBLISHED,
            'rejection_note' => null,
        ]);

        ActivityLogger::log('kos.approved', 'Admin menyetujui & menayangkan listing: ' . $boardingHouse->name, $boardingHouse);
        NotificationService::notify(
            $boardingHouse->mitra_id,
            'kos_approved',
            'Properti Kos Tayang di Katalog',
            $boardingHouse->name . ' telah disetujui Admin dan tampil di pencarian mahasiswa UMY.',
            route('kos.show', $boardingHouse)
        );

        return back()->with('status', "Listing {$boardingHouse->name} disetujui dan tayang di katalog publik.");
    }

    public function unpublish(BoardingHouse $boardingHouse): RedirectResponse
    {
        $boardingHouse->update([
            'status' => BoardingHouse::STATUS_DRAFT,
        ]);

        ActivityLogger::log('kos.unpublished', 'Admin menarik sementara listing ke draf: ' . $boardingHouse->name, $boardingHouse);
        NotificationService::notify(
            $boardingHouse->mitra_id,
            'kos_unpublished',
            'Properti Kos Ditarik Sementara',
            $boardingHouse->name . ' ditarik sementara ke status draf oleh Admin.',
            route('mitra.kos.edit', $boardingHouse)
        );

        return back()->with('status', "Listing {$boardingHouse->name} ditarik sementara dari katalog publik.");
    }

    public function reject(Request $request, BoardingHouse $boardingHouse): RedirectResponse
    {
        $validated = $request->validate([
            'rejection_note' => ['required', 'string', 'max:1000'],
        ]);

        $boardingHouse->update([
            'status' => BoardingHouse::STATUS_REJECTED,
            'rejection_note' => $validated['rejection_note'],
        ]);

        ActivityLogger::log('kos.rejected', 'Admin menolak listing kos: ' . $boardingHouse->name, $boardingHouse, ['reason' => $validated['rejection_note']]);
        NotificationService::notify(
            $boardingHouse->mitra_id,
            'kos_rejected',
            'Listing Kos Perlu Perbaikan',
            $boardingHouse->name . ' ditolak Admin. Catatan: ' . $validated['rejection_note'],
            route('mitra.kos.edit', $boardingHouse)
        );

        return back()->with('status', "Listing {$boardingHouse->name} ditolak dan dikembalikan ke Mitra untuk perbaikan.");
    }
}
