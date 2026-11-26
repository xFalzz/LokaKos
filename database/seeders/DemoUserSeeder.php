<?php

namespace Database\Seeders;

use App\Models\MitraProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@lokakos.test'],
            [
                'name' => 'Admin LokaKos',
                'phone' => '081200000001',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
                'is_active' => true,
            ]
        );

        $mitraApproved1 = User::updateOrCreate(
            ['email' => 'mitra1@lokakos.test'],
            [
                'name' => 'Pengelola Kos Demo 1',
                'phone' => '081200000002',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role' => User::ROLE_MITRA,
                'is_active' => true,
            ]
        );

        MitraProfile::updateOrCreate(
            ['user_id' => $mitraApproved1->id],
            [
                'business_name' => 'Kos Demo Tamantirto',
                'bank_name' => 'BRI',
                'bank_account_number' => '0000-01-000001-50-1',
                'bank_account_name' => 'Pengelola Kos Demo 1',
                'verification_status' => MitraProfile::STATUS_APPROVED,
                'verified_by' => $admin->id,
                'verified_at' => now(),
            ]
        );

        $mitraPending = User::updateOrCreate(
            ['email' => 'mitra2@lokakos.test'],
            [
                'name' => 'Pengelola Kos Demo 2',
                'phone' => '081200000003',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role' => User::ROLE_MITRA,
                'is_active' => true,
            ]
        );

        MitraProfile::updateOrCreate(
            ['user_id' => $mitraPending->id],
            [
                'business_name' => 'Kos Demo Ngebel',
                'bank_name' => 'BSI',
                'bank_account_number' => '7000000002',
                'bank_account_name' => 'Pengelola Kos Demo 2',
                'verification_status' => MitraProfile::STATUS_PENDING,
            ]
        );

        $mitraApproved2 = User::updateOrCreate(
            ['email' => 'mitra3@lokakos.test'],
            [
                'name' => 'Pengelola Kos Demo 3',
                'phone' => '081200000005',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role' => User::ROLE_MITRA,
                'is_active' => true,
            ]
        );

        MitraProfile::updateOrCreate(
            ['user_id' => $mitraApproved2->id],
            [
                'business_name' => 'Kos Demo Geblagan',
                'bank_name' => 'BCA',
                'bank_account_number' => '8000000003',
                'bank_account_name' => 'Pengelola Kos Demo 3',
                'verification_status' => MitraProfile::STATUS_APPROVED,
                'verified_by' => $admin->id,
                'verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'mahasiswa@lokakos.test'],
            [
                'name' => 'Mahasiswa Uji UMY',
                'phone' => '081200000004',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role' => User::ROLE_PENYEWA,
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'penyewa@lokakos.test'],
            [
                'name' => 'Penyewa Demo UMY',
                'phone' => '081200000006',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role' => User::ROLE_PENYEWA,
                'is_active' => true,
            ]
        );
    }
}
