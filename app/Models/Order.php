<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    // Allow mass assignment for these fields
    protected $fillable = [
        'user_id',
        'total',
        'status',
        'delivery_latitude',
        'delivery_longitude',
    ];

    // Optional: cast latitude/longitude to float
    protected $casts = [
        'delivery_latitude' => 'float',
        'delivery_longitude' => 'float',
        'total' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
