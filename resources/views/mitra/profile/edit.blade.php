<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-[var(--text-primary)]">Profil Usaha & Rekening Bank Mitra</h1>
                <p class="mt-1 text-sm text-[var(--text-secondary)]">Rekening ini ditampilkan kepada mahasiswa saat mereka mengajukan pemesanan kos Anda.</p>
            </div>
            @if ($profile)
                <x-lokakos.status-badge :status="$profile->verification_status" />
            @endif
        </div>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('mitra.profile.update') }}" enctype="multipart/form-data" class="lk-card p-6 sm:p-8 space-y-5">
            @csrf
            @method('PUT')

            @if ($profile?->rejection_note)
                <div class="p-4 rounded-[var(--radius-control)] bg-[var(--status-danger-bg)] text-[var(--status-danger)] text-sm">
                    <strong>Catatan Penolakan Admin:</strong> {{ $profile->rejection_note }}
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="business_name" value="Nama Usaha / Pengelola Kos" />
                    <x-text-input id="business_name" name="business_name" type="text" :value="old('business_name', $profile?->business_name)" required />
                    <x-input-error :messages="$errors->get('business_name')" />
                </div>
                <div>
                    <x-input-label for="phone" value="Nomor WhatsApp Aktif" />
                    <x-text-input id="phone" name="phone" type="tel" :value="old('phone', $user->phone)" required />
                    <x-input-error :messages="$errors->get('phone')" />
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="bank_name" value="Nama Bank" />
                    <x-text-input id="bank_name" name="bank_name" type="text" :value="old('bank_name', $profile?->bank_name)" required />
                    <x-input-error :messages="$errors->get('bank_name')" />
                </div>
                <div>
                    <x-input-label for="bank_account_number" value="Nomor Rekening" />
                    <x-text-input id="bank_account_number" name="bank_account_number" type="text" :value="old('bank_account_number', $profile?->bank_account_number)" required class="tabular-nums" />
                    <x-input-error :messages="$errors->get('bank_account_number')" />
                </div>
            </div>

            <div>
                <x-input-label for="bank_account_name" value="Nama Pemilik Rekening" />
                <x-text-input id="bank_account_name" name="bank_account_name" type="text" :value="old('bank_account_name', $profile?->bank_account_name)" required />
                <x-input-error :messages="$errors->get('bank_account_name')" />
            </div>

            <div>
                <x-input-label for="ktp_file" value="Berkas Identitas / KTP (JPG/PNG/PDF, maks 2 MB)" />
                <input id="ktp_file" name="ktp_file" type="file" accept=".jpg,.jpeg,.png,.pdf" class="lk-input text-sm">
                @if ($profile?->ktp_path)
                    <a href="{{ asset('storage/' . $profile->ktp_path) }}" target="_blank" class="mt-2 inline-block text-xs font-semibold text-[var(--brand-gold-text)] hover:underline">
                        Lihat dokumen KTP yang tersimpan
                    </a>
                @endif
                <x-input-error :messages="$errors->get('ktp_file')" />
            </div>

            @if ($profile?->verification_status === \App\Models\MitraProfile::STATUS_REJECTED)
                <label class="inline-flex items-center gap-2.5 cursor-pointer min-h-[44px]">
                    <input type="checkbox" name="resubmit_verification" value="1" checked class="w-4 h-4 rounded border-[var(--border-control)]">
                    <span class="text-sm font-semibold text-[var(--text-primary)]">Ajukan ulang verifikasi akun Mitra ke Admin</span>
                </label>
            @endif

            <div class="pt-3 flex justify-end">
                <button type="submit" class="lk-btn-primary text-sm">Simpan Profil Mitra</button>
            </div>
        </form>
    </div>
</x-app-layout>
