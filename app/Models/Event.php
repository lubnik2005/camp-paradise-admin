<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $casts = [
        'start_on' => 'datetime',
        'end_on' => 'datetime',
        'registration_start_at' => 'datetime',
        'registration_end_at' => 'datetime',
        'refunds_available_until' => 'datetime',
    ];

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function rooms()
    {
        return $this->belongsToMany(Room::class)->withPivot('price');;
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

    public function cots()
    {
        return Cot::select(
            'cots.id as id',
            'cots.description as description',
            'cots.room_id as room_id'
        )
            ->join('rooms', 'cots.room_id', '=', 'rooms.id')
            ->join('event_room', 'rooms.id', '=', 'event_room.room_id')
            ->join('events', 'events.id', '=', 'event_room.event_id')
            ->where('events.id', '=', $this->id);
    }

    public function availableCots()
    {
        return $this->cots()->leftJoin('reservations', 'reservations.cot_id', 'cots.id')->whereNull('reservations.id');
        return Cot::join('rooms', 'cots.room_id', '=', 'rooms.id')
            ->join('event_room', 'rooms.id', '=', 'event_room.room_id')
            ->join('events', 'events.id', '=', 'event_room.event_id')
            ->join('reservations', 'cots.id', '=', 'reservations.cot_id')
            ->where('events.id', '=', $this->id)
            ->whereNull('reservations.id');
    }
}
