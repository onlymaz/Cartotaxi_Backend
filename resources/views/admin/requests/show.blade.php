@extends('layouts.modern')

@section('title')
    <title>Booking #{{ $order->id ?? '' }} | {{ config('app.name', 'Laravel') }}</title>
@stop

@section('content')
<div class="ct-page-header">
    <div>
        <h1 class="ct-page-title">Booking Detail</h1>
        <p class="ct-page-subtitle">Order #{{ $order->id }} — {{ $order->booking_id }}</p>
    </div>
    <div class="ct-page-actions">
        <a href="{{ route('bookings.index') }}" class="ct-btn ct-btn-outline">
            <i class="fas fa-arrow-left"></i> Back to Bookings
        </a>
        <a href="{{ route('bookings.change_status_popup', $order->id) }}" class="ct-btn ct-btn-primary popup" data-type="medium">
            <i class="fas fa-exchange-alt"></i> Change Status
        </a>
    </div>
</div>

<div class="bk-grid">
    {{-- Left column --}}
    <div class="bk-col-main">
        {{-- Order info --}}
        <div class="ct-card" style="margin-bottom:1rem;">
            <div class="bk-card-head"><i class="fas fa-clipboard-list"></i> Order Summary</div>
            <div class="bk-detail-rows">
                <div class="bk-row"><span class="bk-label">Booking ID</span><span class="bk-val booking-id">{{ $order->booking_id }}</span></div>
                <div class="bk-row"><span class="bk-label">Status</span>
                    @php $st = $order->order_status ?? 'pending'; @endphp
                    <span class="bk-status bk-status-{{ $st }}">{{ ucfirst(str_replace('_',' ',$st)) }}</span>
                </div>
                <div class="bk-row"><span class="bk-label">Amount</span><span class="bk-val bk-amount">&euro;{{ number_format((float)$order->total_amount, 2) }}</span></div>
                <div class="bk-row"><span class="bk-label">Distance</span><span class="bk-val">{{ number_format($order->total_meter / 1000, 2) }} km</span></div>
                <div class="bk-row"><span class="bk-label">Scheduled</span><span class="bk-val">{{ $order->picked_time ? \Carbon\Carbon::parse($order->picked_time)->format('M d, Y H:i') : '—' }}</span></div>
                <div class="bk-row"><span class="bk-label">Created</span><span class="bk-val">{{ $order->created_at ? \Carbon\Carbon::parse($order->created_at)->format('M d, Y H:i') : '—' }}</span></div>
                @if($order->description)
                    <div class="bk-row"><span class="bk-label">Note</span><span class="bk-val">{{ $order->description }}</span></div>
                @endif
            </div>
        </div>

        {{-- Route --}}
        <div class="ct-card" style="margin-bottom:1rem;">
            <div class="bk-card-head"><i class="fas fa-route"></i> Route</div>
            <div style="padding: 1.25rem 1.5rem;">
                @if($order->orderSubTrip && $order->orderSubTrip->count() > 0)
                    @foreach($order->orderSubTrip as $ost)
                        <div class="bk-route-step">
                            <span class="bk-route-dot from"></span>
                            <div>
                                <div class="bk-route-label">From</div>
                                <div class="bk-route-addr">{{ $ost->start_location }}</div>
                            </div>
                        </div>
                        <div class="bk-route-line"></div>
                        <div class="bk-route-step">
                            <span class="bk-route-dot to"></span>
                            <div>
                                <div class="bk-route-label">To</div>
                                <div class="bk-route-addr">{{ $ost->end_location }}</div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="bk-route-step">
                        <span class="bk-route-dot from"></span>
                        <div>
                            <div class="bk-route-label">From</div>
                            <div class="bk-route-addr">{{ $order->start_location ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="bk-route-line"></div>
                    <div class="bk-route-step">
                        <span class="bk-route-dot to"></span>
                        <div>
                            <div class="bk-route-label">To</div>
                            <div class="bk-route-addr">{{ $order->end_location ?? '—' }}</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Payment --}}
        @if($order->payment)
            <div class="ct-card">
                <div class="bk-card-head"><i class="fas fa-credit-card"></i> Payment</div>
                <div class="bk-detail-rows">
                    <div class="bk-row"><span class="bk-label">Gateway</span><span class="bk-val">{{ optional(optional($order->payment)->gateway)->name ?? '—' }}</span></div>
                    <div class="bk-row"><span class="bk-label">Amount</span><span class="bk-val bk-amount">&euro;{{ number_format((float)optional($order->payment)->amount, 2) }}</span></div>
                    <div class="bk-row"><span class="bk-label">Status</span><span class="bk-val">{{ ucfirst(optional($order->payment)->status ?? '—') }}</span></div>
                </div>
            </div>
        @endif
    </div>

    {{-- Right column --}}
    <div class="bk-col-side">
        {{-- Customer --}}
        <div class="ct-card" style="margin-bottom:1rem;">
            <div class="bk-card-head"><i class="fas fa-user"></i> Customer</div>
            <div class="bk-person-card">
                <div class="bk-avatar">
                    {{ $order->user ? strtoupper(substr($order->user->first_name,0,1).substr($order->user->last_name,0,1)) : '?' }}
                </div>
                <div>
                    <div class="bk-person-name">{{ $order->user ? trim($order->user->first_name.' '.$order->user->last_name) : '—' }}</div>
                    <div class="bk-person-sub">{{ optional($order->user)->email ?? '' }}</div>
                    <div class="bk-person-sub">{{ optional($order->user)->phone_number ?? '' }}</div>
                </div>
            </div>
        </div>

        {{-- Rider --}}
        <div class="ct-card" style="margin-bottom:1rem;">
            <div class="bk-card-head"><i class="fas fa-motorcycle"></i> Rider</div>
            @if($order->rider)
                <div class="bk-person-card">
                    <div class="bk-avatar rider">
                        {{ strtoupper(substr($order->rider->first_name,0,1).substr($order->rider->last_name,0,1)) }}
                    </div>
                    <div>
                        <div class="bk-person-name">{{ trim($order->rider->first_name.' '.$order->rider->last_name) }}</div>
                        <div class="bk-person-sub">{{ $order->rider->email ?? '' }}</div>
                        <div class="bk-person-sub">{{ $order->rider->phone_number ?? '' }}</div>
                    </div>
                </div>
            @else
                <div class="bk-no-rider"><i class="fas fa-user-slash"></i> No rider assigned yet</div>
            @endif
        </div>

        {{-- Package --}}
        @if($order->package)
            <div class="ct-card">
                <div class="bk-card-head"><i class="fas fa-box"></i> Package</div>
                <div class="bk-detail-rows">
                    <div class="bk-row"><span class="bk-label">Name</span><span class="bk-val">{{ $order->package->name }}</span></div>
                    <div class="bk-row"><span class="bk-label">Weight</span><span class="bk-val">{{ $order->package->weight }} {{ $order->package->unit }}</span></div>
                    <div class="bk-row"><span class="bk-label">Fixed price</span><span class="bk-val">&euro;{{ number_format((float)$order->package->fixed_price,2) }}</span></div>
                </div>
            </div>
        @endif
    </div>
</div>

<style>
    .bk-grid { display: grid; grid-template-columns: 1fr 340px; gap: 1rem; align-items: start; }
    @media (max-width: 900px) { .bk-grid { grid-template-columns: 1fr; } }
    .bk-card-head { display: flex; align-items: center; gap: 0.5rem; padding: 1rem 1.5rem; border-bottom: 1px solid var(--ct-gray-200); font-weight: 700; font-size: 0.9375rem; color: var(--ct-primary); }
    .bk-card-head i { color: var(--ct-accent); }
    .bk-detail-rows { padding: 0.25rem 0; }
    .bk-row { display: flex; align-items: center; justify-content: space-between; padding: 0.6rem 1.5rem; border-bottom: 1px solid var(--ct-gray-50); gap: 1rem; }
    .bk-row:last-child { border-bottom: none; }
    .bk-label { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--ct-gray-500); font-weight: 600; flex-shrink: 0; }
    .bk-val { font-size: 0.875rem; color: var(--ct-gray-800); font-weight: 500; text-align: right; }
    .booking-id { font-family: 'SF Mono', Menlo, Monaco, monospace; font-size: 0.8125rem; }
    .bk-amount { font-weight: 700; color: var(--ct-primary); font-size: 1rem; }
    .bk-status { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 0.75rem; font-weight: 600; text-transform: capitalize; }
    .bk-status-pending { background: #fef3c7; color: #a16207; }
    .bk-status-delivered { background: #dcfce7; color: #15803d; }
    .bk-status-on_way { background: #dbeafe; color: #1d4ed8; }
    .bk-status-picked_up { background: #e0e7ff; color: #4338ca; }
    .bk-status-picking { background: #fef9c3; color: #a16207; }
    .bk-status-cancel { background: #fee2e2; color: #b91c1c; }
    .bk-route-step { display: flex; align-items: flex-start; gap: 0.75rem; margin-bottom: 0; }
    .bk-route-dot { width: 12px; height: 12px; border-radius: 50%; flex-shrink: 0; margin-top: 3px; }
    .bk-route-dot.from { background: #22c55e; box-shadow: 0 0 0 3px rgba(34,197,94,.2); }
    .bk-route-dot.to { background: #ef4444; box-shadow: 0 0 0 3px rgba(239,68,68,.2); }
    .bk-route-line { width: 2px; height: 24px; background: linear-gradient(#22c55e, #ef4444); margin: 4px 0 4px 5px; }
    .bk-route-label { font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--ct-gray-500); font-weight: 600; margin-bottom: 2px; }
    .bk-route-addr { font-size: 0.875rem; color: var(--ct-gray-800); font-weight: 500; }
    .bk-person-card { display: flex; align-items: center; gap: 0.875rem; padding: 1.25rem 1.5rem; }
    .bk-avatar { width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, var(--ct-accent), #65a30d); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.9375rem; font-weight: 700; flex-shrink: 0; }
    .bk-avatar.rider { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
    .bk-person-name { font-weight: 700; color: var(--ct-gray-900); margin-bottom: 2px; }
    .bk-person-sub { font-size: 0.75rem; color: var(--ct-gray-500); }
    .bk-no-rider { padding: 1.25rem 1.5rem; color: var(--ct-gray-400); font-size: 0.875rem; display: flex; align-items: center; gap: 0.5rem; }
</style>
@endsection

@section('scripts')
<script>
$(document).ready(function () {
    $('body').on('click', '.popup', function (e) {
        e.preventDefault();
        var url = $(this).attr('href');
        $.ajax({
            type: 'GET', url: url,
            success: function (data) {
                $('#default_modal .modal-dialog').html(data);
                $('#default_modal').modal('show');
            }
        });
    });
});
</script>
@endsection
