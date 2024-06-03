<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 
 *
 * @property int $id
 * @property int $attendee_id
 * @property string $first_name
 * @property string $last_name
 * @property int $event_id
 * @property int $room_id
 * @property int $cot_id
 * @property string|null $stripe_payment_intent
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $price
 * @property-read \App\Models\Attendee $attendee
 * @property-read \App\Models\Cot $cot
 * @property-read \App\Models\Event $event
 * @property-read \App\Models\Room $room
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation query()
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation whereAttendeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation whereCotId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation whereRoomId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation whereStripePaymentIntent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation withoutTrashed()
 * @mixin \Eloquent
 */
class Reservation extends Model
{
    use HasFactory;
    use SoftDeletes;

    public function attendee()
    {
        return $this->belongsTo(Attendee::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function cot()
    {
        return $this->belongsTo(Cot::class);
    }
}
