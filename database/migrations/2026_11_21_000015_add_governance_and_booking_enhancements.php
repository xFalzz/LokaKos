<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('boarding_houses', function (Blueprint $table) {
            $table->boolean('is_field_verified')->default(false)->after('status');
            $table->timestamp('field_verified_at')->nullable()->after('is_field_verified');
            $table->text('nearby_landmarks')->nullable()->after('description');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('parent_booking_id')->nullable()->after('room_type_id')->constrained('bookings')->nullOnDelete();
            $table->string('identity_card_path')->nullable()->after('notes');
            $table->string('promo_code', 40)->nullable()->after('total_price');
            $table->unsignedBigInteger('discount_amount')->default(0)->after('promo_code');
            $table->timestamp('payment_due_at')->nullable()->after('status');
            $table->string('verification_token', 64)->nullable()->unique()->after('cancellation_reason');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_booking_id');
            $table->dropColumn([
                'identity_card_path',
                'promo_code',
                'discount_amount',
                'payment_due_at',
                'verification_token',
            ]);
        });

        Schema::table('boarding_houses', function (Blueprint $table) {
            $table->dropColumn([
                'is_field_verified',
                'field_verified_at',
                'nearby_landmarks',
            ]);
        });
    }
};
