<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Laravel\Cashier\Events\WebhookReceived;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservedCot;


class StripePurchasedReservationListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle received Stripe webhooks.
     *
     * @param  \Laravel\Cashier\Events\WebhookReceived  $event
     * @return void
     */
    public function handle(WebhookReceived $event)
    {
        // Handle the event
        switch ($event->payload['type']) {
            case 'payment_intent.created':

                break;
            case 'charge.succeeded':
                //case 'payment_intent.succeeded':
                Log::debug('charge.succeeded');
                $object = $event->payload['data']['object'];
                $data = $object['metadata'];
                Log::debug('$data');
                Log::debug($data);
                $cart = json_decode($data['cart']);
                $cart = array_map(function ($product) {
                    return (array) $product;
                }, $cart);
                $data['cart'] = $cart;
                Log::debug($data);

                // $validator = Validator::make($data, [
                //     'cart' => 'required|array|size:1',
                //     'cart.*.user_id' => 'required|integer',
                //     'cart.*.event_id' => 'required|integer',
                //     'cart.*.room_id' => 'required|integer',
                //     'cart.*.cot_id' => 'required|integer'
                // ]);

                // Log::debug($validator->messages());
                // //Send failed response if request is not valid
                // if ($validator->fails()) {
                //     return response()->json(['error' => $validator->messages()], 500);
                // }

                Log::debug('charge.succeeded validated');
                Log::debug($data);

                // HERE

                // Validate existence of all teh stuff in a cart
                foreach ($data['cart'] as $key => $product) {
                    \App\Models\Attendee::findOrFail(intval($product['user_id']));
                    \App\Models\Event::findOrFail(intval($product['event_id']));
                    \App\Models\Room::findOrFail(intval($product['room_id']));
                    \App\Models\Cot::findOrFail(intval($product['cot_id']));
                }
                Log::debug('charge.succeeded validated found');

                foreach ($data['cart'] as $product) {
                    // Verify that reservation hasn't already been made
                    $reservation = \App\Models\Reservation::where('event_id', '=', $product['event_id'])
                        ->where('room_id', '=', $product['room_id'])
                        ->where('cot_id', '=', $product['cot_id']);
                    if ($reservation->exists()) {
                        $reservation_id = $reservation->first()->id;
                        $attendee_id = $product['attendee_id'];

                        Log::error("Duplication of reservation ID:$reservation_id with attendee ID: $attendee_id");
                        return response()->json(['error' => ['cot_id' => 'Reservation failed. Cot already taken.']], 400);
                    }
                }

                // IMPORTANT! Have to secure this
                // 1. Make sure cot is not reserved
                // 2. Make sure that event is published
                // 3. Make sure that room is for gender specific
                // 4. Make sure cot is in room
                // 5. Make sure room is in event

                $reservations = array_map(function ($product) use ($object) {
                    $attendee = \App\Models\Attendee::findOrFail(intval($product['user_id']));
                    return [
                        'attendee_id' => $attendee->id,
                        'first_name' => $attendee->first_name,
                        'last_name' => $attendee->last_name,
                        'event_id' => $product['event_id'],
                        'room_id' => $product['room_id'],
                        'cot_id' => $product['cot_id'],
                        'price' => $product['room_price'],
                        'stripe_payment_intent' => $object['payment_intent'],
                        'created_at' => \Carbon\Carbon::now(),
                        'updated_at' => \Carbon\Carbon::now(),
                    ];
                }, $data['cart']);

                \App\Models\Reservation::insert($reservations);

                foreach ($data['cart'] as $product) {
                    // $attendee = \App\Models\Attendee::findOrFail(intval($model['attendee']['id']));
                    // $camp_event => \App\Models\Event::findOrFail(intval($model['camp_event']['id']));
                    // $room => \App\Models\Room::findOrFail(intval($product['room_id']));
                    // 'cot' => \App\Models\Cot::findOrFail(intval($product['cot_id']))->toArray(),
                    Log::debug($product);
                    $attendee = \App\Models\Attendee::findOrFail(intval($product['user_id']));
                    $camp_event = \App\Models\Event::findOrFail(intval($product['event_id']));
                    $room = \App\Models\Room::findOrFail(intval($product['room_id']));
                    $cot = \App\Models\Cot::findOrFail(intval($product['cot_id']));
                    $reservation = \App\Models\Reservation::where('attendee_id', '=', $attendee->id)
                        ->where('event_id', '=', $camp_event->id)
                        ->where('room_id', '=', $room->id)
                        ->where('cot_id', '=', $cot->id)->firstOrFail();
                    Mail::mailer('ses')
                        ->to($attendee)
                        ->send(new ReservedCot($attendee, $camp_event, $room, $cot, $reservation));
                    Log::debug('Reservation');
                    Log::debug($reservation);
                }
                // ... handle other event types
                break;
            default:
                Log::info('STRIPE: Received unknown event type ' . $event->payload['type']);
        }
    }
}
