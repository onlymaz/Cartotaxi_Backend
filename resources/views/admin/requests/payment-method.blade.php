@php
    $gatewayName = $row->gateway_name ?? '';
    $paymentMethod = '';
    $badgeClass = 'payment-method-badge';
    
    if ($gatewayName) {
        $paymentMethod = $gatewayName;
    } else {
        // Check payment status to determine method
        $paymentStatus = $row->payment_status ?? '';
        if ($paymentStatus == 'completed' || $paymentStatus == 'pending') {
            $paymentMethod = 'Cash on Delivery';
        } else {
            $paymentMethod = 'N/A';
        }
    }
@endphp
@if($paymentMethod && $paymentMethod != 'N/A')
    <span class="{{ $badgeClass }}" style="display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.375rem 0.75rem; font-size: 0.75rem; font-weight: 600; border-radius: 9999px; background: rgba(59, 130, 246, 0.1); color: #1d4ed8;">
        <i class="fas fa-credit-card"></i>
        {{ $paymentMethod }}
    </span>
@else
    <span style="color: var(--ct-gray-400);">N/A</span>
@endif

