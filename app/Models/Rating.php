<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Sponsor;

class Rating extends Model
{
    use HasFactory;
    protected $fillable = ['client_id', 'sponsor_id', 'rating'];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function sponsor()
    {
        return $this->belongsTo(Sponsor::class);
    }
}
