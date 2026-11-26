<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boarding_houses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mitra_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('area_id')->constrained('areas')->restrictOnDelete();
            $table->string('name', 150);
            $table->string('slug', 180)->unique();
            $table->enum('gender_type', ['putra', 'putri', 'campur']);
            $table->boolean('is_syariah')->default(false);
            $table->text('address');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('distance_to_umy_km', 5, 2)->nullable();
            $table->text('rules')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['draft', 'pending_review', 'published', 'rejected'])->default('pending_review');
            $table->text('rejection_note')->nullable();
            $table->timestamps();

            $table->index(['status', 'area_id', 'gender_type']);
            $table->index(['status', 'distance_to_umy_km']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boarding_houses');
    }
};
