<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BoardingQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'boarding_house_id',
        'user_id',
        'question',
        'answer',
        'answered_by',
        'answered_at',
        'is_visible',
    ];

    protected $casts = [
        'answered_at' => 'datetime',
        'is_visible' => 'boolean',
    ];

    public function boardingHouse(): BelongsTo
    {
        return $this->belongsTo(BoardingHouse::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function answerer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'answered_by');
    }
}
