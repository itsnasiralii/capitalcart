<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Keep binary payloads in a separate table so normal product/image
        // queries do not load megabytes of image data into memory.
        Schema::create('product_image_blobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_image_id')
                ->unique()
                ->constrained('product_images')
                ->cascadeOnDelete();
            $table->binary('image_data');
            $table->string('mime_type', 100);
            $table->unsignedInteger('file_size')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_image_blobs');
    }
};
