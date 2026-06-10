<div class="dispatcher-section-head">
    <h2 class="dispatcher-section-title">{{ __('messages.unassigned_list') }}</h2>
    <div style="display: flex; align-items: center; gap: 0.75rem;">
        @if(!empty($orders) && count($orders) > 0)
            <span class="dispatcher-section-meta">{{ $orders->total() }} {{ Str::plural('order', $orders->total()) }} awaiting rider</span>
        @endif
        <div class="export-menu">
            <button type="button" class="btn-map export-toggle">
                <i class="fas fa-download"></i>
                Export
                <i class="fas fa-chevron-down" style="font-size: 10px; margin-left: 4px;"></i>
            </button>
            <div class="export-dropdown">
                <a href="{{ route('dispatcher.export.csv', ['type' => 'search']) }}" class="export-item">
                    <i class="fas fa-file-csv"></i>
                    Download CSV
                </a>
                <a href="{{ route('dispatcher.export.pdf', ['type' => 'search']) }}" target="_blank" class="export-item">
                    <i class="fas fa-file-pdf"></i>
                    Print / Save PDF
                </a>
            </div>
        </div>
    </div>
</div>

@if(!empty($orders) && count($orders) > 0)
    <div class="dispatcher-list">
        @foreach($orders as $key => $order)
            <div class="viewLocations order-card {!! ($key == 0) ? 'highlight' : '' !!}">
                <div class="order-card-body">
                    {{-- Header --}}
                    <div class="order-head">
                        <div class="order-identity">
                            <div class="order-avatar">
                                {{ strtoupper(substr($order->first_name, 0, 1) . substr($order->last_name, 0, 1)) }}
                            </div>
                            <div style="min-width: 0;">
                                <h3 class="order-name">{{ $order->first_name.' '.$order->last_name }}</h3>
                                <div class="order-tags">
                                    <span class="order-id">#{{ $order->id }}</span>
                                    <span class="ord-badge ord-badge-pending">
                                        <i class="fas fa-circle"></i>
                                        Pending
                                    </span>
                                </div>
                            </div>
                        </div>
                        <a href="javascript:void(0);"
                           class="popup btn-assign"
                           data-type="delete"
                           data-url="{{ route('users.rider_popup', $order->id) }}">
                            <i class="fas fa-user-plus"></i>
                            {{ __('messages.assign_rider') }}
                        </a>
                    </div>

                    {{-- Route --}}
                    @php $OrderSubTrip = \App\Models\OrderSubTrip::where('order_id', $order->id)->get(); @endphp
                    <div class="order-route">
                        @if($OrderSubTrip->count() > 0)
                            @foreach($OrderSubTrip as $ost)
                                <input type="hidden" class="slat{!! $ost->order_id !!}" name="start_lat[]" value="{{ $ost->start_lat }}">
                                <input type="hidden" class="slng{!! $ost->order_id !!}" name="start_long[]" value="{{ $ost->start_long }}">
                                <input type="hidden" class="lat{!! $ost->order_id !!}" name="end_lat[]" value="{{ $ost->end_lat }}">
                                <input type="hidden" class="lng{!! $ost->order_id !!}" name="end_long[]" value="{{ $ost->end_long }}">

                                <div class="route-step">
                                    <span class="route-dot from"></span>
                                    <p class="route-label">{{ __('messages.from') }}</p>
                                    <p class="route-address start_address">{{ $ost->start_location }}</p>
                                    <input type="hidden" class="pickup{!! $ost->order_id !!}" name="pickup_location" value="{{ $ost->start_location }}">
                                </div>
                                <div class="route-step">
                                    <span class="route-dot to"></span>
                                    <p class="route-label">{{ __('messages.to') }}</p>
                                    <p class="route-address end_address">{{ $ost->end_location }}</p>
                                    <input type="hidden" class="dropoff{!! $ost->order_id !!}" name="dropoff" value="{{ $ost->end_location }}">
                                </div>
                            @endforeach
                        @else
                            {{-- Fallback to order-level locations when no sub-trips exist --}}
                            <div class="route-step">
                                <span class="route-dot from"></span>
                                <p class="route-label">{{ __('messages.from') }}</p>
                                <p class="route-address start_address">{{ $order->start_location ?? '—' }}</p>
                                <input type="hidden" class="pickup{!! $order->id !!}" name="pickup_location" value="{{ $order->start_location }}">
                            </div>
                            <div class="route-step">
                                <span class="route-dot to"></span>
                                <p class="route-label">{{ __('messages.to') }}</p>
                                <p class="route-address end_address">{{ $order->end_location ?? '—' }}</p>
                                <input type="hidden" class="dropoff{!! $order->id !!}" name="dropoff" value="{{ $order->end_location }}">
                            </div>
                        @endif
                    </div>

                    {{-- Metrics --}}
                    <div class="flip">
                        <input type="hidden" name="polylines" class="mapinput" value="{{ $order['map_image'] }}">
                        <input type="hidden" name="orderid" class="mapinput" value="{{ $order->id }}">

                        <div class="order-meta">
                            <div class="order-metrics">
                                <span class="order-metric amount">
                                    <i class="fas fa-euro-sign"></i>
                                    {{ number_format($order['total_amount'], 2) }}
                                </span>
                                <span class="order-metric distance">
                                    <i class="fas fa-route"></i>
                                    {{ number_format(($order['total_meter'] / 1000), 2) }} km
                                </span>
                                @if(!empty($order['picked_time']))
                                    <span class="order-metric time">
                                        <i class="far fa-clock"></i>
                                        {{ \Carbon\Carbon::parse($order['picked_time'])->format('M d, H:i') }}
                                    </span>
                                @endif
                            </div>
                            <button class="toggler view_map_button btn-map">
                                <i class="far fa-map"></i>
                                View Map
                            </button>
                        </div>

                        <div class='toggled_content' style='display:none;'>
                            <div class="map-container">
                                <div class="show_map"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <input type="hidden" id="tab_type" value="search">
    <div class="custom_pagination">
        {{ $orders->links() }}
    </div>
@else
    <div class="dispatcher-empty">
        <div class="dispatcher-empty-icon amber">
            <i class="fas fa-inbox"></i>
        </div>
        <h3 class="dispatcher-empty-title">No unassigned orders</h3>
        <p class="dispatcher-empty-text">All orders have been assigned to riders.</p>
    </div>
@endif

<script type="text/javascript">
    $('.toggler').off('click.map').on('click.map', function () {
        $(this).closest('.flip').find('.toggled_content').slideToggle(150);
    });
</script>
