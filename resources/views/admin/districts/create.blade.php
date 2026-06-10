<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title">{{__('messages.add_polygons')}}</h5>
        <button type="button" class="close" data-dismiss="modal">
            <i class="fal fa-close"></i>
        </button>
    </div>
    <div class="modal-body">
        <form class="form edit_user" method="POST" action="{!! route('districts.store') !!}">
            <input type="hidden" name="_token" value="{!! csrf_token() !!}">
            <div class="row">
                <div class="form-group col-lg-12">
                    <label>{{__('messages.district_name')}}</label>
                    <input readonly type="text" name="district_name" class="form-control" placeholder="District Name" value="{!! $district->district_name !!}">
                    <input type="hidden" name="polygons" class="form-control" value='{!! $district->polygons !!}'>
                    <input type="hidden" name="district_id" class="form-control" value="{!! $district->id !!}">
                </div>
                <div class="position-relative col-lg-12">
                    <div style="display:none;" id="color-palette"></div>
                    <a class="btn close position-absolute disabled" style="top:10px;right:10px;z-index:9999;opacity:1" href="javascript:void(0);" id="delete-button">
                        <i class="fal fa-close"></i>
                    </a>
                    <div style="width: 100%;height: 450px;" id="map"></div>
                </div>
                <div class="form-group col-lg-12 text-right mt-3">
                    <button data-dismiss="modal" class="btn btn-danger">{{__('messages.cancel')}}</button>
                    <button type="button" class="btn btn-success disabled buttonSubmitDistrict">{{__('messages.update')}}</button>
                </div>
            </div>
        </form>
    </div>
</div>
