<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-[var(--text-primary)]">Masuk ke LokaKos</h2>
        <p class="mt-1.5 text-sm text-[var(--text-secondary)]">
            Gunakan akun Penyewa, Mitra Pemilik Kos, atau Admin Anda.
        </p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="email" value="Alamat Email" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="email@example.com" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div>
            <x-input-label for="password" value="Kata Sandi" />
            <x-text-input id="password" type="password" name="password" required autocomplete="current-password" placeholder="Masukkan kata sandi" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
            <label for="remember_me" class="inline-flex items-center gap-2 min-h-[44px] cursor-pointer">
                <input id="remember_me" type="checkbox" class="w-4 h-4 rounded border-[var(--border-control)] text-[var(--brand-gold-text)] focus:ring-[var(--focus-ring)]" name="remember">
                <span class="text-sm text-[var(--text-secondary)]">Ingat saya</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm font-medium text-[var(--brand-gold-text)] hover:underline inline-flex items-center min-h-[44px]" href="{{ route('password.request') }}">
                    Lupa kata sandi?
                </a>
            @endif
        </div>

        <x-primary-button class="w-full">
            Masuk ke LokaKos
        </x-primary-button>
    </form>

    <div class="mt-6 pt-5 border-t border-[var(--border-subtle)] text-sm text-[var(--text-secondary)] space-y-2">
        <p>
            Belum punya akun mahasiswa/penyewa?
            <a href="{{ route('register') }}" class="font-semibold text-[var(--brand-gold-text)] hover:underline">Daftar Penyewa</a>
        </p>
        <p>
            Pemilik kos di kawasan UMY?
            <a href="{{ route('register.mitra') }}" class="font-semibold text-[var(--brand-gold-text)] hover:underline">Daftar Jadi Mitra Kos</a>
        </p>
    </div>
</x-guest-layout>
