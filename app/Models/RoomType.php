<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoomType extends Model
{
    use HasFactory;

    protected $fillable = [
        'boarding_house_id',
        'name',
        'size_m2',
        'price_monthly',
        'price_3_months',
        'price_6_months',
        'price_yearly',
        'deposit_amount',
        'electricity_included',
        'max_occupancy',
        'total_rooms',
        'available_rooms',
        'description',
        'room_image_path',
    ];

    protected $casts = [
        'price_monthly' => 'integer',
        'price_3_months' => 'integer',
        'price_6_months' => 'integer',
        'price_yearly' => 'integer',
        'deposit_amount' => 'integer',
        'electricity_included' => 'boolean',
        'max_occupancy' => 'integer',
        'total_rooms' => 'integer',
        'available_rooms' => 'integer',
    ];

    public function boardingHouse(): BelongsTo
    {
        return $this->belongsTo(BoardingHouse::class);
    }

    public function facilities(): BelongsToMany
    {
        return $this->belongsToMany(Facility::class, 'room_facility');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(RoomUnit::class)->orderBy('unit_code');
    }

    public function waitlists(): HasMany
    {
        return $this->hasMany(RoomWaitlist::class);
    }

    public function hasAvailableStock(): bool
    {
        return $this->available_rooms > 0;
    }

    public function occupiedRooms(): int
    {
        return max(0, $this->total_rooms - $this->available_rooms);
    }

    public function calculatePrice(int $months): int
    {
        return match ($months) {
            3 => $this->price_3_months ?? ($this->price_monthly * 3),
            6 => $this->price_6_months ?? ($this->price_monthly * 6),
            12 => $this->price_yearly ?? ($this->price_monthly * 12),
            default => $this->price_monthly * $months,
        };
    }
}
