<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

protected $fillable = [
    'name', 'slug', 'description', 'price', 'sale_price', 'stock', 'in_stock',
    'category_id', 'images', 'sku', 'barcode', 'featured', 'is_active',
    'weight', 'length', 'width', 'height', 'rating', 'sold_count',
    'promotion_start', 'promotion_end'
];


    protected $casts = ['images' => 'array'];

    public function category() { return $this->belongsTo(Category::class); }
}
