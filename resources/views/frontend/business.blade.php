@extends('layouts.frontend')

@section('title')
    <title>BUSINESS</title>
@stop
@section('content')
    <section class="section">
        <div class="container">
            <div class="text-center">
                <h3>{!! __('business.become_a_partner') !!}</h3>
                <h2 class="font-40 text-dark text-center mb-3">{!! __('business.our_heart_beats') !!}</h2>
                <p>{!! __('business.our_partner_are_more') !!}</p>
                <p><b>{!! __('business.you_are_at_the') !!}</b></p>
                <p>{!! __('business.for_us_at_cargotaxi') !!}</p>
                <p>{!! __('business.for_us_at_cargotaxi') !!}</p>
                <p><a href="{!! route('contact-us') !!}" class="btn">{!! __('business.register_button') !!}</a> </p>
                <p><a href="#">www.cargotaxi.at</a> </p>
            </div>
        </div>
    </section>
@stop
