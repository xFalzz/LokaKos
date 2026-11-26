<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Services\ActivityLogger;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class ExpireUnpaidBookings extends Command
{
    protected $signature = 'lokakos:expire-bookings';

    protected $description = 'Batalkan otomatis pesanan pending_payment yang melewati batas waktu 24 jam';

    public function handle(): int
    {
        $overdue = Booking::with('roomType.boardingHouse')
            ->where('status', Booking::STATUS_PENDING_PAYMENT)
            ->whereNotNull('payment_due_at')
            ->where('payment_due_at', '<', now())
            ->get();

        $count = 0;

        foreach ($overdue as $booking) {
            $booking->update([
                'status' => Booking::STATUS_CANCELLED,
                'cancellation_reason' => 'Dibatalkan otomatis karena melewati batas waktu unggah bukti transfer 24 jam.',
            ]);

            ActivityLogger::log(
                'booking.auto_expired',
                'Pesanan ' . $booking->booking_code . ' dibatalkan otomatis (lewat 24 jam).',
                $booking
            );

            NotificationService::notify(
                $booking->user_id,
                'booking_expired',
                'Pesanan Dibatalkan Otomatis',
                'Pesanan ' . $booking->booking_code . ' melewati batas waktu pembayaran 24 jam sehingga dibatalkan otomatis.',
                route('penyewa.bookings.show', $booking)
            );

            $count++;
        }

        $this->info("Pesanan kedaluwarsa yang dibatalkan: {$count}");

        return self::SUCCESS;
    }
}
