<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\BoardingHouse;
use App\Models\Facility;
use App\Models\RoomWaitlist;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KosController extends Controller
{
    public function index(Request $request): View
    {
        $query = BoardingHouse::published()
            ->with(['area', 'images', 'roomTypes', 'facilities'])
            ->withMin('roomTypes as min_monthly_price', 'price_monthly')
            ->withSum('roomTypes as total_available_rooms', 'available_rooms')
            ->withAvg(['reviews as visible_rating_avg' => fn ($q) => $q->where('is_visible', true)], 'rating');

        if ($search = trim((string) $request->query('q', ''))) {
            $query->where(function ($sub) use ($search) {
                $sub->where('name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhereHas('area', fn ($areaQ) => $areaQ->where('name', 'like', "%{$search}%"));
            });
        }

        if ($areaSlug = $request->query('area')) {
            $query->whereHas('area', fn ($q) => $q->where('slug', $areaSlug));
        }

        if (in_array($request->query('gender'), ['putra', 'putri', 'campur'], true)) {
            $query->where('gender_type', $request->query('gender'));
        }

        if ($request->boolean('syariah')) {
            $query->where('is_syariah', true);
        }

        if ($request->boolean('verified')) {
            $query->where('is_field_verified', true);
        }

        if ($request->boolean('electricity_included')) {
            $query->whereHas('roomTypes', fn ($q) => $q->where('electricity_included', true));
        }

        $minPrice = is_numeric($request->query('min_price')) ? (int) $request->query('min_price') : null;
        $maxPrice = is_numeric($request->query('max_price')) ? (int) $request->query('max_price') : null;

        if ($minPrice !== null || $maxPrice !== null) {
            $query->whereHas('roomTypes', function ($q) use ($minPrice, $maxPrice) {
                if ($minPrice !== null && $minPrice > 0) {
                    $q->where('price_monthly', '>=', $minPrice);
                }
                if ($maxPrice !== null && $maxPrice > 0) {
                    $q->where('price_monthly', '<=', $maxPrice);
                }
            });
        }

        $selectedFacilities = array_filter(
            array_map('intval', (array) $request->query('facilities', []))
        );

        foreach ($selectedFacilities as $facilityId) {
            $query->where(function ($q) use ($facilityId) {
                $q->whereHas('facilities', fn ($f) => $f->where('facilities.id', $facilityId))
                    ->orWhereHas('roomTypes.facilities', fn ($f) => $f->where('facilities.id', $facilityId));
            });
        }

        $sort = $request->query('sort', 'nearest');

        match ($sort) {
            'cheapest' => $query->orderBy('min_monthly_price'),
            'latest' => $query->latest(),
            default => $query->orderByRaw('CASE WHEN distance_to_umy_km IS NULL THEN 1 ELSE 0 END')
                ->orderBy('distance_to_umy_km'),
        };

        $mapPins = (clone $query)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->take(50)
            ->get()
            ->map(fn (BoardingHouse $kos) => [
                'id' => $kos->id,
                'name' => $kos->name,
                'area' => $kos->area?->name ?? 'UMY',
                'gender' => ucfirst($kos->gender_type),
                'syariah' => $kos->is_syariah,
                'verified' => $kos->is_field_verified,
                'lat' => $kos->latitude,
                'lng' => $kos->longitude,
                'distance' => $kos->distance_to_umy_km,
                'price' => (int) ($kos->min_monthly_price ?? 0),
                'available' => (int) ($kos->total_available_rooms ?? 0),
                'url' => route('kos.show', $kos),
            ])
            ->values();

        $boardingHouses = $query->paginate(9)->withQueryString();
        $areas = Area::orderBy('name')->get();
        $facilities = Facility::orderBy('category')->orderBy('name')->get();

        return view('kos.index', [
            'boardingHouses' => $boardingHouses,
            'mapPins' => $mapPins,
            'areas' => $areas,
            'facilities' => $facilities,
            'selectedFacilities' => $selectedFacilities,
            'filters' => [
                'q' => $request->query('q', ''),
                'area' => $request->query('area', ''),
                'gender' => $request->query('gender', ''),
                'syariah' => $request->boolean('syariah'),
                'verified' => $request->boolean('verified'),
                'electricity_included' => $request->boolean('electricity_included'),
                'min_price' => $request->query('min_price', ''),
                'max_price' => $request->query('max_price', ''),
                'sort' => $sort,
            ],
        ]);
    }

    public function show(Request $request, BoardingHouse $boardingHouse): View
    {
        if ($boardingHouse->status !== BoardingHouse::STATUS_PUBLISHED) {
            abort(404);
        }

        $boardingHouse->load([
            'area',
            'mitra.mitraProfile',
            'images',
            'facilities',
            'roomTypes.facilities',
            'visibleReviews.user',
            'visibleQuestions.user',
            'visibleQuestions.answerer',
        ]);

        $sender = $request->user();
        $isSaved = false;
        $waitlistedRoomIds = [];

        if ($sender?->isPenyewa()) {
            $isSaved = $sender
                ->savedBoardingHouses()
                ->where('boarding_house_id', $boardingHouse->id)
                ->exists();

            $waitlistedRoomIds = RoomWaitlist::where('user_id', $sender->id)
                ->whereIn('room_type_id', $boardingHouse->roomTypes->pluck('id'))
                ->where('status', RoomWaitlist::STATUS_WAITING)
                ->pluck('room_type_id')
                ->all();
        }

        return view('kos.show', [
            'kos' => $boardingHouse,
            'isSaved' => $isSaved,
            'activePromos' => $boardingHouse->activePromos(),
            'waitlistedRoomIds' => $waitlistedRoomIds,
            'whatsappUrl' => $boardingHouse->whatsappUrl(null, $sender),
            'whatsappMessagePreview' => $boardingHouse->buildWhatsappMessage(null, $sender),
            'contactName' => $boardingHouse->resolvedContactName(),
            'displayWhatsapp' => $boardingHouse->resolvedDisplayWhatsapp(),
            'displayPhone' => $boardingHouse->resolvedDisplayPhone(),
            'phoneHref' => $boardingHouse->resolvedPhoneHref(),
        ]);
    }
}
