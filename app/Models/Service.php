<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Service extends Model
{
    use HasApiTokens,HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'categoury_id',
        'type',
        'contact_number',
        'rating',
        'price',
        'location',
        'description',
        'profile_image',

    ];
    public function images()
    {
        return $this->hasMany(ServiceImage::class);
    }
    public function categoury()
    {
        return $this->belongsTo(Categoury::class);
    }

    public function sponsor() : HasOne
    {
        return $this->hasOne(Sponsor::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favourite::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
    // Check if the service is available on a given date

    public function cart_items(): HasMany
    {
        return $this->hasMany(Cart_item::class, 'service_id');
    }
}

