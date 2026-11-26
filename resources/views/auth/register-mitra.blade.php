<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-[var(--text-primary)]">Daftar Mitra Pemilik Kos</h2>
        <p class="mt-1.5 text-sm text-[var(--text-secondary)]">
            Lengkapi data akun dan rekening penerima transfer. Admin akan memverifikasi pendaftaran sebelum kos dapat tayang.
        </p>
    </div>

    <form method="POST" action="{{ route('register.mitra.store') }}" class="space-y-4">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="name" value="Nama Pengelola / Pemilik" />
                <x-text-input id="name" type="text" name="name" :value="old('name')" required autofocus placeholder="Nama lengkap Anda" />
                <x-input-error :messages="$errors->get('name')" />
            </div>

            <div>
                <x-input-label for="phone" value="Nomor WhatsApp Aktif" />
                <x-text-input id="phone" type="tel" name="phone" :value="old('phone')" required placeholder="08xxxxxxxxxx" />
                <x-input-error :messages="$errors->get('phone')" />
            </div>
        </div>

        <div>
            <x-input-label for="business_name" value="Nama Usaha / Pengelola Kos" />
            <x-text-input id="business_name" type="text" name="business_name" :value="old('business_name')" required placeholder="Contoh: Kos Putri Melati Tamantirto" />
            <x-input-error :messages="$errors->get('business_name')" />
        </div>

        <div>
            <x-input-label for="email" value="Alamat Email" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="email@example.com" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="pt-2 border-t border-[var(--border-subtle)] grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="bank_name" value="Nama Bank Penerima" />
                <x-text-input id="bank_name" type="text" name="bank_name" :value="old('bank_name')" required placeholder="Contoh: BRI / BSI / BCA" />
                <x-input-error :messages="$errors->get('bank_name')" />
            </div>

            <div>
                <x-input-label for="bank_account_number" value="Nomor Rekening" />
                <x-text-input id="bank_account_number" type="text" name="bank_account_number" :value="old('bank_account_number')" required placeholder="Nomor rekening tujuan" class="tabular-nums" />
                <x-input-error :messages="$errors->get('bank_account_number')" />
            </div>
        </div>

        <div>
            <x-input-label for="bank_account_name" value="Nama Pemilik Rekening" />
            <x-text-input id="bank_account_name" type="text" name="bank_account_name" :value="old('bank_account_name')" required placeholder="Sesuai buku tabungan" />
            <x-input-error :messages="$errors->get('bank_account_name')" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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
        </div>

        <x-primary-button class="w-full">
            Ajukan Pendaftaran Mitra
        </x-primary-button>
    </form>

    <div class="mt-6 pt-5 border-t border-[var(--border-subtle)] text-sm text-[var(--text-secondary)]">
        Sudah memiliki akun?
        <a href="{{ route('login') }}" class="font-semibold text-[var(--brand-gold-text)] hover:underline">Masuk di sini</a>
    </div>
</x-guest-layout>
