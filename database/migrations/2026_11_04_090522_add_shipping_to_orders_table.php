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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('shipping_courier', 40)->nullable()->after('shipping_village');
            $table->string('shipping_service', 60)->nullable()->after('shipping_courier');
            $table->string('shipping_etd', 40)->nullable()->after('shipping_service');
            $table->unsignedInteger('shipping_weight')->default(0)->after('shipping_etd');
            $table->unsignedBigInteger('shipping_cost')->default(0)->after('shipping_weight');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['shipping_courier', 'shipping_service', 'shipping_etd', 'shipping_weight', 'shipping_cost']);
        });
    }
};
