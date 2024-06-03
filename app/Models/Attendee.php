<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
//use Illuminate\Auth\Authenticatable;
use App\Models\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Laravel\Cashier\Billable;


/**
 *
 *
 * @property int $id
 * @property string $email
 * @property string $first_name
 * @property string $last_name
 * @property string $password
 * @property string $sex
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property string|null $stripe_id
 * @property string|null $pm_type
 * @property string|null $pm_last_four
 * @property string|null $trial_ends_at
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FormAnswer> $forms
 * @property-read int|null $forms_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Reservation> $reservations
 * @property-read int|null $reservations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Cashier\Subscription> $subscriptions
 * @property-read int|null $subscriptions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Illuminate\Database\Eloquent\Builder|Attendee hasExpiredGenericTrial()
 * @method static \Illuminate\Database\Eloquent\Builder|Attendee newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Attendee newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Attendee onGenericTrial()
 * @method static \Illuminate\Database\Eloquent\Builder|Attendee query()
 * @method static \Illuminate\Database\Eloquent\Builder|Attendee whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendee whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendee whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendee whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendee whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendee whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendee whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendee wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendee wherePmLastFour($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendee wherePmType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendee whereSex($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendee whereStripeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendee whereTrialEndsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendee whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
        return $this->hasMany(FormAnswer::class);
    }
}
