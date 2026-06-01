<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('orders', 'stripe_session_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('stripe_session_id')->nullable()->unique()->after('payment_method');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('orders', 'stripe_session_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropUnique(['stripe_session_id']);
                $table->dropColumn('stripe_session_id');
            });
        }
    }
};
