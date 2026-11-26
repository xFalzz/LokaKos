<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mitra_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('boarding_house_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('code', 40)->unique();
            $table->string('title', 120);
            $table->enum('discount_type', ['fixed', 'percent'])->default('fixed');
            $table->unsignedBigInteger('discount_value');
            $table->unsignedTinyInteger('min_duration_months')->default(1);
            $table->date('valid_from');
            $table->date('valid_until');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['mitra_id', 'is_active']);
        });

        Schema::create('boarding_announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boarding_house_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mitra_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 150);
            $table->text('body');
            $table->boolean('is_pinned')->default(false);
            $table->timestamps();

            $table->index(['boarding_house_id', 'is_pinned']);
        });

        Schema::create('mitra_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mitra_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('boarding_house_id')->nullable()->constrained()->nullOnDelete();
            $table->string('category', 60);
            $table->string('description', 200);
            $table->unsignedBigInteger('amount');
            $table->date('expense_date');
            $table->timestamps();

            $table->index(['mitra_id', 'expense_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mitra_expenses');
        Schema::dropIfExists('boarding_announcements');
        Schema::dropIfExists('promos');
    }
};
