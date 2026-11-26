<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Models\SurveyBooking;
use App\Services\ActivityLogger;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SurveyManagementController extends Controller
{
    public function index(Request $request): View
    {
        $mitraId = $request->user()->id;

        $surveys = SurveyBooking::with(['user', 'boardingHouse', 'roomType'])
            ->whereHas('boardingHouse', fn ($q) => $q->where('mitra_id', $mitraId))
            ->latest()
            ->paginate(15);

        return view('mitra.surveys.index', [
            'surveys' => $surveys,
        ]);
    }

    public function update(Request $request, SurveyBooking $surveyBooking): RedirectResponse
    {
        $surveyBooking->load('boardingHouse');
        $this->authorize('update', $surveyBooking->boardingHouse);

        $validated = $request->validate([
            'status' => ['required', 'in:approved,rescheduled,rejected,completed'],
            'preferred_date' => ['nullable', 'date'],
            'preferred_time' => ['nullable', 'string', 'max:30'],
            'mitra_response' => ['nullable', 'string', 'max:500'],
        ]);

        $updates = [
            'status' => $validated['status'],
            'mitra_response' => $validated['mitra_response'] ?? $surveyBooking->mitra_response,
        ];

        if ($validated['status'] === SurveyBooking::STATUS_RESCHEDULED) {
            if (! empty($validated['preferred_date'])) {
                $updates['preferred_date'] = $validated['preferred_date'];
            }
            if (! empty($validated['preferred_time'])) {
                $updates['preferred_time'] = $validated['preferred_time'];
            }
        }

        $surveyBooking->update($updates);

        ActivityLogger::log(
            'survey.' . $validated['status'],
            'Status survei ' . $surveyBooking->boardingHouse->name . ' diperbarui menjadi ' . $validated['status'] . '.',
            $surveyBooking
        );

        NotificationService::notify(
            $surveyBooking->user_id,
            'survey_updated',
            'Pembaruan Jadwal Survei Kos',
            'Pengajuan survei Anda di ' . $surveyBooking->boardingHouse->name . ' kini berstatus: ' . ucfirst($validated['status']) . '.',
            route('penyewa.surveys.index')
        );

        return back()->with('status', 'Status jadwal survei berhasil diperbarui.');
    }
}
