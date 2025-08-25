<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPromotionDatesToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
public function up()
{
    Schema::table('products', function (Blueprint $table) {
        $table->dateTime('promotion_start')->nullable();
        $table->dateTime('promotion_end')->nullable();
    });
}

public function down()
{
    Schema::table('products', function (Blueprint $table) {
        $table->dropColumn(['promotion_start', 'promotion_end']);
    });
}

}
