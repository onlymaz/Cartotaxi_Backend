@extends('layouts.app')
@section('title')
    <title>Create Helper</title>
    <link rel="stylesheet" type="text/css" href="{{url('css/jquery.datetimepicker.css')}}">

@stop
@section('content')
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h1>Add Helper</h1>
                </div>
                <div class="card-body">
                    <form class="form ">
                        @csrf
                        <div class="row">
                            @if(Auth::user()->role->name=="admin")
                            <div class="form-group col-lg-12">
                                <label>{{__('messages.select_a_customer')}}</label>
                                <input type="text" name="select_customer" class="form-control" placeholder="{{__('messages.enter_a_customer')}}" onkeydown="CustomerAutoComplete()">
                                <input type="hidden" name="customer_id" class="form-control">
                            </div>
                            @endif
                            <div class="form-group col-lg-6">
                                <label>Date</label>
                                <input type="date" name="start" 
                                    min="{!! Date('Y-m-d',strtotime('+2 days')) !!}" 
                                    value="{!! Date('Y-m-d',strtotime('+2 days')) !!}" 
                                    id="start" class="form-control" placeholder="Enter Start Time"
                                >
                            </div>
                            <div class="form-group col-lg-6">
                                <label>Time</label>
                                <select name="times" onload="getTime(this.value)" onchange="getTimvalue(this.value)" class="form-control" id="ajaxHours" >
                                    <option selected="" disabled="">Select Time</option>
                                    @foreach($hours as $hour)
                                        <option>{{date("H:i", $hour)}} </option>
                                    @endforeach
                                </select>
                            </div>
                            <input type="hidden" name="time" >
                            <div class="form-group col-lg-12">
                                <label>Hours</label>
                                <input type="number" name="end" id="end" class="form-control" onkeyup="clearPrice()" 
                                oninput="this.value = Math.round(this.value);"
                                placeholder="Enter Hours" value="" min="1">
                            </div>
                            <div class="form-group col-lg-12">
                                <label>Total Helpers</label>
                                <input type="number" name="total_helper" id="total_helper"  onkeyup="priceCal()"                            
                                 oninput="this.value = Math.round(this.value);" 
                                 class="form-control" placeholder="Enter Total Helpers" min="1">
                            </div>
                            <div class="form-group col-lg-12">
                                <label>Address</label>
                                <input type="text" id="address" name="address" placeholder="Enter Address" class="form-control">
                            </div>
                            <div class="form-group col-lg-12">
                                <label>Price</label>
                                <input type="text"  id="price"  value="0" readonly="" name="price" class="form-control">
                            </div>
                            <input type="hidden" name="fee" value="{{$fee->fee}}">
                            <div class="form-group text-left mt-3 col-lg-3">
                                <button type="button" class="btn btn-success popup">Next</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
<script type="text/javascript" src="{{url('js/build/jquery.datetimepicker.full.min.js')}}"> </script>

<script type="text/javascript"> 
    function getTimvalue(value){
        $("input[name='time']").val(value);
    }
    function priceCal(){
        $("#price").val(0);
        var hours =  $('#end').val();
        var fee=$("input[name=fee]").val();
        $("#price").val($("input[name=total_helper]").val()*fee*hours);
    }
    function clearPrice(){
        var hours =  $('#price').val(0);
    }
    $(document).ready(function () {
        $("body").on('change', '#start', function() {
        $.ajax({
            url     :   '{!! route('getTime') !!}',
            type    :   'post',
            data    :   {
                date        : $(this).val(),
                _token      :    '{!! csrf_token() !!}',
            },
            success : function(time){
                $("#ajaxHours").empty();
                for(var i=0;i<time.length;i++){
                    $("#ajaxHours").append('<option>'+getTime(time[i])+'</option>');
                }
            }
            });
        });
    });
    function getTime(seconds) {
        var leftover = seconds;
        var days = Math.floor(leftover / 86400);
        leftover = leftover - (days * 86400);
        var hours = Math.floor(leftover / 3600);
        leftover = leftover - (hours * 3600);
        var minutes = Math.floor(leftover / 60);
        leftover = leftover - (minutes * 60);

        if (hours   < 10) 
            {hours   = "0"+hours;
        }
        if (minutes < 10) {
            minutes = "0"+minutes;
        }
        return  hours + ':' + minutes;
    }
        $( ".popup" ).click(function() {
            LoadPaymentGateway();
    });
    
    function LoadPaymentGateway(){
        $.ajax({
            url     :   '{!! route('SelectPaymentGateway') !!}',
            type    :   'post',
            data    :   {
                _token      :    '{!! csrf_token() !!}',
            },
            success : function(html){
                $('#default_modal .modal-dialog').html(html).removeClass('modal-lg');
                $('#default_modal').modal('show');
                $("#gateway_1").click();
                loadajax();
                var currentGateway  =   $('input[name="gateway"]:checked').val();
                const price         =   $('input[name="gateway"]:checked').attr('data-price');
                if(currentGateway == 1){
                    $('.PaymentButtons .CashOnDelivery').show();
                    $('.PaymentButtons .Paypal').hide();
                }
                if(currentGateway == 2){
                    $('.PaymentButtons .CashOnDelivery').hide();
                    $('.PaymentButtons .Paypal').show();
                    $("#gateway_1").click();
                    // LoadPaypalButtons(price,currentGateway);
                }

            }
        });
    }
        function loadajax(){
        $( ".SaveOrder" ).click(function() {
            $('.SaveOrder').attr('disabled','disabled');            
            var url;
            @if(Auth::user()->role->name=="admin")
                url='{{url("helper/store/admin")}}';
            @else
                url='{{url("helper/store")}}';
            @endif
            $(".input-error").empty();
             $.ajax({
                url: url,
                type: 'POST',
                data:{
                    _token: "{{ csrf_token() }}",
                    customer_id:$("input[name=customer_id]").val(),   
                    address:$("input[name=address]").val(),   
                    gateway:$("input[name=gateway]").val(),
                    total_helper:$("input[name=total_helper]").val(),
                    start:$("input[name=start]").val(),
                    end:$("input[name=end]").val(),
                    price:$("input[name=price]").val(),
                    time:$("input[name=time]").val(),
                },
                success: function (data) { 
                    // $('.SaveOrder').removeAttr('disabled','disabled');            
                    toast.success("Successfully Created");
                    setTimeout(function(){
                        window.location = '{{url("helper")}}';
                }, 1000);                  
                },
                error: function (response, exception) {
                    $('.SaveOrder').removeAttr('disabled','disabled');            
                    toast.error("The given data was invalid.");
                    var errors = JSON.parse(response.responseText).errors;
                    $.each(errors,function (name,error) {
                        $('.form-control[name="'+name+'"]').after('<span class="input-error '+name+'">'+error+'</span>');
                    });

                }
            }); 
        });  
    }
    function CustomerAutoComplete(){
        $('input[name="select_customer"]').autocomplete({
            source: function( request, response ) {
                $.ajax( {
                    url: "{!! route('getCustomers') !!}",
                    dataType: "json",
                    data: {
                        term: request.term
                    },
                    success: function( data ) {
                        response(data);
                    }
                } );
            },
            minLength: 1,
            select: function( event, ui ) {
                var item = ui.item;
                var elem    =   $(this);
                var Index   =   elem.attr('Index');

                $('input[name="customer_id"]').val(item.id);
            }
        } );
    }


    </script>
    <script>
function initAutocomplete() {
   new google.maps.places.Autocomplete(
          (document.getElementById('address')),
          {types: ['geocode']}
   );
}
</script>    
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&libraries=places&callback=initAutocomplete" async defer></script>

@endsection