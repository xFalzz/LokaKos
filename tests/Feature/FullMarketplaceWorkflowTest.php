<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\BoardingHouse;
use App\Models\Booking;
use App\Models\MitraProfile;
use App\Models\RoomType;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FullMarketplaceWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_middleware_blocks_cross_role_access(): void
    {
        $this->seed(DatabaseSeeder::class);

        $mitra = User::where('email', 'mitra1@lokakos.test')->firstOrFail();
        $penyewa = User::where('email', 'mahasiswa@lokakos.test')->firstOrFail();

        $this->actingAs($mitra)->get('/admin/mitra')->assertForbidden();
        $this->actingAs($penyewa)->get('/admin/kos')->assertForbidden();
        $this->actingAs($penyewa)->get('/mitra/kos')->assertForbidden();
        $this->actingAs($mitra)->get('/penyewa/pesanan')->assertForbidden();
    }

    public function test_mitra_cannot_edit_another_mitras_boarding_house_or_rooms(): void
    {
        $this->seed(DatabaseSeeder::class);

        $mitra3 = User::where('email', 'mitra3@lokakos.test')->firstOrFail();
        $kosMitra1 = BoardingHouse::where('status', BoardingHouse::STATUS_PUBLISHED)->with('roomTypes')->firstOrFail();
        $roomMitra1 = $kosMitra1->roomTypes->firstOrFail();

        $this->actingAs($mitra3)->get('/dashboard')
            ->assertOk()
            ->assertSee('Tambah Properti Kos Pertama')
            ->assertDontSee($kosMitra1->name);

        $this->actingAs($mitra3)->get(route('mitra.kos.edit', $kosMitra1))->assertForbidden();
        $this->actingAs($mitra3)->delete(route('mitra.kos.destroy', $kosMitra1))->assertForbidden();
        $this->actingAs($mitra3)->get(route('mitra.rooms.index', $kosMitra1))->assertForbidden();
        $this->actingAs($mitra3)->get(route('mitra.rooms.edit', [$kosMitra1, $roomMitra1]))->assertForbidden();
        $this->actingAs($mitra3)->patch(route('mitra.rooms.stock', [$kosMitra1, $roomMitra1]), ['delta' => -1])->assertForbidden();
    }

    public function test_mitra_can_edit_room_details_and_adjust_stock(): void
    {
        Storage::fake('public');
        $this->seed(DatabaseSeeder::class);

        $mitra1 = User::where('email', 'mitra1@lokakos.test')->firstOrFail();
        $kos = BoardingHouse::where('mitra_id', $mitra1->id)->with('roomTypes')->firstOrFail();
        $room = $kos->roomTypes->firstWhere('available_rooms', '>', 0);

        $this->actingAs($mitra1)
            ->put(route('mitra.rooms.update', [$kos, $room]), [
                'name' => $room->name . ' Renovasi',
                'size_m2' => '4 x 4 m',
                'price_monthly' => 1350000,
                'deposit_amount' => 250000,
                'electricity_included' => true,
                'max_occupancy' => 2,
                'total_rooms' => 6,
                'available_rooms' => 4,
                'room_image' => UploadedFile::fake()->image('kamar-baru.jpg'),
            ])
            ->assertRedirect(route('mitra.rooms.index', $kos));

        $room->refresh();
        $this->assertEquals(250000, $room->deposit_amount);
        $this->assertTrue($room->electricity_included);
        $this->assertEquals(2, $room->max_occupancy);
        $this->assertNotNull($room->room_image_path);

        $this->actingAs($mitra1)
            ->patch(route('mitra.rooms.stock', [$kos, $room]), ['delta' => -1])
            ->assertRedirect();
        $this->assertEquals(3, $room->fresh()->available_rooms);
    }

    public function test_penyewa_can_toggle_saved_favorite_kos_and_admin_can_unpublish(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@lokakos.test')->firstOrFail();
        $penyewa = User::where('email', 'mahasiswa@lokakos.test')->firstOrFail();
        $kos = BoardingHouse::published()->firstOrFail();

        $this->actingAs($penyewa)->post(route('penyewa.saved.toggle', $kos))->assertRedirect();
        $this->assertDatabaseHas('saved_boarding_houses', [
            'user_id' => $penyewa->id,
            'boarding_house_id' => $kos->id,
        ]);

        $this->actingAs($penyewa)->get(route('penyewa.saved.index'))
            ->assertOk()
            ->assertSee($kos->name);

        $this->actingAs($admin)->patch(route('admin.kos.unpublish', $kos))->assertRedirect();
        $this->assertEquals(BoardingHouse::STATUS_DRAFT, $kos->fresh()->status);
    }

    public function test_end_to_end_admin_mitra_penyewa_booking_payment_invoice_review_flow(): void
    {
        Storage::fake('public');
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@lokakos.test')->firstOrFail();
        $mitraPending = User::where('email', 'mitra2@lokakos.test')->firstOrFail();
        $penyewa = User::where('email', 'mahasiswa@lokakos.test')->firstOrFail();
        $area = Area::where('slug', 'ngebel')->firstOrFail();

        $this->actingAs($mitraPending)
            ->post(route('mitra.kos.store'), ['name' => 'Belum Boleh'])
            ->assertForbidden();

        $this->actingAs($admin)
            ->patch(route('admin.mitra.approve', $mitraPending->mitraProfile))
            ->assertRedirect();

        $this->assertEquals(
            MitraProfile::STATUS_APPROVED,
            $mitraPending->mitraProfile->fresh()->verification_status
        );

        $mitraPending->refresh();
        $mitraPending->unsetRelation('mitraProfile');

        $this->actingAs($mitraPending)
            ->post(route('mitra.kos.store'), [
                'name' => 'Kos Baru Mitra Dua',
                'area_id' => $area->id,
                'gender_type' => 'putri',
                'is_syariah' => true,
                'address' => 'Ngebel RT 02, Tamantirto',
                'latitude' => -7.8140,
                'longitude' => 110.3195,
                'description' => 'Kos nyaman dekat UMY.',
                'images' => [UploadedFile::fake()->image('kos.jpg')],
            ])
            ->assertRedirect();

        $kos = BoardingHouse::where('name', 'Kos Baru Mitra Dua')->firstOrFail();
        $this->assertEquals(BoardingHouse::STATUS_PENDING_REVIEW, $kos->status);
        $this->assertNotNull($kos->distance_to_umy_km);
        $this->assertEquals(1, $kos->images()->count());

        $this->actingAs($mitraPending)
            ->post(route('mitra.rooms.store', $kos), [
                'name' => 'Tipe AC Premium',
                'size_m2' => '3 x 4 m',
                'price_monthly' => 1200000,
                'max_occupancy' => 1,
                'total_rooms' => 3,
                'available_rooms' => 2,
            ])
            ->assertRedirect();

        $room = RoomType::where('boarding_house_id', $kos->id)->firstOrFail();

        $this->get(route('kos.show', $kos))->assertNotFound();

        $this->actingAs($admin)
            ->patch(route('admin.kos.approve', $kos))
            ->assertRedirect();
        $this->assertEquals(BoardingHouse::STATUS_PUBLISHED, $kos->fresh()->status);

        $this->actingAs($penyewa)
            ->post(route('penyewa.bookings.store'), [
                'room_type_id' => $room->id,
                'check_in_date' => now()->addDays(5)->toDateString(),
                'duration_months' => 3,
            ])
            ->assertRedirect();

        $booking = Booking::where('user_id', $penyewa->id)->where('room_type_id', $room->id)->firstOrFail();
        $this->assertEquals(Booking::STATUS_PENDING_PAYMENT, $booking->status);
        $this->assertEquals(3600000, $booking->total_price);

        $this->actingAs($penyewa)
            ->post(route('penyewa.bookings.payment', $booking), [
                'sender_bank' => 'BRI',
                'sender_name' => 'Mahasiswa Uji UMY',
                'proof_image' => UploadedFile::fake()->image('bukti.jpg'),
            ])
            ->assertRedirect();
        $this->assertEquals(Booking::STATUS_WAITING_VERIFICATION, $booking->fresh()->status);

        $this->actingAs($mitraPending)
            ->patch(route('mitra.bookings.approve', $booking))
            ->assertRedirect();

        $this->assertEquals(Booking::STATUS_CONFIRMED, $booking->fresh()->status);
        $this->assertEquals(1, $room->fresh()->available_rooms);

        $this->actingAs($penyewa)
            ->get(route('penyewa.bookings.invoice', $booking))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->actingAs($mitraPending)
            ->patch(route('mitra.bookings.complete', $booking))
            ->assertRedirect();
        $this->assertEquals(Booking::STATUS_COMPLETED, $booking->fresh()->status);

        $this->actingAs($penyewa)
            ->post(route('penyewa.bookings.review', $booking), [
                'rating' => 5,
                'comment' => 'Kamar bersih dan sangat dekat kampus UMY.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('reviews', [
            'booking_id' => $booking->id,
            'rating' => 5,
        ]);

        $this->actingAs($mitraPending)
            ->get(route('mitra.report.pdf'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->actingAs($admin)
            ->get(route('admin.report.pdf'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_penyewa_cannot_book_room_with_zero_available_stock(): void
    {
        $this->seed(DatabaseSeeder::class);

        $penyewa = User::where('email', 'mahasiswa@lokakos.test')->firstOrFail();
        $fullRoom = RoomType::where('available_rooms', 0)->firstOrFail();

        $this->actingAs($penyewa)
            ->post(route('penyewa.bookings.store'), [
                'room_type_id' => $fullRoom->id,
                'check_in_date' => now()->addDays(2)->toDateString(),
                'duration_months' => 1,
            ])
            ->assertSessionHas('error');

        $this->assertEquals(0, Booking::where('room_type_id', $fullRoom->id)->count());
    }

    public function test_mitra_can_configure_per_kos_whatsapp_contact_and_unique_auto_messages(): void
    {
        $this->seed(DatabaseSeeder::class);

        $mitra = User::where('email', 'mitra1@lokakos.test')->firstOrFail();
        $kosList = BoardingHouse::published()->with(['area', 'roomTypes', 'mitra.mitraProfile'])->orderBy('id')->take(3)->get();

        $messages = $kosList->map(fn (BoardingHouse $kos) => $kos->buildWhatsappMessage())->unique();
        $this->assertCount(3, $messages);

        $targetKos = $kosList->first();
        $this->actingAs($mitra)
            ->put(route('mitra.kos.update', $targetKos), [
                'name' => $targetKos->name,
                'area_id' => $targetKos->area_id,
                'gender_type' => $targetKos->gender_type,
                'is_syariah' => $targetKos->is_syariah ? 1 : 0,
                'address' => $targetKos->address,
                'latitude' => $targetKos->latitude,
                'longitude' => $targetKos->longitude,
                'rules' => $targetKos->rules,
                'description' => $targetKos->description,
                'contact_name' => 'Ibu Kartika (Penjaga Ngebel)',
                'contact_whatsapp' => '0813-9900-8877',
                'contact_phone' => '0274-889900',
                'contact_hours' => 'Setiap hari 07.00 - 21.00 WIB',
                'whatsapp_message_template' => 'Halo {pengelola}, saya ingin survei kamar di {nama_kos} area {area}.',
            ])
            ->assertRedirect(route('mitra.kos.edit', $targetKos));

        $targetKos->refresh();
        $this->assertSame('6281399008877', $targetKos->resolvedWhatsappDigits());
        $this->assertStringContainsString('Ibu Kartika (Penjaga Ngebel)', $targetKos->buildWhatsappMessage());
        $this->assertStringContainsString($targetKos->name, $targetKos->buildWhatsappMessage());

        $room = $targetKos->roomTypes->firstOrFail();
        $roomMessage = $targetKos->buildWhatsappMessage($room);
        $this->assertStringContainsString($room->name, $roomMessage);

        $this->get(route('kos.show', $targetKos))
            ->assertOk()
            ->assertSee('https://wa.me/6281399008877', false)
            ->assertSee('Ibu Kartika (Penjaga Ngebel)')
            ->assertSee('0274-889900')
            ->assertSee('Setiap hari 07.00 - 21.00 WIB')
            ->assertSee('Tanya Tipe Ini via WA');
    }
}
