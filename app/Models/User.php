<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_MITRA = 'mitra';
    public const ROLE_PENYEWA = 'penyewa';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isMitra(): bool
    {
        return $this->role === self::ROLE_MITRA;
    }

    public function isPenyewa(): bool
    {
        return $this->role === self::ROLE_PENYEWA;
    }

    public function mitraProfile(): HasOne
    {
        return $this->hasOne(MitraProfile::class);
    }

    public function boardingHouses(): HasMany
    {
        return $this->hasMany(BoardingHouse::class, 'mitra_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function savedBoardingHouses(): BelongsToMany
    {
        return $this->belongsToMany(BoardingHouse::class, 'saved_boarding_houses')->withTimestamps();
    }

    public function surveyBookings(): HasMany
    {
        return $this->hasMany(SurveyBooking::class);
    }

    public function maintenanceTickets(): HasMany
    {
        return $this->hasMany(MaintenanceTicket::class);
    }

    public function roomWaitlists(): HasMany
    {
        return $this->hasMany(RoomWaitlist::class);
    }

    public function promos(): HasMany
    {
        return $this->hasMany(Promo::class, 'mitra_id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(MitraExpense::class, 'mitra_id');
    }

    public function appNotifications(): HasMany
    {
        return $this->hasMany(UserNotification::class)->latest();
    }

    public function unreadNotificationsCount(): int
    {
        return $this->appNotifications()->whereNull('read_at')->count();
    }
}
