<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
class service extends Model
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
      public function categoury()
      {
          return $this->belongsTo(Categoury::class);
      }
}

