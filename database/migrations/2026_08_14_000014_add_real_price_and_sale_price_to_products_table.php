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
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('real_price', 12, 2)->nullable()->after('price');
            $table->decimal('sale_price', 12, 2)->nullable()->after('real_price');
        });

        DB::table('products')->update([
            'real_price' => DB::raw('price'),
            'sale_price' => DB::raw('price'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['real_price', 'sale_price']);
        });
    }
};
