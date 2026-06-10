@php
    $firstName = $row->rider_first_name ?? ($row->rider_name ?? '');
    $lastName = $row->rider_last_name ?? '';
    $fullName = trim($firstName . ' ' . $lastName);
@endphp
@if($fullName)
    <div style="font-weight: 500; color: var(--ct-gray-900);">{{ $fullName }}</div>
@else
    <span style="color: var(--ct-gray-400);">No Rider Assigned</span>
@endif

