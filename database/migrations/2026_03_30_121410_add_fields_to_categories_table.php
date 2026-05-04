<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */



public function up()
{
    // 1. Add new columns (slug nullable initially)
    Schema::table('categories', function (Blueprint $table) {
        $table->string('slug')->nullable()->after('name');
        $table->unsignedBigInteger('parent_id')->nullable()->after('slug');
        $table->foreign('parent_id')->references('id')->on('categories')->onDelete('cascade');
        $table->integer('position')->default(0)->after('parent_id');
        $table->boolean('status')->default(true)->after('position');
    });

    // 2. Generate a unique slug for every existing category
    $categories = DB::table('categories')->get();
    foreach ($categories as $category) {
        $slug = Str::slug($category->name);
        $original = $slug;
        $counter = 1;
        while (DB::table('categories')->where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
            $slug = $original . '-' . $counter++;
        }
        DB::table('categories')->where('id', $category->id)->update(['slug' => $slug]);
    }

    // 3. Make slug NOT NULL and UNIQUE
    Schema::table('categories', function (Blueprint $table) {
        $table->string('slug')->nullable(false)->unique()->change();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            //
        });
    }
};
