@extends('layouts.app')
@section('title')
    <title> Verify | {{ config('app.name', 'Laravel') }}</title>
@stop
@section('content')
    <div class="container">
        <div class="flex-row">
            <div class="col col1" style="background-image: url(images/loginbg.jpg)">
                <div class="login-text">
                    <div class="logo">
                        <a href="#">
                            <img src="{{url('images/logo-round.png')}}">
                        </a>
                    </div>
                </div>
            </div>
            <div class="col col2">
                 <form class="form" action="{{ route('verify.code') }}" method="POST">
                    <div class="form-group">
                        <h1>Verification Code</h1>
                    </div>
                    <div class="form-group">
                        <h6>Before submiting please verify the confirmation code form email,</h6>
                    </div>
                    @csrf
                    @if(isset($message))
                    <div class="form-group">
                        <span>{{$message}}</span>
                    </div>
                    @endif
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" placeholder="Enter Email" value="{{ old('email') }}" required autocomplete="email">
                        @error('email')
                        <span class="input-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label>Verification Code</label>
                        <input type="text" name="code" class="form-control" placeholder="Enter Verification Code" value="{{ old('code') }}" required autocomplete="one-time-code" autofocus>
                        @error('code')
                        <span class="input-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group text-center">
                        <button type="submit" class="btn btn-success">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection