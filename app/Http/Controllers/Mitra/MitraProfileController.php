<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Models\MitraProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MitraProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user()->load('mitraProfile');

        return view('mitra.profile.edit', [
            'user' => $user,
            'profile' => $user->mitraProfile,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:25'],
            'business_name' => ['required', 'string', 'max:150'],
            'bank_name' => ['required', 'string', 'max:60'],
            'bank_account_number' => ['required', 'string', 'max:50'],
            'bank_account_name' => ['required', 'string', 'max:120'],
            'ktp_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'resubmit_verification' => ['nullable', 'boolean'],
        ]);

        $user = $request->user();
        $user->update(['phone' => $validated['phone']]);

        $profile = $user->mitraProfile ?? new MitraProfile(['user_id' => $user->id]);

        if ($request->hasFile('ktp_file')) {
            if ($profile->ktp_path) {
                Storage::disk('public')->delete($profile->ktp_path);
            }
            $profile->ktp_path = $request->file('ktp_file')->store('ktp_documents', 'public');
        }

        $profile->business_name = $validated['business_name'];
        $profile->bank_name = $validated['bank_name'];
        $profile->bank_account_number = $validated['bank_account_number'];
        $profile->bank_account_name = $validated['bank_account_name'];

        if (
            $profile->verification_status === MitraProfile::STATUS_REJECTED
            && $request->boolean('resubmit_verification')
        ) {
            $profile->verification_status = MitraProfile::STATUS_PENDING;
            $profile->rejection_note = null;
        }

        $profile->save();

        return back()->with('status', 'Profil Mitra dan informasi rekening berhasil diperbarui.');
    }
}
