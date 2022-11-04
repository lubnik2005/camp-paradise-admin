<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Cashier\Events\WebhookReceived;
use Illuminate\Support\Facades\Validator;

class StripeController extends Controller
{
    public function config(Request $request)
    {
        return response()->json(['publishableKey' => env('STRIPE_KEY', null)]);
    }

    public function createPaymentIntent(Request $request)
    {
        $data = $request->only('event_id', 'cot_id', 'room_id');
        $validator = Validator::make($data, [
            'event_id' => 'required|integer',
            'cot_id' => 'required|integer',
            'room_id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 500);
        }

        $attendee = auth('api')->user();
        $event = \App\Models\Event::findOrFail($data['event_id']);
        $cot = \App\Models\Cot::findOrFail($data['cot_id']);
        $room = \App\Models\Room::findOrFail($data['room_id']);

        $payment = $attendee->pay(
            $amount = 15000,
            [
                'metadata' => [
                    'user_id' => $attendee->id,
                    'event_id' => $event->id,
                    'cot_id' => $cot->id,
                    'room_id' => $room->id
                ]
            ]
        );
        return response()->json(['paymentInfo' => $payment, 'clientSecret' => $payment->client_secret]);
    }

    public function stripeWebhook(Request $request)
    {
        try {
            WebhookReceived::dispatch($request->toArray());
            return response(200);
        } catch (Throwable $e) {
            report($e);
            abort(500);
        }
    }
}
