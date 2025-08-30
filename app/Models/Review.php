<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'comment',
        'rating',
    ];

    // Relation to Product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Relation to User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
