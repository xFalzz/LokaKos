<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Promo extends Model
{
    use HasFactory;

    public const TYPE_FIXED = 'fixed';
    public const TYPE_PERCENT = 'percent';

    protected $fillable = [
        'mitra_id',
        'boarding_house_id',
        'code',
        'title',
        'discount_type',
        'discount_value',
        'min_duration_months',
        'valid_from',
        'valid_until',
        'is_active',
    ];

    protected $casts = [
        'discount_value' => 'integer',
        'min_duration_months' => 'integer',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'is_active' => 'boolean',
    ];

    public function mitra(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mitra_id');
    }

    public function boardingHouse(): BelongsTo
    {
        return $this->belongsTo(BoardingHouse::class);
    }

    public function scopeActiveNow(Builder $query): Builder
    {
        $today = now()->toDateString();

        return $query->where('is_active', true)
            ->whereDate('valid_from', '<=', $today)
            ->whereDate('valid_until', '>=', $today);
    }

    public function calculateDiscount(int $baseAmount, int $durationMonths): int
    {
        if (! $this->is_active || $durationMonths < $this->min_duration_months) {
            return 0;
        }

        $today = now()->startOfDay();
        if ($this->valid_from->gt($today) || $this->valid_until->lt($today)) {
            return 0;
        }

        if ($this->discount_type === self::TYPE_PERCENT) {
            $pct = min(80, max(1, $this->discount_value));

            return (int) round($baseAmount * ($pct / 100));
        }

        return min($baseAmount, max(0, $this->discount_value));
    }

    public function badgeLabel(): string
    {
        if ($this->discount_type === self::TYPE_PERCENT) {
            return 'Diskon ' . $this->discount_value . '% (Kode: ' . $this->code . ')';
        }

        return 'Potongan Rp ' . number_format($this->discount_value, 0, ',', '.') . ' (Kode: ' . $this->code . ')';
    }
}
