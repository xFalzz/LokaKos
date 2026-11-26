<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boarding_facility', function (Blueprint $table) {
            $table->foreignId('boarding_house_id')->constrained()->cascadeOnDelete();
            $table->foreignId('facility_id')->constrained()->cascadeOnDelete();
            $table->primary(['boarding_house_id', 'facility_id']);
        });

        Schema::create('room_facility', function (Blueprint $table) {
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('facility_id')->constrained()->cascadeOnDelete();
            $table->primary(['room_type_id', 'facility_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_facility');
        Schema::dropIfExists('boarding_facility');
    }
};
