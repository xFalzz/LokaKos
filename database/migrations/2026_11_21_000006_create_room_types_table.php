<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boarding_house_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('size_m2', 30)->nullable();
            $table->unsignedBigInteger('price_monthly');
            $table->unsignedBigInteger('price_3_months')->nullable();
            $table->unsignedBigInteger('price_6_months')->nullable();
            $table->unsignedBigInteger('price_yearly')->nullable();
            $table->unsignedInteger('total_rooms')->default(1);
            $table->unsignedInteger('available_rooms')->default(1);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['boarding_house_id', 'available_rooms']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_types');
    }
};
