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

    public function get_list($favourites)
    {
        foreach ($favourites as $key => $favourite) {
            $favourites[$key] = $this->get_info($favourite);
        }
        return $favourites;
    }

    public function get_info($favourite)
    {
        return [
            'id' => $favourite->id,
            'service' =>$favourite->service
        ];
    }
}
