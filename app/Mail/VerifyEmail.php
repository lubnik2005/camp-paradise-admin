<?php

namespace App\Mail;

use App\Models\Attendee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;
use Lcobucci\JWT\Encoding\ChainedFormatter;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Token\Builder;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Log;

class VerifyEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The attendee instance.
     *
     * @var \App\Models\Attendee
     */
    public $attendee;

    /**
     * The the url to verify the email
     *
     * @var string
     */
    public $url;

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
    public function __construct(Attendee $attendee)
    {

        // https://lcobucci-jwt.readthedocs.io/en/latest/issuing-tokens/
        $tokenBuilder = (new Builder(new JoseEncoder(), ChainedFormatter::default()));
        $algorithm    = new Sha256();
        //$signingKey   =
        $signingKey   = InMemory::plainText(config('jwt.secret'));
        $iat = CarbonImmutable::now();
        $nbf = CarbonImmutable::now();
        $exp = CarbonImmutable::now()->add(1, 'day');
        $token = $tokenBuilder
            // Configures the issuer (iss claim)
            ->issuedBy(config('app.url'))
            // Configures the audience (aud claim)
            //->permittedFor('')
            // Configures the id (jti claim)
            //->identifiedBy('4f1g23a12aa')
            // Configures the time that the token was issue (iat claim)
            ->issuedAt($iat)
            // Configures the time that the token can be used (nbf claim)
            ->canOnlyBeUsedAfter($nbf)
            // Configures the expiration time of the token (exp claim)
            ->expiresAt($exp)
            // Configures a new claim, called "uid"
            ->withClaim('email', $attendee['email'])
            // Configures a new header, called "foo"
            //->withHeader('foo', 'bar')
            // Builds a new token
            ->getToken($algorithm, $signingKey);

        $tokenString = urlencode($token->toString());


        $clientUrl = env('CLIENT_URL', 'http://localhost:3000');

        $this->url = "$clientUrl/auth/verify?token=$tokenString";
        Log::debug('TOKEN');
        Log::debug($tokenString);
        Log::debug($clientUrl);
        Log::debug($this->url);
        Log::debug("$clientUrl/auth/verify?token=$tokenString");
        $this->attendee = $attendee;
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
            subject: 'Camp Paradise Verify E-mail',
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
            view: 'emails.verify-email-section',
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
