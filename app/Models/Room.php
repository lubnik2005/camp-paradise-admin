<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    public function cots()
    {
        return $this->hasMany(Cot::class);
    }

    public function events()
    {
        return $this->belongsToMany(Event::class);
    }
}
