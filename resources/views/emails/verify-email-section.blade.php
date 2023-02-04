@extends('emails/base-email')

@section('title')
    Camp Paradise Welcome
@endsection

@section('greeting')
    Welcome {{ $attendee->first_name }} {{ $attendee->last_name }},
@endsection

@section('content')
    Please click the button below to <a href="{{ $url }}" target="_blank"
        style="color: #1c6937; text-decoration: none; font-weight: 500;">complete
        your registration</a>. Once you have
    completed the process, you can login and start registering for camps.
@endsection

@section('button-url', $url)
@section('button-content', 'Complete your registration')

@section('footer')
    If link doesn't work, go to: <a href="{{ $url }}"> {{ $url }}</a>
@endsection
