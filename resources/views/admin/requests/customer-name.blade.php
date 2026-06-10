@php
    $firstName = $row->customer_first_name ?? ($row->first_name ?? '');
    $lastName = $row->customer_last_name ?? ($row->last_name ?? '');
    $fullName = trim($firstName . ' ' . $lastName);
@endphp
@if($fullName)
    <div style="font-weight: 500; color: var(--ct-gray-900);">{{ $fullName }}</div>
@else
    <span style="color: var(--ct-gray-400);">N/A</span>
@endif

