<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->integer('position')->default(0)->after('link');
            $table->boolean('status')->default(true)->after('position');
            $table->datetime('starts_at')->nullable()->after('status');
            $table->datetime('ends_at')->nullable()->after('starts_at');
            $table->enum('device_type', ['all', 'mobile', 'desktop'])->default('all')->after('ends_at');
            $table->unsignedInteger('clicks')->default(0)->after('device_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            //
        });
    }
};
