<?php

namespace App\Http\Controllers\Penyewa;

use App\Http\Controllers\Controller;
use App\Models\BoardingHouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SavedKosController extends Controller
{
    public function index(Request $request): View
    {
        $savedKos = $request->user()
            ->savedBoardingHouses()
            ->published()
            ->with(['area', 'images', 'roomTypes', 'facilities'])
            ->withMin('roomTypes as min_monthly_price', 'price_monthly')
            ->withSum('roomTypes as total_available_rooms', 'available_rooms')
            ->latest('saved_boarding_houses.created_at')
            ->get();

        return view('penyewa.saved.index', [
            'savedKos' => $savedKos,
        ]);
    }

    public function toggle(Request $request, BoardingHouse $boardingHouse): RedirectResponse
    {
        if ($boardingHouse->status !== BoardingHouse::STATUS_PUBLISHED) {
            abort(404);
        }

        $user = $request->user();
        $exists = $user->savedBoardingHouses()->where('boarding_house_id', $boardingHouse->id)->exists();

        if ($exists) {
            $user->savedBoardingHouses()->detach($boardingHouse->id);

            return back()->with('status', "{$boardingHouse->name} dihapus dari daftar kos tersimpan.");
        }

        $user->savedBoardingHouses()->attach($boardingHouse->id);

        return back()->with('status', "{$boardingHouse->name} disimpan ke daftar favorit Anda.");
    }
}
