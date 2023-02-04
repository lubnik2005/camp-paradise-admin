@extends('emails/base-email')

@section('title')
    Camp Paradise Reset Password
@endsection

@section('greeting')
    Hi {{ $attendee->first_name }} {{ $attendee->last_name }},
@endsection

@section('content')
    Seems like you forgot your Camp Paradise password. To reset it, just click the button below.
@endsection

@section('button-url', $url)
@section('button-content', 'Reset Password')

@section('footer')
    If link doesn't work, go to: <a href="{{ $url }}"> {{ $url }}</a>
@endsection
