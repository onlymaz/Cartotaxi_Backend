@extends('layouts.app')
@section('title')
    <title> {{__('messages.customer_dashboard')}} | {{ config('app.name', 'Laravel') }}</title>
@stop
@section('content')
    <div class="card">
        <div class="card-header">
            <h1>Pick Up Now
                <a class="pull-right btn ico-btn btn-success" href="{!! route('customer.mybookings') !!}"><i class="fal fa-list"></i>{{__('messages.my_bookings')}}</a>
            </h1>
        </div>
        <div class="card-body">
            <div id="bodyContent" class="tab position-relative">
                <div class="loader-layout" style="display: none;">
                    <div class="loading"><i class="fal fa-sync"></i>
                        <p>Please wait...</p></div>
                </div>
                <div class="BodyContent">
                    @include('admin.dispatcher.create')
                </div>
            </div>
        </div>

        @endsection
        @section('scripts')
            @include('admin.dispatcher.dispatcher_scripts')
            <script>
                // window.clearMarkers();
                window.initMap();
                $('input[name="pick_location"]').prop('disabled', true);
                $('#pick_address').prop('disabled', true);
                $('input[name="end_location"]').prop('disabled', true);
                // $('input[name="mid[]"]').prop('disabled', true);
                mid =0;
                startPoint = 0;
                endPoint = 0;
                
                

            </script>
@endsection
