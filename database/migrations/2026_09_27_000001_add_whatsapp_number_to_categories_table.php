<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('whatsapp_number', 20)->nullable()->after('image_url');
        });

        DB::table('categories')
            ->whereIn('name', ['Iqbal Herbal Store', 'اقبال ہربل اسٹور'])
            ->update([
                'whatsapp_number' => '03009362584',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('whatsapp_number');
        });
    }
};
