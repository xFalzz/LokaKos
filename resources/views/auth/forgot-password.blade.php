<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-[var(--text-primary)]">Atur Ulang Kata Sandi</h2>
        <p class="mt-1.5 text-sm text-[var(--text-secondary)] leading-relaxed">
            Masukkan alamat email terdaftar Anda. Kami akan mengirimkan tautan untuk membuat kata sandi baru.
        </p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="email" value="Alamat Email" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus placeholder="email@example.com" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <x-primary-button class="w-full">
            Kirim Tautan Atur Ulang
        </x-primary-button>
    </form>

    <div class="mt-6 pt-5 border-t border-[var(--border-subtle)] text-sm text-[var(--text-secondary)]">
        <a href="{{ route('login') }}" class="font-semibold text-[var(--brand-gold-text)] hover:underline">Kembali ke halaman masuk</a>
    </div>
</x-guest-layout>
