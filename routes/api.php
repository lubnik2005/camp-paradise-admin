<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\StripeController;
use Illuminate\Support\Facades\Validator;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => ['jwt.verify']], function () {

    Route::get('/cots_temp', function (Request $request) {
        $event = \App\Models\Event::findOrFail($request->event_id);
        return \App\Models\Room::where('event', $event)->toArray();
    });

    Route::get('/rooms_temp', function (Request $request) {
        $attendee = \App\Models\Attendee::findOrFail($request->attendee_id);
        $event = \App\Models\Event::findOrFail($request->event_id);
        $reservations = \App\Models\Reservation::where('event_id', '=', $event->id);
        $rooms = \App\Models\Room::where(function ($query) use ($attendee) {
            return $query->where('sex', '=', $attendee->sex)->orWhere('sex', '=', 'c');
        })->whereNull('deleted_at')->get();
        // IMPORTANT! THIS MUST BE OPTIMIZED!
        $rooms_send = $rooms->map(function ($room) use ($reservations) {
            $room_reservations = $reservations->where('room_id', '=', $room->id);
            $reserved_cot_ids = $room_reservations->pluck('cot_id');
            $available_cots = $room->cots()->whereNotIn('id', $reserved_cot_ids)->pluck('id');
            $reserved_cots = $room->cots()->whereIn('id', $reserved_cot_ids)->pluck('id');
            $room = $room->toArray();
            $room['available_cots'] = $available_cots;
            $room['reserved_cots'] = $reserved_cots;
            return $room;
        });
        return $rooms_send;
    });


    Route::get('/cots_temp2', function (Request $request) {
        $data = $request->only('event_id');
        $validator = Validator::make($data, ['event_id' => 'required|integer']);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        $attendee = auth('api')->user();
        $event = \App\Models\Event::findOrFail($data['event_id']);
        $room_ids = $event->rooms()->get()->pluck('id');
        $cots = \App\Models\Cot::whereHas('room', function ($query) use ($attendee, $room_ids) {
            $query->where('sex', '=', $attendee->sex)
                ->whereIn('id', $room_ids);
        })->get();
        return response()->json($cots);
    });
});

Route::group(['middleware' => 'api'], function ($router) {
    Route::post('create-payment-intent', [StripeController::class, 'createPaymentIntent']);
    Route::get('config', [StripeController::class, 'config']);
    Route::get('upcoming_events', [ApiController::class, 'upcoming_events']);
    Route::get('previous_events', [ApiController::class, 'previous_events']);
    Route::get('current_events', [ApiController::class, 'current_events']);
    Route::get('events', [ApiController::class, 'events']);
    Route::get('rooms', [ApiController::class, 'rooms']);
    Route::get('verify-forms', [ApiController::class, 'verifyForms']);
    Route::get('forms', [ApiController::class, 'forms']);
    Route::post('form', [ApiController::class, 'form']);
    Route::get('capacity', [ApiController::class, 'capacity']);
    Route::get('dorm_rooms', [ApiController::class, 'dorm_rooms']);
    Route::get('cots', [ApiController::class, 'cots']);
    Route::post('reserve', [ApiController::class, 'reserve']);
    Route::post('refund', [StripeController::class, 'refund']);
    Route::post('register', [ApiController::class, 'register']);
    Route::post('login', [ApiController::class, 'login']);
    Route::get('my-account', [ApiController::class, 'myAccount']);
    Route::get('refresh', [ApiController::class, 'refresh']);
    Route::get('me', [ApiController::class, 'me']);
    Route::get('reservations', [ApiController::class, 'reservations']);
    Route::get('verify-reservation', [ApiController::class, 'verifyReservation']);
    Route::post('change-password', [ApiController::class, 'changePassword']);
    Route::get('logout', [ApiController::class, 'logout']);
});

Route::get('/verify', [ApiController::class, 'verify']);
Route::get('/resend', [ApiController::class, 'resend']);
Route::get('/reset-password', [ApiController::class, 'resetPassword']);
Route::post('/new-password', [ApiController::class, 'newPassword']);

Route::post('/stripe-webhook', [StripeController::class, 'stripeWebhook']);

Route::get('/mailable', function () {
    //return view('emails.reserved', ['name' => 'James']);
    $attendee = \App\Models\Attendee::findOrFail(15);
    $camp_event = \App\Models\Event::findOrFail(1);
    $room = \App\Models\Room::findOrFail(3);
    $cot = \App\Models\Cot::findOrFail(1);
    $reservation = \App\Models\Reservation::findOrFail(18);

    return new \App\Mail\ReservedCot($attendee, $camp_event, $room, $cot, $reservation);
});

Route::get('/verify-email', function () {
    $attendee = \App\Models\Attendee::findOrFail(15);
    return new \App\Mail\VerifyEmail($attendee);
});

// Route::get('/receipt-email', function () {
//     $attendee = \App\Models\Attendee::findOrFail(15);
//     $attendee = \App\Models\Attendee::findOrFail(13);
//     $camp_event = \App\Models\Event::findOrFail(1);
//     $room = \App\Models\Room::findOrFail(3);
//     $cot = \App\Models\Cot::findOrFail(1);
//     $reservation = \App\Models\Reservation::findOrFail(17); 
//     return new \App\Mail\ReservedCot($attendee, $event, $room, $cot, $reservation);
// });
