<x-public-layout title="Gabung Mitra Pemilik Kos">
    <div class="max-w-[1260px] mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
        <div class="rounded-[var(--radius-panel)] bg-[var(--bg-espresso)] text-[var(--espresso-ink)] p-8 sm:p-12 lg:p-16 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
            <div class="lg:col-span-7">
                <p class="text-sm font-semibold text-[var(--espresso-gold)]">Kemitraan Pemilik Kos Kawasan UMY</p>
                <h1 class="mt-2 font-display text-3xl sm:text-5xl font-semibold leading-[1.1] text-[var(--espresso-ink)]">
                    Daftarkan properti kos Anda dan kelola pesanan lebih teratur
                </h1>
                <p class="mt-4 text-base text-[var(--espresso-muted)] leading-relaxed max-w-xl">
                    LokaKos membantu pemilik kos di Tamantirto, Ngebel, Geblagan, Brajan, dan Kasihan menayangkan tipe kamar secara rapi serta memverifikasi bukti transfer manual mahasiswa dalam satu halaman.
                </p>
                <div class="mt-7 flex flex-wrap gap-3">
                    <a href="{{ route('register.mitra') }}" class="lk-btn-primary">
                        Daftar Sebagai Mitra Kos
                    </a>
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center min-h-[46px] px-6 rounded-full border border-white/35 text-sm font-medium text-[var(--espresso-ink)] hover:bg-white/10 transition-colors">
                        Sudah Punya Akun? Masuk
                    </a>
                </div>
            </div>

            @if (file_exists(public_path('images/demo/room-boutique-ac.jpg')))
                <div class="lg:col-span-5">
                    <div class="relative rounded-[var(--radius-card)] overflow-hidden aspect-[4/3]">
                        <img
                            src="{{ asset('images/demo/room-boutique-ac.jpg') }}"
                            alt="Ilustrasi demo kamar kos nyaman kawasan UMY"
                            class="w-full h-full object-cover"
                        >
                        <span class="absolute bottom-3 right-3 px-2.5 py-1 rounded bg-black/70 text-[#F5F1EB] text-[11px]">
                            Ilustrasi demo
                        </span>
                    </div>
                </div>
            @endif
        </div>

        <div class="mt-10 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="lk-card p-7 sm:p-8">
                <h2 class="text-xl font-semibold text-[var(--text-primary)]">Alur Pendaftaran & Penayangan</h2>
                <ol class="mt-4 space-y-3 text-sm text-[var(--text-secondary)] list-decimal list-inside leading-relaxed">
                    <li>Pemilik kos mendaftar akun Mitra dengan mengisi nama usaha kos, nomor WhatsApp aktif, dan data rekening penerima transfer.</li>
                    <li>Admin LokaKos meninjau kelengkapan data calon Mitra lalu menyetujui akun.</li>
                    <li>Mitra menambahkan properti kos, menentukan titik lokasi di peta, serta mengatur tipe kamar, tarif, dan stok kamar tersedia.</li>
                    <li>Admin memoderasi data kos sebelum berstatus tayang pada katalog mahasiswa.</li>
                </ol>
            </div>

            <div class="lk-card p-7 sm:p-8">
                <h2 class="text-xl font-semibold text-[var(--text-primary)]">Ketentuan Transaksi Transfer Manual</h2>
                <ul class="mt-4 space-y-3 text-sm text-[var(--text-secondary)] leading-relaxed">
                    <li class="flex items-start gap-2.5">
                        <span class="mt-2 w-1.5 h-1.5 rounded-full bg-[var(--brand-gold)] shrink-0"></span>
                        <span>Mahasiswa mentransfer langsung ke rekening bank yang Anda daftarkan pada profil Mitra.</span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <span class="mt-2 w-1.5 h-1.5 rounded-full bg-[var(--brand-gold)] shrink-0"></span>
                        <span>Bukti transfer diunggah ke sistem sehingga Anda dapat memeriksa kecocokan mutasi sebelum menekan tombol Setujui atau Tolak.</span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <span class="mt-2 w-1.5 h-1.5 rounded-full bg-[var(--brand-gold)] shrink-0"></span>
                        <span>Setelah pembayaran disetujui, stok kamar berkurang otomatis dan bukti pemesanan PDF tersedia untuk penyewa saat check-in.</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</x-public-layout>
