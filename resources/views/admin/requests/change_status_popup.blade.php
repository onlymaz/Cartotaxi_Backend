<div style="padding: 1.5rem;">
    <form class="ajax-form-update change_status_form" method="POST" action="{{route('bookings.change_status', $order->id)}}" enctype="multipart/form-data">
            @csrf
        <input type="hidden" name="_method" value="PATCH">
        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: var(--ct-gray-700);">Change Status</label>
            <select name="order_status" class="nova-select" style="width: 100%; height: 42px; padding: 0 1rem; border: 1px solid var(--ct-gray-300); border-radius: 0.5rem; font-size: 0.875rem; background: white;">
                <option value="pending" {{ $order->order_status=='pending' ? 'selected' : '' }}>Pending</option>
                <option value="processing" {{ $order->order_status=='processing' ? 'selected' : '' }}>Processing</option>
                <option value="picking" {{ $order->order_status=='picking' ? 'selected' : '' }}>Picking</option>
                <option value="picked_up" {{ $order->order_status=='picked_up' ? 'selected' : '' }}>Picked Up</option>
                <option value="on_way" {{ $order->order_status=='on_way' ? 'selected' : '' }}>On The Way</option>
                <option value="accident" {{ $order->order_status=='accident' ? 'selected' : '' }}>Accident</option>
                <option value="not_received" {{ $order->order_status=='not_received' ? 'selected' : '' }}>Not Received</option>
                <option value="refused" {{ $order->order_status=='refused' ? 'selected' : '' }}>Refused</option>
                <option value="delivered" {{ $order->order_status=='delivered' ? 'selected' : '' }}>Delivered</option>
                <option value="cancel" {{ $order->order_status=='cancel' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
        <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
            <button type="button" onclick="if(typeof closeModal === 'function') closeModal();" class="nova-btn nova-btn-secondary" style="padding: 0.625rem 1.5rem;">Cancel</button>
            <button type="submit" class="nova-btn nova-btn-primary" style="padding: 0.625rem 1.5rem; background: var(--ct-accent); color: var(--ct-primary);">Save Changes</button>
            </div>
        </form>
</div>
