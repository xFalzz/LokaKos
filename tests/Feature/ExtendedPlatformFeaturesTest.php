<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\BoardingHouse;
use App\Models\BoardingQuestion;
use App\Models\Booking;
use App\Models\ListingReport;
use App\Models\MaintenanceTicket;
use App\Models\Promo;
use App\Models\RoomType;
use App\Models\RoomWaitlist;
use App\Models\SurveyBooking;
use App\Models\User;
use App\Models\UserNotification;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExtendedPlatformFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_map_compare_and_travel_estimates_work(): void
    {
        $this->seed(DatabaseSeeder::class);

        $kosList = BoardingHouse::published()->orderBy('id')->take(2)->get();
        $first = $kosList[0];
        $second = $kosList[1];

        $this->get(route('kos.index', ['verified' => 1]))
            ->assertOk()
            ->assertSee('Lihat di Peta UMY')
            ->assertSee($first->name);

        $this->get(route('kos.compare', ['ids' => $first->id . ',' . $second->id]))
            ->assertOk()
            ->assertSee($first->name)
            ->assertSee($second->name)
            ->assertSee('Aspek Perbandingan');

        $this->assertGreaterThan(0, $first->estimatedWalkingMinutes());
        $this->assertGreaterThan(0, $first->estimatedMotorMinutes());
        $this->assertGreaterThanOrEqual(50, $first->completenessScore()['percent']);
    }

    public function test_survey_booking_qa_and_waitlist_notification_flow_with_mitra_isolation(): void
    {
        $this->seed(DatabaseSeeder::class);

        $penyewa = User::where('email', 'mahasiswa@lokakos.test')->firstOrFail();
        $mitraOwner = User::where('email', 'mitra1@lokakos.test')->firstOrFail();
        $otherMitra = User::where('email', 'mitra3@lokakos.test')->firstOrFail();
        $kos = BoardingHouse::where('slug', 'kos-putri-melati-ngebel')->firstOrFail();
        $fullRoom = RoomType::where('available_rooms', 0)->firstOrFail();

        $this->actingAs($penyewa)
            ->post(route('penyewa.surveys.store', $kos), [
                'preferred_date' => now()->addDays(2)->toDateString(),
                'preferred_time' => '13.00 - 15.00 WIB',
                'notes' => 'Datang bersama orang tua',
            ])
            ->assertRedirect(route('penyewa.surveys.index'));

        $survey = SurveyBooking::latest()->firstOrFail();

        $this->actingAs($otherMitra)
            ->patch(route('mitra.surveys.update', $survey), ['status' => 'approved'])
            ->assertForbidden();

        $this->actingAs($mitraOwner)
            ->patch(route('mitra.surveys.update', $survey), [
                'status' => 'approved',
                'mitra_response' => 'Silakan datang sesuai jadwal.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('survey_bookings', [
            'id' => $survey->id,
            'status' => 'approved',
        ]);

        $this->actingAs($penyewa)
            ->post(route('kos.questions.store', $kos), [
                'question' => 'Apakah disediakan air galon di dapur bersama?',
            ])
            ->assertRedirect();

        $question = BoardingQuestion::latest('id')->firstOrFail();

        $this->actingAs($otherMitra)
            ->patch(route('mitra.questions.answer', $question), ['answer' => 'Tidak boleh'])
            ->assertForbidden();

        $this->actingAs($mitraOwner)
            ->patch(route('mitra.questions.answer', $question), [
                'answer' => 'Ya, tersedia dispenser air minum gratis di dapur.',
            ])
            ->assertRedirect();

        $this->get(route('kos.show', $kos))
            ->assertOk()
            ->assertSee('Ya, tersedia dispenser air minum gratis di dapur.');

        $this->actingAs($penyewa)
            ->post(route('penyewa.waitlist.toggle', $fullRoom))
            ->assertRedirect();

        $this->assertDatabaseHas('room_waitlists', [
            'user_id' => $penyewa->id,
            'room_type_id' => $fullRoom->id,
            'status' => RoomWaitlist::STATUS_WAITING,
        ]);

        $this->actingAs($mitraOwner)
            ->patch(route('mitra.rooms.stock', [$fullRoom->boardingHouse, $fullRoom]), ['delta' => 1])
            ->assertRedirect();

        $this->assertDatabaseHas('room_waitlists', [
            'user_id' => $penyewa->id,
            'room_type_id' => $fullRoom->id,
            'status' => RoomWaitlist::STATUS_NOTIFIED,
        ]);

        $this->assertTrue(
            UserNotification::where('user_id', $penyewa->id)
                ->where('type', 'waitlist_available')
                ->exists()
        );
    }

    public function test_promo_ktm_contract_qr_invoice_verification_renewal_maintenance_and_auto_expire(): void
    {
        Storage::fake('public');
        $this->seed(DatabaseSeeder::class);

        $penyewa = User::where('email', 'mahasiswa@lokakos.test')->firstOrFail();
        $mitra = User::where('email', 'mitra1@lokakos.test')->firstOrFail();
        $kos = BoardingHouse::where('slug', 'kos-putri-melati-ngebel')->firstOrFail();
        $room = $kos->roomTypes()->where('available_rooms', '>', 0)->firstOrFail();

        $this->actingAs($penyewa)
            ->post(route('penyewa.bookings.store'), [
                'room_type_id' => $room->id,
                'check_in_date' => now()->addDays(3)->toDateString(),
                'duration_months' => 6,
                'promo_code' => 'MABAUMY26',
                'identity_card' => UploadedFile::fake()->image('ktm-umy.jpg'),
            ])
            ->assertRedirect();

        $booking = Booking::latest('id')->firstOrFail();
        $this->assertSame('MABAUMY26', $booking->promo_code);
        $this->assertSame(150000, $booking->discount_amount);
        $this->assertSame($room->calculatePrice(6) - 150000, $booking->total_price);
        $this->assertNotNull($booking->identity_card_path);
        $this->assertNotNull($booking->verification_token);

        $this->actingAs($penyewa)
            ->post(route('penyewa.bookings.payment', $booking), [
                'sender_bank' => 'BRI',
                'sender_name' => $penyewa->name,
                'proof_image' => UploadedFile::fake()->image('bukti.jpg'),
            ])
            ->assertRedirect();

        $this->actingAs($mitra)
            ->patch(route('mitra.bookings.approve', $booking))
            ->assertRedirect();

        $booking->refresh();

        $this->actingAs($penyewa)
            ->get(route('penyewa.bookings.invoice', $booking))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->actingAs($penyewa)
            ->get(route('penyewa.bookings.contract', $booking))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->get($booking->verificationUrl())
            ->assertOk()
            ->assertSee('Dokumen Valid')
            ->assertSee($booking->booking_code);

        $this->actingAs($penyewa)
            ->post(route('penyewa.maintenance.store'), [
                'booking_id' => $booking->id,
                'category' => 'ac_kipas',
                'title' => 'Remote AC perlu ganti baterai',
                'description' => 'Remote AC tidak merespons sejak kemarin sore.',
            ])
            ->assertRedirect(route('penyewa.maintenance.index'));

        $ticket = MaintenanceTicket::latest('id')->firstOrFail();

        $this->actingAs($mitra)
            ->patch(route('mitra.tickets.update', $ticket), [
                'status' => 'resolved',
                'mitra_note' => 'Sudah diganti baterai baru.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('maintenance_tickets', [
            'id' => $ticket->id,
            'status' => 'resolved',
        ]);

        $this->actingAs($penyewa)
            ->post(route('penyewa.bookings.renew', $booking), ['duration_months' => 3])
            ->assertRedirect();

        $renewal = Booking::where('parent_booking_id', $booking->id)->firstOrFail();
        $renewal->update(['payment_due_at' => now()->subHour()]);

        Artisan::call('lokakos:expire-bookings');

        $renewal->refresh();
        $this->assertSame(Booking::STATUS_CANCELLED, $renewal->status);
    }

    public function test_mitra_units_promos_expenses_qr_poster_csv_and_admin_governance(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@lokakos.test')->firstOrFail();
        $mitra = User::where('email', 'mitra1@lokakos.test')->firstOrFail();
        $kos = BoardingHouse::where('slug', 'kos-putri-melati-ngebel')->firstOrFail();
        $room = $kos->roomTypes()->firstOrFail();

        $this->actingAs($mitra)
            ->post(route('mitra.units.store', [$kos, $room]), [
                'unit_code' => 'B-09',
                'floor_label' => 'Lantai 2',
                'status' => 'available',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('room_units', [
            'room_type_id' => $room->id,
            'unit_code' => 'B-09',
        ]);

        $this->actingAs($mitra)
            ->post(route('mitra.promos.store'), [
                'boarding_house_id' => $kos->id,
                'code' => 'HEMAT10',
                'title' => 'Diskon 10 Persen Tahunan',
                'discount_type' => 'percent',
                'discount_value' => 10,
                'min_duration_months' => 12,
                'valid_from' => now()->toDateString(),
                'valid_until' => now()->addMonth()->toDateString(),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('promos', ['code' => 'HEMAT10']);

        $this->actingAs($mitra)
            ->post(route('mitra.expenses.store'), [
                'boarding_house_id' => $kos->id,
                'category' => 'kebersihan',
                'description' => 'Iuran sampah dan kebersihan lorong',
                'amount' => 120000,
                'expense_date' => now()->toDateString(),
            ])
            ->assertRedirect();

        $this->actingAs($mitra)
            ->get(route('mitra.kos.poster-qr', $kos))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->actingAs($mitra)
            ->get(route('mitra.report.csv'))
            ->assertOk();

        $this->post(route('kos.report', $kos), [
            'reporter_name' => 'Mahasiswa Uji',
            'reason' => 'harga_tidak_sesuai',
            'details' => 'Harga di papan kos berbeda dengan yang tercantum di halaman.',
        ])->assertRedirect();

        $report = ListingReport::latest('id')->firstOrFail();

        $this->actingAs($admin)
            ->patch(route('admin.kos.verify-field', $kos))
            ->assertRedirect();

        $this->actingAs($admin)
            ->patch(route('admin.reports.handle', $report), [
                'status' => 'resolved',
                'admin_note' => 'Sudah dikonfirmasi dengan pengelola.',
                'unpublish_kos' => 1,
            ])
            ->assertRedirect();

        $kos->refresh();
        $this->assertSame(BoardingHouse::STATUS_DRAFT, $kos->status);

        $this->actingAs($admin)
            ->get(route('admin.audit.index'))
            ->assertOk()
            ->assertSee('report.resolved');

        $this->actingAs($admin)
            ->get(route('admin.report.csv'))
            ->assertOk();

        $this->assertTrue(ActivityLog::where('action', 'report.resolved')->exists());

        $this->actingAs($mitra)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Pusat Notifikasi');

        $this->actingAs($mitra)
            ->patch(route('notifications.read-all'))
            ->assertRedirect();
    }

    public function test_mitra_google_maps_resolver_extracts_pinpoint_coordinates_short_links_and_auto_fills_on_save(): void
    {
        $this->seed(DatabaseSeeder::class);

        $mitra = User::where('email', 'mitra1@lokakos.test')->firstOrFail();
        $area = \App\Models\Area::firstOrFail();

        $pinpointUrl = 'https://www.google.com/maps/place/Kos+Demo/@-7.8000000,110.3000000,17z/data=!3m1!4b1!4m6!3m5!1s0x0:0x0!8m2!3d-7.8138123!4d110.3198456!16s%2Fg%2F11demo';

        $this->actingAs($mitra)
            ->postJson(route('mitra.maps.resolve'), ['input' => $pinpointUrl])
            ->assertOk()
            ->assertJson([
                'ok' => true,
                'lat' => -7.8138123,
                'lng' => 110.3198456,
            ]);

        $this->actingAs($mitra)
            ->postJson(route('mitra.maps.resolve'), ['input' => "7°48'49.7\"S 110°19'11.4\"E"])
            ->assertOk()
            ->assertJson(['ok' => true]);

        Http::fake([
            'https://maps.app.goo.gl/*' => Http::response('', 302, [
                'Location' => 'https://www.google.com/maps/place/Kos+Ngebel/@-7.8141000,110.3201000,17z/data=!3d-7.8142500!4d110.3199500',
            ]),
        ]);

        $this->actingAs($mitra)
            ->postJson(route('mitra.maps.resolve'), ['input' => 'https://maps.app.goo.gl/testumy123'])
            ->assertOk()
            ->assertJson([
                'ok' => true,
                'lat' => -7.81425,
                'lng' => 110.31995,
                'source' => 'gmaps_redirect',
            ]);

        $this->actingAs($mitra)
            ->post(route('mitra.kos.store'), [
                'name' => 'Kos Auto Gmaps UMY',
                'area_id' => $area->id,
                'gender_type' => 'putri',
                'address' => 'Jl. Ngebel No. 99, Tamantirto',
                'google_maps_url' => $pinpointUrl,
            ])
            ->assertRedirect();

        $created = BoardingHouse::where('name', 'Kos Auto Gmaps UMY')->firstOrFail();
        $this->assertEquals(-7.8138123, $created->latitude);
        $this->assertEquals(110.3198456, $created->longitude);
        $this->assertSame($pinpointUrl, $created->google_maps_url);
        $this->assertNotNull($created->distance_to_umy_km);

        $published = BoardingHouse::published()->firstOrFail();
        $this->get(route('kos.show', $published))
            ->assertOk()
            ->assertSee('Buka di Google Maps');
    }
}
