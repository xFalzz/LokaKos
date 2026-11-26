<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\BoardingHouse;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $areas = Area::withCount([
            'boardingHouses as published_count' => fn ($query) => $query->published(),
        ])->orderBy('name')->get();

        $featuredKos = BoardingHouse::published()
            ->with(['area', 'images', 'roomTypes', 'facilities'])
            ->withAvg(['reviews as visible_rating_avg' => fn ($q) => $q->where('is_visible', true)], 'rating')
            ->orderByRaw('CASE WHEN distance_to_umy_km IS NULL THEN 1 ELSE 0 END')
            ->orderBy('distance_to_umy_km')
            ->take(6)
            ->get();

        return view('home', [
            'areas' => $areas,
            'featuredKos' => $featuredKos,
        ]);
    }
}
