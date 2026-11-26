<?php

namespace App\Http\Controllers\Penyewa;

use App\Http\Controllers\Controller;
use App\Models\BoardingHouse;
use App\Models\SurveyBooking;
use App\Services\ActivityLogger;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SurveyController extends Controller
{
    public function index(Request $request): View
    {
        $surveys = SurveyBooking::with(['boardingHouse.area', 'roomType'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(12);

        return view('penyewa.surveys.index', [
            'surveys' => $surveys,
        ]);
    }

    public function store(Request $request, BoardingHouse $boardingHouse): RedirectResponse
    {
        if ($boardingHouse->status !== BoardingHouse::STATUS_PUBLISHED) {
            abort(404);
        }

        $validated = $request->validate([
            'room_type_id' => ['nullable', 'exists:room_types,id'],
            'preferred_date' => ['required', 'date', 'after_or_equal:today'],
            'preferred_time' => ['required', 'string', 'max:30'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        if (! empty($validated['room_type_id'])) {
            $belongs = $boardingHouse->roomTypes()->where('id', $validated['room_type_id'])->exists();
            if (! $belongs) {
                return back()->with('error', 'Tipe kamar tidak sesuai dengan properti kos ini.');
            }
        }

        $survey = SurveyBooking::create([
            'user_id' => $request->user()->id,
            'boarding_house_id' => $boardingHouse->id,
            'room_type_id' => $validated['room_type_id'] ?? null,
            'preferred_date' => $validated['preferred_date'],
            'preferred_time' => $validated['preferred_time'],
            'notes' => $validated['notes'] ?? null,
            'status' => SurveyBooking::STATUS_PENDING,
        ]);

        ActivityLogger::log(
            'survey.created',
            $request->user()->name . ' mengajukan survei ke ' . $boardingHouse->name . ' pada ' . $survey->preferred_date->format('d/m/Y') . '.',
            $survey
        );

        NotificationService::notify(
            $boardingHouse->mitra_id,
            'survey_requested',
            'Pengajuan Survei Lokasi Baru',
            $request->user()->name . ' mengajukan survei ' . $boardingHouse->name . ' pada ' . $survey->preferred_date->format('d M Y') . ' pukul ' . $survey->preferred_time . '.',
            route('mitra.surveys.index')
        );

        return redirect()
            ->route('penyewa.surveys.index')
            ->with('status', 'Permintaan jadwal survei lokasi berhasil dikirim. Tunggu konfirmasi dari pengelola kos.');
    }

    public function cancel(Request $request, SurveyBooking $surveyBooking): RedirectResponse
    {
        if ($surveyBooking->user_id !== $request->user()->id) {
            abort(403);
        }

        if (! in_array($surveyBooking->status, [SurveyBooking::STATUS_PENDING, SurveyBooking::STATUS_APPROVED, SurveyBooking::STATUS_RESCHEDULED], true)) {
            return back()->with('error', 'Jadwal survei ini tidak dapat dibatalkan.');
        }

        $surveyBooking->update(['status' => SurveyBooking::STATUS_CANCELLED]);

        return back()->with('status', 'Jadwal survei lokasi berhasil dibatalkan.');
    }
}
