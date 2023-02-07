<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
//use Illuminate\Auth\Authenticatable;
use App\Models\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Laravel\Cashier\Billable;


class Attendee extends Authenticatable implements JWTSubject
{
    use HasFactory;
    use Billable;

    public static function boot()
    {
        parent::boot();
        static::created(function (Attendee $user) {
            $user->createAsStripeCustomer([
                'email' => $user->email,
                'name' => $user->first_name . ' ' . $user->last_name
            ]);
        });
    }

    protected $fillable = [
        'first_name',
        'last_name',
        'church',
        'email',
        'sex',
        'password',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function forms()
    {
        return $this->hasMany(Form::class);
    }
}
