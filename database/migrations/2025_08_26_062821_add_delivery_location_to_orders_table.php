<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeliveryLocationToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::table('orders', function (Blueprint $table) {
        $table->decimal('delivery_latitude', 10, 7)->nullable();
        $table->decimal('delivery_longitude', 10, 7)->nullable();
    });
}
    /**
     * Reverse the migrations.
     *
     * @return void
     */public function down()
{
    Schema::table('orders', function (Blueprint $table) {
        $table->dropColumn(['delivery_latitude', 'delivery_longitude']);
    });
}
}
