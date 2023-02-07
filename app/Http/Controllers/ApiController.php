<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyEmail;
use App\Mail\ResetPasswordEmail;
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
        $this->middleware('auth:api', ['except' => ['login', 'register', 'verify', 'resend', 'resetPassword', 'newPassword', 'myAccount']]);
    }


    public function verifyForms(Request $request)
    {
        // Really should be sql stuff
        $unfinishedForms = [];
        $data = $request->only('cart');
        foreach ($data['cart'] as $key => $product) {
            $event_id = $product['event_id'];
            $forms = \App\Models\Form::all();
            $forms->each(function ($form) use ($event_id, &$unfinishedForms) {
                $form_answers = \App\Models\FormAnswer::where('event_id', '=', $event_id)
                    ->where('form_id', '=', $form->id)
                    ->where('attendee_id', auth('api')->user()->id);
                $event = \App\Models\Event::find($event_id);
                if (!$form_answers->exists()) {
                    $unfinishedForms[] = ['campId' => $event_id, 'campName' => $event->name, 'formId' => $form->id,];
                }
            });
        }
        return $unfinishedForms;
    }

    public function forms(Request $request)
    {
        $formAnswers = \App\Models\FormAnswer::where('attendee_id', '=', auth('api')->user()->id)
            ->with('event:id,name', 'form:id,name')->get()->toArray();
        $forms = \App\Models\Form::all();
        $forms_events = [];
        $forms->each(function ($form) use (&$forms_events) {
            $events = \App\Models\Event::where('status', 'published')->get();
            $events->each(function ($event) use ($form, &$forms_events) {
                $form = $form->toArray();
                $form['event'] = $event;
                $forms_events[] = $form;
            });
        });

        foreach ($forms_events as $key => &$form_event) {
            $answers = \App\Models\FormAnswer::where('attendee_id', '=', auth('api')->user()->id)
                ->where('form_id', '=', $form_event['id'])
                ->where('event_id', '=', $form_event['event']['id']);
            $form_event['signedOn'] = '';
            $form_event['status'] =  'not started';
            if ($answers->exists()) {
                $answer = $answers->first();
                $form_event['status'] =  'completed';
                $form_event['signedOn'] = $answer->signed_on;
            }
        }
        unset($form_event);

        return response()->json($forms_events, 200);
    }

    public function form(Request $request)
    {
        $data = $request->only('data', 'campId', 'formId');
        // TODO: ADD VALIDATION HERE
        // Theres a better with like first or new, but it's 2 in the morning. I can't be bothered to
        // read documentation

        $forms = \App\Models\FormAnswer::where('event_id', '=', $data['campId'])
            ->where('form_id', '=', $data['formId'])
            ->where('attendee_id', '=', auth('api')->user()->id);
        if ($forms->exists()) {
            $form = $forms->first();
        } else {
            $form = new \App\Models\FormAnswer();
            $form->event_id = $data['campId'];
            $form->form_id = $data['formId'];
            $form->attendee_id = auth('api')->user()->id;
        }
        $form->answers = $data['data'];
        $form->signed_on = \Carbon\Carbon::now();
        $form->save();
        return response()->json(['success' => 'Form saved', 200]);
    }

    // Right before stripe is sent, confirm that the reservation is still available
    public function verifyReservation(Request $request)
    {
        $data = $request->only('cart');
        $attendee = auth('api')->user();
        $attendee_id = $attendee->id;



        $unfinishedForms = [];
        $data = $request->only('cart');
        foreach ($data['cart'] as $key => $product) {
            $event_id = $product['event_id'];
            $forms = \App\Models\Form::all();
            $forms->each(function ($form) use ($event_id, &$unfinishedForms) {
                $form_answers = \App\Models\FormAnswer::where('event_id', '=', $event_id)
                    ->where('form_id', '=', $form->id)
                    ->where('attendee_id', auth('api')->user()->id);
                $event = \App\Models\Event::find($event_id);
                if (!$form_answers->exists()) {
                    $unfinishedForms[] = ['campId' => $event_id, 'campName' => $event->name, 'formId' => $form->id,];
                }
            });
        }


        if (count($unfinishedForms) > 0) return response()->json(['error' => ['forms' => 'Forms not finished']], 400);




        foreach ($data['cart'] as $key => $product) {
            // Verify formula


            foreach ($data['cart'] as $product) {
                // Verify that reservation hasn't already been made
                $event_id = $product['event_id'];
                $cot_id = $product['cot_id'];
                $room_id = $product['room_id'];
                $attendee = \App\Models\Attendee::findOrFail($attendee_id);
                $reservation = \App\Models\Reservation::where('event_id', '=', $event_id)
                    ->where('room_id', '=', $room_id)
                    ->where('cot_id', '=', $cot_id);
                if ($reservation->exists()) {
                    $reservation_id = $reservation->first()->id;

                    Log::error("Duplication of reservation ID:$reservation_id with attendee ID: $attendee_id");
                    return response()->json(['error' => ['cot_id' => 'Reservation failed. Cot already taken.']], 403);
                }

                $events = \App\Models\Event::where('events.id', '=', $event_id)->where('status', '=', 'published');
                if (!$events->exists()) {
                    Log::error("Event is not published: $event_id | Attendee: $attendee_id");
                    return response()->json(['error' => ['cot_id' => 'Event not available']], 400);
                }
                $event = $events->first();
                $rooms = $event->rooms()->where('rooms.id', '=', $room_id)->where(function ($query) use ($attendee) {
                    return $query->where('sex', '=', $attendee->sex)->orWhere('sex', '=', 'c');
                })->whereNull('deleted_at');
                if (!$rooms->exists()) {
                    Log::error("Room is not available: $room_id | Attendee: $attendee_id");
                    return response()->json(['error' => ['room_id' => 'Room is not available.']], 400);
                }
                $room = $rooms->first();
                $cots = $room->cots();
                if (!$cots->exists()) {
                    Log::error("Cot is not available: $cot_id| Attendee: $attendee_id");
                    return response()->json(['error' => ['room_id' => 'Cot is not available.']], 400);
                }

                return response()->json(['success' => ['reservation' => 'Event, Room, Cot available.']], 200);
            }
        }

        //return response()->json([], 200);
        return response()->json(['message' => 'Reservation/Cot is no longer available.'], 500);
    }

    public function changePassword(Request $request)
    {
        $passwords = request(['oldPassword', 'newPassword']);
        $user = auth('api')->user();
        $credentials = ['email' => $user->email, 'password' => $passwords['oldPassword']];
        if (!$token = auth('api')->attempt($credentials)) return response()->json(['message' => 'Password is wrong.'], 400); //->json(['error' => ['error' => ['Credentials not found.']]], 401);
        $attendee = \App\Models\Attendee::where('email', '=', $user->email)->firstOrFail();
        $attendee->password = bcrypt($passwords['newPassword']);
        $attendee->save();
        return response()->json(['message' => 'Reset Password.', 200]);
    }

    public function register(Request $request)
    {
        //Validate data
        $data = $request->only('firstName', 'lastName', 'email', 'password', 'gender');
        $validator = Validator::make($data, [
            'firstName' => 'required|string',
            'lastName' => 'required|string',
            'email' => 'required|email|unique:attendees',
            'password' => 'required|string|min:8|max:50',
            'gender' => 'required|string|in:m,f',
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 403);
        }
        $data['email'] = strtolower($data['email']); // need to make sure its not uppercase
        $validator = Validator::make($data, [
            'email' => 'required|email|unique:attendees',
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 403);
        }








        //Request is valid, create new user
        $attendee = Attendee::create([
            'first_name' => $data['firstName'],
            'last_name' => $data['lastName'],
            'church' => '',
            'email' => $data['email'],
            'sex' => $data['gender'],
            'password' => bcrypt($data['password']),
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
        $data['email'] = strtolower($data['email']);
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
        $attendee->createOrGetStripeCustomer();

        //JWTAuth::invalidate(new \Tymon\JWTAuth\Token($token->token));
        //return response()->json(['error' => 'temp', '403']);
    }

    /**
     * Send password change link to email
     */
    public function resetPassword(Request $request)
    {
        $data = $request->only('email');
        $validator = Validator::make($data, [
            'email' => 'required|email',
        ]);
        $data['email'] = strtolower($data['email']);
        $attendee = Attendee::where('email', $data['email'])->firstOrFail();

        Mail::mailer('ses')
            ->to($attendee)
            ->send(new ResetPasswordEmail($attendee));
        return response()->json(['success' => ['Password reset e-mail sent.']], 200);
    }

    public function newPassword(Request $request)
    {
        $data = $request->only('email', 'password', 'confirmPassword', 'token');
        $data['email'] = strtolower($data['email']);
        $parser = new Parser(new JoseEncoder());

        try {
            $token = $parser->parse($data['token']);
        } catch (CannotDecodeContent | InvalidTokenStructure | UnsupportedHeaderFound | \TypeError $e) {
            return response()->json(['error' => ['bad_token' => 'Token is invalid. Please resend the E-mail.']], 403);
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
        $tokenEmail = $token->claims()->get('email');
        $email = $data['email'];
        $password = $data['password'];
        if ($email != $tokenEmail) {
            return response()->json(['message' => 'Wrong email for token.', 403]);
        }
        $attendee = \App\Models\Attendee::where('email', '=', $email)->firstOrFail();
        $attendee->password = bcrypt($password);
        $attendee->save();
        return response()->json(['message' => 'Reset Password.', 200]);
    }

    public function myAccount()
    {
        $user = auth('api')->user();
        if (!$user) {
            return response()->json(['message' => 'Can\'t authenticate user.', 403]);
        }
        $user->displayName = $user->first_name . ' ' . $user->last_name;
        $user->photoURL = 'https://www.gravatar.com/avatar/6d0ece2e08fcf747016e398b63d1b678?s=300';
        return response()->json(['user' => $user]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);
        $credentials['email'] = strtolower($credentials['email']);

        if (!$token = auth('api')->attempt($credentials)) return response()->json(['message' => 'Email/Password combination not found.'], 400); //->json(['error' => ['error' => ['Credentials not found.']]], 401);
        $user = auth('api')->user();
        if (!$user->email_verified_at) return response()->json(['message' => 'Email not verified.'], 410); //, ->json(['error' => ['error' => ['Email not verified.']]], 402);
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
        $user->photoURL = 'https://www.gravatar.com/avatar/6d0ece2e08fcf747016e398b63d1b678?s=300';
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
        $data = $request->only('user_id', 'cart');
        $validator = Validator::make($data, [
            'cart' => 'required|array|size:1',
            'cart.*.event_id' => 'required|integer',
            'cart.*.room_id' => 'required|integer',
            'cart.*.cot_id' => 'required|integer'
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
        $rooms = $event->rooms()->whereIn('sex', [$attendee->sex, 'c'])
            ->orderBy('rooms.name')->withCount('cots')->withCount('reservations')->get();
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
        $price = $room->pivot->price;
        $cots = \App\Models\Cot::where('cots.room_id', '=', $room->id)
            ->leftJoin('reservations', 'cots.id', '=', 'reservations.cot_id')
            // ->join('rooms', 'cots.room_id', '=', 'rooms.id')
            // ->join('event_room', 'event_room.room_id' , '=', 'rooms.id')
            // ->where()
            ->select('cots.*', 'reservations.first_name', 'reservations.last_name',)
            ->orderBy('cots.description')
            ->get()->toArray();
        $cots = array_map(function ($cot) use ($price) {
            $cot['price'] = $price;
            return $cot;
        }, $cots);
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
        $reservations = \App\Models\Reservation::with('room:id,name', 'cot:id,description', 'event:id,name')
            //->select('first_name', 'last_name', 'price', 'created_at', 'room.id', 'room.name')

            // ->select(
            //     'reservations.first_name',
            //     'reservations.last_name',
            //     'reservations.price',
            //     'room.name',
            //     'event.name',
            //     'cot.description'
            // )
            ->where('attendee_id', '=', $attendee->id)
            ->get();
        return response()->json($reservations, 200);
    }

    public function events(Request $request)
    {

        $events = \App\Models\Event::select('id', 'name', 'start_on', 'end_on')
            ->where('status', '=', 'published')
            ->orderBy('start_on', 'desc')
            ->get();
        $reservations = \App\Models\Reservation::where('attendee_id', auth('api')->user()->id)
            ->whereIn('event_id', $events->pluck('id'))->get()->toArray();

        return array_map(function ($event) use ($reservations) {
            $event['reservations'] = array_map(function ($reservation) {
                return $reservation['id'];
            }, $reservations);
            return $event;
        }, $events->toArray());
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
        $events = \App\Models\Event::select('id', 'name', 'start_on', 'end_on')
            ->where('end_on', '>=', Carbon::now())
            ->where('status', '=', 'published')
            ->orderBy('start_on', 'desc')
            ->get();
        $reservations = \App\Models\Reservation::where('attendee_id', auth('api')->user()->id)
            ->whereIn('event_id', $events->pluck('id'))->get()->toArray();

        return array_map(function ($event) use ($reservations) {
            $event['reservations'] = array_map(function ($reservation) {
                return $reservation['id'];
            }, $reservations);
            return $event;
        }, $events->toArray());
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
