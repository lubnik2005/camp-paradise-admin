@extends('emails/base-email')

@section('title')
    Hi {{ $attendee->first_name }} {{ $attendee->last_name }},
@endsection

@section('content')
    Seems like you forgot your Coded Mails password. To reset it, just click the button below.
@endsection

@section('button-url', $url)
@section('button-content', 'Complete your registration')

@section('footer')
    If link doesn't work, go to: <a href="{{ $url }}"> {{ $url }}</a>
@endsection
