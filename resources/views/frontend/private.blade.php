@extends('layouts.frontend')

@section('title')
    <title>Private  Customers</title>
@stop
@section('content')
    <section class="section">
        <div class="container">
            <div class="text-center">
                <h3>Simple as well as Ingenious.</h3>
                <h2 class="font-40 text-dark text-center mb-3">{!! __('private.h21') !!}</h2>
                <p>{!! __('private.p1_we_bring_your_shipment') !!}</p>
                <p>{!! __('private.cargoTax_text') !!}</p>
                <p><b>{!! __('private.would_you_like_to_send') !!}</b>
                <ul class="img-list justify-content-center">
                    <li><h2 class="text-orange">Available On</h2></li>
                    <li>
                        <a href="#">
                            <img src="images/playstore.png" loading="lazy">
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <img src="images/appstore.png" loading="lazy">
                        </a>
                    </li>
                </ul>
                </p>
                <p>{!! __('private.just_book_your_order') !!}
                </p>
                <p>{!! __('private.we_are_a_fast_delivery') !!}</p>
                <p>{!! __('private.thanks_to_our_tracking_systems') !!}</p>
                <p><b>{!! __('private.we_are_first_loginstic') !!}</b></p>
                <p><a href="#" class="btn">Register Now</a> </p>
                <p><a href="#">www.cargotaxi.at</a> </p>
            </div>
        </div>
    </section>
@stop
