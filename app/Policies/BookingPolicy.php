<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function view(User $user, Booking $booking): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isPenyewa()) {
            return $booking->user_id === $user->id;
        }

        if ($user->isMitra()) {
            return $booking->roomType?->boardingHouse?->mitra_id === $user->id;
        }

        return false;
    }

    public function verifyAsMitra(User $user, Booking $booking): bool
    {
        return $user->isMitra()
            && $booking->roomType?->boardingHouse?->mitra_id === $user->id;
    }

    public function cancelAsPenyewa(User $user, Booking $booking): bool
    {
        return $user->isPenyewa()
            && $booking->user_id === $user->id
            && $booking->status === Booking::STATUS_PENDING_PAYMENT;
    }
}
