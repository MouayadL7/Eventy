<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoury extends Model
{
    use HasFactory;
    protected $hidden = [
        'created_at', 'updated_at'
    ];
    const ROLE_PLACES = 1;
    const ROLE_CATERING  = 2;
    const ROLE_SOUND  = 3;
    const ROLE_DECORATION  = 4;
    const ROLE_PHOTOGRAPHY  = 5;
    const ROLE_DESERT  = 6;
    const ROLE_CAR  = 7;
    const ROLE_ORGANIZER  = 8;
    const ROLE_CARDS  = 9;
    const ROLE_MAKEUPARTIST  = 10;
    protected $fillable = ['name'];
    public function services()
    {
        return $this->hasMany(service::class);
    }

    public function sponsors() {
        return $this->hasMany(Sponsor::class);
    }
}
