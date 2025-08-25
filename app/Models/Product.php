<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
    'name', 'slug', 'description', 'price', 'stock', 'category_id', 'images',
    'promotion_start', 'promotion_end'
];

    protected $casts = ['images' => 'array'];

    public function category() { return $this->belongsTo(Category::class); }
}
