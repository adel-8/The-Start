<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Skip this migration if the column already uses the correct enum
        if (DB::connection()->getDriverName() !== 'mysql') {
            return; // SQLite doesn't support ENUM, we'll skip
        }
        DB::statement("ALTER TABLE orders MODIFY payment_method ENUM('cash_on_delivery', 'stripe', 'baridimob') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE orders MODIFY payment_method ENUM('cash_on_delivery', 'stripe') NOT NULL");
    }
};