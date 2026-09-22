<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->string('group', 50)->default('general');
            $table->string('type', 20)->default('string'); // string, boolean, integer, json
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Seed default initial settings
        $defaults = [
            ['key' => 'whatsapp_number', 'value' => '03002922584', 'group' => 'whatsapp', 'type' => 'string', 'description' => 'Business WhatsApp mobile number'],
            ['key' => 'show_visitor_counter', 'value' => '1', 'group' => 'storefront', 'type' => 'boolean', 'description' => 'Show live active visitor counter'],
            ['key' => 'show_recent_orders', 'value' => '1', 'group' => 'storefront', 'type' => 'boolean', 'description' => 'Show privacy-safe recent purchase activity'],
            ['key' => 'shipping_cost', 'value' => '200', 'group' => 'shipping', 'type' => 'integer', 'description' => 'Standard delivery fee in PKR'],
            ['key' => 'free_shipping_threshold', 'value' => '2000', 'group' => 'shipping', 'type' => 'integer', 'description' => 'Minimum order amount for free shipping'],
            ['key' => 'slider_autoplay_duration', 'value' => '5', 'group' => 'storefront', 'type' => 'integer', 'description' => 'Homepage hero slider autoplay interval in seconds'],
            ['key' => 'store_email', 'value' => 'contact@capitalcart.pk', 'group' => 'general', 'type' => 'string', 'description' => 'Store support email'],
            ['key' => 'store_phone', 'value' => '03002922584', 'group' => 'general', 'type' => 'string', 'description' => 'Store contact phone number'],
            ['key' => 'store_city', 'value' => 'Islamabad', 'group' => 'general', 'type' => 'string', 'description' => 'Operating city'],
        ];

        foreach ($defaults as $item) {
            $item['created_at'] = now();
            $item['updated_at'] = now();
            DB::table('settings')->insert($item);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
