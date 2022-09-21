<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
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


    Route::get('/events', function (Request $request) {
        return \App\Models\Event::select('id', 'name')->where('status', '=', 'published')->get();
    });

    // Route::get('/rooms', function (Request $request) {
    //     $event = \App\Models\Event::findOrFail($request->event_id);
    //     return \App\Models\Room::where('event', $event)->toArray();
    // });


    Route::get('/cots', function (Request $request) {
        $event = \App\Models\Event::findOrFail($request->event_id);
        return \App\Models\Room::where('event', $event)->toArray();
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

    Route::get('/cots', function (Request $request) {
        $data = $request->only('event_id');
        $validator = Validator::make($data, [
            'event_id' => 'required|integer',
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        $attendee = auth('api')->user();
        $event = \App\Models\Event::findOrFail($data['event_id']);
        $cots = \App\Models\Cot::whereHas('room', function ($query) use ($attendee) {
                $query->where('sex', '=', $attendee->sex);
            })->get();
        return response()->json($cots);
    });
});

Route::group(['middleware' => 'api'], function ($router) {
    Route::post('reserve', [ApiController::class, 'reserve']);
    Route::post('register', [ApiController::class, 'register']);
    Route::post('login', [ApiController::class, 'login']);
    Route::post('logout', [ApiController::class, 'logout']);
    Route::post('refresh', [ApiController::class, 'logout']);
    Route::post('me', [ApiController::class, 'me']);
});
