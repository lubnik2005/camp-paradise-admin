<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $id
 * @property int $form_id
 * @property int $attendee_id
 * @property array $answers
 * @property \Illuminate\Support\Carbon $signed_on
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $event_id
 * @property-read \App\Models\Attendee|null $attendee
 * @property-read \App\Models\Event|null $event
 * @property-read \App\Models\Form $form
 * @method static \Illuminate\Database\Eloquent\Builder|FormAnswer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FormAnswer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FormAnswer query()
 * @method static \Illuminate\Database\Eloquent\Builder|FormAnswer whereAnswers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormAnswer whereAttendeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormAnswer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormAnswer whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormAnswer whereFormId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormAnswer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormAnswer whereSignedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormAnswer whereUpdatedAt($value)
 *
 * @mixin \Illuminate\Database\Eloquent\Collection
 */
class FormAnswer extends Model
{
    use HasFactory;

    protected $table = 'formAnswers';


    /**
     * The attributes that should be cast.
     *
     * @var array
     */

    protected $casts = [
        'answers' => 'json',
        'signed_on' => 'datetime'
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function attendee()
    {
        return $this->belongsTo(Attendee::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
