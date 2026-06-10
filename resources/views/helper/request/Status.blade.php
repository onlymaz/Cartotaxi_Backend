@if($row->status=== 0)
	<span>Pending</span>
	@elseif($row->status === 1)
	<span>Approved</span>
	@else
	<span>invalid</span>
	@endif