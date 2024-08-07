<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use App\Models\Rating;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Sponsor extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'image',
        'work_experience',
        'service_id',
        'location'

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
        return $this->ratings->avg('rating');
    }

    public function clients()
    {
        return $this->belongsToMany(Client::class, 'ratings')
                    ->withPivot('rating')
                    ->withTimestamps();
    }

    public function service() : BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function abrove() : HasOne
    {
        return $this->hasOne(Abrove::class);
    }

    public function get_info()
    {
        return [
            'id' => $this->user->id,
            'name' => $this->first_name . ' ' . $this->last_name,
            'work_experience' => $this->work_experience,
            'location' => $this->location,
            'image' => $this->image,
            'rating' => $this->averageRating() == null ? 0 : $this->averageRating()
        ];
    }
}
