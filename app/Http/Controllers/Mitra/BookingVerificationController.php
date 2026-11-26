<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\MitraExpense;
use App\Models\Payment;
use App\Models\RoomType;
use App\Services\ActivityLogger;
use App\Services\NotificationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BookingVerificationController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status', '');

        $query = Booking::with(['user', 'roomType.boardingHouse', 'payment'])
            ->whereHas('roomType.boardingHouse', fn ($q) => $q->where('mitra_id', $request->user()->id))
            ->latest();

        if ($status !== '') {
            $query->where('status', $status);
        }

        return view('mitra.bookings.index', [
            'bookings' => $query->paginate(12)->withQueryString(),
            'statusFilter' => $status,
        ]);
    }

    public function approve(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorize('verifyAsMitra', $booking);

        if ($booking->status !== Booking::STATUS_WAITING_VERIFICATION || ! $booking->payment) {
            return back()->with('error', 'Pesanan ini tidak sedang menunggu verifikasi pembayaran.');
        }

        $verified = DB::transaction(function () use ($request, $booking) {
            $room = RoomType::where('id', $booking->room_type_id)->lockForUpdate()->first();

            if (! $room || $room->available_rooms <= 0) {
                return false;
            }

            $room->decrement('available_rooms');

            $booking->payment->update([
                'status' => Payment::STATUS_VERIFIED,
                'verified_by' => $request->user()->id,
                'verified_at' => now(),
                'rejection_note' => null,
            ]);

            $booking->update([
                'status' => Booking::STATUS_CONFIRMED,
            ]);

            return true;
        });

        if (! $verified) {
            return back()->with('error', 'Stok kamar tersedia sudah habis sehingga pembayaran tidak dapat dikonfirmasi.');
        }

        ActivityLogger::log(
            'payment.approved',
            'Mitra menyetujui pembayaran pesanan ' . $booking->booking_code . '.',
            $booking
        );

        NotificationService::notify(
            $booking->user_id,
            'payment_approved',
            'Pembayaran Dikonfirmasi: ' . $booking->booking_code,
            'Bukti transfer Anda telah disetujui pengelola. Invoice PDF dan Surat Perjanjian Sewa kini siap diunduh.',
            route('penyewa.bookings.show', $booking)
        );

        return back()->with('status', 'Pembayaran disetujui. Stok kamar otomatis berkurang 1 dan Invoice PDF penyewa aktif.');
    }

    public function reject(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorize('verifyAsMitra', $booking);

        if ($booking->status !== Booking::STATUS_WAITING_VERIFICATION || ! $booking->payment) {
            return back()->with('error', 'Pesanan ini tidak sedang menunggu verifikasi pembayaran.');
        }

        $validated = $request->validate([
            'rejection_note' => ['required', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($request, $booking, $validated) {
            $booking->payment->update([
                'status' => Payment::STATUS_REJECTED,
                'verified_by' => $request->user()->id,
                'verified_at' => now(),
                'rejection_note' => $validated['rejection_note'],
            ]);

            $booking->update([
                'status' => Booking::STATUS_REJECTED,
            ]);
        });

        ActivityLogger::log(
            'payment.rejected',
            'Mitra menolak pembayaran pesanan ' . $booking->booking_code . '.',
            $booking,
            ['reason' => $validated['rejection_note']]
        );

        NotificationService::notify(
            $booking->user_id,
            'payment_rejected',
            'Pembayaran Ditolak: ' . $booking->booking_code,
            'Catatan pengelola: ' . $validated['rejection_note'],
            route('penyewa.bookings.show', $booking)
        );

        return back()->with('status', 'Pembayaran ditolak beserta catatan alasan. Stok kamar tidak berubah.');
    }

    public function complete(Booking $booking): RedirectResponse
    {
        $this->authorize('verifyAsMitra', $booking);

        if ($booking->status !== Booking::STATUS_CONFIRMED) {
            return back()->with('error', 'Hanya pesanan berstatus Terkonfirmasi yang dapat ditandai selesai check-in.');
        }

        $booking->update(['status' => Booking::STATUS_COMPLETED]);

        ActivityLogger::log(
            'booking.completed',
            'Mitra mengonfirmasi check-in pesanan ' . $booking->booking_code . '.',
            $booking
        );

        NotificationService::notify(
            $booking->user_id,
            'booking_completed',
            'Check-in Dikonfirmasi: ' . $booking->booking_code,
            'Selamat menempati kamar Anda. Form ulasan dan laporan perbaikan kini tersedia di halaman pesanan.',
            route('penyewa.bookings.show', $booking)
        );

        return back()->with('status', 'Check-in penyewa dikonfirmasi. Form ulasan sekarang terbuka bagi penyewa.');
    }

    public function reportPdf(Request $request): Response
    {
        $from = $request->query('from');
        $to = $request->query('to');
        $mitraId = $request->user()->id;

        $query = Booking::with(['user', 'roomType.boardingHouse', 'payment'])
            ->whereHas('roomType.boardingHouse', fn ($q) => $q->where('mitra_id', $mitraId))
            ->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_COMPLETED])
            ->latest();

        $expenseQuery = MitraExpense::with('boardingHouse')
            ->where('mitra_id', $mitraId)
            ->latest('expense_date');

        if ($from) {
            $query->whereDate('created_at', '>=', $from);
            $expenseQuery->whereDate('expense_date', '>=', $from);
        }
        if ($to) {
            $query->whereDate('created_at', '<=', $to);
            $expenseQuery->whereDate('expense_date', '<=', $to);
        }

        $bookings = $query->get();
        $expenses = $expenseQuery->get();
        $totalIncome = (int) $bookings->sum('total_price');
        $totalExpenses = (int) $expenses->sum('amount');

        $pdf = Pdf::loadView('pdf.mitra-report', [
            'mitra' => $request->user()->load('mitraProfile'),
            'bookings' => $bookings,
            'expenses' => $expenses,
            'totalIncome' => $totalIncome,
            'totalExpenses' => $totalExpenses,
            'netIncome' => $totalIncome - $totalExpenses,
            'from' => $from,
            'to' => $to,
        ]);

        return $pdf->download('laporan-pemasukan-lokakos-' . now()->format('Ymd') . '.pdf');
    }
}
