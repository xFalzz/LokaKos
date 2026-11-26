<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\BoardingHouse;
use App\Models\Booking;
use App\Models\Facility;
use App\Models\MitraProfile;
use App\Models\RoomType;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class LokaKosFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeders_populate_umy_areas_facilities_and_demo_users(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertEquals(8, Area::count());
        $this->assertEquals(16, Facility::count());
        $this->assertDatabaseHas('areas', ['slug' => 'ngebel']);
        $this->assertDatabaseHas('areas', ['slug' => 'tamantirto']);
        $this->assertDatabaseHas('users', ['email' => 'admin@lokakos.test', 'role' => User::ROLE_ADMIN]);
        $this->assertDatabaseHas('users', ['email' => 'mitra1@lokakos.test', 'role' => User::ROLE_MITRA]);
        $this->assertDatabaseHas('users', ['email' => 'mahasiswa@lokakos.test', 'role' => User::ROLE_PENYEWA]);
    }

    public function test_boarding_house_calculates_haversine_distance_to_umy(): void
    {
        $this->seed(DatabaseSeeder::class);

        $mitra = User::where('email', 'mitra1@lokakos.test')->firstOrFail();
        $area = Area::where('slug', 'ngebel')->firstOrFail();

        $kos = BoardingHouse::create([
            'mitra_id' => $mitra->id,
            'area_id' => $area->id,
            'name' => 'Kos Uji Ngebel',
            'slug' => 'kos-uji-ngebel',
            'gender_type' => 'putri',
            'is_syariah' => true,
            'address' => 'Ngebel, Tamantirto, Kasihan, Bantul',
            'latitude' => -7.8145000,
            'longitude' => 110.3190000,
            'status' => BoardingHouse::STATUS_PENDING_REVIEW,
        ]);

        $this->assertNotNull($kos->distance_to_umy_km);
        $this->assertGreaterThan(0.2, $kos->distance_to_umy_km);
        $this->assertLessThan(1.0, $kos->distance_to_umy_km);
    }

    public function test_room_type_calculates_package_and_fallback_prices(): void
    {
        $this->seed(DatabaseSeeder::class);

        $mitra = User::where('email', 'mitra1@lokakos.test')->firstOrFail();
        $area = Area::where('slug', 'tamantirto')->firstOrFail();

        $kos = BoardingHouse::create([
            'mitra_id' => $mitra->id,
            'area_id' => $area->id,
            'name' => 'Kos Uji Tarif',
            'slug' => 'kos-uji-tarif',
            'gender_type' => 'putra',
            'address' => 'Tamantirto, Kasihan, Bantul',
        ]);

        $room = RoomType::create([
            'boarding_house_id' => $kos->id,
            'name' => 'Tipe AC KM Dalam',
            'price_monthly' => 1000000,
            'price_6_months' => 5700000,
            'price_yearly' => 11000000,
            'total_rooms' => 5,
            'available_rooms' => 3,
        ]);

        $this->assertEquals(1000000, $room->calculatePrice(1));
        $this->assertEquals(3000000, $room->calculatePrice(3));
        $this->assertEquals(5700000, $room->calculatePrice(6));
        $this->assertEquals(11000000, $room->calculatePrice(12));
        $this->assertTrue($room->hasAvailableStock());
    }

    public function test_multi_mitra_policies_isolate_boarding_house_and_booking_ownership(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@lokakos.test')->firstOrFail();
        $mitraApproved = User::where('email', 'mitra1@lokakos.test')->firstOrFail();
        $mitraOther = User::where('email', 'mitra2@lokakos.test')->firstOrFail();
        $penyewa = User::where('email', 'mahasiswa@lokakos.test')->firstOrFail();
        $area = Area::where('slug', 'tamantirto')->firstOrFail();

        $kosMilikMitra1 = BoardingHouse::create([
            'mitra_id' => $mitraApproved->id,
            'area_id' => $area->id,
            'name' => 'Kos Mitra Satu',
            'slug' => 'kos-mitra-satu',
            'gender_type' => 'campur',
            'address' => 'Tamantirto, Kasihan',
            'status' => BoardingHouse::STATUS_PUBLISHED,
        ]);

        $room = RoomType::create([
            'boarding_house_id' => $kosMilikMitra1->id,
            'name' => 'Tipe Standar',
            'price_monthly' => 800000,
            'total_rooms' => 4,
            'available_rooms' => 2,
        ]);

        $booking = Booking::create([
            'booking_code' => Booking::generateCode(),
            'user_id' => $penyewa->id,
            'room_type_id' => $room->id,
            'check_in_date' => now()->addDays(7)->toDateString(),
            'duration_months' => 1,
            'total_price' => 800000,
            'status' => Booking::STATUS_PENDING_PAYMENT,
        ]);

        $this->assertTrue(Gate::forUser($mitraApproved)->allows('create', BoardingHouse::class));
        $this->assertFalse(Gate::forUser($mitraOther)->allows('create', BoardingHouse::class));

        $this->assertTrue(Gate::forUser($mitraApproved)->allows('update', $kosMilikMitra1));
        $this->assertFalse(Gate::forUser($mitraOther)->allows('update', $kosMilikMitra1));
        $this->assertTrue(Gate::forUser($admin)->allows('update', $kosMilikMitra1));

        $this->assertTrue(Gate::forUser($mitraApproved)->allows('verifyAsMitra', $booking));
        $this->assertFalse(Gate::forUser($mitraOther)->allows('verifyAsMitra', $booking));
        $this->assertTrue(Gate::forUser($penyewa)->allows('cancelAsPenyewa', $booking));
        $this->assertFalse(Gate::forUser($mitraApproved)->allows('cancelAsPenyewa', $booking));
    }
}
