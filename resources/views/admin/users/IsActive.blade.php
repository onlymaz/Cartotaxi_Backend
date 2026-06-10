<div class="custom-control custom-switch">
    <input type="checkbox" class="custom-control-input is_active" id="{{$row->id}}" @if($row->IsActive) checked data-value="0" @else data-value="1" @endif data-url="{{route('users.change_status')}}" data-id="{{$row->id}}">
    <label class="custom-control-label" for="{{$row->id}}"></label>
</div>
