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
    const CATEGOURY_PLACES = 1;
    const CATEGOURY_CATERING  = 2;
    const CATEGOURY_SOUND  = 3;
    const CATEGOURY_DECORATION  = 4;
    const CATEGOURY_PHOTOGRAPHY  = 5;
    const CATEGOURY_DESERT  = 6;
    const CATEGOURY_CAR  = 7;
    const CATEGOURY_ORGANIZER  = 8;
    const CATEGOURY_CARDS  = 9;
    const CATEGOURY_MAKEUPARTIST  = 10;
    protected $fillable = ['name'];

    public function services()
    {
        return $this->hasMany(Service::class);
    }
}
