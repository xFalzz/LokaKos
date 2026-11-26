<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->string('unit_code', 30);
            $table->string('floor_label', 30)->nullable();
            $table->enum('status', ['available', 'occupied', 'maintenance'])->default('available');
            $table->foreignId('current_booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->string('tenant_name', 120)->nullable();
            $table->date('lease_ends_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['room_type_id', 'unit_code']);
        });

        Schema::create('maintenance_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('boarding_house_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('category', 60);
            $table->string('title', 150);
            $table->text('description');
            $table->string('photo_path')->nullable();
            $table->enum('status', ['open', 'in_progress', 'resolved'])->default('open');
            $table->text('mitra_note')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['boarding_house_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_tickets');
        Schema::dropIfExists('room_units');
    }
};
