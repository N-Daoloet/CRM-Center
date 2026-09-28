<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('english_name')->nullable()->after('name');
            $table->unsignedBigInteger('province_id')->nullable()->after('channel');
            $table->unsignedBigInteger('district_id')->nullable()->after('province_id');
            $table->unsignedBigInteger('sub_district_id')->nullable()->after('district_id');
            $table->string('zipcode')->nullable()->after('address');
            $table->enum('status', ['active', 'inactive'])->default('active')->after('zipcode');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['english_name', 'province_id', 'district_id', 'sub_district_id', 'zipcode', 'status']);
        });
    }
};
