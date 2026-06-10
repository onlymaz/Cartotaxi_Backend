@php
    $dateTime = $row->created_at ?? ($row->picked_time ?? now());
    $carbonDate = new \Carbon\Carbon($dateTime);
@endphp
<div style="display: flex; flex-direction: column; gap: 0.25rem;">
    <div style="font-weight: 500; color: var(--ct-gray-900);">{{ $carbonDate->format('M d, Y') }}</div>
    <div style="font-size: 0.75rem; color: var(--ct-gray-500);">{{ $carbonDate->format('h:i A') }}</div>
    <div style="font-size: 0.7rem; color: var(--ct-gray-400);">{{ $carbonDate->diffForHumans() }}</div>
</div>

