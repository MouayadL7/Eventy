<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recipiants extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'user_id',
        'message_id',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function message() : BelongsTo
    {
        return $this->belongsTo(Message::class);
    }
}
