<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-[var(--text-primary)]">Daftar Akun Penyewa</h2>
        <p class="mt-1.5 text-sm text-[var(--text-secondary)]">
            Buat akun untuk mengajukan pemesanan kamar, mengunggah bukti transfer, dan mengunduh invoice PDF.
        </p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="name" value="Nama Lengkap" />
            <x-text-input id="name" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Nama lengkap Anda" />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" value="Alamat Email" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="email@example.com" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div>
            <x-input-label for="password" value="Kata Sandi" />
            <x-text-input id="password" type="password" name="password" required autocomplete="new-password" placeholder="Minimal 8 karakter" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="Konfirmasi Kata Sandi" />
            <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Ulangi kata sandi" />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <x-primary-button class="w-full">
            Buat Akun Penyewa
        </x-primary-button>
    </form>

    <div class="mt-6 pt-5 border-t border-[var(--border-subtle)] text-sm text-[var(--text-secondary)] space-y-2">
        <p>
            Sudah terdaftar?
            <a href="{{ route('login') }}" class="font-semibold text-[var(--brand-gold-text)] hover:underline">Masuk di sini</a>
        </p>
        <p>
            Ingin mendaftarkan properti kos milik Anda?
            <a href="{{ route('register.mitra') }}" class="font-semibold text-[var(--brand-gold-text)] hover:underline">Daftar Sebagai Mitra Kos</a>
        </p>
    </div>
</x-guest-layout>
