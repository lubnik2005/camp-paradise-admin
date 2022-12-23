<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $casts = ['start_on' => 'datetime', 'end_on' => 'datetime'];

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function rooms()
    {
        return $this->belongsToMany(Room::class);
    }

    public function reserved_rooms()
    {
        return $this->rooms()->where('rooms.id', 1);
    }

    public function cabins()
    {
        return $this->rooms()->where('type', 'cabin');
    }

    public function dorms()
    {
        return $this->rooms()->where('type', 'dorm');
    }

    public function vips()
    {
        return $this->rooms()->where('type', 'vip');
    }
}
