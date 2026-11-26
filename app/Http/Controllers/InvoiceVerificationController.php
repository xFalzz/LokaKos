<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\View\View;

class InvoiceVerificationController extends Controller
{
    public function show(string $bookingCode, string $token): View
    {
        $booking = Booking::with(['user', 'roomType.boardingHouse.area', 'payment'])
            ->where('booking_code', $bookingCode)
            ->where('verification_token', $token)
            ->first();

        return view('public.verify-invoice', [
            'booking' => $booking,
            'isValid' => $booking !== null && $booking->canDownloadInvoice(),
            'searchedCode' => $bookingCode,
        ]);
    }
}
