<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class BoardingHouse extends Model
{
    use HasFactory;

    public const UMY_LATITUDE = -7.8113000;
    public const UMY_LONGITUDE = 110.3216000;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING_REVIEW = 'pending_review';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'mitra_id',
        'area_id',
        'name',
        'slug',
        'gender_type',
        'is_syariah',
        'address',
        'latitude',
        'longitude',
        'google_maps_url',
        'distance_to_umy_km',
        'rules',
        'description',
        'nearby_landmarks',
        'contact_name',
        'contact_whatsapp',
        'contact_phone',
        'contact_hours',
        'whatsapp_message_template',
        'status',
        'is_field_verified',
        'field_verified_at',
        'rejection_note',
    ];

    protected $casts = [
        'is_syariah' => 'boolean',
        'is_field_verified' => 'boolean',
        'field_verified_at' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
        'distance_to_umy_km' => 'float',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $boardingHouse) {
            if ($boardingHouse->latitude !== null && $boardingHouse->longitude !== null) {
                $boardingHouse->distance_to_umy_km = $boardingHouse->calculateDistanceToUmy();
            }
        });
    }

    public function calculateDistanceToUmy(): ?float
    {
        if ($this->latitude === null || $this->longitude === null) {
            return null;
        }

        $earthRadiusKm = 6371.0;
        $latFrom = deg2rad($this->latitude);
        $lonFrom = deg2rad($this->longitude);
        $latTo = deg2rad(self::UMY_LATITUDE);
        $lonTo = deg2rad(self::UMY_LONGITUDE);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(
            pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)
        ));

        return round($angle * $earthRadiusKm, 2);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function mitra(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mitra_id');
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(BoardingImage::class)->orderByDesc('is_primary')->orderBy('sort_order');
    }

    public function roomTypes(): HasMany
    {
        return $this->hasMany(RoomType::class);
    }

    public function facilities(): BelongsToMany
    {
        return $this->belongsToMany(Facility::class, 'boarding_facility');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function visibleReviews(): HasMany
    {
        return $this->hasMany(Review::class)->where('is_visible', true);
    }

    public function surveyBookings(): HasMany
    {
        return $this->hasMany(SurveyBooking::class)->latest();
    }

    public function questions(): HasMany
    {
        return $this->hasMany(BoardingQuestion::class)->latest();
    }

    public function visibleQuestions(): HasMany
    {
        return $this->hasMany(BoardingQuestion::class)->where('is_visible', true)->latest();
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(BoardingAnnouncement::class)->orderByDesc('is_pinned')->latest();
    }

    public function promos(): HasMany
    {
        return $this->hasMany(Promo::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(ListingReport::class)->latest();
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(MitraExpense::class)->latest('expense_date');
    }

    public function activePromos()
    {
        return Promo::activeNow()
            ->where('mitra_id', $this->mitra_id)
            ->where(function (Builder $q) {
                $q->whereNull('boarding_house_id')
                    ->orWhere('boarding_house_id', $this->id);
            })
            ->orderByDesc('discount_value')
            ->get();
    }

    public function estimatedWalkingMinutes(): ?int
    {
        if ($this->distance_to_umy_km === null) {
            return null;
        }

        return max(1, (int) round(($this->distance_to_umy_km / 4.5) * 60));
    }

    public function estimatedMotorMinutes(): ?int
    {
        if ($this->distance_to_umy_km === null) {
            return null;
        }

        return max(1, (int) round(($this->distance_to_umy_km / 24.0) * 60));
    }

    public function googleMapsDirectionsUrl(): ?string
    {
        if ($this->latitude !== null && $this->longitude !== null) {
            return 'https://www.google.com/maps/search/?api=1&query=' . $this->latitude . ',' . $this->longitude;
        }

        if (filled($this->google_maps_url)) {
            return trim((string) $this->google_maps_url);
        }

        return null;
    }

    public function landmarkList(): array
    {
        if (! filled($this->nearby_landmarks)) {
            return [];
        }

        return collect(preg_split('/\r\n|\r|\n/', trim((string) $this->nearby_landmarks)))
            ->map(fn ($line) => trim((string) $line))
            ->filter()
            ->values()
            ->all();
    }

    public function completenessScore(): array
    {
        $checks = [
            'Deskripsi lingkungan kos' => filled($this->description),
            'Peraturan penghuni' => filled($this->rules),
            'Koordinat peta UMY' => $this->latitude !== null && $this->longitude !== null,
            'Foto properti kos' => $this->relationLoaded('images') ? $this->images->isNotEmpty() : $this->images()->exists(),
            'Minimal satu tipe kamar' => $this->relationLoaded('roomTypes') ? $this->roomTypes->isNotEmpty() : $this->roomTypes()->exists(),
            'Kontak WhatsApp khusus kos' => filled($this->contact_whatsapp),
            'Penanda lokasi sekitar' => filled($this->nearby_landmarks),
        ];

        $total = count($checks);
        $completed = count(array_filter($checks));
        $missing = array_keys(array_filter($checks, fn ($ok) => ! $ok));

        return [
            'percent' => (int) round(($completed / $total) * 100),
            'completed' => $completed,
            'total' => $total,
            'missing' => $missing,
        ];
    }

    public function lowestMonthlyPrice(): ?int
    {
        return $this->roomTypes()->min('price_monthly');
    }

    public function totalAvailableRooms(): int
    {
        return (int) $this->roomTypes()->sum('available_rooms');
    }

    public function averageRating(): ?float
    {
        $avg = $this->visibleReviews()->avg('rating');

        return $avg !== null ? round((float) $avg, 1) : null;
    }

    public function resolvedContactName(): string
    {
        if (filled($this->contact_name)) {
            return trim((string) $this->contact_name);
        }

        if (filled($this->mitra?->mitraProfile?->business_name)) {
            return 'Pengelola ' . trim((string) $this->mitra->mitraProfile->business_name);
        }

        if (filled($this->mitra?->name)) {
            return trim((string) $this->mitra->name);
        }

        return 'Pengelola ' . $this->name;
    }

    public function resolvedDisplayWhatsapp(): ?string
    {
        $number = $this->contact_whatsapp ?: $this->mitra?->phone;

        return filled($number) ? trim((string) $number) : null;
    }

    public function resolvedDisplayPhone(): ?string
    {
        $number = $this->contact_phone ?: $this->contact_whatsapp ?: $this->mitra?->phone;

        return filled($number) ? trim((string) $number) : null;
    }

    public function resolvedWhatsappDigits(): ?string
    {
        $source = $this->contact_whatsapp ?: $this->contact_phone ?: $this->mitra?->phone;
        $digits = preg_replace('/[^0-9]/', '', (string) $source);

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            return '62' . substr($digits, 1);
        }

        if (str_starts_with($digits, '8')) {
            return '62' . $digits;
        }

        return $digits;
    }

    public function resolvedPhoneHref(): ?string
    {
        $source = $this->resolvedDisplayPhone();
        if (! $source) {
            return null;
        }

        $clean = preg_replace('/[^0-9+]/', '', $source);

        return $clean !== '' ? 'tel:' . $clean : null;
    }

    public function buildWhatsappMessage(?RoomType $room = null, ?User $sender = null): string
    {
        $pengelola = $this->resolvedContactName();
        $area = $this->area?->name ?? 'sekitar UMY';
        $jarak = $this->distance_to_umy_km !== null
            ? number_format($this->distance_to_umy_km, 2, ',', '.') . ' km dari UMY'
            : 'dekat Kampus Terpadu UMY';

        $genderLabel = match ($this->gender_type) {
            'putra' => 'kos putra',
            'putri' => 'kos putri',
            default => 'kos campur',
        };
        if ($this->is_syariah) {
            $genderLabel .= ' syariah';
        }

        $penanya = ($sender && $sender->isPenyewa() && filled($sender->name))
            ? $sender->name . ', mahasiswa UMY'
            : 'mahasiswa UMY';

        $roomPriceFormatted = $room ? 'Rp ' . number_format($room->price_monthly, 0, ',', '.') . '/bulan' : '';
        $roomLabel = $room
            ? (Str::startsWith(strtolower(trim($room->name)), 'tipe ') ? trim($room->name) : 'tipe ' . trim($room->name))
            : 'kamar yang tersedia';

        if (filled($this->whatsapp_message_template)) {
            $template = trim((string) $this->whatsapp_message_template);
            $hasRoomToken = str_contains($template, '{tipe_kamar}');

            $message = str_replace(
                ['{nama_kos}', '{pengelola}', '{area}', '{jarak}', '{tipe_kamar}', '{harga}', '{penanya}'],
                [$this->name, $pengelola, $area, $jarak, $room?->name ?? 'kamar yang tersedia', $roomPriceFormatted, $penanya],
                $template
            );

            if ($room && ! $hasRoomToken) {
                $message .= ' Saya tertarik menanyakan kamar ' . $roomLabel . ' (' . $roomPriceFormatted . ').';
            }

            return $message;
        }

        $salam = $this->is_syariah ? 'Assalamualaikum' : 'Halo';

        if ($room) {
            $electricInfo = $room->electricity_included ? 'sudah termasuk listrik' : 'listrik token mandiri';
            $detailKamar = ' Khususnya saya tertarik pada kamar ' . $roomLabel . ' (' . $roomPriceFormatted . ', ' . $electricInfo . ').';
        } else {
            $avail = $this->relationLoaded('roomTypes')
                ? (int) $this->roomTypes->sum('available_rooms')
                : $this->totalAvailableRooms();
            $minPrice = $this->relationLoaded('roomTypes')
                ? $this->roomTypes->min('price_monthly')
                : $this->lowestMonthlyPrice();

            if ($avail > 0 && $minPrice) {
                $detailKamar = ' Di LokaKos tertulis masih ada ' . $avail . ' kamar tersedia mulai Rp ' . number_format((int) $minPrice, 0, ',', '.') . '/bulan.';
            } else {
                $detailKamar = '';
            }
        }

        $variants = [
            "{$salam} {$pengelola}, perkenalkan saya {$penanya} yang sedang mencari {$genderLabel} di area {$area}. Saya melihat {$this->name} ({$jarak}) di LokaKos dan tertarik dengan suasananya.{$detailKamar} Apakah masih ada unit kosong untuk waktu dekat, dan bolehkah saya menjadwalkan survei lokasi? Terima kasih banyak.",
            "{$salam} {$pengelola}, saya {$penanya} baru menemukan listing {$this->name} di LokaKos (area {$area}, {$jarak}).{$detailKamar} Boleh minta info tipe kamar yang paling siap huni saat ini serta ketentuan pembayaran awalnya? Terima kasih sebelumnya.",
            "Permisi {$pengelola}, salam kenal dari saya {$penanya}. Saat ini saya sedang memilih {$genderLabel} di sekitar {$area} lewat LokaKos, dan {$this->name} masuk pilihan utama saya.{$detailKamar} Kapan waktu yang nyaman jika saya ingin mampir melihat kamar secara langsung? Terima kasih.",
            "{$salam} {$pengelola}, saya {$penanya}. Saya tertarik dengan {$this->name} di {$area} ({$jarak}) yang tampil di LokaKos karena lokasinya praktis menuju kampus.{$detailKamar} Mohon info ketersediaan kamar serta aturan penghuni terbarunya. Terima kasih atas waktunya.",
            "{$salam} {$pengelola}, saya {$penanya} mendapatkan kontak {$this->name} ({$area}) dari LokaKos.{$detailKamar} Rencananya saya sedang mencari kamar untuk semester mendatang. Apakah masih tersedia kamar kosong dan bagaimana alur survei atau bookingnya? Terima kasih banyak.",
            "{$salam} {$pengelola}, izin bertanya mengenai {$this->name} di wilayah {$area} ({$jarak}) yang saya temukan di LokaKos.{$detailKamar} Apakah saat ini masih menerima penghuni baru dan bolehkah saya berkunjung untuk survei kamar terlebih dahulu? Terima kasih atas bantuannya.",
        ];

        $index = abs((int) ($this->id ?? crc32((string) $this->name))) % count($variants);

        return $variants[$index];
    }

    public function whatsappUrl(?RoomType $room = null, ?User $sender = null): ?string
    {
        $digits = $this->resolvedWhatsappDigits();
        if (! $digits) {
            return null;
        }

        return 'https://wa.me/' . $digits . '?text=' . rawurlencode($this->buildWhatsappMessage($room, $sender));
    }
}
