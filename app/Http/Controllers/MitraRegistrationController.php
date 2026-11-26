<?php

namespace App\Http\Controllers;

use App\Models\MitraProfile;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class MitraRegistrationController extends Controller
{
    public function info(): View
    {
        return view('mitra.info');
    }

    public function create(): View
    {
        return view('auth.register-mitra');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'string', 'max:25'],
            'business_name' => ['required', 'string', 'max:150'],
            'bank_name' => ['required', 'string', 'max:60'],
            'bank_account_number' => ['required', 'string', 'max:50'],
            'bank_account_name' => ['required', 'string', 'max:120'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'role' => User::ROLE_MITRA,
                'is_active' => true,
            ]);

            MitraProfile::create([
                'user_id' => $user->id,
                'business_name' => $validated['business_name'],
                'bank_name' => $validated['bank_name'],
                'bank_account_number' => $validated['bank_account_number'],
                'bank_account_name' => $validated['bank_account_name'],
                'verification_status' => MitraProfile::STATUS_PENDING,
            ]);

            return $user;
        });

        event(new Registered($user));
        Auth::login($user);

        return redirect()->route('dashboard')->with(
            'status',
            'Pendaftaran Mitra tersimpan. Akun Anda menunggu verifikasi dari Admin sebelum dapat menayangkan kos.'
        );
    }
}
