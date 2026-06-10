<div id="assignedID">
    <div class="dispatcher-section-head">
        <h2 class="dispatcher-section-title">{{ __('messages.assigned_list') }}</h2>
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            @if(!empty($orders) && count($orders) > 0)
                <span class="dispatcher-section-meta">{{ $orders->total() }} {{ Str::plural('order', $orders->total()) }} in progress</span>
            @endif
            <button id="filter" class="btn-map">
                <i class="fas fa-filter"></i>
                Filter
            </button>
            <div class="export-menu">
                <button type="button" class="btn-map export-toggle">
                    <i class="fas fa-download"></i>
                    Export
                    <i class="fas fa-chevron-down" style="font-size: 10px; margin-left: 4px;"></i>
                </button>
                <div class="export-dropdown">
                    <a href="{{ route('dispatcher.export.csv', ['type' => 'assigned']) }}" class="export-item">
                        <i class="fas fa-file-csv"></i>
                        Download CSV
                    </a>
                    <a href="{{ route('dispatcher.export.pdf', ['type' => 'assigned']) }}" target="_blank" class="export-item">
                        <i class="fas fa-file-pdf"></i>
                        Print / Save PDF
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="filterDetail filter-panel" style="display: none;">
        <div class="filter-grid">
            <form class="form filterFormHelper" action="" method="get">
                <label class="filter-label">Booking Type</label>
                <select name="filterFormHelper" class="filterFormHelper filter-select">
                    <option selected disabled>Select filter…</option>
                    <option value="all">All bookings</option>
                    <option value="withouthelper">Without Helper</option>
                    <option value="withhelper">With Helper</option>
                </select>
            </form>
            <form class="form filterForm" action="" method="get" style="display: none;">
                <label class="filter-label">Order Status</label>
                <select name="statusFilter" class="formFilter filter-select">
                    <option selected disabled>Select status…</option>
                    <option value="all">All statuses</option>
                    <option value="picking">Processing</option>
                    <option value="picked_up">Picked Up</option>
                    <option value="on_way">On the way</option>
                    <option value="delivered">Delivered</option>
                </select>
            </form>
        </div>
    </div>

    @if(!empty($orders) && count($orders) > 0)
        <div class="dispatcher-list">
            @foreach($orders as $key => $order)
                @php
                    $statusLabels = [
                        'pending'    => 'Pending',
                        'processing' => 'Processing',
                        'picking'    => 'Picking',
                        'pickup'     => 'Pickup',
                        'picked_up'  => 'Picked Up',
                        'on_way'     => 'On the way',
                        'delivered'  => 'Delivered',
                        'cancel'     => 'Cancelled',
                    ];
                    $statusKey = $order['order_status'] ?? 'pending';
                    $statusLabel = $statusLabels[$statusKey] ?? ucfirst($statusKey);
                @endphp

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
                                        <span class="ord-badge ord-badge-{{ $statusKey }}">
                                            <i class="fas fa-circle"></i>
                                            {{ $statusLabel }}
                                        </span>
                                        @if(!empty($order->rider_name))
                                            <span class="ord-badge ord-badge-rider">
                                                <i class="fas fa-user"></i>
                                                {{ $order->rider_name }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <button class="rider-details btn-rider-details">
                                <input type="hidden" name="rider_detail" value="{{ $order->id }}">
                                <i class="fas fa-info-circle"></i>
                                Rider Details
                            </button>
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
                                    @if($order->order_status == 'on_way')
                                        <a href="javascript:void(0)"
                                           rider_id="{!! $order['rider_id'] !!}"
                                           customer_id="{!! $order['customer_id'] !!}"
                                           class="ord-badge ord-badge-live">
                                            <i class="fas fa-broadcast-tower"></i>
                                            {{ __('messages.live_location') }}
                                        </a>
                                    @endif
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

        <input type="hidden" id="tab_type" value="assigned">
        <div class="custom_pagination">
            {{ $orders->links() }}
        </div>
    @else
        <div class="dispatcher-empty">
            <div class="dispatcher-empty-icon info">
                <i class="fas fa-route"></i>
            </div>
            <h3 class="dispatcher-empty-title">No assigned orders</h3>
            <p class="dispatcher-empty-text">Orders in progress will appear here.</p>
        </div>
    @endif

    {{-- Rider details modal --}}
    <div id="rider_model" class="rider-modal-overlay">
        <div class="rider-modal">
            <div class="rider-modal-head">
                <h4>Rider Information</h4>
                <button type="button" class="rider-model-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="rider-modal-body">
                <div class="rider-row">
                    <span class="rider-row-label">Rider</span>
                    <span id="rider_name" class="rider-row-value">—</span>
                </div>
                <div class="rider-row">
                    <span class="rider-row-label">Email</span>
                    <span id="rider_email" class="rider-row-value">—</span>
                </div>
                <div class="rider-row">
                    <span class="rider-row-label">Phone</span>
                    <span id="rider_no" class="rider-row-value">—</span>
                </div>
                <div class="rider-row">
                    <span class="rider-row-label">Received by</span>
                    <span id="recived_by" class="rider-row-value">—</span>
                </div>
                <div class="rider-row">
                    <span class="rider-row-label">Booked</span>
                    <span id="booking_time" class="rider-row-value">—</span>
                </div>
                <div class="rider-row">
                    <span class="rider-row-label">Delivered</span>
                    <span id="delivery_time" class="rider-row-value">—</span>
                </div>
                <div class="rider-signature">
                    <p class="rider-signature-label">Signature</p>
                    <img id="sign" src="" alt="signature">
                </div>
            </div>
            <div class="rider-modal-foot">
                <button type="button" class="rider-model-close ct-btn ct-btn-outline ct-btn-sm">Close</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        var statusHelper = '';
        var statusOrder = '';
        $('body').on('change', '.filterFormHelper', function () {
            $('.filterForm').css('display', 'block');
            statusHelper = $('.filterFormHelper option:selected').val();

            $('body').on('change', '.formFilter', function () {
                statusOrder = $('.formFilter option:selected').val();
                $.ajax({
                    url: "{{ url('dispatcher/create?action=assigned') }}" + '&orderFilterType=' + statusOrder + '&statusFilter=' + statusHelper,
                    type: 'get',
                    data: { _token: '{!! csrf_token() !!}' },
                    success: function (data) {
                        $('#assignedID').empty();
                        $('#assignedID').append(data);
                    }
                });
            });
        });
    });

    $('.toggler').off('click.map').on('click.map', function () {
        $(this).closest('.flip').find('.toggled_content').slideToggle(150);
    });

    $('body').on('click', '.rider-details', function () {
        var rider_id = $(this).find("input[name='rider_detail']").val();
        $.ajax({
            url: "{{ url('rider/info') }}",
            type: 'post',
            data: { id: rider_id, _token: '{!! csrf_token() !!}' },
            success: function (data) {
                var f_name = data.rider['first_name'] + ' ' + data.rider['last_name'];
                $('#rider_model').addClass('open');
                $('#rider_name').text(f_name);
                $('#rider_email').text(data.rider['email']);
                $('#rider_no').text(data.rider['phone_number']);
                $('#sign').attr('src', data['sign']);
                $('#delivery_time').text(data.end_time || '—');
                $('#booking_time').text(data.created_at || '—');
                if (data.reciver_name) {
                    $('#recived_by').text(data.reciver_name + ' From ' + data.reciver_address);
                } else if (data.user) {
                    $('#recived_by').text(data.user['first_name'] + ' ' + data.user['last_name']);
                } else {
                    $('#recived_by').text('—');
                }
            }
        });
    });

    $('body').on('click', '.rider-model-close', function () {
        $('#rider_model').removeClass('open');
    });
    $('body').on('click', '#rider_model', function (e) {
        if (e.target === this) {
            $(this).removeClass('open');
        }
    });
</script>
