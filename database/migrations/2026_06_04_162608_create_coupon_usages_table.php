<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('guest_email')->nullable();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->index(['coupon_id', 'user_id']);
            $table->index(['coupon_id', 'guest_email']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('coupon_usages');
    }
};