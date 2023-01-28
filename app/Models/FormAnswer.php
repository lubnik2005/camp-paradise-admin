<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'answers' => 'array',
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
}
