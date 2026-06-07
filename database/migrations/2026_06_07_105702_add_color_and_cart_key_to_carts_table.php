<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up()
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->string('cart_key')->nullable()->after('user_id');
            $table->foreignId('color_id')->nullable()->after('product_id')
                  ->constrained('product_colors')->nullOnDelete();
            $table->string('product_name')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('image_path')->nullable();
            $table->string('color_name')->nullable();
            $table->unique(['user_id', 'cart_key'])->whereNotNull('user_id');
        });
    }

    public function down()
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'cart_key']);
            $table->dropColumn(['cart_key', 'color_id', 'product_name', 'price', 'image_path', 'color_name']);
        });
    }
};