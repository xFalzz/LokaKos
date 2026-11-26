<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Models\BoardingHouse;
use App\Models\Facility;
use App\Models\RoomType;
use App\Models\RoomWaitlist;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class RoomTypeController extends Controller
{
    public function index(BoardingHouse $boardingHouse): View
    {
        $this->authorize('update', $boardingHouse);

        $boardingHouse->load(['area', 'roomTypes.facilities', 'roomTypes.units']);

        return view('mitra.rooms.index', [
            'kos' => $boardingHouse,
            'facilities' => Facility::where('category', Facility::CATEGORY_KAMAR)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, BoardingHouse $boardingHouse): RedirectResponse
    {
        $this->authorize('update', $boardingHouse);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'size_m2' => ['nullable', 'string', 'max:30'],
            'price_monthly' => ['required', 'integer', 'min:100000'],
            'price_3_months' => ['nullable', 'integer', 'min:100000'],
            'price_6_months' => ['nullable', 'integer', 'min:100000'],
            'price_yearly' => ['nullable', 'integer', 'min:100000'],
            'deposit_amount' => ['nullable', 'integer', 'min:0'],
            'electricity_included' => ['nullable', 'boolean'],
            'max_occupancy' => ['required', 'integer', 'min:1', 'max:4'],
            'total_rooms' => ['required', 'integer', 'min:1'],
            'available_rooms' => ['required', 'integer', 'min:0', 'lte:total_rooms'],
            'description' => ['nullable', 'string', 'max:2000'],
            'room_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'facilities' => ['nullable', 'array'],
            'facilities.*' => ['integer', 'exists:facilities,id'],
        ]);

        $imagePath = null;
        if ($request->hasFile('room_image')) {
            $imagePath = $request->file('room_image')->store('room_images', 'public');
        }

        $room = RoomType::create([
            'boarding_house_id' => $boardingHouse->id,
            'name' => $validated['name'],
            'size_m2' => $validated['size_m2'] ?? null,
            'price_monthly' => $validated['price_monthly'],
            'price_3_months' => $validated['price_3_months'] ?? null,
            'price_6_months' => $validated['price_6_months'] ?? null,
            'price_yearly' => $validated['price_yearly'] ?? null,
            'deposit_amount' => $validated['deposit_amount'] ?? 0,
            'electricity_included' => $request->boolean('electricity_included'),
            'max_occupancy' => (int) $validated['max_occupancy'],
            'total_rooms' => $validated['total_rooms'],
            'available_rooms' => $validated['available_rooms'],
            'description' => $validated['description'] ?? null,
            'room_image_path' => $imagePath,
        ]);

        $room->facilities()->sync($validated['facilities'] ?? []);

        return redirect()
            ->route('mitra.rooms.index', $boardingHouse)
            ->with('status', 'Tipe kamar baru berhasil ditambahkan.');
    }

    public function edit(BoardingHouse $boardingHouse, RoomType $roomType): View
    {
        $this->authorize('update', $boardingHouse);
        abort_if($roomType->boarding_house_id !== $boardingHouse->id, 404);

        $roomType->load('facilities');

        return view('mitra.rooms.edit', [
            'kos' => $boardingHouse,
            'room' => $roomType,
            'facilities' => Facility::where('category', Facility::CATEGORY_KAMAR)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, BoardingHouse $boardingHouse, RoomType $roomType): RedirectResponse
    {
        $this->authorize('update', $boardingHouse);
        abort_if($roomType->boarding_house_id !== $boardingHouse->id, 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'size_m2' => ['nullable', 'string', 'max:30'],
            'price_monthly' => ['required', 'integer', 'min:100000'],
            'price_3_months' => ['nullable', 'integer', 'min:100000'],
            'price_6_months' => ['nullable', 'integer', 'min:100000'],
            'price_yearly' => ['nullable', 'integer', 'min:100000'],
            'deposit_amount' => ['nullable', 'integer', 'min:0'],
            'electricity_included' => ['nullable', 'boolean'],
            'max_occupancy' => ['required', 'integer', 'min:1', 'max:4'],
            'total_rooms' => ['required', 'integer', 'min:1'],
            'available_rooms' => ['required', 'integer', 'min:0', 'lte:total_rooms'],
            'description' => ['nullable', 'string', 'max:2000'],
            'room_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'facilities' => ['nullable', 'array'],
            'facilities.*' => ['integer', 'exists:facilities,id'],
        ]);

        $imagePath = $roomType->room_image_path;
        if ($request->hasFile('room_image')) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $request->file('room_image')->store('room_images', 'public');
        }

        $previousStock = $roomType->available_rooms;

        $roomType->update([
            'name' => $validated['name'],
            'size_m2' => $validated['size_m2'] ?? null,
            'price_monthly' => $validated['price_monthly'],
            'price_3_months' => $validated['price_3_months'] ?? null,
            'price_6_months' => $validated['price_6_months'] ?? null,
            'price_yearly' => $validated['price_yearly'] ?? null,
            'deposit_amount' => $validated['deposit_amount'] ?? 0,
            'electricity_included' => $request->boolean('electricity_included'),
            'max_occupancy' => (int) $validated['max_occupancy'],
            'total_rooms' => $validated['total_rooms'],
            'available_rooms' => $validated['available_rooms'],
            'description' => $validated['description'] ?? null,
            'room_image_path' => $imagePath,
        ]);

        $roomType->facilities()->sync($validated['facilities'] ?? []);

        if ($previousStock === 0 && (int) $validated['available_rooms'] > 0) {
            $this->notifyWaitlist($boardingHouse, $roomType);
        }

        return redirect()
            ->route('mitra.rooms.index', $boardingHouse)
            ->with('status', 'Data tipe kamar berhasil diperbarui.');
    }

    public function adjustStock(Request $request, BoardingHouse $boardingHouse, RoomType $roomType): RedirectResponse
    {
        $this->authorize('update', $boardingHouse);
        abort_if($roomType->boarding_house_id !== $boardingHouse->id, 404);

        $validated = $request->validate([
            'delta' => ['required', 'integer', 'in:-1,1'],
        ]);

        $previousStock = $roomType->available_rooms;
        $nextStock = $previousStock + (int) $validated['delta'];

        if ($nextStock < 0 || $nextStock > $roomType->total_rooms) {
            return back()->with('error', 'Jumlah kamar tersedia harus berada di antara 0 dan total unit kamar.');
        }

        $roomType->update(['available_rooms' => $nextStock]);

        if ($previousStock === 0 && $nextStock > 0) {
            $this->notifyWaitlist($boardingHouse, $roomType);
        }

        return back()->with('status', "Stok {$roomType->name} diperbarui menjadi {$nextStock} dari {$roomType->total_rooms} kamar.");
    }

    private function notifyWaitlist(BoardingHouse $boardingHouse, RoomType $roomType): void
    {
        $waiters = RoomWaitlist::where('room_type_id', $roomType->id)
            ->where('status', RoomWaitlist::STATUS_WAITING)
            ->get();

        foreach ($waiters as $waiter) {
            $waiter->update([
                'status' => RoomWaitlist::STATUS_NOTIFIED,
                'notified_at' => now(),
            ]);

            NotificationService::notify(
                $waiter->user_id,
                'waitlist_available',
                'Kamar Incaran Kini Tersedia',
                $roomType->name . ' di ' . $boardingHouse->name . ' kini kembali memiliki stok kamar tersedia.',
                route('kos.show', $boardingHouse)
            );
        }
    }

    public function destroy(BoardingHouse $boardingHouse, RoomType $roomType): RedirectResponse
    {
        $this->authorize('update', $boardingHouse);
        abort_if($roomType->boarding_house_id !== $boardingHouse->id, 404);

        if ($roomType->bookings()->exists()) {
            return back()->with('error', 'Tipe kamar tidak dapat dihapus karena sudah memiliki riwayat pemesanan.');
        }

        if ($roomType->room_image_path) {
            Storage::disk('public')->delete($roomType->room_image_path);
        }

        $roomType->delete();

        return redirect()
            ->route('mitra.rooms.index', $boardingHouse)
            ->with('status', 'Tipe kamar berhasil dihapus.');
    }
}
