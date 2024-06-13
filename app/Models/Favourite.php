<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Client;
use App\Models\service;

class Favourite extends Model
{
    use HasFactory;

    protected $table = 'favourites';
    protected $fillable = ['client_id', 'service_id'];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

}
