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
        static::created(function ($user) {
            $user->createAsStripeCustomer([
                'email' => $user->email
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
}
