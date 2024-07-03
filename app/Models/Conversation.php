<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

use function PHPSTORM_META\map;

class Conversation extends Model
{
    use HasFactory;
    protected $fillable = ['last_message_id'];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function participants()
    {
        return $this->belongsToMany(User::class, 'participants');
    }

    public function recipiants()
    {
        return $this->hasManyThrough(Recipiants::class, Message::class, 'conversation_id', 'message_id', 'id', 'id');
    }

    public function messages() :HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function last_message()
    {
        return $this->belongsTo(Message::class, 'last_message_id', 'id')
            ->whereNull('deleted_at')
            ->withDefault([
                'message' => 'Message deleted'
            ]);
    }
}
