<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Abrove extends Model
{
    use HasFactory;

    protected $fillable = ['sponsor_id', 'price'];

    public function sponsor() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function get_info()
    {
        
    }
}
