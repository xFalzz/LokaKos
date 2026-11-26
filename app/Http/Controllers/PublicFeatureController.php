<?php

namespace App\Http\Controllers;

use App\Models\BoardingHouse;
use App\Models\BoardingQuestion;
use App\Models\ListingReport;
use App\Models\RoomType;
use App\Models\RoomWaitlist;
use App\Services\ActivityLogger;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicFeatureController extends Controller
{
    public function compare(Request $request): View
    {
        $rawIds = $request->query('ids', '');
        $ids = collect(is_array($rawIds) ? $rawIds : explode(',', (string) $rawIds))
            ->map(fn ($id) => (int) trim((string) $id))
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->take(3)
            ->values();

        $selectedKos = $ids->isNotEmpty()
            ? BoardingHouse::published()
                ->with(['area', 'images', 'facilities', 'roomTypes.facilities', 'mitra.mitraProfile'])
                ->whereIn('id', $ids)
                ->get()
                ->sortBy(fn ($k) => $ids->search($k->id))
                ->values()
            : collect();

        $availableKos = BoardingHouse::published()
            ->with('area')
            ->orderBy('name')
            ->get(['id', 'name', 'area_id', 'gender_type', 'distance_to_umy_km']);

        return view('kos.compare', [
            'selectedKos' => $selectedKos,
            'availableKos' => $availableKos,
            'selectedIds' => $ids->all(),
        ]);
    }

    public function storeReport(Request $request, BoardingHouse $boardingHouse): RedirectResponse
    {
        if ($boardingHouse->status !== BoardingHouse::STATUS_PUBLISHED) {
            abort(404);
        }

        $validated = $request->validate([
            'reporter_name' => ['nullable', 'string', 'max:120'],
            'reason' => ['required', 'in:harga_tidak_sesuai,stok_tidak_sesuai,foto_tidak_sesuai,kontak_tidak_aktif,lainnya'],
            'details' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $report = ListingReport::create([
            'boarding_house_id' => $boardingHouse->id,
            'reporter_user_id' => $request->user()?->id,
            'reporter_name' => $request->user()?->name ?? ($validated['reporter_name'] ?: 'Pengunjung Anonim'),
            'reason' => $validated['reason'],
            'details' => $validated['details'],
            'status' => ListingReport::STATUS_OPEN,
        ]);

        ActivityLogger::log(
            'listing.reported',
            'Laporan ketidaksesuaian masuk untuk ' . $boardingHouse->name . ' (' . $validated['reason'] . ').',
            $report
        );

        NotificationService::notifyAdmins(
            'listing_report',
            'Laporan Listing Baru',
            'Ada laporan ketidaksesuaian data untuk ' . $boardingHouse->name . '.',
            route('admin.reports.index')
        );

        return back()->with('status', 'Laporan Anda diterima dan telah diteruskan ke Admin untuk ditinjau.');
    }

    public function storeQuestion(Request $request, BoardingHouse $boardingHouse): RedirectResponse
    {
        if ($boardingHouse->status !== BoardingHouse::STATUS_PUBLISHED) {
            abort(404);
        }

        $validated = $request->validate([
            'question' => ['required', 'string', 'min:8', 'max:500'],
        ]);

        $question = BoardingQuestion::create([
            'boarding_house_id' => $boardingHouse->id,
            'user_id' => $request->user()->id,
            'question' => trim($validated['question']),
            'is_visible' => true,
        ]);

        NotificationService::notify(
            $boardingHouse->mitra_id,
            'kos_question',
            'Pertanyaan Baru di ' . $boardingHouse->name,
            $request->user()->name . ' bertanya: "' . str($question->question)->limit(90) . '"',
            route('mitra.announcements.index') . '#tanya-jawab'
        );

        return back()->with('status', 'Pertanyaan berhasil dikirim dan akan dijawab oleh pengelola kos.');
    }

    public function toggleWaitlist(Request $request, RoomType $roomType): RedirectResponse
    {
        $roomType->load('boardingHouse');

        if ($roomType->boardingHouse->status !== BoardingHouse::STATUS_PUBLISHED) {
            abort(404);
        }

        if ($roomType->available_rooms > 0) {
            return back()->with('status', 'Kamar tipe ini sedang tersedia, Anda dapat langsung mengajukan pemesanan.');
        }

        $existing = RoomWaitlist::where('user_id', $request->user()->id)
            ->where('room_type_id', $roomType->id)
            ->first();

        if ($existing && $existing->status === RoomWaitlist::STATUS_WAITING) {
            $existing->update(['status' => RoomWaitlist::STATUS_CANCELLED]);

            return back()->with('status', 'Anda keluar dari daftar tunggu untuk ' . $roomType->name . '.');
        }

        RoomWaitlist::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'room_type_id' => $roomType->id,
            ],
            [
                'status' => RoomWaitlist::STATUS_WAITING,
                'notified_at' => null,
            ]
        );

        return back()->with('status', 'Berhasil masuk daftar tunggu. Anda akan mendapat notifikasi saat stok ' . $roomType->name . ' tersedia kembali.');
    }
}
