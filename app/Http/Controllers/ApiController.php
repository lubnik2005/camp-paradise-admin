<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ApiController extends Controller
{

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }


    public function register(Request $request)
    {
        //Validate data
        $data = $request->only('first_name', 'last_name', 'email', 'password', 'gender');
        $validator = Validator::make($data, [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email|unique:attendees|unique:attendees',
            'password' => 'required|string|min:6|max:50',
            'gender' => 'required|string|in:m,f',
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 403);
        }

        //Request is valid, create new user
        $attendee = Attendee::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'church' => '',
            'email' => $request->email,
            'sex' => $request->gender,
            'password' => bcrypt($request->password),
        ]);

        //User created, return success response
        return response()->json([
            'success' => true,
            'message' => 'Account created successfully',
            'data' => $attendee
        ], Response::HTTP_OK);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);

        if (!$token = auth('api')->attempt($credentials)) return response()->json(['error' => 'Unauthorized'], 401);
        $user = auth('api')->user();
        $accessToken = [
            'accessToken' => $token,
            'tokenType' => 'bearer',
            'expiresIn' => auth('api')->factory()->getTTL() * 60
        ];
        return response()->json(['accessToken' => $accessToken, 'user' => $user]);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth('api')->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth('api')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        $token = auth('api')->refresh();
        $user = auth('api')->user();
        $accessToken = [
            'accessToken' => $token,
            'tokenType' => 'bearer',
            'expiresIn' => auth('api')->factory()->getTTL() * 60
        ];
        return response()->json(['accessToken' => $accessToken, 'user' => $user]);
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'accessToken' => $token,
            'tokenType' => 'bearer',
            'expiresIn' => auth('api')->factory()->getTTL() * 60
        ]);
    }

    public function reserve(Request $request)
    {
        $data = $request->only('event_id', 'room_id', 'cot_id');
        $validator = Validator::make($data, [
            'event_id' => 'required|integer',
            'room_id' => 'required|integer',
            'cot_id' => 'required|integer'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }


        $attendee = auth('api')->user();
        $event = \App\Models\Event::findOrFail($data['event_id']);
        $room = \App\Models\Room::findOrFail($data['room_id']);
        $cot = \App\Models\Cot::findOrFail($data['cot_id']);

        $reservation = \App\Models\Reservation::where('event_id', '=', $event->id)
            ->where('room_id', '=', $room->id)
            ->where('cot_id', '=', $cot->id);

        if ($reservation->exists()) {
            return response()->json(['error' => ['cot_id' => 'Reservation failed. Cot already taken.']], 200);
        }

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
        // Stripe goes here

        $response = $reservation->save();
        return $response ? response()->json($reservation, 200) : response()->json(['error' => ['cot_id' => 'Reservation creation failed']]);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function rooms(Request $request)
    {
        $attendee = auth('api')->user();
        $data = $request->only('event_id');
        $validator = Validator::make($data, [
            'event_id' => 'required|integer',
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        $event = \App\Models\Event::findOrFail($data['event_id']);
        $rooms = $event->rooms()->whereIn('sex', [$attendee->sex, 'c'])->get();
        return response()->json($rooms, 200);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function dorm_rooms(Request $request)
    {
        $attendee = auth('api')->user();
        $data = $request->only('event_id');
        $validator = Validator::make($data, [
            'event_id' => 'required|integer',
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        $event = \App\Models\Event::findOrFail($data['event_id']);
        $rooms = $event->rooms()->whereIn('sex', [$attendee->sex, 'c'])->get();
        return response()->json($rooms, 200);
    }



    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function cots(Request $request)
    {
        $attendee = auth('api')->user();
        $data = $request->only('event_id', 'room_id');
        $validator = Validator::make($data, [
            'event_id' => 'required|integer',
            'room_id' => 'required|integer',
        ]);
        if ($validator->fails()) return response()->json(['error' => $validator->messages()], 200);
        $event = \App\Models\Event::findOrFail($data['event_id']);
        $room = $event->rooms()->whereIn('sex', [$attendee->sex, 'c'])->findOrFail($data['room_id']);
        $cots = \App\Models\Cot::where('cots.room_id', '=', $room->id)
            ->leftJoin('reservations', 'cots.id', '=', 'reservations.cot_id')
            ->select('cots.*', 'reservations.first_name', 'reservations.last_name')
            ->get();
        return response()->json($cots, 200);
    }


    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function reservations(Request $request)
    {
        $attendee = auth('api')->user();
        $reservations = \App\Models\Reservation::where('attendee_id', '=', $attendee->id);
        return response()->json($reservations, 200);
    }

    public function events(Request $request)
    {
        return \App\Models\Event::select('id', 'name', 'start_on', 'end_on')
            ->where('status', '=', 'published')
            ->orderBy('start_on', 'desc')
            ->get();
    }

    public function previous_events(Request $request)
    {
        return \App\Models\Event::select('id', 'name', 'start_on', 'end_on')
            ->where('end_on', '<', Carbon::now())
            ->where('status', '=', 'published')
            ->orderBy('start_on', 'desc')
            ->get();
    }

    public function upcoming_events(Request $request)
    {
        return \App\Models\Event::select('id', 'name', 'start_on', 'end_on')
            ->where('end_on', '>=', Carbon::now())
            ->where('status', '=', 'published')
            ->orderBy('start_on', 'desc')
            ->get();
    }

    public function capacity(Request $request)
    {
        $data = $request->only('event_id');
        $event = \App\Models\Event::find($data['event_id']);
        return [
            'capacity' => $event->rooms()->count(),
            'reserved' => $event->reservations()->count(),
            'cabins' => $event->cabins()->count(),
            'dorms' => $event->dorms()->count(),
            'vips' => $event->vips()->count(),
        ];
    }
}
