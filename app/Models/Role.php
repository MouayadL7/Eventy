<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    const ROLE_ADMINISTRATOR = 1;
    const ROLE_CLIENT        = 2;
    const ROLE_SPONSOR       = 3;

    protected $fillable = ['name'];

    public function user() : HasMany
    {
        return $this->hasMany(User::class);
    }
}
