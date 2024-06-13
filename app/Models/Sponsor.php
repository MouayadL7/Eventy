<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use App\Models\Rating;


class Sponsor extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'image',
        'work_experience',
        'categoury_id'
    ];
    protected $hidden = [
        'created_at', 'updated_at'
    ];
    public function user() : Morphone
    {
        return $this->morphOne(User::class , 'userable');
    }
    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function averageRating()
    {
        return $this->ratings()->avg('rating');
    }
    public function clients()
    {
        return $this->belongsToMany(Client::class, 'ratings')
                    ->withPivot('rating')
                    ->withTimestamps();
    }
    public function category() {
        return $this->belongsTo(Categoury::class);
    }
}
