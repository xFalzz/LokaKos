<?php

namespace Tests\Feature;

use App\Models\BoardingHouse;
use App\Models\MitraProfile;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders_lokakos_and_only_shows_published_listings(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('LokaKos');
        $response->assertSee('Kos Putri Melati Ngebel');
        $response->assertDontSee('Kos Putri Cendana Kasihan (Menunggu Moderasi)');
    }

    public function test_catalog_filters_by_area_gender_syariah_and_handles_empty_state(): void
    {
        $this->seed(DatabaseSeeder::class);

        $filtered = $this->get('/kos?area=ngebel&gender=putri&syariah=1');
        $filtered->assertOk();
        $filtered->assertSee('Kos Putri Melati Ngebel');
        $filtered->assertDontSee('Kos Putra Griya Geblagan');

        $empty = $this->get('/kos?q=nama-kos-tidak-ada-xyz');
        $empty->assertOk();
        $empty->assertSee('Tidak ada kos yang cocok dengan filter ini');
    }

    public function test_kos_detail_shows_published_and_returns_404_for_pending_review(): void
    {
        $this->seed(DatabaseSeeder::class);

        $published = BoardingHouse::where('status', BoardingHouse::STATUS_PUBLISHED)->firstOrFail();
        $pending = BoardingHouse::where('status', BoardingHouse::STATUS_PENDING_REVIEW)->firstOrFail();

        $this->get(route('kos.show', $published))
            ->assertOk()
            ->assertSee($published->name);

        $this->get(route('kos.show', $pending))->assertNotFound();
    }

    public function test_mitra_info_and_mitra_registration_work(): void
    {
        $this->get('/gabung-mitra')->assertOk()->assertSee('Daftar Sebagai Mitra Kos');
        $this->get('/register/mitra')->assertOk()->assertSee('Daftar Mitra Pemilik Kos');

        $response = $this->post('/register/mitra', [
            'name' => 'Pemilik Baru UMY',
            'email' => 'mitra-baru@lokakos.test',
            'phone' => '081234567890',
            'business_name' => 'Kos Baru Geblagan',
            'bank_name' => 'BRI',
            'bank_account_number' => '1234567890',
            'bank_account_name' => 'Pemilik Baru UMY',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('users', [
            'email' => 'mitra-baru@lokakos.test',
            'role' => User::ROLE_MITRA,
        ]);
        $this->assertDatabaseHas('mitra_profiles', [
            'business_name' => 'Kos Baru Geblagan',
            'verification_status' => MitraProfile::STATUS_PENDING,
        ]);
    }

    public function test_dashboard_renders_for_admin_mitra_and_penyewa(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@lokakos.test')->firstOrFail();
        $mitra = User::where('email', 'mitra1@lokakos.test')->firstOrFail();
        $penyewa = User::where('email', 'mahasiswa@lokakos.test')->firstOrFail();

        $this->actingAs($admin)->get('/dashboard')->assertOk()->assertSee('Ringkasan Back-Office LokaKos');
        $this->actingAs($mitra)->get('/dashboard')->assertOk()->assertSee('Dashboard Pengelolaan Kos');
        $this->actingAs($penyewa)->get('/dashboard')->assertOk()->assertSee('Ringkasan Sewa & Kos Incaran', false);
    }
}
