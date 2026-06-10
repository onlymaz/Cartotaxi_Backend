@extends('layouts.app')

@section('title')
    <title> {{__('messages.my_bookings')}} | {{ config('app.name', 'Laravel') }}</title>
@stop
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <div class="pull-left">
                        <h1>{{__('messages.my_bookings')}} </h1>
                    </div>
                    <div class="pull-right">
                        <button class="btn btn-success ico-btn  Reload pull-right"><i class="fal fa-refresh"></i>{{__('messages.reload')}}</button>
                    </div>
                </div>
                <div class="card-body">
                    <ul class="tabset mt-0 Content">
                        <li @if($action=='pickup') class="active" @endif>
                            <a href="{{route('rider.bookings',['action'=>'pickup'])}}" type="pickup">{{__('messages.pick_up_now')}}</a>
                        </li>
{{--                        <li @if($action=='schedule') class="active" @endif>
                            <a href="{{route('rider.bookings',['action'=>'schedule'])}}" type="schedule">{{__('messages.scheduled_delivery')}}</a>
                        </li>
--}}
                        <li @if($action=='history') class="active" @endif>
                            <a href="{{route('rider.bookings',['action'=>'history'])}}" type="history">{{__('messages.history')}}</a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div id="bodyContent" class="tab position-relative">
                            <div class="loader-layout" style="display: none;">
                                <div class="loading"><i class="fal fa-sync"></i>
                                    <p>Please wait...</p></div>
                            </div>
                            <div class="BodyContent">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="table-responsive">
                                            {!! $html->table(['class' => 'table table-condensed table-hover','id' =>'bookings'], true) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    {!! $html->scripts() !!}
    <script type="text/javascript">
        $(document).ready(function () {
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
                        if(type && type==='view'){
                            $("#default_modal .modal-dialog").removeClass('modal-lg');
                            $("#default_modal .modal-dialog").addClass('modal-xl');
                        }

                        $("#default_modal").modal("show");
                    }
                });
            });
            $('body').on('submit','.ajax-form-update',function(event) {
                event.preventDefault();
                var elem    =   $(this);
                var url     =   elem.attr('data-url');
                var data    =   $('.ajax-form-update')[0];
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
                            window.LaravelDataTables["bookings"].ajax.reload( null, false );
                            $('#default_modal').modal('toggle');
                            toast.success("Updated successfully");
                        }
                    },
                    error: function (response, exception) {
                        toast.error("Please resolve following errors");
                        var errors = JSON.parse(response.responseText).errors;
                        $.each(errors,function (name,error) {
                            $('.form-control[name="'+name+'"]').after('<span class="input-error '+name+'">'+error+'</span>');
                        });
                    },
                });
            });
        })
    </script>
@endsection
