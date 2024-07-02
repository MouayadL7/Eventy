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

    public function participants(): HasMany
    {
        return $this->hasMany(User::class, 'participants');
    }

    public function last_message() :HasMany
    {
        return $this->hasMany(Message::class);
    }
}
