<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('room_types', function (Blueprint $table) {
            $table->unsignedBigInteger('deposit_amount')->default(0)->after('price_yearly');
            $table->boolean('electricity_included')->default(false)->after('deposit_amount');
            $table->unsignedTinyInteger('max_occupancy')->default(1)->after('electricity_included');
            $table->string('room_image_path')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('room_types', function (Blueprint $table) {
            $table->dropColumn([
                'deposit_amount',
                'electricity_included',
                'max_occupancy',
                'room_image_path',
            ]);
        });
    }
};
