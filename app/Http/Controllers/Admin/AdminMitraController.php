<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MitraProfile;
use App\Services\ActivityLogger;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminMitraController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status', '');

        $query = MitraProfile::with(['user', 'verifier'])->latest();

        if (in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $query->where('verification_status', $status);
        }

        return view('admin.mitra.index', [
            'profiles' => $query->paginate(12)->withQueryString(),
            'statusFilter' => $status,
        ]);
    }

    public function approve(Request $request, MitraProfile $mitraProfile): RedirectResponse
    {
        $mitraProfile->update([
            'verification_status' => MitraProfile::STATUS_APPROVED,
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
            'rejection_note' => null,
        ]);

        ActivityLogger::log('mitra.approved', 'Admin menyetujui akun Mitra: ' . $mitraProfile->business_name, $mitraProfile);
        NotificationService::notify(
            $mitraProfile->user_id,
            'mitra_approved',
            'Akun Mitra Disetujui',
            'Selamat, akun Mitra ' . $mitraProfile->business_name . ' telah diverifikasi Admin. Anda kini dapat menambahkan properti kos.',
            route('mitra.kos.create')
        );

        return back()->with('status', "Akun Mitra {$mitraProfile->business_name} disetujui.");
    }

    public function reject(Request $request, MitraProfile $mitraProfile): RedirectResponse
    {
        $validated = $request->validate([
            'rejection_note' => ['required', 'string', 'max:1000'],
        ]);

        $mitraProfile->update([
            'verification_status' => MitraProfile::STATUS_REJECTED,
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
            'rejection_note' => $validated['rejection_note'],
        ]);

        ActivityLogger::log('mitra.rejected', 'Admin menolak pendaftaran Mitra: ' . $mitraProfile->business_name, $mitraProfile, ['reason' => $validated['rejection_note']]);
        NotificationService::notify(
            $mitraProfile->user_id,
            'mitra_rejected',
            'Pendaftaran Mitra Perlu Perbaikan',
            'Catatan Admin: ' . $validated['rejection_note'],
            route('mitra.profile.edit')
        );

        return back()->with('status', "Pendaftaran Mitra {$mitraProfile->business_name} ditolak beserta catatan.");
    }
}
