<div style="padding: 1.5rem;">
        @if($order)
        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1rem; margin-bottom: 1rem;">
            <dt style="font-weight: 600; color: var(--ct-gray-700);">{{__('messages.user_name', [], 'en') ?: 'User Name'}}:</dt>
            <dd style="color: var(--ct-gray-900);">{{$order->user && $order->user->full_name ? $order->user->full_name : 'N/A'}}</dd>
            
            <dt style="font-weight: 600; color: var(--ct-gray-700);">{{__('messages.rider_name', [], 'en') ?: 'Rider Name'}}:</dt>
            <dd style="color: var(--ct-gray-900);">{{$order->rider && $order->rider->full_name ? $order->rider->full_name : 'N/A'}}</dd>
            
            <dt style="font-weight: 600; color: var(--ct-gray-700);">Description:</dt>
            <dd style="color: var(--ct-gray-900);">{{$order->description ?: 'N/A'}}</dd>
            
            <dt style="font-weight: 600; color: var(--ct-gray-700);">Package Detail:</dt>
            <dd style="color: var(--ct-gray-900);">{{$order->package && $order->package->name ? $order->package->name : 'N/A'}}</dd>
            
            <dt style="font-weight: 600; color: var(--ct-gray-700);">Total Distance in KM:</dt>
            <dd style="color: var(--ct-gray-900);">{{$order->total_meter ? number_format((float)$order->total_meter/1000, 2, '.', '') : 'N/A'}}</dd>

            <dt style="font-weight: 600; color: var(--ct-gray-700);">{{__('messages.rider_start_time', [], 'en') ?: 'Rider Start Time'}}:</dt>
            <dd style="color: var(--ct-gray-900);">{{$order->start_time ?: 'N/A'}}</dd>
            
            <dt style="font-weight: 600; color: var(--ct-gray-700);">Rider Delivery Time:</dt>
            <dd style="color: var(--ct-gray-900);">{{$order->end_time ?: 'N/A'}}</dd>
            
            <dt style="font-weight: 600; color: var(--ct-gray-700);">Customer Phone Number:</dt>
            <dd style="color: var(--ct-gray-900);">{{$order->user && isset($order->user->phone_number) && $order->user->phone_number ? $order->user->phone_number : 'N/A'}}</dd>
            
            <dt style="font-weight: 600; color: var(--ct-gray-700);">Cargo Stand:</dt>
            <dd style="color: var(--ct-gray-900);">Freudenauer Hafenstraße 8-10, 1020 Wien, Austria</dd>
            
            @if($order->orderSubTrip && $order->orderSubTrip->count() > 0)
            @php $i=0; @endphp
            @foreach($order->orderSubTrip as $orderTrip)
            @php $i++; @endphp
                @if($loop->first)
                        <dt style="font-weight: 600; color: var(--ct-gray-700);">Pick Up:</dt>
                        <dd style="color: var(--ct-gray-900);">{{$orderTrip->start_location ?? 'N/A'}}</dd>
                @endif
                    <dt style="font-weight: 600; color: var(--ct-gray-700);">Drop Off @if($loop->last)|Last Location|@else{{$i}}@endif:</dt>
                    <dd style="color: var(--ct-gray-900);">{{$orderTrip->end_location ?? 'N/A'}}</dd>
            @endforeach
            @else
                <dt style="font-weight: 600; color: var(--ct-gray-700);">Pick Up:</dt>
                <dd style="color: var(--ct-gray-900);">{{$order->start_location ?: 'N/A'}}</dd>
                <dt style="font-weight: 600; color: var(--ct-gray-700);">Drop Off:</dt>
                <dd style="color: var(--ct-gray-900);">{{$order->end_location ?: 'N/A'}}</dd>
            @endif

            <dt style="font-weight: 600; color: var(--ct-gray-700);">{{__('messages.total_amount', [], 'en') ?: 'Total Amount'}}:</dt>
            <dd style="color: var(--ct-gray-900); font-weight: 600; color: var(--ct-accent);">
                {{config('app.currency_symbol')}}{{number_format((float)$order->total_amount, 2, '.', '')}}
            </dd>
            
            <dt style="font-weight: 600; color: var(--ct-gray-700);">{{__('messages.ride_status', [], 'en') ?: 'Ride Status'}}:</dt>
            <dd style="color: var(--ct-gray-900);">
                <span style="display: inline-block; padding: 0.25rem 0.75rem; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 500; background: rgba(99, 102, 241, 0.1); color: #6366f1;">
                    {{ucfirst(str_replace('_', ' ', $order->order_status))}}
                </span>
            </dd>
        </div>
        @else
        <div style="text-align: center; padding: 2rem; color: var(--ct-gray-500);">
            <i class="fas fa-exclamation-circle" style="font-size: 2rem; margin-bottom: 1rem; color: #ef4444;"></i>
            <p>Order not found</p>
        </div>
        @endif
        
        @if($helper && is_object($helper) && isset($helper->helper))
        <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--ct-gray-200);">
            <h4 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1rem; color: var(--ct-gray-900);">Helper Details</h4>
            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1rem;">
                <dt style="font-weight: 600; color: var(--ct-gray-700);">Total Helper:</dt>
                <dd style="color: var(--ct-gray-900);">{{ $helper->helper ? $helper->helper->total_helper : '-' }}</dd>
                
                <dt style="font-weight: 600; color: var(--ct-gray-700);">Total Hours:</dt>
                <dd style="color: var(--ct-gray-900);">{{ $helper->helper ? $helper->helper->end_time : '-' }}</dd>
                
                <dt style="font-weight: 600; color: var(--ct-gray-700);">Date & Time:</dt>
                <dd style="color: var(--ct-gray-900);">{{ $helper->helper ? $helper->helper->start_time : '-' }}</dd>
                
                <dt style="font-weight: 600; color: var(--ct-gray-700);">Payment Method:</dt>
                <dd style="color: var(--ct-gray-900);">{{ $helper->helper ? 'COD' : '-' }}</dd>
                
                <dt style="font-weight: 600; color: var(--ct-gray-700);">Address:</dt>
                <dd style="color: var(--ct-gray-900);">{{ $helper->helper ? $helper->helper->address : '-' }}</dd>
                
                <dt style="font-weight: 600; color: var(--ct-gray-700);">Fee:</dt>
                <dd style="color: var(--ct-gray-900);">{{ $helper->helper ? $helper->helper->price : '-' }}</dd>
    </div>
    </div>
    @endif
</div>
