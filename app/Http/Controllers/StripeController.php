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
        $data = $request->only('user_id', 'cart', 'billing');
        $validator = Validator::make($data, [
            'cart' => 'required|array|size:1',
            'cart.*.event_id' => 'required|integer',
            'cart.*.cot_id' => 'required|integer',
            'cart.*.room_id' => 'required|integer',
            'billing' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 500);
        }

        $attendee = auth('api')->user();

        $totalPrice = 0;
        $confirm_cart = array_map(function ($product) use ($attendee, &$totalPrice) {
            $event = \App\Models\Event::findOrFail($product['event_id']);
            $cot = \App\Models\Cot::findOrFail($product['cot_id']);
            $room = \App\Models\Room::findOrFail($product['room_id']);
            $room = $event->rooms()->whereIn('sex', [$attendee->sex, 'c'])->findOrFail($product['room_id']);
            $price = $room->pivot->price;
            $totalPrice += $price;
            return [
                'user_id' => strval($attendee->id),
                'user_email' => $attendee->email,
                'event_id' => strval($event->id),
                'event_name' => $event->name,
                'cot_id' => strval($cot->id),
                'cot_description' => $cot->description,
                'room_id' => strval($room->id),
                'room_name' => $room->name,
                'room_price' => $price
            ];
        }, $data['cart']);

        $confirm_cart = json_encode($confirm_cart);
        $billing = $data['billing'];

        $payment = $attendee->pay(
            $amount = $totalPrice,
            [
                'metadata' => [
                    'cart' => json_encode($confirm_cart) // NOTE: Maximum of 500 characters
                ],
                // 'billing_details' => [
                //     'email' => $attendee->email,
                //     'phone' => $billing['phoneNumber'],
                //     'name' => $billing['receiver'],
                //     'address' => [
                //         'city' => $billing['city'],
                //         'country' => $billing['countryCode'],
                //         'postal_code' => $billing['zipCode'],
                //         'state' => $billing['state'],
                //         'line1' => $billing['line1'],
                //     ]
                // ],
                'description' => 'Youth Camp Reservation'
            ]
        );
        unset($totalPrice);
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
