<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $casts = ['start_on' => 'datetime', 'end_on' => 'datetime'];

    public function rooms()
    {
        return $this->belongsToMany(Room::class);
    }
}
