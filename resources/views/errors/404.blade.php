@extends('layouts.app')
@section('title')
    <title> 404 page | {{ config('app.name', 'Laravel') }}</title>
@stop
{{--@section('banner')
    <div class="banner" style="background-image: url({{asset('images/banner01.jpg')}})">
    </div>
@endsection--}}
@section('content')
    <div class="page-error row">
        <div class="col-lg-6">
            <h1>Oops!</h1>
            <div class="icon-box">
                <i class="fal fa-frown"></i>
            </div>
            <h1>404</h1>
            <h2>Page not found</h2>
            <p>The page you are looking for doesn't exist or another error occurred.</p>
            <p><a href="{{route('login')}}">Go back</a> to choose a new direction. </p>
        </div>
    </div>
@stop
