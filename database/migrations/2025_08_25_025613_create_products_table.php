<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('sale_price', 10, 2)->nullable(); // discounted price
            $table->integer('stock')->default(0);
            $table->boolean('in_stock')->default(true); // easy availability check
            $table->unsignedBigInteger('category_id')->nullable();
            $table->json('images')->nullable(); // multiple images
            $table->string('sku')->nullable()->unique(); // product SKU
            $table->string('barcode')->nullable()->unique(); // optional barcode
            $table->boolean('featured')->default(false); // highlight product
            $table->boolean('is_active')->default(true); // published/unpublished
            $table->integer('weight')->nullable(); // grams
            $table->integer('length')->nullable(); // cm
            $table->integer('width')->nullable();  // cm
            $table->integer('height')->nullable(); // cm
            $table->decimal('rating', 2, 1)->default(0); // average rating
            $table->integer('sold_count')->default(0); // number sold
            $table->timestamps();

            // foreign key
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
};
