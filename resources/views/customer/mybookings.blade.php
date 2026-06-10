@extends('layouts.app')
@section('title')
    <title> {{__('messages.my_bookings')}} | {{ config('app.name', 'Laravel') }}</title>
@stop
@section('content')
    <div class="card">
        <div class="card-header">
            <h1>{{__('messages.my_bookings')}} <a class="pull-right btn ico-btn btn-success" href="{!! route('customer.bookings') !!}"><i class="fal fa-plus"></i>{{__('messages.create_booking')}}</a></h1>
        </div>
        <div class="card-body">
            <div id="bodyContent" class="tab position-relative">
                <div class="loader-layout" style="display: none;">
                    <div class="loading"><i class="fal fa-sync"></i>
                        <p>Please wait...</p></div>
                </div>
                <div class="BodyContent" style="overflow:hidden;">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="scrollbar">
                                @if(empty($data['bookings']['list']))
                                <div class="row">
                                    <div class="col-lg-4">
                                        
                                    </div>
                                    <div class="col-lg-4">
                                        <span style="text-align: center;">
                                            <h1>{{__('messages.record_not_found')}}</h1>
                                        </span>
                                    </div>
                                    <div class="col-lg-4">
                                        
                                    </div>
                                </div>
                                @elseif(!empty($data))
                                @foreach($data['bookings']['list'] as $key => $order)
                                <div class="mapclass clearfix viewLocations {!! ($key ==0)?'active':'' !!}">
                                    <div class="clearfix">
                                        <p class="mb-2 pull-left"><b>{{__('messages.pick_time')}}:</b>{{date('M d,Y h:i A',strtotime($order['picked_time']))}}</p>
                                        <div class=" pull-right">
                                            <span title="Rider Info" class="badge @if($order['rider_id'] ==  0) badge-warning @else badge-success rider_detail @endif">
                                                @if($order['rider_id'] ==  0)
                                                 
                                                Rider Not Assign
                                                @else
                                                <input type="hidden" name="rider_id" value="{!! $order['order_id'] !!}"> 
                                                Rider Detail:{!! $order['rider_id'] !!}
                                                @endif
                                            </span>

                                            <span title="Booking ID" class="badge badge-success">{{__('messages.bookings_id')}}:{!! $order['booking_id'] !!}</span>

                                            <span title="Booking ID" class="badge badge-warning">Order Id :{!! $order['order_id'] !!}</span>

                                            @if($order['order_status'] == 'pending')
                                                <a href="javascript:void(0);" class="badge text-white badge-secondary" >{!! $order['order_status'] !!}</a>
                                            @elseif($order['order_status'] == 'processing')
                                                <a href="javascript:void(0);" class="badge text-white badge-inverse">{!! $order['order_status'] !!}</a>
                                            @elseif($order['order_status'] == 'picking')
                                                <a href="javascript:void(0);" class="badge text-white badge-info">{!! $order['order_status'] !!}</a>
                                            @elseif($order['order_status'] == 'pickup')
                                                <a href="javascript:void(0);" class="badge text-white badge-warning">{!! $order['order_status'] !!}</a>
                                            @elseif($order['order_status'] == 'on_way')
                                                <a href="javascript:void(0);" class="badge text-white badge-info">On the way</a>
                                            @elseif($order['order_status'] == 'picked_up')
                                                <a href="javascript:void(0);" class="badge text-white badge-info">Picked Up</a>
                                            @elseif($order['order_status'] == 'delivered')
                                                <a href="javascript:void(0);" class="badge text-white badge-success">{!! $order['order_status'] !!}</a>
                                                <a class="badge text-white badge-success rating" id="{!! $order['order_id'] !!}" >Rating</a>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="helperinput">
                                    <input type="hidden" name="helper_id" value="{{$order['helper_id']}}"> 
                                    <div class="clearfix">
                                        
                                    </div>
                                        <div class="pull-right">
                                        @if($order['order_status']!='refused')
                                            <a href="#" id="{{$order['order_id']}}" class="badge text-white  
                                            @if($order['rider_id'] ==  0)  
                                                badge-danger refused_order
                                            @endif">
                                            Refuse
                                            </a>
                                        @else
                                            <a class="badge text-white " href="#">
                                                Refused
                                            </a>
                                        @endif
                                        </div>
                                    </div>
                                    <p class="mb-1"><strong>Cargo Stand :</strong> 
                                            <span >Freudenauer Hafenstraße 8-10, 1020 Wien, Austria  </span>
                                    </p>
                                    <div class="myclass">
                                    @php $i=1; @endphp
                                    @foreach($order['location'] as $o)
                                        @if($loop->first)
                                            <p class="mb-1 main_latlng"><strong>Pick Up:</strong> 
                                                <span class="start_address">{{$o->start_location}}</span>
                                                <input type="hidden" class="slat{!! $o->order_id !!}" name="start_lat[]" value="{{$o->start_lat}}">
                                                <input type="hidden" class="slng{!! $o->order_id !!}" name="start_long[]" value="{{$o->start_long}}">
                                                <input type="hidden" class="pickup{!! $o->order_id !!}" name="pickup_location" value="{{$o->start_location}}">
                                            </p>
                                        @endif
                                        <p class="mb-1">
                                            <strong>Drop Off 
                                        @if($loop->last) 
                                                <small>
                                                    <b>|Last Location|
                                                    </b>
                                                </small> 
                                        @else  
                                            {{$i++}} 
                                        @endif:</strong> 
                                            <span class="end_address">{{$o->end_location}} </span>
                                            <input type="hidden" class="dropoff{!! $o->order_id !!}" name="dropoff" value="{{$o->end_location}}">
                                                <input type="hidden" class="lat{!! $order['order_id'] !!}" name="end_lat[]" value="{{$o->end_lat}}">
                                                <input type="hidden" class="lng{!! $order['order_id'] !!}" name="end_long[]" value="{{$o->end_long}}">
                                        </p>
                                    @endforeach
                                    </div>
                                    <p class="mb-1"><strong>{{__('messages.from')}}Package:</strong> <span class="badge badge-success">{{$order['package_name']}}</span>
                                    </p>
                                    <p class="mb-1 Helper-info"><strong>Helper :</strong>
                                    @if($order['helper_id']) 
                                    <input type="hidden" name="helper_id" value="{{$order['helper_id']}}"> 
                                        <span class="badge text-white badge-info  with_helper">With Helper</span>
                                     @else 
                                        <span class="badge text-white badge-secondary">
                                        <input type="hidden" name="helper_id" value="{{$order['helper_id']}}"> 
                                         No Helper</span>@endif
                                    </p>
                                    <div class="clearfix">
                                        <p class="mb-1 pull-left">
                                            <strong>{{__('messages.payment')}} </strong>
                                            <span class="text-uppercase badge badge-info">{!! !empty($order['gateway_name'])?$order['gateway_name']:'N/A' !!}</span>
                                            <span class="text-uppercase badge badge-info">
                                            {!! !empty($order['payment_status'])?$order['payment_status']:'N/A' !!}
                                            </span>

                                        </p>
                                    </div>
                                    <div class="clearfix mt-3 flip " >
                                        <input type="hidden" name="polylines" class="mapinput" value="{{$order['map_image']}}">
                                        <input type="hidden" name="order" class="mapinput" value="{!! $order['order_id'] !!}">
                                        <div class="progress progress-small  mt-2 mb-1">
                                            <div class="progress-bar progress-bar-warning" style="width:100%"></div>
                                        </div>
                                        
                                                
                                        <!-- <a href="javascript:void(0)" class="view_locations badge badge-info"><i class="fal fa-eye"></i> -->
                                        <a href="#" class="flip-opener view_map_button view_locations d-block mb-2">{{__('messages.view_on_map')}} <i class="fal fa-eye"></i></a>
                                        
                                        <div class="overflow-hidden">

                                            <span><b> {{__('messages.amount')}} :</b>&euro;{!! $order['total_cost'] !!}</span>
                                            <span><b> {{__('messages.distance')}} :</b>{!! number_format(($order['total_distance']),2) !!} km</span>
                                        </div>
                                        <div class="overflow-hidden">
                                           <!--  <button class="flip-opener pull-right view_map_button btn btn-success">

                                                <i class="fa fa-angle-down" aria-hidden="true"></i>
                                            </button> -->
                                            <span class="d-block font-12 hint-text overflow-hidden mt-2">
                                                {{__('messages.manual_assignment')}}: {{date('Y-m-d H:i:s'),strtotime($order['picked_time'])}}
                                            </span>
                                         </div>
                                        <div class="flip-dropdown"> 
                                            <div class="map-box">
                                                <!-- <img src="{{$order['map_image']}}"  style="height: 430px !important"> -->
                                                <div class="show_map" style="height: 430px !important"></div>
                                            </div>
                                        </div>
                                    </div>

                                  <!--   <div class="clearfix mt-3">
                                        <div class="progress progress-small  mt-2 mb-1">
                                            <div class="progress-bar progress-bar-warning" style="width:100%"></div>
                                        </div>
                                        
                                    </div>-->
                                </div> 
                                @endforeach
                                <input type="hidden" id="tab_type" value="search">
                                <div class="custom_pagination">
                                    {!! $bookings->links() !!}
                                </div>
                                @endif
                            </div>
                        </div>
                       <!--  <div class="col-lg-7">
                            <div class="map-box">
                                <div id="search_map" style="height: 430px"></div>
                            </div>
                        </div> -->
                    </div>

                </div>
            </div>
        </div>

  <!-- Modal -->
  <div class="modal fade" id="default_modal" role="dialog">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Helper Information</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <strong>Total Helper : </strong ><span id="total_helper"></span> <br>
          <strong>Price : </strong ><span id="price"></span><br>
          <strong>Booking Time : </strong ><span id="start_time"></span><br>
          <strong>Total Hours : </strong ><span id="total_hours"></span>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
      
    </div>
  </div>

  <div class="modal fade" id="rider_model" role="dialog">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Rider Information</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <strong>Rider Name: </strong ><span id="rider_name"></span> <br>
          <strong>Email : </strong ><span id="rider_email"> </span><br>
          <strong>Phone Number : </strong ><span id="rider_no"> </span><br>
          <strong>Recvied By : </strong ><span id="recvied_by"></span><br>
          <strong>Booking Time : </strong ><span id="booking_time"></span><br>
          <strong>Delived Data-Time : </strong ><span id="deliver_time"></span><br>
          <strong>Sign : </strong ><br><img width="300" height="300" src="" id="sign_image"><br>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>

        </div>
      </div>
      
    </div>
  </div>

  <div class="modal fade" id="refuse_model" role="dialog">
    <div class="modal-dialog">
    @include('customer.booking_cancel_options');
    </div>
  </div>
  @include('customer.rating_rider')
@endsection
@section('scripts')
    <script src="https://www.gstatic.com/firebasejs/7.17.1/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/7.17.1/firebase-database.js"></script>
    @include('customer.customer_maps_scripts')
        <script>

            $("body").on('click', '.rating', function() {
                var order_id = $(this).attr("id");
                $("#rating_o_id").val(order_id);
               $("#rating_model").modal("show");
            });
            $("body").on('click', '.view_map_button', function() {
                var order = 0;
                var order = $(this).closest(".flip-active").find("input[name='order']").val();

                var pickuplocation = document.querySelectorAll('.pickup'+order);
                var dropoff = document.querySelectorAll('.dropoff'+order);
                var slat = document.querySelectorAll('.slat'+order);
                var slng = document.querySelectorAll('.slng'+order);
                var array = document.querySelectorAll('.lat'+order);
                var array2 = document.querySelectorAll('.lng'+order);
                var total = new Array();
                var arrlength = array.length;

                if($("#my_map").length){
                    $("#my_map").attr("id","no_map");
                }
                var encoding_string = $(this).closest(".flip-active").find("input[name='polylines']").val();
                var encoding_str2 = JSON.parse(encoding_string);
                var encoding_str = encoding_str2.toString();


                if(encoding_str){ 
                $(this).closest(".mapclass").find(".show_map").attr("id", "my_map");
                function initialize() {
                    var map = new google.maps.Map(document.getElementById('my_map'), {
                        center: {
                            lat: 48.182499, 
                            lng:16.465839
                        },
                        zoom:8
                    });
                    var encoded_data = encoding_str2;
                    for(var i=0;i<=encoded_data.length;i++){
                           var decode = google.maps.geometry.encoding.decodePath(encoded_data[i]);
                    var line = new google.maps.Polyline({
                        path: decode,
                        strokeColor: '#00008B',
                        strokeOpacity: 1.0,
                        strokeWeight: 4,
                        zIndex: 3
                    });
                    line.setMap(map);
                    addMarker(
                            48.182499,
                            16.465839,
                            "S",
                            "Freudenauer Hafenstraße 8-10, Vienna, Austria",
                            map
                        );

                    addMarker(
                            slat[0].value,
                            slng[0].value,
                            "P",
                            pickuplocation[0].value,
                            map
                        );
                    var dropoff1 = 0;
                  for (var b = 0; b <arrlength; b++) {                        
                    addMarker(
                                array[b].value,
                                array2[b].value,
                                "D"+ ++dropoff1,
                                dropoff[b].value,
                                map
                            );
                        }
                 } 
                }

                initialize();

            }
            else{
                $(this).closest(".mapclass").find(".show_map").attr("id", "no_map");
            }
            });


            $("body").on('click', '.rider_detail', function() {
                var rider_id = $(this).find("input[name='rider_id']").val();
                //alert(rider_detail);
                
            $.ajax({
                url     :   "{{url('customer.bookings.RiderDetail')}}",
                type    :   'post',
                data    :   {
                      id        : rider_id,
                    _token      :    '{!! csrf_token() !!}',
                },
                success : function(data){
                    console.log(data);
                   $("#rider_model").modal("show");
                   $("#rider_name").text(data['rider']['first_name']+data['rider']['last_name']);
                   $("#rider_email").text(data['rider']['email']);
                   $("#rider_no").text(data['rider']['phone_number']);
                   //$("#recvied_by").text(data['reciver_name']);
                   $("#booking_time").text(data['created_at']);
                   $("#deliver_time").text(data['end_time']);
                   $("#sign_image").attr("src",data['sign']);
                   if(data.reciver_name){
                      $("#recvied_by").text(data.reciver_name+ " From " + data.reciver_address);
                   }else{
                      $("#recvied_by").text(data.user['first_name']+ " "+data.user['last_name'] );
                   }

                   //html('<img height="250" width="250" src="data[0]["order"]["sign"]" alt="good" id="sign">');
                }
                });
            });

            $("body").on('click', '.with_helper', function() {
                var bb = $(this).closest(".Helper-info");
                var helper_id = $(this).closest(".Helper-info").find("input[name='helper_id']").val();
            $.ajax({
                url     :   '{!! route('customer.bookings.helper-info-ajax') !!}',
                type    :   'post',
                data    :   {
                      id        : helper_id,
                    _token      :    '{!! csrf_token() !!}',
                },
                success : function(data){
                    console.log(data);
                   var total_helper =  data[0]['total_helper'];
                   var price = data[0]['price'];
                   var start_time =data[0]['start_time'];
                   var total_hours = data[0]['end_time']
                   $("#default_modal").modal("show");
                   $("#total_helper").text(total_helper);
                   $("#price").text(price);
                   $("#start_time").text(start_time);
                   $("#total_hours").text(total_hours);
                }
                });
            });
            $("body").on('click', '.refused_order', function() {
                //alert('good');
                helper = $(this).closest('.helperinput').find("input[name='helper_id']").val();
                order_id = $(this).attr('id');
                if(helper>0)
                {
                    $("#helperoption").html('<select id="myselection" class="form-control"><option value="cancelhelper">Cancel only helper</option><option value="cancelbooking">Cancel only booking</option><option value="cancelboth">Cancel both</option></select>');
                }else{
                    $("#helperoption").html('<select class="form-control" id="myselection"><option value="cancelbooking">Cancel only booking</option></select>');
                }
               $("#refuse_model").modal("show");
            });
            $("body").on('click', '#submitdata', function() {
               // alert(helper);
               select_value =$( "#myselection" ).val();
            $.ajax({
                url     :   '{!! route('customer.bookings.change_status_popup') !!}',
                type    :   'post',
                data    :   {
                      id        : order_id,
                      helper_id : helper,
                      order_status:select_value,
                    _token      :    '{!! csrf_token() !!}',
                },
                success : function(){
                    toast.success("Updated successfully");
                    window.location.reload();
                }
                });
            });
            var rider_id       =   0;
            var customer_id    =   0;
            var firebaseConfig = {
                apiKey: "{{env('FIREBASE_API_KEY')}}",
                authDomain: "{{env('FIREBASE_AUTH_DOMAIN')}}",
                databaseURL: "{{env('FIREBASE_DATABASE_URL')}}",
                projectId: "{{env('FIREBASE_PROJECT_ID')}}",
                storageBucket: "{{env('FIREBASE_STORAGE_BUCKET')}}",
                messagingSenderId: "{{env('FIREBASE_MESSAGING_SENDER_ID')}}",
                appId: "{{env('FIREBASE_APP_ID')}}",
                measurementId: "{{env('FIREBASE_MEASUREMENT_ID')}}"
            };
            firebase.initializeApp(firebaseConfig);
            var dbRef = firebase.database().ref();
            $(document).ready(function () {

                $('body').on('click','.view_live_locations',function(){
                    var elem    =   $(this);
                    rider_id    =   elem.attr('rider_id');
                    customer_id    =   elem.attr('customer_id');
                    if(customer_id !=0 && rider_id != 0){
                        RealTimeMap(rider_id,customer_id);
                    }
                });
            });
            function RealTimeMap(rider_id,customer_id){
                dbRef.child('users/'+rider_id).on('value', function(rider){
                    var rider = rider.val();
                    if(rider){
                        dbRef.child('users/'+customer_id).on('value', function(customer){
                            var customer = customer.val();
                            loadMapOnPopup(rider,customer);
                        });
                    }
                    else
                    {
                        dbRef.child('users/'+customer_id).on('value', function(customer){
                            var customer = customer.val();
                            loadMapOnPopup(rider,customer);
                        });
                    }
                });
            }
            function loadMapOnPopup(rider,customer){
                var html = '<div class="modal-content">\n' +
                    '    <div class="modal-header">\n' +
                    '        <h5 class="modal-title">Live Map</h5>\n' +
                    '        <button type="button" class="close" data-dismiss="modal">\n' +
                    '            <i class="fal fa-close"></i>\n' +
                    '        </button>\n' +
                    '    </div>\n' +
                    '    <div class="modal-body">' +
                        '<div id="live_map" style="width:100%;height:400px;"></div>'+
                    '   </div>';
                $('#default_modal .modal-dialog').html(html);
                $('#default_modal').modal('show');
                var start_latlng    =   {
                    lat     :   customer.lat?Number(customer.lat):48.203231,
                    lng     :   customer.long?Number(customer.long):16.3667583
                };
                var end_latlng    =   {
                    lat     :   rider.lat?Number(rider.lat):48.203231,
                    lng     :   rider.long?Number(rider.long):16.3667583
                };
                SearchMapRealTimePoint(start_latlng,end_latlng,'live_map');
            }
            function addMarker(lats,long,label,pdetail,map){
                const marker = new google.maps.Marker({
                    position: { 
                        lat: parseFloat(lats),
                        lng: parseFloat(long) 
                    },
                    label:label,
                    title:pdetail,
                    map: map,
                });
                  marker['infowindow'] = new google.maps.InfoWindow({
                    content: pdetail
                    });

                google.maps.event.addListener(marker, 'click', function() {
                    this['infowindow'].open(map, this);
                    });
                }
            </script>
@endsection
