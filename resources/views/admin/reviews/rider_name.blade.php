<div class="">

    @if(empty($row->rider_name))
    good<!-- 
        <li><a row="{!! $row->id !!}" class="btn btn-view add_polygons" href="javascript:void(0);"><i class="fal fa-plus"></i></a></li> -->
    @else

    <label  style="pointer-events: none;"  for="rider_name" title="rider_name" >{{$row->rider_name}} {{$row->last_name}} </label>
    @endif
</div>
