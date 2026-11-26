<?php

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Seeder;

class FacilitySeeder extends Seeder
{
    public function run(): void
    {
        $facilities = [
            ['name' => 'Wi-Fi', 'category' => Facility::CATEGORY_KOS, 'icon_key' => 'wifi-high'],
            ['name' => 'Parkir Motor', 'category' => Facility::CATEGORY_KOS, 'icon_key' => 'moped'],
            ['name' => 'Parkir Mobil', 'category' => Facility::CATEGORY_KOS, 'icon_key' => 'car'],
            ['name' => 'Dapur Bersama', 'category' => Facility::CATEGORY_KOS, 'icon_key' => 'cooking-pot'],
            ['name' => 'CCTV 24 Jam', 'category' => Facility::CATEGORY_KOS, 'icon_key' => 'security-camera'],
            ['name' => 'Ruang Tamu', 'category' => Facility::CATEGORY_KOS, 'icon_key' => 'armchair'],
            ['name' => 'Area Jemuran', 'category' => Facility::CATEGORY_KOS, 'icon_key' => 'sun'],
            ['name' => 'Akses 24 Jam', 'category' => Facility::CATEGORY_KOS, 'icon_key' => 'clock'],

            ['name' => 'Kamar Mandi Dalam', 'category' => Facility::CATEGORY_KAMAR, 'icon_key' => 'shower'],
            ['name' => 'AC', 'category' => Facility::CATEGORY_KAMAR, 'icon_key' => 'wind'],
            ['name' => 'Kipas Angin', 'category' => Facility::CATEGORY_KAMAR, 'icon_key' => 'fan'],
            ['name' => 'Kasur & Bantal', 'category' => Facility::CATEGORY_KAMAR, 'icon_key' => 'bed'],
            ['name' => 'Lemari Pakaian', 'category' => Facility::CATEGORY_KAMAR, 'icon_key' => 'dresser'],
            ['name' => 'Meja Belajar', 'category' => Facility::CATEGORY_KAMAR, 'icon_key' => 'desk'],
            ['name' => 'Water Heater', 'category' => Facility::CATEGORY_KAMAR, 'icon_key' => 'thermometer-hot'],
            ['name' => 'Termasuk Listrik', 'category' => Facility::CATEGORY_KAMAR, 'icon_key' => 'plug'],
        ];

        foreach ($facilities as $facility) {
            Facility::updateOrCreate(
                [
                    'name' => $facility['name'],
                    'category' => $facility['category'],
                ],
                [
                    'icon_key' => $facility['icon_key'],
                ]
            );
        }
    }
}
