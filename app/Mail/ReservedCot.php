<?php

namespace App\Mail;

use App\Models\Attendee;
use App\Models\Event;
use App\Models\Room;
use App\Models\Cot;
use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class ReservedCot extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The attendee instance.
     *
     * @var \App\Models\Order
     */
    public $attendee;

    /**
     * The event instance.
     *
     * @var \App\Models\Order
     */
    public $event;

    /**
     * The room instance.
     *
     * @var \App\Models\Order
     */
    public $room;

    /**
     * The cot instance.
     *
     * @var \App\Models\Order
     */
    public $cot;

    /**
     * The reservation instance.
     *
     * @var \App\Models\Order
     */
    public $reservation;

    /**
     * Create a new message instance.
     * 
     * @var \App\Models\Attendee 
     * @var \App\Models\Event
     * @var \App\Models\Room
     * @var \App\Models\Cot
     *
     * @return void
     */
    public function __construct(Attendee $attendee, Event $event, Room $room, Cot $cot, Reservation $reservation)
    {
        $this->attendee = $attendee;
        $this->event = $event;
        $this->room = $room;
        $this->cot = $cot;
        $this->reservation = $reservation;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: 'Camp Paradise Reservation Confirmation',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'emails.reserved',
            //text: 'emails.orders.reserved-text'
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
