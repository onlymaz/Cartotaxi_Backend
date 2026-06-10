@if (auth()->user()->role_id==3)
    <!-- <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&libraries=geometry"></script> -->

    <script src= 
"https://cdn.jsdelivr.net/npm/html2canvas@1.0.0-rc.5/dist/html2canvas.min.js"> 
    </script> 
    @if (!empty(env('PAYPAL_LIVE')) && env('PAYPAL_LIVE') =='sandbox')
        <script src="https://www.paypal.com/sdk/js?client-id={!! env('PAYPAL_SANDBOX_CLIENT_ID') !!}&disable-funding=credit,card&currency=EUR" data-namespace="paypal_sdk"></script>
    @else
        <script src="https://www.paypal.com/sdk/js?client-id={!! env('PAYPAL_PRODUCTION_CLIENT_ID') !!}&disable-funding=credit,card&currency=EUR" data-namespace="paypal_sdk"></script>
    @endif
@endif
<script type="text/javascript">
    var per_km_charges = 0;
    var startPoint = 0; /* add marker for start location when 1*/
    var waypots = 0;
    var endPoint = 0; /* add marker for end location when 1*/
    var markers = [];
    var map;
    var polygons = [];
    var mid_district=0;

    $(document).ready(function () {
        /* initalize();*/
        $('body').on('click','.popup',function () {
            var elem    =   $(this);
            var url     =   elem.attr('data-url');
            var type     =   elem.attr('data-type');
            $.ajax({
                type:'get',
                url:url,
                success:function(data) {
                    $("#default_modal .modal-dialog").html(data);
                    if(type && type==='delete'){
                        $("#default_modal .modal-dialog").removeClass('modal-lg');
                    }
                    $("#default_modal").modal("show");
                }
            });
        });
        $('body').on('submit','.ajax-form-update',function(event) {
            event.preventDefault();
            var elem    =   $(this);
            // forms set either data-url or a plain action (e.g. the rider
            // assignment modal); without the fallback the request went to the
            // current page and 405'd.
            var url     =   elem.attr('data-url') || elem.attr('action');
            var data    =   this;
            $('span.input-error').remove();
            var formData = new FormData(data);
            formData.append('_token', $('meta[name=csrf-token]').attr("content"));
            formData.append('_method', 'PATCH');
            $.ajax({
                type:'POST',
                url:url,
                data        :   formData,
                processData: false,
                contentType: false,
                success:function(data) {
                    if(data.success){
                        loadPageData(data.type,data.url);
                        $('#default_modal').modal('toggle');
                        toast.success(data.message || "Rider updated successfully");
                    }
                },
                error: function (response, exception) {
                    var payload = null;
                    try { payload = JSON.parse(response.responseText); } catch (e) {}
                    if (payload && payload.errors) {
                        toast.error("Please resolve following errors");
                        $.each(payload.errors,function (name,error) {
                            $('.form-control[name="'+name+'"]').after('<span class="input-error '+name+'">'+error+'</span>');
                        });
                    } else {
                        toast.error((payload && payload.message) || "Something went wrong, please try again");
                    }
                },
            });
        });
        @if(!empty($type))
        loadPageData("{{$type}}","{{$url}}");
        @endif
        $('body').on('change', 'select[name="select_package"]', function () {
            var elem = $(this);

                var select_package = $(this). children("option:selected"). val();
                 $.ajax({
                        method: 'POST', 
                        url: '{{url("customer/package-ajax-price")}}', 
                        data: {
                            _token :   '{!! csrf_token() !!}',
                            'id' : select_package
                        }, 
                    success: function(response){ 
                        // $('select[name="select_package"]').attr('disabled','disabled');
                        $("input[name='total_amount']").val(response.fixed_price);
                        $("input[name='unit_price']").val(response.fixed_price);
                        
                    }
                });
            if (elem.val() != '') {
                per_km_charges = elem.find('option:selected').attr('per_km_charges');
                $('input[name="pick_location"]').prop('disabled', false);
                $('#pick_address').prop('disabled', false);
                $('input[name="end_location"]').prop('disabled', false);
                // $('input[name="mid"]').prop('disabled', false);
                startPoint = 1;
                waypots=0;
                endPoint = 0;
            } else {
                per_km_charges = 0;
                $('input[name="pick_location"]').prop('disabled', true);
                $('#pick_address').prop('disabled', true);
                $('input[name="end_location"]').prop('disabled', true);
                // $('input[name="mid"]').prop('disabled', false);
                startPoint = 0;
                endPoint = 0;
            }
            $('input[name="per_km_charges"]').val(per_km_charges);
        });
        $('body').on('click', '.refresh-btn', function () {
            var elem = $(this);
            loadPageData('add','{!! route('dispatcher.create') !!}',elem);
        });

        function loadPageData(type,url,elem='') {
            $('.Content li').removeClass('active');
            /*var type = elem.attr('type');
            var url = elem.attr('url');*/
            $.ajax({
                url: url,
                type: 'get',
                data: {
                    action: type
                },
                beforeSend: function () {
                    showLoader();
                    if(elem){
                        elem.parents('li').addClass('active');
                    }

                },
                success: function (html) {
                    hideLoader();
                    $('#bodyContent .BodyContent').html(html);
                    if (type == 'add') {
                        // window.clearMarkers();
                        polygons = [];
                        window.initMap();
                        $('input[name="pick_location"]').prop('disabled', true);
                        $('input[name="end_location"]').prop('disabled', true);
                        $('input[name="mid"]').prop('disabled', true);
                        startPoint = 0;
                        endPoint = 0;
                        CustomerAutoComplete();
                    }
                    else if(type == 'search'){
                        var viewLocations       =       $('.viewLocations.active');
                        var start_address       =       viewLocations.find('span.start_address').html();
                        var mid_address       =       viewLocations.find('span.mid').html();
                        var end_address         =       viewLocations.find('span.end_address').html();
                        DrawLineOnMap(start_address,end_address);
                    }
                    else if(type == 'assigned'){
                        var viewLocations       =       $('.viewLocations.active');
                        var start_address       =       viewLocations.find('span.start_address').html();
                        var mid_address       =       viewLocations.find('span.mid').html();
                        var end_address         =       viewLocations.find('span.end_address').html();
                        DrawLineOnMap(start_address,end_address);
                    }

                    // perfectScrollbar shipped with the old theme; the plugin
                    // is no longer loaded, so guard to avoid a TypeError.
                    if ($.fn.perfectScrollbar) {
                        $('.scrollbar').perfectScrollbar();
                    }
                }
            });
        }

        // .tabset-link covers both the tab list (.Content li a) and the
        // "New Order" button in the page header, which sits outside the tabs.
        $('body').on('click', '.tabset-link', function () {
            var elem = $(this);
            var type = elem.attr('type');
            var url = elem.attr('url');
            loadPageData(type,url,elem);
        });
        $('body').on('click', '.SaveOrder', function (event) {
            event.preventDefault();
            var elem = $(this);
            $('.SaveOrder').attr('disabled','disabled');
            $('select[name="select_package"]').removeAttr('disabled');
            // var url = elem.parents('form').attr('action');
            var data = $('.order_form')[0];
            var formData = new FormData(data);
            $('span.input-error').remove();

            $.ajax({
                type: 'post',
                url: "{{url('dispatcher/store')}}",
                data: formData,
                processData: false,
                contentType: false,
                success: function (data) {
                    if (data.success) {
                        toast.success("Order created successfully.please wait..");

                        location.reload();
                        {{--                          
                        @if(auth()->user()->role_id ==3)
                        if(data.data){
                            var order  =data.data;
                            // LoadPaymentGateway(order.id);
                        }
                        @endif
                        --}}
                    }
                },
                error: function (response, exception) {
                    toast.error("Please resolve following errors");
                    $('.SaveOrder').removeAttr('disabled');
                    var errors = JSON.parse(response.responseText).errors;
                    $.each(errors, function (name, error) {
                        if ($('span.text-danger.' + name).length == 0) {
                            $('.form-control[name="' + name + '"]').after('<span class="input-error ' + name + '">' + error + '</span>');
                            $('textarea[name="' + name + '"]').after('<span class="input-error ' + name + '">' + error + '</span>');
                        }
                    });
                },
            });
        });
        $('body').on('click', '.SaveOrderMain', function (event) {
            showLoader();
                setTimeout(function(){
                     html2canvas($('.gm-style>div:eq(0)')[0],{
                            useCORS: true,
                            async:false,
                        }).then(function(canvas){
                        // $("#mapPrint").append(canvas);
                         var dataURL = canvas.toDataURL();
                        $("#mapPrint").append("<input type='hidden' crossorigin='anonymous' name='map_data_url' value='"+dataURL+"'>");
                        // $("#mapPrint").append("<img src="+dataURL+" id='img_map_id'>");
                        hideLoader();

                        LoadPaymentGateway();
                    });
                 }, 2000);
        });


        $('body').on('click','.custom_pagination ul li .page-link',function(e){
            e.preventDefault();
            var elem        =   $(this);
            var link        =   elem.attr('href');
            var type        =   $('#tab_type').val();
            var url         =   "{{route('dispatcher.create')}}";
            var split_data  =   link.split('?page=');
            if(split_data.length>1) {
                var page = Number(split_data[1]);
                var url  = url+'?page='+page;

            }
            loadPageData(type,url,elem);
        });

        $('body').on('change','input[name="gateway"]',function(){
            var elem    =   $(this);
            const price = elem.attr('data-price');
            const order_id = elem.attr('order');
            showLoader();
            if(elem.val() ==1){
                $('.PaymentButtons .CashOnDelivery').show();
                $('.PaymentButtons .Paypal').hide().html('');
                hideLoader();
            }
            if(elem.val() ==2){
                $('.PaymentButtons .CashOnDelivery').hide();
                $('.PaymentButtons .Paypal').show();
                LoadPaypalButtons(price,order_id,elem.val());
                hideLoader();
            }
        });

        $('body').on('click','.close_refresh',function(){
            var elem        =   $(this);
            $('#default_modal .modal-dialog').html('');
            $('#default_modal').modal('show');
            window.location.href = '{!! route('customer.bookings') !!}';
        });
        $('body').on('click','.PayAmount',function(){
            var elem    =   $(this);
            var gateway_id  =   $('input[name="gateway"]:checked').val();
            const price  =   $('input[name="gateway"]:checked').attr('data-price');
            const order_id  =   $('input[name="gateway"]:checked').attr('order');
            $.ajax({
                url         :   '{!! route('CreatePaymentTransaction') !!}',
                type        :   'post',
                data        :   {
                    _token :   '{!! csrf_token() !!}',
                    transaction_id : 'COD',
                    order_id:order_id,
                    response:'',
                    gateway_id:gateway_id,
                    price: price
                },
                beforeSend      :   function () {
                    $(".PaymentGateway .loader-layout").show();
                },
                success:    function(html){
                    $(".PaymentGateway .loader-layout").hide();
                    $('#default_modal .modal-dialog').html(html);
                    $('#default_modal').modal('show');
                    hideLoader();
                    $(".PaymentGateway .loader-layout").hide();
                }
            });
        });
    });



    function LoadPaymentGateway(){
        $.ajax({
            url     :   '{!! route('SelectPaymentGateway') !!}',
            type    :   'post',
            data    :   {
                _token      :    '{!! csrf_token() !!}',
            },
            beforeSend      :   function(){
                // showLoader();
            },
            success : function(html){
                $('#default_modal .modal-dialog').html(html).removeClass('modal-lg');
                $('#default_modal').modal('show');
                var currentGateway  =   $('input[name="gateway"]:checked').val();
                const price         =   $('input[name="gateway"]:checked').attr('data-price');
                if(currentGateway == 1){
                    $('.PaymentButtons .CashOnDelivery').show();
                    $('.PaymentButtons .Paypal').hide();
                }
                if(currentGateway == 2){
                    $('.PaymentButtons .CashOnDelivery').hide();
                    $('.PaymentButtons .Paypal').show();
                    LoadPaypalButtons(price,currentGateway);
                }

            }
        });
    }

    function LoadPaypalButtons(price,order_id,gateway_id){
        $('.PaymentButtons .Paypal').html('');
        paypal_sdk.Buttons({
            style: {
                color:  'gold',
                shape:  'rect',
                size: 'responsive',
                fundingicons: false,
            },
            env: '{!! env('PAYPAL_LIVE') !!}',
            client: {
                sandbox:    '{!! env('PAYPAL_SANDBOX_CLIENT_ID') !!}',
                production: '{!! env('PAYPAL_PRODUCTION_CLIENT_ID') !!}'
            },
            createOrder: function(data, actions) {
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: price
                        }
                    }]
                });
            },
            onApprove: function(data, actions) {
                return actions.order.capture().then(function(details) {
                     $.ajax({
                         url: "{!! route('CreatePaymentTransaction') !!}",
                         type: 'post',
                         data:{
                             _token :   '{!! csrf_token() !!}',
                             transaction_id : details.id,
                             order_id:order_id,
                             response:JSON.stringify(details),
                             gateway_id:gateway_id,
                             price: price
                         },
                         success: function(html){
                             $(".PaymentGateway .loader-layout").hide();
                             $('#default_modal .modal-dialog').html(html);
                             $('#default_modal').modal('show');
                             hideLoader();
                             $(".PaymentGateway .loader-layout").hide();
                         }
                     });
                });
            },
            onClick: function(){
                $(".PaymentGateway .loader-layout").show();
            }
        }).render('.PaymentButtons .Paypal');
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

    function initMap() {

        markers = [];
        /*startPoint = 1;
        endPoint    = 0;*/
        // clearMarkers();
        var input_start_address = 'start_address';
        var input_pick_address = 'pick_address';
        var input_end_address = 'end_address';
        var input_mid_address = 'mid';


        var directionsService = new google.maps.DirectionsService();
        var directionsRenderer = new google.maps.DirectionsRenderer();
        map = new google.maps.Map(document.getElementById("map"), {
            clickableIcons: false,
            zoom: 6,
            center: {
                lat: 47.5162,
                lng: 14.5501
            }
        });
        directionsRenderer.setMap(map);

        var input_start_address_tag = document.getElementById(input_start_address);
        var input_start_address_autocomplete = new google.maps.places.Autocomplete(input_start_address_tag);
        input_start_address_autocomplete.bindTo("bounds", map);
        input_start_address_autocomplete.setComponentRestrictions(
            {'country': ['at']});
        input_start_address_autocomplete.setFields([
            "address_components",
            "geometry",
            "icon",
            "name"
        ]);

        // input_start_address_autocomplete.addListener("place_changed", function () {
        // });
        setTimeout(function(){
            var latlng =  new google.maps.LatLng(48.182499, 16.465839);//{ lat: 48.182499, lng: 16.465839 };
            //console.log(latlng);
            addMarker(latlng, 0);
            onChangeHandler();
            var elem = $(this);
            startPoint = 1;
            endPoint = 0;
          }, 3000);

        /* Mid */
        var input_pick_address_tag = document.getElementById(input_pick_address);
        var input_pick_address_autocomplete = new google.maps.places.Autocomplete(input_pick_address_tag);
        input_pick_address_autocomplete.bindTo("bounds", map);
        input_pick_address_autocomplete.setComponentRestrictions(
            {'country': ['at']});
        input_pick_address_autocomplete.setFields([
            "address_components",
            "geometry",
            "icon",
            "name"
        ]);

        
        input_pick_address_autocomplete.addListener("place_changed", function () {
            var place = input_pick_address_autocomplete.getPlace();
            var latlng = place.geometry.location;
            addMarker(latlng, 4);
            onChangeHandler(); 
        });


        /* End */
        var input_end_address_tag = document.getElementById(input_end_address);
        var input_end_address_autocomplete = new google.maps.places.Autocomplete(input_end_address_tag);
        input_end_address_autocomplete.bindTo("bounds", map);
        input_end_address_autocomplete.setComponentRestrictions(
            {'country': ['at']});
        input_end_address_autocomplete.setFields([
            "address_components",
            "geometry",
            "icon",
            "name"
        ]);
        $('body').on('focus', '#start_address', function () {
            var elem = $(this);
            startPoint = 1;
            endPoint = 0;
        });
        $('body').on('focus', '#end_address', function () {
            var elem = $(this);
            onChangeHandler();
            endPoint = 1;
            startPoint = 0;
        });

        input_end_address_autocomplete.addListener("place_changed", function () {
            var place = input_end_address_autocomplete.getPlace();
            var latlng = place.geometry.location;
            addMarker(latlng, 1);
            onChangeHandler();
            $('select[name="select_package"]').attr('disabled','disabled');
            //start

            
            //end
        });
        if ($("#polygons").length > 0) {
            var polygons_data = $("#polygons").html();
            polygons_data = JSON.parse(polygons_data);
            $.each(polygons_data, function (i, polygon) {
                polygon = JSON.parse(polygon);
                var poly = new google.maps.Polygon({
                    paths: polygon,
                    strokeColor: '#d91b1b',
                    district_id: Number(i),
                    strokeOpacity: 0.8,
                    strokeWeight: 1,
                    fillColor: '#e0a9a8',
                    fillOpacity: 0.35,
                    editable: false,
                    cursor: 'crosshair'
                });
                poly.setMap(map);
                /* polygon_d.addListener("mousedown", function (e) {
                     console.log(e)
                 });*/
                polygons.push(poly);
            });

        }
        var onChangeHandler = function () {
            showMarkers();
            calculateAndDisplayRoute(directionsService, directionsRenderer);
        };
        $(document).ready(function() {
                    var max_fields      = 5; //maximum input boxes allowed
                    var wrapper         = $(".input_fields_wrap"); //Fields wrapper
                    var add_button      = $(".add_field_button"); //Add button ID
                 
                    var x = 1; //initlal text box count
                    $(add_button).click(function(e){ //on add input button click
                        e.preventDefault();
                        if(x < max_fields){ //max input box allowed
                            x++; //text box increment
                            var i =increment();
                                $(wrapper).append('<div><label>Drop Address</label><i class="remove_field">  <i class="fas fa-window-close"></i></i><input type="text" name="mid[]" id="mid'+i+'" class="form-control" /></div>');
                            var input_mid_address_tag = document.getElementById('mid'+i);
                            var input_mid_address_autocomplete = new google.maps.places.Autocomplete(input_mid_address_tag);
                            input_mid_address_autocomplete.bindTo("bounds", map);
                            input_mid_address_autocomplete.setComponentRestrictions(
                                {'country': ['at']});
                            input_mid_address_autocomplete.setFields([
                                "address_components",
                                "geometry",
                                "icon",
                                "name"
                            ]);
                            input_mid_address_autocomplete.addListener("place_changed", function () {
                                var place = input_mid_address_autocomplete.getPlace();
                                var latlng = place.geometry.location;
                                // clearMarkers();
                                addMarker(latlng, 3);
                                onChangeHandler();
                            });
                        }else{
                            clearMarkers();
                        }
                });
                    $(wrapper).on("click",".remove_field", function(e){ //user click on remove text
                        e.preventDefault(); $(this).parent('div').remove(); x--;
                    })
                });

        function SetStartLocation(latlng, district_id) {
            var geocoder = new google.maps.Geocoder();
            geocoder.geocode({location: latlng}, function (results, status) {
                if (status === "OK") {

                    if (results[0]) {
                        $("#start_address").val(
                            results[0].formatted_address
                        ).attr('district_id', district_id);
                        hideLoader();
                        onChangeHandler();
                        endPoint = 1;
                        startPoint = 0;
                    }
                }
            });
        }

        function SetEndLocation(latlng, district_id) {
            var geocoder = new google.maps.Geocoder();
            geocoder.geocode({location: latlng}, function (results, status) {
                if (status === "OK") {
                    var i = increment();
                    if (results[0]) {
                        $("#end_address").val(results[0].formatted_address).attr('district_id', district_id); 
                            input = $('<div class="form-group col-lg-12" > <label>Drop Address</label> <input type="text" name="mid[]" id="mid'+i+'" class="form-control" value="'+results[0].formatted_address+'" district_id="'+district_id +'"> </div>');
                            jQuery('#div').append(input);                            
                        hideLoader();
                        onChangeHandler();
                    }
                }
            });
        }
    }

    function addMarker(location, Index) {
        var icon = '{!! asset('images/start.png') !!}';
        if (Index == 1) {
            icon = '{!! asset('images/end.png') !!}';
        }
        var district_id     =   0;
        showLoader();
        $.each(polygons,function(i,polygon){
            var isInside =  google.maps.geometry.poly.containsLocation(location,polygon);
            if(isInside){
                district_id =  polygon.district_id;
            }
        });
        console.log(district_id,"DISTRICT ID");
        // clearMarkers();
        if(district_id != 0){
            var marker = new google.maps.Marker({
                position: location,
                map: map,
                icon: icon,
                draggable: false
            });
            var curpoint = new google.maps.LatLng(
                location.lat(),
                location.lng()
            );
            map.setCenter(curpoint);
            marker.setPosition(curpoint);
            if(Index == 0){
                $('input[name="pick_location"]').attr('district_id',district_id);
            }
            else if(Index == 1){
                $('input[name="end_location"]').attr('district_id',district_id);
            }
            else if(Index == 3){
                Index=2;
                // if($('input[name="mid[]"]').attr('district_id')!=1 || $('input[name="mid[]"]').attr('district_id')!=2  ){
                    mid_district++;
                    $('input[name="mid[]"]').eq(mid_district).attr('district_id',district_id);
                // }

            }else if(Index == 4){
                Index=2;
                $('#pick_address').attr('district_id',district_id);
            }
            markers[Index] = marker;
            hideLoader();
        }
        else
        {
            if(Index == 0){
                $('input[name="pick_location"]').val('').attr('district_id',district_id);
            }
            else if(Index == 1){
                $('input[name="end_location"]').val('').attr('district_id',district_id);
            }
            toast.error("This address is outside our service area (Vienna & Lower Austria)");
            hideLoader();
        }
    }

    async function getDistrictForAPoint(location){
        $.each(polygons,function(i,polygon){
            var isInside =  google.maps.geometry.poly.containsLocation(location,polygon);
            if(isInside){
                return polygon.district_id;
            }
        });
        return 0;
    }
    function setMapOnAll(map) {
        // markers is sparse (slots 0/1/2 are assigned independently), so skip holes
        for (let i = 0; i < markers.length; i++) {
            if (markers[i]) markers[i].setMap(map);
        }
    }

    function clearMarkers() {
        setMapOnAll(null);

    }
    function showMarkers() {
        setMapOnAll(map);
    }


    function calculateAndDisplayRoute(directionsService, directionsRenderer) {
        var icons = {
            start: new google.maps.MarkerImage(
                '{!! asset('images/start.png') !!}',
                new google.maps.Size( 44, 32 ),
                new google.maps.Point( 0, 0 ),
                new google.maps.Point( 22, 32 )
            ),
            end: new google.maps.MarkerImage( 
                '{!! asset('images/end.png') !!}',
                new google.maps.Size( 44, 32 ),
                new google.maps.Point( 0, 0 ),
                new google.maps.Point( 22, 32 )
            )
        };
        const waypts = [];

        const checkboxArray  = $("input[name='mid[]']").map(function(){return $(this).val();}).get();
          for (let i = 0; i < checkboxArray.length; i++) {
              waypts.push({
                location: checkboxArray[i],
                stopover: true
              });
          }


        const unit_price = $('input[name="unit_price"]').val();
        // The create form is loaded/replaced via ajax; a stale autocomplete
        // listener can fire after the inputs are gone, so guard for null.
        const startAddressEl = document.getElementById("start_address");
        const endAddressEl = document.getElementById("end_address");
        if (startAddressEl && endAddressEl && startAddressEl.value != '' && endAddressEl.value != '') {
            showLoader();
            directionsService.route(
                {
                    origin: {
                        query: document.getElementById("start_address").value
                    },
                    destination: {
                        query: document.getElementById("end_address").value
                    },
                    waypoints: waypts,
                    optimizeWaypoints: true,
                    travelMode: google.maps.TravelMode.DRIVING
                },
                function (response, status) {
                    // console.log(response);
                    var myencodestring = [];
                    var count = 0 ;
                    for(var i = 0; i < response.routes[0].legs.length; i++)
                    {
                        //console.log(response.routes[0].legs[i].steps.length);
                    
                        for( var j=0; j < response.routes[0].legs[i].steps.length; j++)
                        {
                            myencodestring[count]= response.routes[0].legs[i].steps[j].encoded_lat_lngs;
                            count ++;
                            
                        }
                    }
                    var myArr = myencodestring;
                    myArrString = JSON.stringify(myArr);
                   // console.log([myencodestring]);
                    // console.log(response);
                   // console.log(response);
                   // console.log(response);
                      /*var encoding_string = response.routes[0].overview_polyline;

                       var regExpr = /[|]/g;
                        var userText = encoding_string;
                        var encodeString1 = userText.replace(regExpr, '","');*/
                        var encodeString = myArrString; 
                        //console.log(encodeString);
                        //alert(encodeString);

                    if (status === "OK") {
                        // const mid_length=$("input[name='mid[]']").length +1;
                        var start_address_district_id = $('#start_address').attr('district_id');
                        var end_address_district_id = $('#end_address').attr('district_id');
                        var district_ids = $("input[name='mid[]']").map(function(){return $(this).attr('district_id');}).get();
                        // var found = district_ids.find(element => element = '2');
                        var found =district_ids.includes('1');
                        var distance =0;
                        var total_time_sec=0.0;
                        $('input[name="single_distance[]"]').remove();
                        $('input[name="single_time[]"]').remove();
                        $('input[name="district_ids[]"]').remove();
                        for(var total_legs=0; total_legs <= response.routes[0].legs.length-1 ; total_legs++){
                            jQuery('.hiddenVal').append('<input type="hidden" name="single_distance[]" class="form-control" value="'+response.routes[0].legs[total_legs].distance.value+'">');
                            jQuery('.hiddenVal').append('<input type="hidden" name="single_time[]" class="form-control" value="'+response.routes[0].legs[total_legs].duration.value+'">'); 
                            distance += response.routes[0].legs[total_legs].distance.value;
                            total_time_sec += response.routes[0].legs[total_legs].duration.value; 
                        }
                        for (let i = 0; i < district_ids.length; i++) {
                            jQuery('.hiddenVal').append('<input type="hidden" name="district_ids[]" class="form-control" value="'+district_ids[i]+'">');
                        }
                        var km = distance / 1000;
                        var duration=  new Date(total_time_sec * 1000).toISOString().substr(11, 8);
                        $('input[name="total_amount"]').val(0);
                        
                        var end =$("#end").val();
                        var totalHelper=$("#total_helper").val();
                        var fee=$("input[name=fee]").val();
                        var helper=fee*totalHelper*end

                        if (start_address_district_id == '1' || end_address_district_id == '1') 
                        {
                            //Austria
                                const mid_length=$("input[name='mid[]']").length+1;
                                console.log('d2');
                                var total_per_km_price = $('input[name="per_km_charges"]').val();
                                var total_price =  ((total_per_km_price/1000) * distance);
                                total_price = total_price.toFixed(2)*2;
                                if($("#total_helper").val() && $("#end").val()){
                                    $('input[name="total_amount"]').val(parseInt(total_price)+parseInt(helper));
                                    $('input[name="total_amount_fee"]').val(total_price);
                                }else{
                                    $('input[name="total_amount"]').val(total_price);
                                    $('input[name="total_amount_fee"]').val(total_price);
                                }

                        }else if(start_address_district_id == '2' && end_address_district_id == '2'){
                            //viena
                                console.log('d1');
                                console.log(found);
                                console.log(district_ids.find(element => element = '2'));
                                if((typeof district_ids.find(element => element = '2') == 'undefined')){
                                    const mid_length=$("input[name='mid[]']").length;
                                    if($("#total_helper").val() && $("#end").val()){
                                        $('input[name="total_amount_fee"]').val(unit_price*mid_length);
                                        $('input[name="total_amount"]').val(parseInt(unit_price*mid_length)+parseInt(helper));
                                    }
                                    else{
                                        $('input[name="total_amount_fee"]').val(unit_price*mid_length);
                                        $('input[name="total_amount"]').val(unit_price*mid_length);
                                    }

                                }else if(found == true) {
                                    var total_per_km_price = $('input[name="per_km_charges"]').val();
                                    var total_price =  ((total_per_km_price/1000) * distance);
                                    total_price = total_price.toFixed(2)*2;

                                    if($("#total_helper").val() && $("#end").val()){
                                        $('input[name="total_amount_fee"]').val(total_price);
                                        $('input[name="total_amount"]').val(parseInt(total_price)+parseInt(helper));
                                    }else{
                                        $('input[name="total_amount"]').val(total_price);
                                        $('input[name="total_amount_fee"]').val(total_price);
                                    }


                                }else if(found == false){
                                    const mid_length=$("input[name='mid[]']").length;
                                    if($("#total_helper").val() && $("#end").val()){
                                        $('input[name="total_amount_fee"]').val(unit_price*mid_length);
                                        $('input[name="total_amount"]').val(parseInt(unit_price*mid_length)+parseInt(helper));
                                    }
                                    else{
                                        $('input[name="total_amount_fee"]').val(unit_price*mid_length);
                                        $('input[name="total_amount"]').val(unit_price*mid_length);
                                    }
                                }
                        }
                        $('input[name="helperPayment"]').val(helper);
                        $('input[name="total_km"]').val(km.toFixed(2)+" km");
                        $('input[name="total_km_meter"]').val(distance);
                        $('input[name="total_time"]').val(duration);
                        $('input[name="total_time_sec"]').val(total_time_sec);
                        $('input[name="start_address_district_id"]').val(start_address_district_id);
                        $('input[name="end_address_district_id"]').val(end_address_district_id);
                        $('input[name="encodeString"]').val(encodeString);
                        directionsRenderer.setDirections(response);
                        var leg = response.routes[ 0 ].legs[ 0 ];
                        toast.success("Calculated distances successfully.");
                        hideLoader();
                    } else {
                        toast.error("Drop Off Location must not be null ");
                        hideLoader();
                    }
                }
            );
        }
    }
    function makeMarker( position, icon, title ) {
        new google.maps.Marker({
            position: position,
            map: map,
            icon: icon,
            title: title,
            draggable: false
        });
    }


    /*exports.calculateAndDisplayRoute = calculateAndDisplayRoute;
    exports.initMap = initMap;
    exports.clearMarkers = clearMarkers;
})((this.window = this.window || {}));*/
var n = 0;
function increment(){
  n++;
  if(n<5){
    return n;
  }else{    
    location.reload();
  }
}

function takeshot() { 
    let div = 
        document.getElementById('meep'); 
    html2canvas(div).then( 
        function (canvas) { 
            document 
            .getElementById('output') 
            .appendChild(canvas); 
        }) 
    $("#output").css('border','1px solid black')
    var list = document.getElementsByTagName("canvas")[0];
    // list.getContext("2d");
    list = trimCanvas(list);


    var dataURL = list.toDataURL();
} 
  // MIT http://rem.mit-license.org
function trimCanvas(c) {
    var ctx = c.getContext('2d'),
        copy = document.createElement('canvas').getContext('2d'),
        pixels = ctx.getImageData(0, 0, c.width, c.height),
        l = pixels.data.length,
        i,
        bound = {
            top: null,
            left: null,
            right: null,
            bottom: null
        },
        x, y;
    
    // Iterate over every pixel to find the highest
    // and where it ends on every axis ()
    for (i = 0; i < l; i += 4) {
        if (pixels.data[i + 3] !== 0) {
            x = (i / 4) % c.width;
            y = ~~((i / 4) / c.width);

            if (bound.top === null) {
                bound.top = y;
            }

            if (bound.left === null) {
                bound.left = x;
            } else if (x < bound.left) {
                bound.left = x;
            }

            if (bound.right === null) {
                bound.right = x;
            } else if (bound.right < x) {
                bound.right = x;
            }

            if (bound.bottom === null) {
                bound.bottom = y;
            } else if (bound.bottom < y) {
                bound.bottom = y;
            }
        }
    }
    
    // Calculate the height and width of the content
    var trimHeight = bound.bottom - bound.top,
        trimWidth = bound.right - bound.left,
        trimmed = ctx.getImageData(bound.left, bound.top, trimWidth, trimHeight);

    copy.canvas.width = trimWidth;
    copy.canvas.height = trimHeight;
    copy.putImageData(trimmed, 0, 0);

    // Return trimmed canvas
    return copy.canvas;
}
</script>
