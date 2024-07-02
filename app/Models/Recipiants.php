<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recipiants extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'message_id',
        'read_at',
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
