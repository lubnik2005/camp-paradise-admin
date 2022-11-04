<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Laravel\Cashier\Events\WebhookReceived;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

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
            case 'payment_intent.succeeded':
                Log::debug('charge.succeeded');
                $object = $event->payload['data']['object'];
                $data = $object['metadata'];

                $validator = Validator::make($data, [
                    'user_id' => 'required|integer',
                    'event_id' => 'required|integer',
                    'room_id' => 'required|integer',
                    'cot_id' => 'required|integer'
                ]);

                Log::debug($validator->messages());
                //Send failed response if request is not valid
                if ($validator->fails()) {
                    return response()->json(['error' => $validator->messages()], 500);
                }

                Log::debug('charge.succeeded validated');
                Log::debug($data);

                $attendee = \App\Models\Attendee::findOrFail(intval($data['user_id']));
                $camp_event = \App\Models\Event::findOrFail(intval($data['event_id']));
                $room = \App\Models\Room::findOrFail(intval($data['room_id']));
                $cot = \App\Models\Cot::findOrFail(intval($data['cot_id']));

                Log::debug('charge.succeeded validated found');

                $reservation = \App\Models\Reservation::where('event_id', '=', $camp_event->id)
                    ->where('room_id', '=', $room->id)
                    ->where('cot_id', '=', $cot->id);

                if ($reservation->exists()) {
                    return response()->json(['error' => ['cot_id' => 'Reservation failed. Cot already taken.']], 400);
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
                $reservation->event_id = $camp_event->id;
                $reservation->room_id = $room->id;
                $reservation->cot_id = $cot->id;
                Log::debug('Reservation 0');
                $reservation->stripe_payment_intent = $object['payment_intent'];
                // Stripe goes here

                $response = $reservation->save();
                Log::debug('Reservation');
                Log::debug($reservation);
                // ... handle other event types
                break;
            default:
                Log::info('STRIPE: Received unknown event type ' . $event->payload['type']);
        }
    }
}
