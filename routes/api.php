<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;

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


    Route::get('/events', function (Request $request) {
        return \App\Models\Event::select('id', 'name')->where('status', '=', 'published')->get();
    });

    Route::get('/rooms', function (Request $request) {
        $event = \App\Models\Event::findOrFail($request->event_id);
        return \App\Models\Room::where('event', $event)->toArray();
    });

    Route::post('/reserve', function (Request $request) {
        $attendee = \App\Models\Attendee::findOrFail($request->attendee_id);
        $event = \App\Models\Event::findOrFail($request->event_id);
        $room = \App\Models\Room::findOrFail($request->room_id);
        $cot = \App\Models\Cot::findOrFail($request->cot_id);
        // IMPORTANT! Have to secure this
        // 1. Make sure cot is not reserved
        // 2. Make sure that event is published
        // 3. Make sure that room is for gender specific
        // 4. Make sure cot is in room
        // 5. Make sure room is in event
        $reservation = new \App\Models\Reservation;
        $reservation->attendee_id = $attendee->id;
        $reservation->first_name = $attendee->first_name;
        $reservation->last_name = $attendee->last_name;
        $reservation->event_id = $event->id;
        $reservation->room_id = $room->id;
        $reservation->cot_id = $cot->id;
        $response = $reservation->save();
        return $response;
    });

    Route::get('/rooms', function (Request $request) {
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
});

Route::post('login', [ApiController::class, 'authenticate']);
Route::post('register', [ApiController::class, 'register']);
