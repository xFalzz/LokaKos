<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('boarding_houses', function (Blueprint $table) {
            $table->string('contact_name', 100)->nullable()->after('description');
            $table->string('contact_whatsapp', 25)->nullable()->after('contact_name');
            $table->string('contact_phone', 25)->nullable()->after('contact_whatsapp');
            $table->string('contact_hours', 80)->nullable()->after('contact_phone');
            $table->text('whatsapp_message_template')->nullable()->after('contact_hours');
        });
    }

    public function down(): void
    {
        Schema::table('boarding_houses', function (Blueprint $table) {
            $table->dropColumn([
                'contact_name',
                'contact_whatsapp',
                'contact_phone',
                'contact_hours',
                'whatsapp_message_template',
            ]);
        });
    }
};
