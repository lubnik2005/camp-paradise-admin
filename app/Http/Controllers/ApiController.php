<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyEmail;
use Lcobucci\JWT\Encoding\CannotDecodeContent;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token\InvalidTokenStructure;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Token\UnsupportedHeaderFound;
use Lcobucci\JWT\UnencryptedToken;
use Illuminate\Support\Facades\Log;
use Carbon\CarbonImmutable;

class ApiController extends Controller
{

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'verify', 'resend']]);
    }

    public function register(Request $request)
    {
        //Validate data
        $data = $request->only('firstName', 'lastName', 'email', 'password', 'gender');
        $validator = Validator::make($data, [
            'firstName' => 'required|string',
            'lastName' => 'required|string',
            'email' => 'required|email|unique:attendees|unique:attendees',
            'password' => 'required|string|min:6|max:50',
            'gender' => 'required|string|in:m,f',
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 403);
        }

        //Request is valid, create new user
        $attendee = Attendee::create([
            'first_name' => $request->firstName,
            'last_name' => $request->lastName,
            'church' => '',
            'email' => $request->email,
            'sex' => $request->gender,
            'password' => bcrypt($request->password),
        ]);

        Mail::mailer('ses')
            ->to($attendee)
            ->send(new VerifyEmail($attendee));

        //User created, return success response
        return response()->json([
            'success' => true,
            'message' => 'Account created successfully',
            'data' => $attendee
        ], Response::HTTP_OK);
    }

    public function resend(Request $request)
    {
        $data = $request->only('email');
        $validator = Validator::make($data, [
            'email' => 'required|email',
        ]);
        $attendee = Attendee::where('email', $data['email'])->firstOrFail();
        if ($attendee->email_verified_at) {
            return response()->json(['error' => ['validated' => ['Email already validated.']]], 403);
        }
        Mail::mailer('ses')
            ->to($attendee)
            ->send(new VerifyEmail($attendee));
        return response()->json(['success' => ['Sent validation email']], 200);
    }

    public function verify(Request $request)
    {

        $parser = new Parser(new JoseEncoder());

        try {
            $token = $parser->parse($request->only(['token'])['token']);
        } catch (CannotDecodeContent | InvalidTokenStructure | UnsupportedHeaderFound $e) {
            echo 'Oh no, an error: ' . $e->getMessage();
        }
        assert($token instanceof UnencryptedToken);

        $now = CarbonImmutable::now();
        $exp = $token->claims()->get('exp');
        Log::debug('TIME');
        Log::debug($exp->format('Y-m-d H:i:s'));
        Log::debug($now->format('Y-m-d H:i:s'));
        if ($now >= $exp) {
            return response()->json(['error' => ['token_expired' => ['Email validation expired. Please send another.']]], 403);
        }
        $email = $token->claims()->get('email');
        $attendee = \App\Models\Attendee::where('email', '=', $email)->firstOrFail();
        if ($attendee->email_verified_at) return response()->json(['success' => ['Verified']]);
        $attendee->email_verified_at = Carbon::now();
        $attendee->save();
        $attendee->createAsStripeCustomer();

        //JWTAuth::invalidate(new \Tymon\JWTAuth\Token($token->token));
        //return response()->json(['error' => 'temp', '403']);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);

        if (!$token = auth('api')->attempt($credentials)) return response()->json(['message' => 'Email/Password combination not found.'], 400); //->json(['error' => ['error' => ['Credentials not found.']]], 401);
        $user = auth('api')->user();
        if (!$user->email_verified_at) return response()->json(['message' => 'Email not verified.'], 400); //, ->json(['error' => ['error' => ['Email not verified.']]], 402);
        $accessToken = [
            'accessToken' => $token,
            'tokenType' => 'bearer',
            'expiresIn' => auth('api')->factory()->getTTL() * 60
        ];
        // "user": {
        //     "id": "8864c717-587d-472a-929a-8e5f298024da-0",
        //     "displayName": "Jaydon Frankie",
        //     "email": "demo@minimals.cc",
        //     "password": "demo1234",
        //     "photoURL": "https://api-dev-minimal-v4.vercel.app/assets/images/avatars/avatar_default.jpg",
        //     "phoneNumber": "+40 777666555",
        //     "country": "United States",
        //     "address": "90210 Broadway Blvd",
        //     "state": "California",
        //     "city": "San Francisco",
        //     "zipCode": "94116",
        //     "about": "Praesent turpis. Phasellus viverra nulla ut metus varius laoreet. Phasellus tempus.",
        //     "role": "admin",
        //     "isPublic": true
        // }
        $user->displayName = $user->first_name . ' ' . $user->last_name;
        $user->photoURL = 'https://api-dev-minimal-v4.vercel.app/assets/images/avatars/avatar_default.jpg';
        return response()->json(['accessTokenFull' => $accessToken, 'accessToken' => $token, 'user' => $user]);
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
        $rooms = $event->rooms()->whereIn('sex', [$attendee->sex, 'c'])->withCount('cots')->withCount('reservations')->get();
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
        $rooms = $event->rooms()->withCount('cots')->get()->toArray();
        return [
            'capacity' => array_sum(array_column($rooms, 'cots_count')),
            'reserved' => $event->reservations()->count(),
            'cabins' => $event->cabins()->count(),
            'dorms' => $event->dorms()->count(),
            'vips' => $event->vips()->count(),
        ];
    }
}
