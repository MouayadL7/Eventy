<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceImage extends Model
{
    use HasFactory;
    protected $hidden = [
        'id', 'created_at', 'updated_at', 'service_id'
    ];
    protected $fillable = [
        'service_id', 'image_path'
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
