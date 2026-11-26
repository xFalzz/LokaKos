<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\BoardingAnnouncement;
use App\Models\BoardingHouse;
use App\Models\BoardingQuestion;
use App\Models\Facility;
use App\Models\MitraExpense;
use App\Models\Promo;
use App\Models\RoomType;
use App\Models\RoomUnit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoBoardingHouseSeeder extends Seeder
{
    public function run(): void
    {
        $mitra = User::where('email', 'mitra1@lokakos.test')->first();

        if (! $mitra) {
            return;
        }

        $areas = Area::pluck('id', 'slug');
        $kosFacilities = Facility::where('category', Facility::CATEGORY_KOS)->pluck('id', 'name');
        $roomFacilities = Facility::where('category', Facility::CATEGORY_KAMAR)->pluck('id', 'name');

        $listings = [
            [
                'area_slug' => 'ngebel',
                'name' => 'Kos Putri Melati Ngebel',
                'gender_type' => 'putri',
                'is_syariah' => true,
                'address' => 'Jl. Ngebel Raya No. 14, Tamantirto, Kasihan, Bantul',
                'latitude' => -7.8138000,
                'longitude' => 110.3198000,
                'rules' => "Khusus mahasiswi Muslim.\nJam malam pukul 22.00 WIB, izin pengelola bila pulang terlambat.\nTamu lawan jenis hanya di ruang tamu depan.",
                'description' => 'Lingkungan tenang dengan akses jalan kaki sekitar 5 menit menuju gerbang barat Kampus Terpadu UMY.',
                'nearby_landmarks' => "350 m ke Gerbang Barat UMY\n200 m ke Masjid & Minimarket\n120 m ke Laundry & Warung Makan",
                'is_field_verified' => true,
                'contact_name' => 'Pengelola Melati Ngebel (Demo)',
                'contact_whatsapp' => '0812-0000-1101',
                'contact_phone' => '0274-000101',
                'contact_hours' => 'Setiap hari 08.00 - 20.30 WIB',
                'whatsapp_message_template' => null,
                'status' => BoardingHouse::STATUS_PUBLISHED,
                'kos_facilities' => ['Wi-Fi', 'Parkir Motor', 'Dapur Bersama', 'CCTV 24 Jam', 'Area Jemuran'],
                'rooms' => [
                    [
                        'name' => 'Tipe AC + Kamar Mandi Dalam',
                        'size_m2' => '3,5 x 4 m',
                        'price_monthly' => 1150000,
                        'price_3_months' => 3350000,
                        'price_6_months' => 6500000,
                        'price_yearly' => 12600000,
                        'total_rooms' => 6,
                        'available_rooms' => 3,
                        'description' => 'Kamar berjendela menghadap halaman, dilengkapi perabot lengkap.',
                        'facilities' => ['Kamar Mandi Dalam', 'AC', 'Kasur & Bantal', 'Lemari Pakaian', 'Meja Belajar'],
                    ],
                    [
                        'name' => 'Tipe Kipas + Kamar Mandi Dalam',
                        'size_m2' => '3 x 3,5 m',
                        'price_monthly' => 825000,
                        'price_6_months' => 4800000,
                        'price_yearly' => 9200000,
                        'total_rooms' => 4,
                        'available_rooms' => 2,
                        'description' => 'Sirkulasi udara baik, cocok untuk mahasiswa yang menginginkan tarif hemat.',
                        'facilities' => ['Kamar Mandi Dalam', 'Kipas Angin', 'Kasur & Bantal', 'Lemari Pakaian'],
                    ],
                ],
            ],
            [
                'area_slug' => 'geblagan',
                'name' => 'Kos Putra Griya Geblagan',
                'gender_type' => 'putra',
                'is_syariah' => false,
                'address' => 'Geblagan RT 03, Jl. Brawijaya, Tamantirto, Kasihan, Bantul',
                'latitude' => -7.8101000,
                'longitude' => 110.3239000,
                'rules' => "Khusus mahasiswa putra.\nMenjaga ketenangan lingkungan setelah pukul 22.30 WIB.\nDilarang merokok di dalam kamar ber-AC.",
                'description' => 'Dekat pintu utama UMY, warung makan, laundry, dan minimarket yang mudah dijangkau tanpa kendaraan.',
                'nearby_landmarks' => "290 m ke Gerbang Utama UMY\n100 m ke Minimarket & ATM\n150 m ke Sentra Kuliner Geblagan",
                'is_field_verified' => true,
                'contact_name' => 'Pengelola Griya Geblagan (Demo)',
                'contact_whatsapp' => '0812-0000-1102',
                'contact_phone' => '0812-0000-2102',
                'contact_hours' => '07.30 - 21.30 WIB',
                'whatsapp_message_template' => null,
                'status' => BoardingHouse::STATUS_PUBLISHED,
                'kos_facilities' => ['Wi-Fi', 'Parkir Motor', 'Parkir Mobil', 'CCTV 24 Jam', 'Akses 24 Jam'],
                'rooms' => [
                    [
                        'name' => 'Tipe Standar Kipas',
                        'size_m2' => '3 x 3 m',
                        'price_monthly' => 700000,
                        'price_6_months' => 4050000,
                        'price_yearly' => 7800000,
                        'total_rooms' => 8,
                        'available_rooms' => 4,
                        'description' => 'Kamar rapi dengan kamar mandi luar terawat (rasio 1 KM untuk 2 kamar).',
                        'facilities' => ['Kipas Angin', 'Kasur & Bantal', 'Lemari Pakaian', 'Meja Belajar', 'Termasuk Listrik'],
                    ],
                    [
                        'name' => 'Tipe AC Eksklusif',
                        'size_m2' => '3,5 x 4 m',
                        'price_monthly' => 1250000,
                        'price_yearly' => 13800000,
                        'total_rooms' => 4,
                        'available_rooms' => 0,
                        'description' => 'Kamar ber-AC dan water heater; saat ini seluruh unit sedang terisi.',
                        'facilities' => ['Kamar Mandi Dalam', 'AC', 'Water Heater', 'Kasur & Bantal', 'Meja Belajar'],
                    ],
                ],
            ],
            [
                'area_slug' => 'tamantirto',
                'name' => 'Kos Syariah Az-Zahra Tamantirto',
                'gender_type' => 'putri',
                'is_syariah' => true,
                'address' => 'Jl. Sunan Kudus, Tamantirto, Kasihan, Bantul',
                'latitude' => -7.8124000,
                'longitude' => 110.3251000,
                'rules' => "Khusus putri Muslimah.\nWajib menjaga kebersihan dapur bersama.\nTamu menginap hanya mahram atau keluarga inti dengan lapor pengelola.",
                'description' => 'Bangunan dua lantai dengan halaman teduh dan pengelola yang tinggal di area depan rumah.',
                'nearby_landmarks' => "400 m ke Masjid KH Ahmad Dahlan UMY\n250 m ke Apotek & Minimarket Tamantirto",
                'is_field_verified' => true,
                'contact_name' => 'Pengelola Az-Zahra (Demo)',
                'contact_whatsapp' => '0812-0000-1103',
                'contact_phone' => null,
                'contact_hours' => '08.00 - 20.00 WIB',
                'whatsapp_message_template' => 'Assalamualaikum {pengelola}, saya {penanya} tertarik dengan {nama_kos} di {area} ({jarak}) yang saya temukan di LokaKos. Apakah masih tersedia kamar syariah untuk bulan depan dan kapan saya bisa berkunjung survei bersama orang tua? Terima kasih.',
                'status' => BoardingHouse::STATUS_PUBLISHED,
                'kos_facilities' => ['Wi-Fi', 'Parkir Motor', 'Dapur Bersama', 'Ruang Tamu', 'CCTV 24 Jam'],
                'rooms' => [
                    [
                        'name' => 'Tipe Syariah AC',
                        'size_m2' => '3 x 4 m',
                        'price_monthly' => 1050000,
                        'price_3_months' => 3050000,
                        'price_6_months' => 6000000,
                        'price_yearly' => 11500000,
                        'total_rooms' => 10,
                        'available_rooms' => 5,
                        'description' => 'Dilengkapi meja belajar lebar, lemari dua pintu, dan kamar mandi dalam.',
                        'facilities' => ['Kamar Mandi Dalam', 'AC', 'Kasur & Bantal', 'Lemari Pakaian', 'Meja Belajar'],
                    ],
                ],
            ],
            [
                'area_slug' => 'brajan',
                'name' => 'Kos Teduh Brajan',
                'gender_type' => 'putra',
                'is_syariah' => true,
                'address' => 'Dusun Brajan RT 05, Tamantirto, Kasihan, Bantul',
                'latitude' => -7.8172000,
                'longitude' => 110.3243000,
                'rules' => "Khusus putra.\nParkir motor wajib dikunci ganda di dalam gerbang.\nIuran sampah sudah termasuk sewa bulanan.",
                'description' => 'Suasana permukiman yang tenang di selatan kampus, cocok bagi mahasiswa tingkat akhir yang butuh fokus skripsi.',
                'contact_name' => 'Pengelola Teduh Brajan (Demo)',
                'contact_whatsapp' => '0812-0000-1104',
                'contact_phone' => null,
                'contact_hours' => '08.00 - 21.00 WIB',
                'whatsapp_message_template' => null,
                'status' => BoardingHouse::STATUS_PUBLISHED,
                'kos_facilities' => ['Wi-Fi', 'Parkir Motor', 'Dapur Bersama', 'Area Jemuran', 'Akses 24 Jam'],
                'rooms' => [
                    [
                        'name' => 'Tipe Reguler KM Dalam',
                        'size_m2' => '3 x 3,5 m',
                        'price_monthly' => 775000,
                        'price_6_months' => 4500000,
                        'price_yearly' => 8700000,
                        'total_rooms' => 7,
                        'available_rooms' => 3,
                        'description' => 'Kamar mandi dalam, listrik token mandiri, dan sirkulasi udara menghadap taman.',
                        'facilities' => ['Kamar Mandi Dalam', 'Kipas Angin', 'Kasur & Bantal', 'Lemari Pakaian', 'Meja Belajar'],
                    ],
                ],
            ],
            [
                'area_slug' => 'rukeman',
                'name' => 'Kos Campur Harmoni Rukeman',
                'gender_type' => 'campur',
                'is_syariah' => false,
                'address' => 'Rukeman, Tamantirto, Kasihan, Bantul',
                'latitude' => -7.8159000,
                'longitude' => 110.3302000,
                'rules' => "Lantai 1 khusus putra, lantai 2 khusus putri.\nTamu wajib lapor dan batas bertamu pukul 21.30 WIB.\nMenjaga kebersihan lorong bersama.",
                'description' => 'Bangunan terpisah lantai putra dan putri dengan area parkir luas dan akses cepat menuju Ringroad Selatan.',
                'contact_name' => 'Pengelola Harmoni Rukeman (Demo)',
                'contact_whatsapp' => '0812-0000-1105',
                'contact_phone' => '0274-000105',
                'contact_hours' => '08.00 - 22.00 WIB',
                'whatsapp_message_template' => 'Halo {pengelola}, saya {penanya} baru melihat {nama_kos} ({area}) di LokaKos. Kebetulan saya tertarik dengan kamar luas ber-AC yang dekat Ringroad Selatan. Apakah unitnya masih tersedia untuk survei minggu ini? Terima kasih.',
                'status' => BoardingHouse::STATUS_PUBLISHED,
                'kos_facilities' => ['Wi-Fi', 'Parkir Motor', 'Parkir Mobil', 'Dapur Bersama', 'CCTV 24 Jam', 'Akses 24 Jam'],
                'rooms' => [
                    [
                        'name' => 'Tipe Studio AC',
                        'size_m2' => '4 x 4 m',
                        'price_monthly' => 1450000,
                        'price_6_months' => 8400000,
                        'price_yearly' => 16200000,
                        'total_rooms' => 6,
                        'available_rooms' => 2,
                        'description' => 'Kamar luas dilengkapi AC, water heater, meja kerja, dan balkon kecil.',
                        'facilities' => ['Kamar Mandi Dalam', 'AC', 'Water Heater', 'Kasur & Bantal', 'Lemari Pakaian', 'Meja Belajar'],
                    ],
                ],
            ],
            [
                'area_slug' => 'ambarketawang',
                'name' => 'Kos Putra Mandiri Ambarketawang',
                'gender_type' => 'putra',
                'is_syariah' => false,
                'address' => 'Jl. Wates KM 5, Ambarketawang, Gamping, Sleman',
                'latitude' => -7.8029000,
                'longitude' => 110.3125000,
                'rules' => "Khusus putra.\nBebas jam malam dengan membawa kunci gerbang masing-masing.\nListrik token per kamar.",
                'description' => 'Strategis di sisi barat Ringroad dekat Jalan Wates, mudah menjangkau UMY dalam 5 menit bermotor.',
                'contact_name' => 'Pengelola Mandiri Ambarketawang (Demo)',
                'contact_whatsapp' => '0812-0000-1106',
                'contact_phone' => null,
                'contact_hours' => '09.00 - 21.00 WIB',
                'whatsapp_message_template' => null,
                'status' => BoardingHouse::STATUS_PUBLISHED,
                'kos_facilities' => ['Wi-Fi', 'Parkir Motor', 'Area Jemuran', 'Akses 24 Jam'],
                'rooms' => [
                    [
                        'name' => 'Tipe Ekonomis',
                        'size_m2' => '3 x 3 m',
                        'price_monthly' => 625000,
                        'price_6_months' => 3600000,
                        'price_yearly' => 7000000,
                        'total_rooms' => 8,
                        'available_rooms' => 5,
                        'description' => 'Pilihan hemat dengan fasilitas dasar lengkap dan parkir beratap.',
                        'facilities' => ['Kipas Angin', 'Kasur & Bantal', 'Lemari Pakaian', 'Termasuk Listrik'],
                    ],
                ],
            ],
            [
                'area_slug' => 'kasihan',
                'name' => 'Kos Putri Cendana Kasihan (Menunggu Moderasi)',
                'gender_type' => 'putri',
                'is_syariah' => true,
                'address' => 'Kasihan, Bantul, Daerah Istimewa Yogyakarta',
                'latitude' => -7.8211000,
                'longitude' => 110.3338000,
                'rules' => 'Khusus putri.',
                'description' => 'Data contoh berstatus pending_review untuk menguji moderasi Admin.',
                'contact_name' => 'Pengelola Cendana Kasihan (Demo)',
                'contact_whatsapp' => '0812-0000-1107',
                'contact_phone' => null,
                'contact_hours' => '08.00 - 20.00 WIB',
                'whatsapp_message_template' => null,
                'status' => BoardingHouse::STATUS_PENDING_REVIEW,
                'kos_facilities' => ['Wi-Fi', 'Parkir Motor'],
                'rooms' => [
                    [
                        'name' => 'Tipe Standar',
                        'size_m2' => '3 x 3 m',
                        'price_monthly' => 850000,
                        'total_rooms' => 4,
                        'available_rooms' => 4,
                        'description' => 'Unit standar menunggu peninjauan Admin.',
                        'facilities' => ['Kamar Mandi Dalam', 'Kasur & Bantal'],
                    ],
                ],
            ],
        ];

        foreach ($listings as $item) {
            $areaId = $areas[$item['area_slug']] ?? null;
            if (! $areaId) {
                continue;
            }

            $slug = Str::slug($item['name']);

            $kos = BoardingHouse::updateOrCreate(
                ['slug' => $slug],
                [
                    'mitra_id' => $mitra->id,
                    'area_id' => $areaId,
                    'name' => $item['name'],
                    'gender_type' => $item['gender_type'],
                    'is_syariah' => $item['is_syariah'],
                    'address' => $item['address'],
                    'latitude' => $item['latitude'],
                    'longitude' => $item['longitude'],
                    'rules' => $item['rules'],
                    'description' => $item['description'],
                    'nearby_landmarks' => $item['nearby_landmarks'] ?? null,
                    'contact_name' => $item['contact_name'] ?? null,
                    'contact_whatsapp' => $item['contact_whatsapp'] ?? null,
                    'contact_phone' => $item['contact_phone'] ?? null,
                    'contact_hours' => $item['contact_hours'] ?? null,
                    'whatsapp_message_template' => $item['whatsapp_message_template'] ?? null,
                    'status' => $item['status'],
                    'is_field_verified' => $item['is_field_verified'] ?? false,
                    'field_verified_at' => ($item['is_field_verified'] ?? false) ? now() : null,
                ]
            );

            $kosFacilityIds = collect($item['kos_facilities'])
                ->map(fn (string $name) => $kosFacilities[$name] ?? null)
                ->filter()
                ->values()
                ->all();
            $kos->facilities()->sync($kosFacilityIds);

            foreach ($item['rooms'] as $roomData) {
                $room = RoomType::updateOrCreate(
                    [
                        'boarding_house_id' => $kos->id,
                        'name' => $roomData['name'],
                    ],
                    [
                        'size_m2' => $roomData['size_m2'] ?? null,
                        'price_monthly' => $roomData['price_monthly'],
                        'price_3_months' => $roomData['price_3_months'] ?? null,
                        'price_6_months' => $roomData['price_6_months'] ?? null,
                        'price_yearly' => $roomData['price_yearly'] ?? null,
                        'total_rooms' => $roomData['total_rooms'],
                        'available_rooms' => $roomData['available_rooms'],
                        'description' => $roomData['description'] ?? null,
                    ]
                );

                $roomFacilityIds = collect($roomData['facilities'] ?? [])
                    ->map(fn (string $name) => $roomFacilities[$name] ?? null)
                    ->filter()
                    ->values()
                    ->all();
                $room->facilities()->sync($roomFacilityIds);
            }
        }

        $melati = BoardingHouse::where('slug', 'kos-putri-melati-ngebel')->first();
        $mahasiswa = User::where('email', 'mahasiswa@lokakos.test')->first();

        if ($melati) {
            Promo::updateOrCreate(
                ['code' => 'MABAUMY26'],
                [
                    'mitra_id' => $mitra->id,
                    'boarding_house_id' => null,
                    'title' => 'Potongan Awal Semester Mahasiswa UMY (Demo)',
                    'discount_type' => Promo::TYPE_FIXED,
                    'discount_value' => 150000,
                    'min_duration_months' => 6,
                    'valid_from' => now()->subDays(5)->toDateString(),
                    'valid_until' => now()->addMonths(3)->toDateString(),
                    'is_active' => true,
                ]
            );

            $firstRoom = $melati->roomTypes()->first();
            if ($firstRoom) {
                RoomUnit::updateOrCreate(
                    ['room_type_id' => $firstRoom->id, 'unit_code' => 'A-01'],
                    ['floor_label' => 'Lantai 1', 'status' => RoomUnit::STATUS_AVAILABLE]
                );
                RoomUnit::updateOrCreate(
                    ['room_type_id' => $firstRoom->id, 'unit_code' => 'A-02'],
                    ['floor_label' => 'Lantai 1', 'status' => RoomUnit::STATUS_OCCUPIED, 'tenant_name' => 'Penghuni Demo A-02', 'lease_ends_at' => now()->addMonths(5)->toDateString()]
                );
            }

            BoardingAnnouncement::updateOrCreate(
                ['boarding_house_id' => $melati->id, 'title' => 'Jadwal Perawatan Jaringan Wi-Fi Bulanan'],
                [
                    'mitra_id' => $mitra->id,
                    'body' => 'Pengecekan router lantai 1 dan 2 dijadwalkan setiap hari Minggu pukul 09.00 WIB sekitar 15 menit.',
                    'is_pinned' => true,
                ]
            );

            if ($mahasiswa) {
                BoardingQuestion::updateOrCreate(
                    ['boarding_house_id' => $melati->id, 'question' => 'Apakah area parkir motor beratap dan cukup untuk motor ukuran besar?'],
                    [
                        'user_id' => $mahasiswa->id,
                        'answer' => 'Iya, garasi motor seluruhnya beratap kanopi dengan pagar terkunci ganda dan muat untuk motor matic besar.',
                        'answered_by' => $mitra->id,
                        'answered_at' => now(),
                        'is_visible' => true,
                    ]
                );
            }

            MitraExpense::updateOrCreate(
                ['mitra_id' => $mitra->id, 'description' => 'Tagihan Internet Wi-Fi Kos Melati (Demo)'],
                [
                    'boarding_house_id' => $melati->id,
                    'category' => 'wifi',
                    'amount' => 350000,
                    'expense_date' => now()->startOfMonth()->toDateString(),
                ]
            );
        }
    }
}
