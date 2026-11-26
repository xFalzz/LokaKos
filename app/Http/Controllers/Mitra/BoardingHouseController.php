<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\BoardingHouse;
use App\Models\BoardingImage;
use App\Models\Facility;
use App\Services\ActivityLogger;
use App\Services\GoogleMapsResolver;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BoardingHouseController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->load('mitraProfile');

        $boardingHouses = BoardingHouse::with(['area', 'roomTypes', 'images'])
            ->where('mitra_id', $user->id)
            ->latest()
            ->get();

        return view('mitra.kos.index', [
            'user' => $user,
            'boardingHouses' => $boardingHouses,
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        if (! $request->user()->can('create', BoardingHouse::class)) {
            return redirect()->route('mitra.kos.index')->with(
                'error',
                'Akun Mitra Anda belum disetujui oleh Admin. Lengkapi profil Mitra dan tunggu verifikasi sebelum menambahkan kos.'
            );
        }

        return view('mitra.kos.create', [
            'areas' => Area::orderBy('name')->get(),
            'facilities' => Facility::where('category', Facility::CATEGORY_KOS)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', BoardingHouse::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'area_id' => ['required', 'exists:areas,id'],
            'gender_type' => ['required', 'in:putra,putri,campur'],
            'is_syariah' => ['nullable', 'boolean'],
            'address' => ['required', 'string', 'max:1000'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'google_maps_url' => ['nullable', 'string', 'max:500'],
            'rules' => ['nullable', 'string', 'max:3000'],
            'description' => ['nullable', 'string', 'max:3000'],
            'nearby_landmarks' => ['nullable', 'string', 'max:1500'],
            'contact_name' => ['nullable', 'string', 'max:100'],
            'contact_whatsapp' => ['nullable', 'string', 'max:25', 'regex:/^[0-9+\-\s()]+$/'],
            'contact_phone' => ['nullable', 'string', 'max:25', 'regex:/^[0-9+\-\s()]+$/'],
            'contact_hours' => ['nullable', 'string', 'max:80'],
            'whatsapp_message_template' => ['nullable', 'string', 'max:800'],
            'facilities' => ['nullable', 'array'],
            'facilities.*' => ['integer', 'exists:facilities,id'],
            'images' => ['nullable', 'array', 'max:6'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $lat = $validated['latitude'] ?? null;
        $lng = $validated['longitude'] ?? null;
        $gmapsUrl = isset($validated['google_maps_url']) ? trim((string) $validated['google_maps_url']) : null;

        if (($lat === null || $lat === '' || $lng === null || $lng === '') && filled($gmapsUrl)) {
            $resolved = app(GoogleMapsResolver::class)->resolve($gmapsUrl);
            if ($resolved) {
                $lat = $resolved['lat'];
                $lng = $resolved['lng'];
            }
        }

        $boardingHouse = DB::transaction(function () use ($request, $validated, $lat, $lng, $gmapsUrl) {
            $baseSlug = Str::slug($validated['name']);
            $slug = $baseSlug;
            $counter = 1;
            while (BoardingHouse::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . (++$counter);
            }

            $kos = BoardingHouse::create([
                'mitra_id' => $request->user()->id,
                'area_id' => $validated['area_id'],
                'name' => $validated['name'],
                'slug' => $slug,
                'gender_type' => $validated['gender_type'],
                'is_syariah' => $request->boolean('is_syariah'),
                'address' => $validated['address'],
                'latitude' => $lat !== '' ? $lat : null,
                'longitude' => $lng !== '' ? $lng : null,
                'google_maps_url' => filled($gmapsUrl) ? $gmapsUrl : null,
                'rules' => $validated['rules'] ?? null,
                'description' => $validated['description'] ?? null,
                'nearby_landmarks' => $validated['nearby_landmarks'] ?? null,
                'contact_name' => $validated['contact_name'] ?? null,
                'contact_whatsapp' => $validated['contact_whatsapp'] ?? null,
                'contact_phone' => $validated['contact_phone'] ?? null,
                'contact_hours' => $validated['contact_hours'] ?? null,
                'whatsapp_message_template' => $validated['whatsapp_message_template'] ?? null,
                'status' => BoardingHouse::STATUS_PENDING_REVIEW,
            ]);

            $kos->facilities()->sync($validated['facilities'] ?? []);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $file) {
                    $path = $file->store('boarding_images', 'public');
                    BoardingImage::create([
                        'boarding_house_id' => $kos->id,
                        'image_path' => $path,
                        'is_primary' => $index === 0,
                        'sort_order' => $index,
                    ]);
                }
            }

            return $kos;
        });

        ActivityLogger::log('kos.created', 'Mitra menambahkan properti baru: ' . $boardingHouse->name, $boardingHouse);
        NotificationService::notifyAdmins(
            'kos_submitted',
            'Pengajuan Listing Kos Baru',
            $boardingHouse->name . ' menunggu moderasi Admin.',
            route('admin.kos.index', ['status' => BoardingHouse::STATUS_PENDING_REVIEW])
        );

        return redirect()
            ->route('mitra.rooms.index', $boardingHouse)
            ->with('status', 'Data kos tersimpan dengan status Menunggu Moderasi Admin. Tambahkan minimal satu tipe kamar sekarang.');
    }

    public function edit(BoardingHouse $boardingHouse): View
    {
        $this->authorize('update', $boardingHouse);

        $boardingHouse->load(['area', 'mitra.mitraProfile', 'roomTypes', 'facilities', 'images']);

        return view('mitra.kos.edit', [
            'kos' => $boardingHouse,
            'areas' => Area::orderBy('name')->get(),
            'facilities' => Facility::where('category', Facility::CATEGORY_KOS)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, BoardingHouse $boardingHouse): RedirectResponse
    {
        $this->authorize('update', $boardingHouse);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'area_id' => ['required', 'exists:areas,id'],
            'gender_type' => ['required', 'in:putra,putri,campur'],
            'is_syariah' => ['nullable', 'boolean'],
            'address' => ['required', 'string', 'max:1000'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'google_maps_url' => ['nullable', 'string', 'max:500'],
            'rules' => ['nullable', 'string', 'max:3000'],
            'description' => ['nullable', 'string', 'max:3000'],
            'nearby_landmarks' => ['nullable', 'string', 'max:1500'],
            'contact_name' => ['nullable', 'string', 'max:100'],
            'contact_whatsapp' => ['nullable', 'string', 'max:25', 'regex:/^[0-9+\-\s()]+$/'],
            'contact_phone' => ['nullable', 'string', 'max:25', 'regex:/^[0-9+\-\s()]+$/'],
            'contact_hours' => ['nullable', 'string', 'max:80'],
            'whatsapp_message_template' => ['nullable', 'string', 'max:800'],
            'facilities' => ['nullable', 'array'],
            'facilities.*' => ['integer', 'exists:facilities,id'],
            'images' => ['nullable', 'array', 'max:6'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'resubmit' => ['nullable', 'boolean'],
        ]);

        $lat = $validated['latitude'] ?? null;
        $lng = $validated['longitude'] ?? null;
        $gmapsUrl = isset($validated['google_maps_url']) ? trim((string) $validated['google_maps_url']) : null;

        if (($lat === null || $lat === '' || $lng === null || $lng === '') && filled($gmapsUrl)) {
            $resolved = app(GoogleMapsResolver::class)->resolve($gmapsUrl);
            if ($resolved) {
                $lat = $resolved['lat'];
                $lng = $resolved['lng'];
            }
        }

        DB::transaction(function () use ($request, $boardingHouse, $validated, $lat, $lng, $gmapsUrl) {
            $nextStatus = $boardingHouse->status;
            $rejectionNote = $boardingHouse->rejection_note;

            if ($boardingHouse->status === BoardingHouse::STATUS_REJECTED && $request->boolean('resubmit')) {
                $nextStatus = BoardingHouse::STATUS_PENDING_REVIEW;
                $rejectionNote = null;
            }

            $boardingHouse->update([
                'area_id' => $validated['area_id'],
                'name' => $validated['name'],
                'gender_type' => $validated['gender_type'],
                'is_syariah' => $request->boolean('is_syariah'),
                'address' => $validated['address'],
                'latitude' => $lat !== '' ? $lat : null,
                'longitude' => $lng !== '' ? $lng : null,
                'google_maps_url' => filled($gmapsUrl) ? $gmapsUrl : null,
                'rules' => $validated['rules'] ?? null,
                'description' => $validated['description'] ?? null,
                'nearby_landmarks' => $validated['nearby_landmarks'] ?? null,
                'contact_name' => $validated['contact_name'] ?? null,
                'contact_whatsapp' => $validated['contact_whatsapp'] ?? null,
                'contact_phone' => $validated['contact_phone'] ?? null,
                'contact_hours' => $validated['contact_hours'] ?? null,
                'whatsapp_message_template' => $validated['whatsapp_message_template'] ?? null,
                'status' => $nextStatus,
                'rejection_note' => $rejectionNote,
            ]);

            $boardingHouse->facilities()->sync($validated['facilities'] ?? []);

            if ($request->hasFile('images')) {
                $hasPrimary = $boardingHouse->images()->where('is_primary', true)->exists();
                $order = (int) $boardingHouse->images()->max('sort_order');

                foreach ($request->file('images') as $index => $file) {
                    $path = $file->store('boarding_images', 'public');
                    BoardingImage::create([
                        'boarding_house_id' => $boardingHouse->id,
                        'image_path' => $path,
                        'is_primary' => ! $hasPrimary && $index === 0,
                        'sort_order' => ++$order,
                    ]);
                }
            }
        });

        return redirect()
            ->route('mitra.kos.edit', $boardingHouse)
            ->with('status', 'Perubahan data kos berhasil disimpan.');
    }

    public function destroy(BoardingHouse $boardingHouse): RedirectResponse
    {
        $this->authorize('delete', $boardingHouse);

        foreach ($boardingHouse->images as $image) {
            Storage::disk('public')->delete($image->image_path);
        }

        $boardingHouse->delete();

        return redirect()
            ->route('mitra.kos.index')
            ->with('status', 'Properti kos berhasil dihapus.');
    }

    public function destroyImage(BoardingHouse $boardingHouse, BoardingImage $image): RedirectResponse
    {
        $this->authorize('update', $boardingHouse);

        if ($image->boarding_house_id !== $boardingHouse->id) {
            abort(404);
        }

        Storage::disk('public')->delete($image->image_path);
        $wasPrimary = $image->is_primary;
        $image->delete();

        if ($wasPrimary) {
            $boardingHouse->images()->first()?->update(['is_primary' => true]);
        }

        return back()->with('status', 'Foto kos dihapus.');
    }

    public function resolveMapLocation(Request $request, GoogleMapsResolver $resolver): JsonResponse
    {
        $validated = $request->validate([
            'input' => ['required', 'string', 'max:500'],
        ]);

        $result = $resolver->resolve($validated['input']);

        if (! $result) {
            return response()->json([
                'ok' => false,
                'message' => 'Lokasi tidak dapat dibaca. Tempel link Google Maps (termasuk maps.app.goo.gl), koordinat seperti -7.8138, 110.3198, atau ketik alamat lengkap beserta kelurahan/kecamatan.',
            ], 422);
        }

        return response()->json([
            'ok' => true,
            ...$result,
        ]);
    }
}
