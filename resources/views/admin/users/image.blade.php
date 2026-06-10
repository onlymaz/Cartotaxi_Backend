@if($row->profile_image)
    <img src="{{url($row->profile_image)}}" class="user-img" alt="{{$row->first_name}}">
@else
    <img src="{{url('images/avatar.jpg')}}" alt="{{$row->first_name}}" class="user-img">
@endif

