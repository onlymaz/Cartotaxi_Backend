<ul class="action-list">
    <li><a href="{{ route('users.show', $row->id) }}" class="btn btn-view" title="View profile & order history"><i class="fal fa-eye"></i></a></li>
    <li><a href="javascript:void(0);" class="btn btn-edit popup" data-url="{{route('users.edit',$row->id)}}"><i class="fal fa-edit" ></i></a></li>
    {{--<li><a href="javascript:void(0);" class="btn btn-delete popup" data-url="{{route('users.delete_modal',$row->id)}}" data-type="small"><i class="fal fa-trash-alt"></i></a></li>--}}

</ul>
