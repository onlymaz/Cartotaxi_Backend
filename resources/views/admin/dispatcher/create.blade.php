<div id="output"></div> 
<div class="row">
    <div class="col-lg-5">
        @if(Session::has('message'))
                    <div class="alert alert-success">
                        {{session('message')}}
                    </div>
                @endif
        @if (auth()->user()->role_id == 1)
        <h2>{{__('messages.book_a_order')}}</h2>
        @endif
        <div class="clearfix pt-3 pb-3 mb-4">
            <form class="form order_form" method="post" action="{!! route('dispatcher.store') !!}">
                @csrf
                <div class="row">
                    @if (auth()->user()->role_id == 1)
                        <div class="form-group col-lg-6">
                            <label>{{__('messages.select_a_customer')}}</label>
                            <input type="text" name="select_customer" class="form-control" placeholder="{{__('messages.enter_a_customer')}}">
                            <input type="hidden" name="customer_id" class="form-control">
                        </div>
                        <div class="form-group col-lg-6">
                            <label>{{__('messages.select_a_package')}}</label>
                            <select name="select_package" class="form-control custom-select">
                                <option value="">{{__('messages.select_a_package')}}</option>
                                @foreach($packages as $package)
                                    <option per_km_charges="{!! $package->per_km_charges !!}" value="{!! $package->id !!}">{!! $package->name !!}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <div class="form-group col-lg-12">
                            <label>{{__('messages.select_a_package')}}</label>
                            <select name="select_package" class="form-control custom-select">
                                <option value="">{{__('messages.select_a_package')}}</option>
                                @foreach($packages as $package)
                                    <option per_km_charges="{!! $package->per_km_charges !!}" value="{!! $package->id !!}">{!! $package->name !!}</option>
                                @endforeach
                            </select>
                            <input type="hidden" name="customer_id" class="form-control" value="{{auth()->user()->id}}">
                        </div>
                    @endif

                    <div class="form-group col-lg-12">
                        <label>{{__('messages.description')}}</label>
                        <textarea row="4" name="description" placeholder="{{__('messages.short_description')}}"></textarea>
                    </div>
                        
                    <div class="form-group col-lg-12">
                        <ul class="nav nav-tabs">

                          <li class="nav-item col-lg-6">
                            <a class="nav-link pickschedual"  id="ScheduleBooking">Schedule Booking</a>
                          </li>
                          <li class="nav-item col-lg-6">
                            <a class="nav-link pickschedual"  id="PickupNow">Pickup Now</a>
                          </li>
                        </ul>
                    </div>          
                    <div class="col-lg-12 row schedulePick">
                       <div class="col-lg-12 form-group">
                            <div class="row">
                                <div class="custom-radio col-lg-6">
                                  <input type="radio" class="form-control-input" value="Without" id="helperwithout" name="helper" checked>
                                  <label class="form-control-label" for="helperwithout">Without Helper</label>
                                </div>
                                <div class="custom-radio col-lg-6">
                                  <input type="radio" class="form-control-input" id="helperwith" value="With" name="helper">
                                  <label class="form-control-label" for="helperwith">With Helper</label>
                                </div>
                            </div>
                       </div>
                        <div class="form-group col-lg-12 without">
                            <label>{{__('messages.schedule_date')}}</label>
                            <input type="date" value="{!! date('Y-m-d') !!}" name="picked_date" class="form-control" placeholder="{{__('messages.schedule_date')}}">
                        </div>
                        <div class="form-group col-lg-12 without">
                            <label>{{__('messages.schedule_time')}}</label>
                            <input type="time" value="{!! date('H:i:s') !!}" name="picked_time" class="form-control" placeholder="{{__('messages.schedule_time')}}">
                        </div>          
                        <div class="form-group col-lg-12 with d-none">
                            <label>Total Helpers</label>
                            <input type="number" name="total_helper" id="total_helper" 
                            oninput="this.value = Math.round(this.value);" 
                            class="form-control" placeholder="Enter Total Helpers" min="1">
                        </div>
                        <div class="form-group col-lg-12 with d-none">
                            <div class="form-group col-lg-12">

                                <label>Date & Time</label>
                                <input type="date" name="start" 
                                    min="{!! Date('Y-m-d',strtotime('+2 days')) !!}" 
                                    value="{!! Date('Y-m-d',strtotime('+2 days')) !!}" 
                                    id="start" onchange="onChangeAjax(this.value)" class="form-control" placeholder="Enter Start Time"
                                >

                            </div>
                            <div class="form-group col-lg-12">
                                <label>Time</label>
                                <select name="times" class="form-control" onchange="getTimvalue(this.value)" id="ajaxHours">
                                    <option selected="" disabled="">Select Time</option>
                                    @php $hours=App\Models\Helper::getOptionsTimes('h:i') @endphp
                                    @foreach($hours as $hour)
                                        <option>{{date("H:i", $hour)}} </option>
                                    @endforeach
                                </select>
                            </div>
                            <input type="hidden" name="time" value="">
                            <div class="form-group col-lg-12">
                                <label>Hours</label>
                                <input type="number" name="end" id="end" class="form-control" 
                                oninput="this.value = Math.round(this.value);"
                                 placeholder="Enter End Time" value="" min="1" >
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-lg-12">
                        <label>{{__('messages.cargo_taxi_stand')}}</label>
                        <input type="text" name="pick_location" id="start_address" class="form-control"
                               placeholder="{{__('messages.cargo_taxi_stand')}}" value="Freudenauer Hafenstraße 8-10, Vienna, Austria" readonly=""  district_id='2'>
                    </div>
                    <div class="form-group col-lg-12">
                        <label>{{__('messages.pickup_address')}}</label>
                        <input type="text" name="mid[]" id="pick_address" class="form-control pac-target-input" autocomplete="off"
                               placeholder="{{__('messages.enter_pickup_address')}}" >
                    </div>
                    <div id="div" class="form-group col-lg-12">
                        <div class="input_fields_wrap form-group col-lg-12">
                            <div><a class="add_field_button pointer">+ {{__('messages.add_drop_address')}}</a></div>
                        </div>
                    </div>
                    <div class="form-group col-lg-12">
                        <label>{{__('messages.dropoff_end_address')}}</label>
                        <input type="text" name="end_location" id="end_address" class="form-control"
                               placeholder="{{__('messages.enter_dropoff_end_address')}}">
                    </div>
                    <div class="form-group col-lg-12">
                        <input  type="hidden" name="encodeString" class="form-control">
                    </div>
                    <div class="form-group col-lg-4">
                        <label>{{__('messages.est_amount')}} <b>{!! $currency !!}</b></label>
                        <input type="hidden" name="unit_price" class="form-control" value="{!! $price !!}">
                        <input readonly type="text" name="total_amount" class="form-control" value="0">
                    </div>
                    <div class="form-group col-lg-4">
                        <label>{{__('messages.distance')}}</label>
                        <input readonly type="text" name="total_km" class="form-control" value="0 km">
                        <input type="hidden" name="total_km_meter" class="form-control" value="">
                    </div>
                    <div class="form-group col-lg-4 hiddenVal">
                        <label>{{__('messages.total_est_time')}}</label>
                        <input readonly type="text" name="total_time" class="form-control" value="0 mins">
                        <input type="hidden" name="total_time_sec" class="form-control" value="">
                        <input type="hidden" name="per_km_charges" class="form-control" value="">
                        <input type="hidden" name="start_address_district_id" class="form-control" value="">
                        <input type="hidden" name="end_address_district_id" class="form-control" value="">
                        <input type="hidden" name="fee" value="@php echo App\Models\HelperFee::first()->fee @endphp">
                        <div id="mapPrint"></div>
                    </div>
                    <div class="form-group col-lg-4">
                    </div>
                    <div class="form-group col-lg-4">
                    </div>
                    <div class="form-group col-lg-4">
                        <label>My Booking</label>
                        <input type="number" readonly="" name="total_amount_fee" placeholder="Booking Fee" class="form-control">
                        <label>Helper</label>
                        <input type="number" readonly="" name="helperPayment" placeholder="Helper Fee" class="form-control">
                    </div>

                    <div class="form-group col-lg-12 text-right mt-3">
                        <a href="javascript:void(0)" class="btn btn-danger refresh-btn" onClick="window.location.reload();return false;" type="add">{{__('messages.cancel')}}</a>
<!--                         <button type="button" style="display: none" class="btn btn-success SaveOrder">Next</button>
 -->                        <a type="button" href="#map" class="btn btn-success SaveOrderMain">{{__('messages.next')}}</a>

                    </div>
                </div>
                <textarea style="display: none;" id="polygons">{!! json_encode($polygons) !!}</textarea>
            </form>
        </div>
    </div>
    <div class="col-lg-7" id="meep">
        <div class="map-box" >
            <div id="map" style="height: 500px">
            </div>
        </div>
    </div>
<script type="text/javascript">
            $(document).ready(function () {

            $( ".pickschedual" ).click(function() {
                if($(this).attr('id')=="ScheduleBooking"){  
                    $(".schedulePick").attr('class','d-block schedulePick col-lg-12 ');  
                }                
                if($(this).attr('id')=="PickupNow"){    
                    console.log($(this).attr('id'));
                    $(".schedulePick").attr('class','d-none schedulePick col-lg-12 ');  
                }                
            });
            $( "[name='helper']" ).click(function() {
                if($(this).attr('id')=="helperwithout"){    
                    $(".without").attr('class','d-block without form-group col-lg-12')
                    $(".with").attr('class','d-none with form-group col-lg-12')

                }else if($(this).attr('id')=="helperwith"){
                    $(".without").attr('class','d-none without form-group col-lg-12')
                    $(".with").attr('class','d-block with form-group col-lg-12')
                }

            });

        });

</script>