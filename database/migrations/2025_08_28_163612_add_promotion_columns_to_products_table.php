<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPromotionColumnsToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
public function up(): void
{
    Schema::table('products', function (Blueprint $table) {
        $table->decimal('promotion_price', 10, 2)->nullable()->after('sale_price');
        $table->unsignedTinyInteger('discount_percent')->nullable()->after('promotion_price');
    });
}

public function down(): void
{
    Schema::table('products', function (Blueprint $table) {
        $table->dropColumn(['promotion_price', 'discount_percent']);
    });
}

}
