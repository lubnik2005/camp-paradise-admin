<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    public function attendee() {
        return $this->belongsTo(Attendee::class);
    }

    public function event() {
        return $this-> belongsTo(Event::class);
    }

    public function room() {
        return $this->belongsTo(Room::class);
    }
}
