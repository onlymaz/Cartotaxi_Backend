@php
    $status = $row->order_status ?? 'pending';
    $statusConfig = [
        'pending' => ['label' => 'Pending', 'class' => 'status-pending', 'icon' => 'fa-clock'],
        'processing' => ['label' => 'Processing', 'class' => 'status-processing', 'icon' => 'fa-cog'],
        'picking' => ['label' => 'Picking', 'class' => 'status-picking', 'icon' => 'fa-box'],
        'picked_up' => ['label' => 'Picked Up', 'class' => 'status-on_way', 'icon' => 'fa-check-circle'],
        'on_way' => ['label' => 'On The Way', 'class' => 'status-on_way', 'icon' => 'fa-truck'],
        'delivered' => ['label' => 'Delivered', 'class' => 'status-delivered', 'icon' => 'fa-check-circle'],
        'cancel' => ['label' => 'Cancelled', 'class' => 'status-cancel', 'icon' => 'fa-times-circle'],
        'accident' => ['label' => 'Accident', 'class' => 'status-cancel', 'icon' => 'fa-exclamation-triangle'],
        'not_received' => ['label' => 'Not Received', 'class' => 'status-cancel', 'icon' => 'fa-times'],
        'refused' => ['label' => 'Refused', 'class' => 'status-cancel', 'icon' => 'fa-ban'],
    ];
    $config = $statusConfig[$status] ?? ['label' => ucfirst($status), 'class' => 'status-pending', 'icon' => 'fa-circle'];
@endphp
<span class="status-badge {{ $config['class'] }}" style="display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.375rem 0.75rem; font-size: 0.75rem; font-weight: 600; border-radius: 9999px;">
    <i class="fas {{ $config['icon'] }}"></i>
    {{ $config['label'] }}
</span>

