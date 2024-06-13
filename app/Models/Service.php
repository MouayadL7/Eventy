<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Service extends Model
{
    use HasApiTokens,HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
      protected $fillable = [
        'name' ,
        'categoury_id' ,
        'type' ,
        'contact_number' ,
        'rating' ,
        'price' ,
        'location',
        'description',
        'image',
      ];
       // Cast availability field as an array
    protected $casts = [
    ];
      public function categoury()
      {
          return $this->belongsTo(Categoury::class);
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

    public function cart() : BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }
    public function cartItems(): HasMany
    {
        return $this->hasMany(Cart_item::class);
    }
}

