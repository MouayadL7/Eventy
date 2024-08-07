<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Relations\HasOne;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    public function orders() : HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function favourites()
    {
        return $this->hasMany(Favourite::class);
    }

    public function hasFav($service_id)
    {
        return $this->favourites()->where('service_id', $service_id)->exists();
    }

    public function hasRated($sponsor_id)
    {
        return $this->rates()->where('sponsor_id', $sponsor_id)->exists();
    }

    public function get_info()
    {
        return [
            'id' => $this->user->id,
            'name' => $this->first_name . ' ' . $this->last_name,
            'address' => $this->address,
            'gender' => $this->gender,
            'image' => $this->image
        ];
    }

}
