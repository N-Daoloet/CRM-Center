<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->unsignedBigInteger('shipping_province_id')->nullable()->after('shipping_address');
            $table->unsignedBigInteger('shipping_district_id')->nullable()->after('shipping_province_id');
            $table->unsignedBigInteger('shipping_sub_district_id')->nullable()->after('shipping_district_id');
            $table->string('shipping_zipcode', 20)->nullable()->after('shipping_sub_district_id');
            $table->text('shipping_address_line')->nullable()->after('shipping_zipcode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn([
                'shipping_province_id',
                'shipping_district_id',
                'shipping_sub_district_id',
                'shipping_zipcode',
                'shipping_address_line',
            ]);
        });
    }
};
