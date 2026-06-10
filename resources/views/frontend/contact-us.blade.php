@extends('layouts.frontend')

@section('title')
    <title>Contact Us</title>
@stop
@section('content')
    <section class="section contactus">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h3>Feel Free to Contact us at any time </h3>
                    <p>You can contact us with anything related to ventuno.
                        We'll get in touch with you as soon as possible.</p>
                    <form class="form">
                        <div class="form-group">
                            <input type="text" class="form-control" placeholder="Name">
                        </div>
                        <div class="form-group">
                            <input type="Email" class="form-control" placeholder="Email">
                        </div>
                        <div class="form-group">
                            <textarea placeholder="Write message here"></textarea>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn">Submit</button>
                        </div>
                    </form>
                </div>
                <div class="col-md-6">
                    <div class="map-box">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2658.9062612811263!2d16.348484515209876!3d48.20842145420376!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x476d0794a879cf3f%3A0xfd4483a1e2d57a5!2sPiaristengasse%2023%2C%201080%20Wien%2C%20Austria!5e0!3m2!1sen!2s!4v1619720427667!5m2!1sen!2s" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>

                                                    </div>
                    <ul class="contact-list">
                        <li><i class="fal fa-map-marker-alt"></i>  Piaristengasse 23/101 <br/>1080 Vienna   </li>
                        <li><i class="fal fa-envelope"></i> service@cargotaxi.at</li>
                        <li><i class="fal fa-phone"></i>+ 00 000 00 00</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
@stop
