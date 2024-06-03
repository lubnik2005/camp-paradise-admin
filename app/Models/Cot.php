<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property int $room_id
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Reservation> $reservations
 * @property-read int|null $reservations_count
 * @property-read \App\Models\Room $room
 * @method static \Illuminate\Database\Eloquent\Builder|Cot newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cot newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cot query()
 * @method static \Illuminate\Database\Eloquent\Builder|Cot whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cot whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cot whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cot whereRoomId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cot whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Cot extends Model
{
    use HasFactory;

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
