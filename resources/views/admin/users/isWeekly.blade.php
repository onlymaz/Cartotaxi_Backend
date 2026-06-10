<div class="custom-control custom-switch">

@if($row->role_id == 3)
    @if($row->isWeekly==1)
    	Weekly Customer
    @else
    	Regular Customer
    @endif
@endif
</div>
