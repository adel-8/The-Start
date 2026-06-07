<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('carts', function (Blueprint $table) {
            // Add cart_key – unique identifier for the cart item (e.g., "123_5")
            $table->string('cart_key')->nullable()->after('user_id');
            // Add color_id foreign key
            $table->foreignId('color_id')->nullable()->after('product_id')
                  ->constrained('product_colors')->nullOnDelete();
            // Store product name, price, image, color_name as snapshot (in case product changes)
            $table->string('product_name')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('image_path')->nullable();
            $table->string('color_name')->nullable();

            // Add unique constraint for user + cart_key (only when user is logged in)
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
}