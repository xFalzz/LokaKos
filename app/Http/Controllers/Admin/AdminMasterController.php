<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Booking;
use App\Models\Facility;
use App\Models\Review;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminMasterController extends Controller
{
    public function masterIndex(): View
    {
        return view('admin.master.index', [
            'areas' => Area::withCount('boardingHouses')->orderBy('name')->get(),
            'facilities' => Facility::orderBy('category')->orderBy('name')->get(),
        ]);
    }

    public function storeArea(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:areas,name'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        Area::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
        ]);

        return back()->with('status', "Area {$validated['name']} berhasil ditambahkan.");
    }

    public function updateArea(Request $request, Area $area): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:areas,name,' . $area->id],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $area->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
        ]);

        return back()->with('status', "Area {$area->name} berhasil diperbarui.");
    }

    public function destroyArea(Area $area): RedirectResponse
    {
        if ($area->boardingHouses()->exists()) {
            return back()->with('error', 'Area tidak dapat dihapus karena masih digunakan oleh properti kos.');
        }

        $area->delete();

        return back()->with('status', 'Area berhasil dihapus.');
    }

    public function storeFacility(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'category' => ['required', 'in:kos,kamar'],
        ]);

        $exists = Facility::where('name', $validated['name'])
            ->where('category', $validated['category'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'Fasilitas dengan nama dan kategori tersebut sudah tersedia.');
        }

        Facility::create([
            'name' => $validated['name'],
            'category' => $validated['category'],
        ]);

        return back()->with('status', "Fasilitas {$validated['name']} berhasil ditambahkan.");
    }

    public function destroyFacility(Facility $facility): RedirectResponse
    {
        $facility->delete();

        return back()->with('status', 'Fasilitas berhasil dihapus.');
    }

    public function monitoring(Request $request): View
    {
        $bookings = Booking::with(['user', 'roomType.boardingHouse.mitra', 'payment'])
            ->latest()
            ->paginate(12, ['*'], 'bookings_page');

        $users = User::with('mitraProfile')
            ->orderBy('role')
            ->orderBy('name')
            ->get();

        $reviews = Review::with(['user', 'boardingHouse'])
            ->latest()
            ->take(20)
            ->get();

        return view('admin.monitoring.index', [
            'bookings' => $bookings,
            'users' => $users,
            'reviews' => $reviews,
        ]);
    }

    public function toggleUser(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Admin yang sedang digunakan.');
        }

        $user->update(['is_active' => ! $user->is_active]);

        $label = $user->is_active ? 'diaktifkan kembali' : 'dinonaktifkan';

        return back()->with('status', "Akun {$user->name} berhasil {$label}.");
    }

    public function toggleReview(Review $review): RedirectResponse
    {
        $review->update(['is_visible' => ! $review->is_visible]);

        $label = $review->is_visible ? 'ditampilkan' : 'disembunyikan';

        return back()->with('status', "Ulasan berhasil {$label}.");
    }

    public function reportPdf(Request $request): Response
    {
        $from = $request->query('from');
        $to = $request->query('to');

        $query = Booking::with(['user', 'roomType.boardingHouse.mitra', 'payment'])
            ->latest();

        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        $bookings = $query->get();

        $pdf = Pdf::loadView('pdf.admin-report', [
            'bookings' => $bookings,
            'confirmedTotal' => $bookings
                ->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_COMPLETED])
                ->sum('total_price'),
            'from' => $from,
            'to' => $to,
        ]);

        return $pdf->download('laporan-transaksi-platform-lokakos-' . now()->format('Ymd') . '.pdf');
    }
}
