<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Append verify_status to API responses
    protected $appends = ['verify_status'];

    /**
     * Get verification status for frontend
     *
     * @return string
     */
    // public function getVerifyStatusAttribute()
    // {
    //     return $this->email_verified_at ? 'completed' : 'pending';
    // }

    // User's favorites
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    // User's cart items
    public function cart()
    {
            return $this->hasMany(\App\Models\Cart::class);
    }
}

