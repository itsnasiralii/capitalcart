<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * FIX: Upgrade product_image_blobs.image_data from BLOB (64KB max) to LONGBLOB (4GB max)
 * 
 * The original migration used $table->binary() which maps to:
 *   - MySQL: BLOB (max 65,535 bytes = ~64KB) ← TOO SMALL for images!
 *   - PostgreSQL: bytea (unlimited) ← already OK
 *
 * Product images can easily be 1-5MB, causing a data truncation error → HTTP 500.
 * This migration upgrades MySQL column to LONGBLOB. PostgreSQL needs no change.
 */
return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE product_image_blobs MODIFY COLUMN image_data LONGBLOB NOT NULL');
            echo "MySQL: image_data column upgraded to LONGBLOB\n";
        } elseif ($driver === 'pgsql') {
            // PostgreSQL bytea has no size limit - already fine, nothing to do
            echo "PostgreSQL: bytea column already supports unlimited size, no change needed\n";
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE product_image_blobs MODIFY COLUMN image_data BLOB NOT NULL');
        }
    }
};
