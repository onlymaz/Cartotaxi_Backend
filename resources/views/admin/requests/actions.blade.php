@php
    // Try multiple ways to get the ID
    $orderId = null;
    if (isset($row->id)) {
        $orderId = $row->id;
    } elseif (isset($row['id'])) {
        $orderId = $row['id'];
    } elseif (is_object($row) && property_exists($row, 'id')) {
        $orderId = $row->id;
    } elseif (is_array($row) && isset($row['id'])) {
        $orderId = $row['id'];
    }
    
    // If still no ID, try to get from attributes (for Eloquent models)
    if (!$orderId && is_object($row) && method_exists($row, 'getAttribute')) {
        $orderId = $row->getAttribute('id');
    }
@endphp
@if($orderId && $orderId > 0)
@php
    try {
        $eyeShowUrl = route('bookings.show', $orderId);
    } catch (\Exception $e) {
        $eyeShowUrl = '/bookings/show/' . $orderId;
    }
@endphp
{{-- Direct view button: the detail popup (incl. Dispatch History) in one click --}}
<button type="button" class="popup" data-url="{{$eyeShowUrl}}" data-type="view" title="View detail & dispatch history" style="cursor: pointer; padding: 0.5rem; display: inline-flex; align-items: center; justify-content: center; border-radius: 0.375rem; transition: all 0.2s; border: none; background: transparent;" onmouseover="this.style.background='var(--ct-gray-100)'" onmouseout="this.style.background='transparent'">
    <i class="fas fa-eye" style="color: #3b82f6;"></i>
</button>
<div class="action-dropdown" style="position: relative; display: inline-block;">
    <button type="button" class="dropdown-toggle-btn" data-order-id="{{$orderId}}" style="cursor: pointer; padding: 0.5rem; display: inline-flex; align-items: center; justify-content: center; border-radius: 0.375rem; transition: all 0.2s; border: none; background: transparent;" onmouseover="this.style.background='var(--ct-gray-100)'" onmouseout="this.style.background='transparent'">
        <i class="fas fa-ellipsis-v" style="color: var(--ct-gray-600);"></i>
    </button>
    <div class="action-dropdown-menu" data-order-id="{{$orderId}}" style="display: none; position: absolute; right: 0; top: 100%; z-index: 1000; min-width: 200px; margin-top: 0.5rem; padding: 0.5rem; border-radius: 0.5rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1); background: white; border: 1px solid var(--ct-gray-200);">
        @php
            try {
                $showUrl = route('bookings.show', $orderId);
            } catch (\Exception $e) {
                $showUrl = '/bookings/show/' . $orderId;
            }
        @endphp
        <a href="javascript:void(0);" class="popup view-details-btn" data-url="{{$showUrl}}" data-type="view" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; color: var(--ct-gray-700); text-decoration: none; border-radius: 0.375rem; transition: all 0.2s; cursor: pointer;" onmouseover="this.style.background='var(--ct-gray-50)'; this.style.color='var(--ct-primary)'" onmouseout="this.style.background='transparent'; this.style.color='var(--ct-gray-700)'">
            <i class="fas fa-info-circle" style="color: #3b82f6; font-size: 1rem;"></i>
            <span>{{__('messages.view_detail', [], 'en') ?: 'View Detail'}}</span>
        </a>
    	@if(!isset($_GET['action']) || $_GET['action']!="schedule")    
		    @php 
		        $riderId = $row->rider_id ?? ($row['rider_id'] ?? null);
		        try {
		            $riderPopupUrl = route('users.rider_popup', $orderId);
		            $changeStatusUrl = route('bookings.change_status_popup', $orderId);
		        } catch (\Exception $e) {
		            $riderPopupUrl = '/users/rider-popup/' . $orderId;
		            $changeStatusUrl = '/orders/change-status/' . $orderId;
		        }
		    @endphp
		    @if(!$riderId || $riderId == 0)
		    <a href="javascript:void(0);" class="popup add-rider-btn" data-type="add_driver" data-url="{{$riderPopupUrl}}" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; color: var(--ct-gray-700); text-decoration: none; border-radius: 0.375rem; transition: all 0.2s; cursor: pointer;" onmouseover="this.style.background='var(--ct-gray-50)'; this.style.color='var(--ct-primary)'" onmouseout="this.style.background='transparent'; this.style.color='var(--ct-gray-700)'">
		        <i class="fas fa-user-plus" style="color: #10b981; font-size: 1rem;"></i>
		        <span>{{__('messages.add_driver', [], 'en') ?: 'Add Driver'}}</span>
		    </a>
		    @else
		    <a href="javascript:void(0);" class="popup change-rider-btn" data-type="change_rider" data-url="{{$riderPopupUrl}}" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; color: var(--ct-gray-700); text-decoration: none; border-radius: 0.375rem; transition: all 0.2s; cursor: pointer;" onmouseover="this.style.background='var(--ct-gray-50)'; this.style.color='var(--ct-primary)'" onmouseout="this.style.background='transparent'; this.style.color='var(--ct-gray-700)'">
		        <i class="fas fa-user-edit" style="color: #3b82f6; font-size: 1rem;"></i>
		        <span>{{__('messages.change_rider', [], 'en') ?: 'Change Rider'}}</span>
		    </a>
		    @endif
		    <a href="javascript:void(0);" class="popup change-status-btn" data-url="{{$changeStatusUrl}}" data-type="change_status" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; color: var(--ct-gray-700); text-decoration: none; border-radius: 0.375rem; transition: all 0.2s; cursor: pointer;" onmouseover="this.style.background='var(--ct-gray-50)'; this.style.color='var(--ct-primary)'" onmouseout="this.style.background='transparent'; this.style.color='var(--ct-gray-700)'">
		        <i class="fas fa-edit" style="color: #f59e0b; font-size: 1rem;"></i>
		        <span>{{__('messages.change_status', [], 'en') ?: 'Change Status'}}</span>
		    </a>
		@endif
    </div>
</div>
@else
<div style="padding: 0.5rem; color: #ef4444; font-size: 0.875rem;">ID not found</div>
@endif
