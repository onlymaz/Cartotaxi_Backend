<div class="row" >
    <div class="col-lg-12">
        <h2>{{__('messages.delivered_list')}}</h2>
        <form data-action="delivered"  class="form row" action="" method="get">
            <div class="col-lg-2 pl-3">
                <label>{!! __('messages.booking_types') !!}</label>
                <select style="color: gray" name="filterFormHelper" class="formFilter custom-select">
                    <option  value="">Select Filter</option>
                    <option {!! ($statusFilter ==='all')?'selected':'' !!}  value="all">All</option>
                    <option {!! ($statusFilter ==='withouthelper')?'selected':'' !!} value="withouthelper">Without Helper</option>
                    <option {!! ($statusFilter ==='withhelper')?'selected':'' !!} value="withhelper">With Helper</option>
                </select>
            </div>
            <div class="col-lg-2">
                <label>{!! __('messages.payment_gatwayss') !!}</label>
                <?php $gateways =   \App\Models\Gateway::get();?>
                <select id="gateway" style="color: gray;" name="gateway" class="formFilter custom-select">
                    <option value="">Select a Gateway</option>
                    @foreach($gateways as $row)
                        <option {!! ($gateway ==$row->id)?'selected':'' !!} value="{!! $row->id !!}">{!! $row->name !!}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2">
                <label>{!! "Riders" !!}</label>
                <?php $riders =   \App\Models\User::where('role_id',2)->select('id','first_name','last_name')->get();?>
                <select id="rider_Id" style="color: gray;" name="rider_id" class="formFilter custom-select">
                    <option value="">Select a Rider</option>
                    @foreach($riders as $row)
                        <option {!! ((int)$rider_id == (int)$row->id) ? 'selected="selected"':'' !!} value="{!! $row->id !!}">{!! $row->frist_name." ".$row->last_name !!}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-1 pl-3">
                <label>{!! __('messages.order_number') !!}</label>
                <input name="order_number" value="{!! $order_number !!}" type="text" class="form-control" placeholder="{!! __('messages.order_number') !!}">
            </div>
            <div class="col-lg-1 pl-3">
                <label>{!! __('messages.customer_name') !!}</label>
                <input name="customer_name" type="text" value="{!! $customer_name !!}" class="form-control" placeholder="{!! __('messages.customer_name') !!}">
            </div>
            <div class="col-lg-3">
                <div class="input-group">
                    <label style="width: 100%;">{!! __('messages.order_date') !!}</label>
                    <input name="order_date" value="{!! $order_date !!}" type="text" class="form-control order_date" placeholder="{!! __('messages.order_date') !!}"/>
                    <div class="input-group-append">
                        <button type="button" class="btn btn-outline-secondary SearchBtn" type="button">Search</button>
                    </div>
                </div>
            </div>

        </form>
        <hr>
    </div>
    <div class="col-lg-12">
        <div class="scrollbars">
            @if(count($orders)>0)
                @foreach($orders as $key => $order)
                    <div class="clearfix viewLocations {!! ($key ==0)?'active':'' !!}">
                        <div class="clearfix">
                            <h5 class="mb-2 pull-left">{{$order->first_name.' '.$order->last_name}}</h5>
                            <div class=" pull-right">
                                <span class="badge text-white badge-info rider-details" >
                                    <input type="hidden" name="rider_detail" value="{{ $order->id }}">Rider Detail </span>
                                <span class="badge text-white badge-success " >
                                    {{__('messages.assigned')}}
                                </span>
                            </div>
                        </div>
                        <div class="clearfix">
                            <div class=" pull-right">
                                <span class="badge text-white badge-warning" >Order Id : {{ $order->id}}</span>
                            </div>
                        </div>

                        @php $OrderSubTrip=App\Models\OrderSubTrip::where('order_id',$order->id)->get(); @endphp
                        @foreach($OrderSubTrip as $ost)
                            <input type="hidden" class="slat{!! $ost->order_id !!}" name="start_lat[]" value="{{$ost->start_lat}}">
                            <input type="hidden" class="slng{!! $ost->order_id !!}" name="start_long[]" value="{{$ost->start_long}}">
                            <input type="hidden" class="lat{!! $ost->order_id !!}" name="end_lat[]" value="{{$ost->end_lat}}">
                            <input type="hidden"class="lng{!! $ost->order_id !!}" name="end_long[]" value="{{$ost->end_long}}">
                            <p class="mb-1"><strong>{{__('messages.from')}}:</strong> <span class="start_address">{{$ost->start_location}}</span>
                                <input type="hidden" class="pickup{!! $ost->order_id !!}" name="pickup_location" value="{{$ost->start_location}}">
                            </p>
                            <p class="mb-1"><strong>{{__('messages.to')}}:</strong> <span class="end_address">{{$ost->end_location}}</span>
                                <input type="hidden" class="dropoff{!! $ost->order_id !!}" name="dropoff" value="{{$ost->end_location}}">
                            </p>
                        @endforeach
                        @if($order->order_status == 'pending')
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
                        @endif
                        <div class="clearfix mt-3 flip">
                            <input type="hidden" name="polylines" class="mapinput" value="{{$order['map_image']}}">
                            <input type="hidden" name="orderid" class="mapinput" value="{{$order->id}}">
                            <div class="progress progress-small  mt-2 mb-1">
                                <div class="progress-bar progress-bar-warning" style="width:100%"></div>
                            </div>
                            <div class="pull-right">
                                <span><b> {{__('messages.amount')}} :</b>&euro;{!! $order['total_amount'] !!}</span>
                                <span><b> {{__('messages.distance')}} :</b>{!! number_format((($order['total_meter']/1000)),2) !!} km</span>
                                <button class="toggler pull-right  btn btn-success view_map_button delivered_class">
                                    <i class="fa fa-angle-down" aria-hidden="true"></i>
                                </button>
                            </div>
                            <div class="overflow-hidden">
                                <!-- <div class="flip-dropdown"> -->
                                <div class='toggled_content' style='display:none;'>
                                    <div class="map-box">
                                        <div class="show_map" style="height: 430px !important"></div>
                                    </div>
                                </div>
                                <span class="d-block font-12 hint-text overflow-hidden mt-2">
                                    {{--<a href="javascript:void(0)" class="badge badge-info">{{__('messages.view_locations')}}</a>--}}
                                    {{__('messages.manual_assignment')}}: {{date('Y-m-d H:i:s',strtotime($order['picked_time']))}}
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
                <input type="hidden" id="tab_type" value="delivered">
                <div class="custom_pagination">
                    {{$orders->links()}}
                </div>
            @else
                <div class="alert alert-danger">{!! __('messages.orders_not_founds') !!}</div>
            @endif
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
                <strong>Email : </strong ><span id="rider_email">54.</span><br>
                <strong>Phone Number : </strong ><span id="rider_no">3650.</span><br>
                <strong>Rider Car Number : </strong ><span id="car_number"></span><br>
                <strong>Recvied By : </strong ><span id="recived_by"></span><br>
                <strong>Booking Time : </strong ><span id="booking_time"></span><br>
                <strong>Delived Data-Time : </strong ><span id="delivery_time"></span><br>
                <strong>Sign : </strong ><img height="250" width="250" src="" id="sign"><br>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>
