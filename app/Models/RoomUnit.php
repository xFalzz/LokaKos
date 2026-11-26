<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomUnit extends Model
{
    use HasFactory;

    public const STATUS_AVAILABLE = 'available';
    public const STATUS_OCCUPIED = 'occupied';
    public const STATUS_MAINTENANCE = 'maintenance';

    protected $fillable = [
        'room_type_id',
        'unit_code',
        'floor_label',
        'status',
        'current_booking_id',
        'tenant_name',
        'lease_ends_at',
        'notes',
    ];

    protected $casts = [
        'lease_ends_at' => 'date',
    ];

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function currentBooking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'current_booking_id');
    }
}
