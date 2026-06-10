<ul class="action-list">
    <li>
        <div class="custom-control custom-switch mr-1">
            <input type="checkbox" class="custom-control-input is_active" id="{{$row->id}}" @if($row->IsActive) checked data-value="0" @else data-value="1" @endif data-url="{{route('districts.change_status')}}" data-id="{{$row->id}}">
            <label class="custom-control-label" for="{{$row->id}}"></label>
        </div>
    </li>
    @if(empty($row->polygons))
        <li><a row="{!! $row->id !!}" class="btn btn-view add_polygons" href="javascript:void(0);"><i class="fal fa-plus"></i></a></li>
    @else
        <li><a row="{!! $row->id !!}" class="btn btn-edit add_polygons" href="javascript:void(0);"><i class="fal fa-edit"></i></a></li>
    @endif
</ul>
