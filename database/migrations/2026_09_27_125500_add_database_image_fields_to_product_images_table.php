<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->binary('image_data')->nullable()->after('image_url');
            $table->string('mime_type', 100)->nullable()->after('image_data');
            $table->unsignedInteger('file_size')->nullable()->after('mime_type');
        });
    }

    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropColumn(['image_data', 'mime_type', 'file_size']);
        });
    }
};
