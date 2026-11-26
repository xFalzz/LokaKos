<?php

namespace Database\Seeders;

use App\Models\Area;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AreaSeeder extends Seeder
{
    public function run(): void
    {
        $areas = [
            [
                'name' => 'Ngebel',
                'description' => 'Kawasan terdekat di sekitar Kampus Terpadu UMY dengan banyak akses jalan kaki menuju gerbang kampus.',
            ],
            [
                'name' => 'Geblagan',
                'description' => 'Wilayah sekitar Jalan Brawijaya dan gerbang utama UMY dengan akses mudah ke kuliner dan minimarket.',
            ],
            [
                'name' => 'Brajan',
                'description' => 'Lingkungan permukiman tenang di sekitar Tamantirto yang banyak diminati mahasiswa tingkat akhir.',
            ],
            [
                'name' => 'Tamantirto',
                'description' => 'Area inti kelurahan lokasi Kampus Terpadu UMY, dekat fasilitas umum dan jalur angkutan.',
            ],
            [
                'name' => 'Rukeman',
                'description' => 'Kawasan sebelah selatan dan timur Tamantirto dengan pilihan kos ekonomis hingga eksklusif.',
            ],
            [
                'name' => 'Kasihan',
                'description' => 'Wilayah kecamatan sekitar kampus dengan akses cepat menuju pusat Kota Yogyakarta.',
            ],
            [
                'name' => 'Ambarketawang',
                'description' => 'Area sisi barat Ringroad dekat Jalan Wates, strategis untuk mahasiswa yang membawa kendaraan.',
            ],
            [
                'name' => 'Ringroad Barat',
                'description' => 'Koridor utama sepanjang Jalan Brawijaya dengan akses transportasi antarwilayah yang lancar.',
            ],
        ];

        foreach ($areas as $item) {
            Area::updateOrCreate(
                ['slug' => Str::slug($item['name'])],
                [
                    'name' => $item['name'],
                    'description' => $item['description'],
                ]
            );
        }
    }
}
