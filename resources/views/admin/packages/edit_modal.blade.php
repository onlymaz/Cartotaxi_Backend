<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title">{{__('messages.update_package')}}</h5>
        <button type="button" class="close" data-dismiss="modal">
            <i class="fal fa-close"></i>
        </button>
    </div>
    <div class="modal-body">
        <form class="form ajax-form-update" method="POST" data-url="{{route('packages.update',$package->id)}}" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="form-group col-lg-12">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" placeholder="Enter Name" value="{{$package->name}}">
                </div>
                <div class="form-group col-lg-12">
                    <label>{{__('messages.weight')}}</label>
                    <input type="number" name="weight" class="form-control" placeholder="Enter Weight" value="{{$package->weight}}" step="0.1">
                </div>
                <div class="form-group col-lg-12">
                    <label>{{__('messages.unit')}}</label>
                    <input type="text" name="unit" class="form-control" placeholder="Enter Unit" value="{{$package->unit}}">
                </div>
                <div class="form-group col-lg-12">
                    <label>{{__('messages.fixed_price')}}</label>
                    <input type="number" name="fixed_price" class="form-control" placeholder="Enter Fixed Price" step="0.1" value="{{$package->fixed_price}}">
                </div>
                <div class="form-group col-lg-12">
                    <label>{{__('messages.per_km_charges')}}</label>
                    <input type="number" name="per_km_charges" class="form-control" placeholder="Enter Per KM Charges" value="{{$package->per_km_charges}}" step="0.001">
                </div>
                <div class="form-group col-lg-12 text-right mt-3">
                    <button data-dismiss="modal" class="btn btn-danger">{{__('messages.cancel')}}</button>
                    <button type="submit" class="btn btn-success">{{__('messages.update')}}</button>
                </div>
            </div>
        </form>
    </div>
</div>
