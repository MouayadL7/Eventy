<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Relations\HasOne;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'address',
        'gender',
        'image',
    ];

    public function user() : MorphOne
    {
        return $this->morphOne(User::class , 'userable');
    }

    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class, 'client_id');
    }

    public function rates() {
        return $this->hasMany(Rating::class);
    }
    public function sponsors()
    {
        return $this->belongsToMany(Sponsor::class, 'ratings')
                    ->withPivot('rating')
                    ->withTimestamps();
    }
}
