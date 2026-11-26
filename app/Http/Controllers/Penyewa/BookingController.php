<?php

namespace App\Http\Controllers\Penyewa;

use App\Http\Controllers\Controller;
use App\Models\BoardingHouse;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Promo;
use App\Models\Review;
use App\Models\RoomType;
use App\Services\ActivityLogger;
use App\Services\NotificationService;
use App\Services\QrCodeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(Request $request): View
    {
        $bookings = Booking::with(['roomType.boardingHouse.area', 'payment', 'review'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return view('penyewa.bookings.index', [
            'bookings' => $bookings,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'room_type_id' => ['required', 'integer', 'exists:room_types,id'],
            'check_in_date' => ['required', 'date', 'after_or_equal:today'],
            'duration_months' => ['required', 'integer', 'in:1,3,6,12'],
            'promo_code' => ['nullable', 'string', 'max:40'],
            'notes' => ['nullable', 'string', 'max:500'],
            'identity_card' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        $room = RoomType::with('boardingHouse')->findOrFail($validated['room_type_id']);

        if (! $room->boardingHouse || $room->boardingHouse->status !== BoardingHouse::STATUS_PUBLISHED) {
            return back()->with('error', 'Kos ini belum berstatus tayang sehingga tidak dapat dipesan.');
        }

        if ($room->available_rooms <= 0) {
            return back()->with('error', 'Stok tipe kamar yang Anda pilih sedang penuh.');
        }

        $duration = (int) $validated['duration_months'];
        $basePrice = $room->calculatePrice($duration);
        $discountAmount = 0;
        $appliedCode = null;

        if (! empty($validated['promo_code'])) {
            $code = strtoupper(trim($validated['promo_code']));
            $promo = Promo::activeNow()
                ->where('code', $code)
                ->where('mitra_id', $room->boardingHouse->mitra_id)
                ->where(function ($q) use ($room) {
                    $q->whereNull('boarding_house_id')
                        ->orWhere('boarding_house_id', $room->boarding_house_id);
                })
                ->first();

            if (! $promo) {
                return back()->with('error', 'Kode promo ' . $code . ' tidak valid atau sudah berakhir untuk kos ini.')->withInput();
            }

            $discountAmount = $promo->calculateDiscount($basePrice, $duration);
            if ($discountAmount <= 0) {
                return back()->with('error', 'Kode promo ' . $code . ' membutuhkan minimal durasi sewa ' . $promo->min_duration_months . ' bulan.')->withInput();
            }

            $appliedCode = $promo->code;
        }

        $identityCardPath = $request->hasFile('identity_card')
            ? $request->file('identity_card')->store('identity_cards', 'public')
            : null;

        $booking = Booking::create([
            'booking_code' => Booking::generateCode(),
            'user_id' => $request->user()->id,
            'room_type_id' => $room->id,
            'check_in_date' => $validated['check_in_date'],
            'duration_months' => $duration,
            'total_price' => max(0, $basePrice - $discountAmount),
            'promo_code' => $appliedCode,
            'discount_amount' => $discountAmount,
            'status' => Booking::STATUS_PENDING_PAYMENT,
            'payment_due_at' => now()->addHours(24),
            'notes' => $validated['notes'] ?? null,
            'identity_card_path' => $identityCardPath,
        ]);

        ActivityLogger::log(
            'booking.created',
            $request->user()->name . ' membuat pesanan ' . $booking->booking_code . ' di ' . $room->boardingHouse->name . '.',
            $booking
        );

        NotificationService::notify(
            $room->boardingHouse->mitra_id,
            'booking_created',
            'Pesanan Baru Masuk: ' . $booking->booking_code,
            $request->user()->name . ' memesan ' . $room->name . ' (' . $room->boardingHouse->name . ') selama ' . $duration . ' bulan.',
            route('mitra.bookings.index')
        );

        return redirect()
            ->route('penyewa.bookings.show', $booking)
            ->with('status', 'Pemesanan berhasil dibuat. Unggah bukti transfer dalam 24 jam ke rekening Mitra.');
    }

    public function renew(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorize('view', $booking);

        if ($booking->user_id !== $request->user()->id) {
            abort(403);
        }

        if (! $booking->canRenew()) {
            return back()->with('error', 'Perpanjangan sewa hanya tersedia untuk pesanan yang sudah terkonfirmasi atau selesai check-in.');
        }

        $validated = $request->validate([
            'duration_months' => ['required', 'integer', 'in:1,3,6,12'],
        ]);

        $booking->load('roomType.boardingHouse');
        $room = $booking->roomType;
        $duration = (int) $validated['duration_months'];
        $nextStartDate = $booking->leaseEndDate()->gt(now())
            ? $booking->leaseEndDate()
            : now()->addDay();

        $renewal = Booking::create([
            'booking_code' => Booking::generateCode(),
            'user_id' => $request->user()->id,
            'room_type_id' => $room->id,
            'parent_booking_id' => $booking->id,
            'check_in_date' => $nextStartDate->toDateString(),
            'duration_months' => $duration,
            'total_price' => $room->calculatePrice($duration),
            'status' => Booking::STATUS_PENDING_PAYMENT,
            'payment_due_at' => now()->addHours(24),
            'notes' => 'Perpanjangan sewa dari pesanan ' . $booking->booking_code,
            'identity_card_path' => $booking->identity_card_path,
        ]);

        ActivityLogger::log(
            'booking.renewed',
            'Perpanjangan sewa ' . $renewal->booking_code . ' diajukan dari ' . $booking->booking_code . '.',
            $renewal
        );

        NotificationService::notify(
            $room->boardingHouse->mitra_id,
            'booking_renewed',
            'Pengajuan Perpanjangan Sewa',
            $request->user()->name . ' mengajukan perpanjangan sewa ' . $room->name . ' (' . $duration . ' bulan).',
            route('mitra.bookings.index')
        );

        return redirect()
            ->route('penyewa.bookings.show', $renewal)
            ->with('status', 'Pengajuan perpanjangan sewa berhasil dibuat. Silakan transfer dan unggah bukti pembayaran.');
    }

    public function show(Booking $booking): View
    {
        $this->authorize('view', $booking);

        $booking->load([
            'user',
            'parentBooking',
            'roomType.boardingHouse.area',
            'roomType.boardingHouse.mitra.mitraProfile',
            'payment',
            'review',
            'maintenanceTickets',
        ]);

        return view('penyewa.bookings.show', [
            'booking' => $booking,
        ]);
    }

    public function uploadPayment(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorize('view', $booking);

        if ($booking->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($booking->status !== Booking::STATUS_PENDING_PAYMENT) {
            return back()->with('error', 'Bukti pembayaran hanya dapat diunggah saat pesanan menunggu pembayaran.');
        }

        $validated = $request->validate([
            'sender_bank' => ['required', 'string', 'max:60'],
            'sender_name' => ['required', 'string', 'max:120'],
            'proof_image' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        DB::transaction(function () use ($request, $booking, $validated) {
            if ($booking->payment?->proof_image_path) {
                Storage::disk('public')->delete($booking->payment->proof_image_path);
            }

            $path = $request->file('proof_image')->store('payment_proofs', 'public');

            Payment::updateOrCreate(
                ['booking_id' => $booking->id],
                [
                    'amount' => $booking->total_price,
                    'sender_bank' => $validated['sender_bank'],
                    'sender_name' => $validated['sender_name'],
                    'proof_image_path' => $path,
                    'status' => Payment::STATUS_PENDING,
                    'verified_by' => null,
                    'verified_at' => null,
                    'rejection_note' => null,
                ]
            );

            $booking->update([
                'status' => Booking::STATUS_WAITING_VERIFICATION,
            ]);
        });

        $booking->load('roomType.boardingHouse');

        ActivityLogger::log(
            'payment.uploaded',
            'Bukti transfer diunggah untuk pesanan ' . $booking->booking_code . '.',
            $booking
        );

        NotificationService::notify(
            $booking->roomType->boardingHouse->mitra_id,
            'payment_uploaded',
            'Bukti Pembayaran Menunggu Verifikasi',
            $request->user()->name . ' mengunggah bukti transfer untuk ' . $booking->booking_code . '.',
            route('mitra.bookings.index', ['status' => Booking::STATUS_WAITING_VERIFICATION])
        );

        return back()->with('status', 'Bukti transfer berhasil diunggah. Menunggu verifikasi dari Mitra pemilik kos.');
    }

    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorize('cancelAsPenyewa', $booking);

        $validated = $request->validate([
            'cancellation_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $booking->update([
            'status' => Booking::STATUS_CANCELLED,
            'cancellation_reason' => $validated['cancellation_reason'] ?? 'Dibatalkan oleh penyewa.',
        ]);

        ActivityLogger::log('booking.cancelled', 'Pesanan ' . $booking->booking_code . ' dibatalkan oleh penyewa.', $booking);

        return back()->with('status', 'Pemesanan telah dibatalkan.');
    }

    public function invoicePdf(Booking $booking): Response
    {
        $this->authorize('view', $booking);

        if (! $booking->canDownloadInvoice()) {
            abort(403, 'Invoice hanya tersedia untuk pesanan yang pembayarannya sudah terkonfirmasi.');
        }

        $booking->load([
            'user',
            'roomType.boardingHouse.area',
            'roomType.boardingHouse.mitra.mitraProfile',
            'payment',
        ]);

        $verificationUrl = $booking->verificationUrl();
        $qrDataUri = QrCodeService::pngDataUri($verificationUrl, 5);

        $pdf = Pdf::loadView('pdf.invoice', [
            'booking' => $booking,
            'verificationUrl' => $verificationUrl,
            'qrDataUri' => $qrDataUri,
        ]);

        return $pdf->download('invoice-' . $booking->booking_code . '.pdf');
    }

    public function contractPdf(Booking $booking): Response
    {
        $this->authorize('view', $booking);

        if (! $booking->canDownloadInvoice()) {
            abort(403, 'Surat perjanjian sewa hanya tersedia untuk pesanan terkonfirmasi.');
        }

        $booking->load([
            'user',
            'roomType.boardingHouse.area',
            'roomType.boardingHouse.mitra.mitraProfile',
            'payment',
        ]);

        $pdf = Pdf::loadView('pdf.contract', [
            'booking' => $booking,
        ]);

        return $pdf->download('perjanjian-sewa-' . $booking->booking_code . '.pdf');
    }

    public function storeReview(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorize('view', $booking);

        if ($booking->user_id !== $request->user()->id) {
            abort(403);
        }

        if (! $booking->canBeReviewed()) {
            return back()->with('error', 'Ulasan hanya dapat dikirim satu kali setelah pesanan berstatus Selesai (Check-in).');
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1500'],
        ]);

        Review::create([
            'booking_id' => $booking->id,
            'user_id' => $request->user()->id,
            'boarding_house_id' => $booking->roomType->boarding_house_id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'is_visible' => true,
        ]);

        return back()->with('status', 'Terima kasih. Ulasan Anda berhasil disimpan dan tampil pada halaman kos.');
    }
}
