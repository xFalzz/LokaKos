<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Booking extends Model
{
    use HasFactory;

    public const STATUS_PENDING_PAYMENT = 'pending_payment';
    public const STATUS_WAITING_VERIFICATION = 'waiting_verification';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'booking_code',
        'user_id',
        'room_type_id',
        'parent_booking_id',
        'check_in_date',
        'duration_months',
        'total_price',
        'promo_code',
        'discount_amount',
        'status',
        'payment_due_at',
        'notes',
        'identity_card_path',
        'cancellation_reason',
        'verification_token',
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'duration_months' => 'integer',
        'total_price' => 'integer',
        'discount_amount' => 'integer',
        'payment_due_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $booking) {
            if (empty($booking->verification_token)) {
                $booking->verification_token = Str::random(40);
            }
            if ($booking->status === self::STATUS_PENDING_PAYMENT && empty($booking->payment_due_at)) {
                $booking->payment_due_at = now()->addHours(24);
            }
        });
    }

    public static function generateCode(): string
    {
        do {
            $code = 'LK-' . now()->format('Ym') . '-' . strtoupper(Str::random(5));
        } while (self::where('booking_code', $code)->exists());

        return $code;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function parentBooking(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_booking_id');
    }

    public function renewals(): HasMany
    {
        return $this->hasMany(self::class, 'parent_booking_id');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function maintenanceTickets(): HasMany
    {
        return $this->hasMany(MaintenanceTicket::class);
    }

    public function roomUnit(): HasOne
    {
        return $this->hasOne(RoomUnit::class, 'current_booking_id');
    }

    public function leaseEndDate(): Carbon
    {
        return $this->check_in_date->copy()->addMonths($this->duration_months);
    }

    public function canDownloadInvoice(): bool
    {
        return in_array($this->status, [self::STATUS_CONFIRMED, self::STATUS_COMPLETED], true);
    }

    public function canBeReviewed(): bool
    {
        return $this->status === self::STATUS_COMPLETED && $this->review === null;
    }

    public function canRenew(): bool
    {
        return in_array($this->status, [self::STATUS_CONFIRMED, self::STATUS_COMPLETED], true);
    }

    public function canReportMaintenance(): bool
    {
        return in_array($this->status, [self::STATUS_CONFIRMED, self::STATUS_COMPLETED], true);
    }

    public function isPaymentExpired(): bool
    {
        return $this->status === self::STATUS_PENDING_PAYMENT
            && $this->payment_due_at !== null
            && $this->payment_due_at->isPast();
    }

    public function ensureVerificationToken(): string
    {
        if (empty($this->verification_token)) {
            $this->verification_token = Str::random(40);
            $this->save();
        }

        return $this->verification_token;
    }

    public function verificationUrl(): string
    {
        return route('invoice.verify', [
            'bookingCode' => $this->booking_code,
            'token' => $this->ensureVerificationToken(),
        ]);
    }
}
