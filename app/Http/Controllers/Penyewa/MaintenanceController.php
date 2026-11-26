<?php

namespace App\Http\Controllers\Penyewa;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\MaintenanceTicket;
use App\Services\ActivityLogger;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MaintenanceController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $activeBookings = Booking::with('roomType.boardingHouse')
            ->where('user_id', $user->id)
            ->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_COMPLETED])
            ->latest()
            ->get();

        $tickets = MaintenanceTicket::with(['boardingHouse', 'booking.roomType'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(12);

        return view('penyewa.maintenance.index', [
            'activeBookings' => $activeBookings,
            'tickets' => $tickets,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'booking_id' => ['required', 'exists:bookings,id'],
            'category' => ['required', 'in:listrik,air_kamar_mandi,ac_kipas,wifi,perabot,kebersihan,lainnya'],
            'title' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'min:10', 'max:1500'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $booking = Booking::with('roomType.boardingHouse')
            ->where('id', $validated['booking_id'])
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if (! $booking->canReportMaintenance()) {
            return back()->with('error', 'Laporan perbaikan hanya tersedia untuk kamar yang sudah terkonfirmasi atau sedang dihuni.');
        }

        $photoPath = $request->hasFile('photo')
            ? $request->file('photo')->store('maintenance_photos', 'public')
            : null;

        $boardingHouse = $booking->roomType->boardingHouse;

        $ticket = MaintenanceTicket::create([
            'booking_id' => $booking->id,
            'boarding_house_id' => $boardingHouse->id,
            'user_id' => $request->user()->id,
            'category' => $validated['category'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'photo_path' => $photoPath,
            'status' => MaintenanceTicket::STATUS_OPEN,
        ]);

        ActivityLogger::log(
            'maintenance.created',
            'Laporan perbaikan baru di ' . $boardingHouse->name . ': ' . $ticket->title,
            $ticket
        );

        NotificationService::notify(
            $boardingHouse->mitra_id,
            'maintenance_opened',
            'Keluhan / Perbaikan Baru di ' . $boardingHouse->name,
            $request->user()->name . ' melaporkan: ' . $ticket->title,
            route('mitra.tickets.index')
        );

        return redirect()
            ->route('penyewa.maintenance.index')
            ->with('status', 'Laporan perbaikan berhasil dikirim ke pengelola kos.');
    }
}
