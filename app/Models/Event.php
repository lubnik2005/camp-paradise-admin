<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $id
 * @property string $name
 * @property string $status
 * @property \Illuminate\Support\Carbon $start_on
 * @property \Illuminate\Support\Carbon $end_on
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $registration_start_at
 * @property \Illuminate\Support\Carbon|null $registration_end_at
 * @property int $refund_percentage
 * @property \Illuminate\Support\Carbon|null $refunds_available_until
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Reservation> $reservations
 * @property-read int|null $reservations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Room> $rooms
 * @property-read int|null $rooms_count
 * @method static \Illuminate\Database\Eloquent\Builder|Event newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Event newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Event query()
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereEndOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereRefundPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereRefundsAvailableUntil($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereRegistrationEndAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereRegistrationStartAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereStartOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
