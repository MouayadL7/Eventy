<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reports extends Model
{
    use HasFactory;
    protected $casts = [
        'user_id',
        'body',
        'titie',
        'read_at'
    ];


    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
