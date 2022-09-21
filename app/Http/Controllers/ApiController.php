<?php

namespace App\Http\Controllers;

use JWTAuth;
use App\Models\Attendee;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use App\Config;

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
        $data = $request->only('first_name', 'last_name', 'email', 'church', 'password', 'sex');
        $validator = Validator::make($data, [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'church' => 'required|string',
            'email' => 'required|email|unique:attendees',
            'password' => 'required|string|min:6|max:50',
            'sex' => 'required|string|in:m,f'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        //Request is valid, create new user
        $attendee = Attendee::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'church' => $request->church,
            'email' => $request->email,
            'sex' => $request->sex,
            'password' => bcrypt($request->password)
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

        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
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
        return $this->respondWithToken(auth('api')->refresh());
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
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
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
        $response = $reservation->save();
        return $response ? response()->json($reservation, 200) : response()->json(['error' => ['cot_id' => 'Reservation creation failed']]);
    }
}
