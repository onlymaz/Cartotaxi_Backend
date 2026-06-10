@php
    $bookingId = $row->booking_id ?? '';
@endphp
@if($bookingId)
    <div style="font-weight: 600; color: var(--ct-primary); font-family: monospace;">#{{ $bookingId }}</div>
@else
    <span style="color: var(--ct-gray-400);">N/A</span>
@endif

