@extends('layouts.modern')

@section('title')
    <title>Dispatch Logs | {{ config('app.name', 'Laravel') }}</title>
@stop

@section('content')
<div class="ct-page-header">
    <div>
        <h1 class="ct-page-title">Dispatch Logs</h1>
        <p class="ct-page-subtitle">Every ride offer the auto-dispatch made — who got the call, who answered, and how rides transferred between riders</p>
    </div>
</div>

{{-- Stats --}}
<div class="dl-stats">
    <div class="ct-card dl-stat"><div class="dl-stat-num">{{ $stats['offers_today'] }}</div><div class="dl-stat-label">Calls today</div></div>
    <div class="ct-card dl-stat"><div class="dl-stat-num" style="color:#15803d;">{{ $stats['accepted'] }}</div><div class="dl-stat-label">Accepted</div></div>
    <div class="ct-card dl-stat"><div class="dl-stat-num" style="color:#b91c1c;">{{ $stats['rejected'] }}</div><div class="dl-stat-label">Declined</div></div>
    <div class="ct-card dl-stat"><div class="dl-stat-num" style="color:#64748b;">{{ $stats['expired'] }}</div><div class="dl-stat-label">No answer</div></div>
    <div class="ct-card dl-stat"><div class="dl-stat-num" style="color:#a16207;">{{ $stats['waiting'] }}</div><div class="dl-stat-label">Waiting now</div></div>
    <div class="ct-card dl-stat {{ $stats['needs_manual'] > 0 ? 'dl-stat-alert' : '' }}"><div class="dl-stat-num" style="color:#dc2626;">{{ $stats['needs_manual'] }}</div><div class="dl-stat-label">Need manual dispatch</div></div>
</div>

<div class="ct-card">
    {{-- Filters --}}
    <form method="GET" class="dl-toolbar">
        <div class="dl-filters">
            @php
                $tabs = ['' => 'All', 'pending' => 'Waiting', 'assign' => 'Accepted', 'rejected' => 'Declined', 'expired' => 'No answer', 'cancelled' => 'Superseded'];
            @endphp
            @foreach($tabs as $value => $label)
                <a href="{{ request()->fullUrlWithQuery(['status' => $value ?: null, 'page' => null]) }}"
                   class="dl-tab {{ ($status ?? '') === $value ? 'active' : '' }}">{{ $label }}</a>
            @endforeach
        </div>
        <div class="dl-search">
            <i class="fas fa-search"></i>
            <input type="text" name="q" value="{{ $q }}" placeholder="Booking ID or rider name…">
            @if($status)<input type="hidden" name="status" value="{{ $status }}">@endif
        </div>
    </form>

    @if($logs->count())
        <div class="dl-table-wrap">
            <table class="dl-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th style="width:60px;">Call #</th>
                        <th>Rider</th>
                        <th>Distance</th>
                        <th>Called at</th>
                        <th>Outcome</th>
                        <th>Answered</th>
                        <th>Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                        @php
                            $badge = [
                                'pending'   => ['#fef3c7', '#a16207', 'Waiting'],
                                'assign'    => ['#dcfce7', '#15803d', 'Accepted'],
                                'rejected'  => ['#fee2e2', '#b91c1c', 'Declined'],
                                'expired'   => ['#f1f5f9', '#64748b', 'No answer'],
                                'deleted'   => ['#f1f5f9', '#64748b', 'No answer'],
                                'cancelled' => ['#ede9fe', '#6d28d9', 'Superseded'],
                            ][$log->assign_status] ?? ['#f1f5f9', '#64748b', ucfirst($log->assign_status)];
                        @endphp
                        <tr>
                            <td>
                                @if($log->order)
                                    <a href="javascript:void(0);" class="dl-order popup" data-url="{{ route('bookings.show', $log->order_id) }}" title="Open booking detail">
                                        {{ $log->order->booking_id ?: '#'.$log->order_id }}
                                    </a>
                                @else
                                    <span class="dl-order-gone">#{{ $log->order_id }} (deleted)</span>
                                @endif
                            </td>
                            <td><span class="dl-attempt">{{ $log->attempt }}</span></td>
                            <td class="dl-rider">{{ $log->rider ? trim($log->rider->first_name.' '.$log->rider->last_name) : 'Rider #'.$log->rider_id }}</td>
                            <td class="dl-muted">{{ $log->distance_km ? number_format($log->distance_km, 1).' km' : '—' }}</td>
                            <td class="dl-muted">{{ $log->created_at->format('M d, H:i:s') }}</td>
                            <td><span class="dl-badge" style="background: {{ $badge[0] }}; color: {{ $badge[1] }};">{{ $badge[2] }}</span></td>
                            <td class="dl-muted">
                                @if($log->responded_at)
                                    {{ $log->responded_at->format('H:i:s') }} ({{ $log->created_at->diffInSeconds($log->responded_at) }}s)
                                @elseif(in_array($log->assign_status, ['expired','deleted']))
                                    timed out
                                @elseif($log->assign_status === 'pending')
                                    waiting…
                                @else
                                    —
                                @endif
                            </td>
                            <td class="dl-note">{{ $log->note ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="dl-pag">{{ $logs->links() }}</div>
    @else
        <div class="dl-empty">
            <div class="dl-empty-icon"><i class="fas fa-broadcast-tower"></i></div>
            <h3>No dispatch activity {{ $status || $q ? 'matches your filter' : 'yet' }}</h3>
            <p>Every time the auto-dispatch calls a rider — and every transfer, acceptance, or manual assignment — shows up here.</p>
        </div>
    @endif
</div>

<style>
    .dl-stats { display: grid; grid-template-columns: repeat(6, 1fr); gap: 0.75rem; margin-bottom: 1rem; }
    @media (max-width: 1100px) { .dl-stats { grid-template-columns: repeat(3, 1fr); } }
    .dl-stat { padding: 0.875rem 1rem; text-align: center; }
    .dl-stat-num { font-size: 1.375rem; font-weight: 800; color: var(--ct-gray-900); }
    .dl-stat-label { font-size: 0.6875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; color: var(--ct-gray-500); margin-top: 2px; }
    .dl-stat-alert { border: 1px solid #fecaca; background: #fef2f2; }
    .dl-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 1rem 1.25rem; border-bottom: 1px solid var(--ct-gray-200); flex-wrap: wrap; }
    .dl-filters { display: flex; gap: 0.375rem; flex-wrap: wrap; }
    .dl-tab { padding: 0.375rem 0.875rem; border-radius: 999px; font-size: 0.8125rem; font-weight: 600; color: var(--ct-gray-600); text-decoration: none; border: 1px solid var(--ct-gray-200); }
    .dl-tab:hover { border-color: var(--ct-accent); color: #3f6212; }
    .dl-tab.active { background: #f7fee7; border-color: var(--ct-accent); color: #3f6212; }
    .dl-search { position: relative; min-width: 240px; }
    .dl-search i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--ct-gray-400); font-size: 0.8125rem; }
    .dl-search input { width: 100%; padding: 0.5rem 0.75rem 0.5rem 2.25rem; border: 1px solid var(--ct-gray-200); border-radius: 8px; font-size: 0.8125rem; }
    .dl-search input:focus { outline: none; border-color: var(--ct-accent); box-shadow: 0 0 0 3px rgba(132,204,22,.15); }
    .dl-table-wrap { overflow-x: auto; }
    .dl-table { width: 100%; border-collapse: collapse; font-size: 0.8125rem; }
    .dl-table thead th { background: var(--ct-gray-50); text-align: left; padding: 0.75rem 1rem; font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--ct-gray-600); font-weight: 600; border-bottom: 1px solid var(--ct-gray-200); white-space: nowrap; }
    .dl-table tbody td { padding: 0.75rem 1rem; border-bottom: 1px solid var(--ct-gray-100); vertical-align: middle; }
    .dl-table tbody tr:hover td { background: var(--ct-gray-50); }
    .dl-order { font-family: 'SF Mono', Menlo, monospace; font-size: 0.75rem; font-weight: 600; color: #1d4ed8; text-decoration: none; cursor: pointer; }
    .dl-order:hover { text-decoration: underline; }
    .dl-order-gone { font-family: 'SF Mono', Menlo, monospace; font-size: 0.75rem; color: var(--ct-gray-400); }
    .dl-attempt { display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; border-radius: 50%; background: var(--ct-accent); color: #fff; font-size: 0.6875rem; font-weight: 700; }
    .dl-rider { font-weight: 600; color: var(--ct-gray-900); }
    .dl-muted { color: var(--ct-gray-500); white-space: nowrap; }
    .dl-badge { display: inline-block; padding: 2px 10px; border-radius: 999px; font-size: 0.6875rem; font-weight: 600; white-space: nowrap; }
    .dl-note { color: var(--ct-gray-600); font-size: 0.75rem; max-width: 320px; }
    .dl-pag { padding: 1rem 1.25rem; border-top: 1px solid var(--ct-gray-200); display: flex; justify-content: flex-end; }
    .dl-pag svg { width: 14px; height: 14px; }
    .dl-pag nav[role="navigation"] { display: inline-flex; align-items: center; gap: 4px; }
    .dl-pag span[aria-disabled], .dl-pag a[rel], .dl-pag span[aria-current] { display: inline-flex; align-items: center; justify-content: center; min-width: 34px; height: 34px; padding: 0 10px; border-radius: 8px; border: 1px solid var(--ct-gray-200); background: var(--ct-white); color: var(--ct-gray-700); font-size: 0.8125rem; text-decoration: none; }
    .dl-pag span[aria-current] { background: var(--ct-primary); color: #fff; border-color: var(--ct-primary); }
    .dl-empty { padding: 4rem 2rem; text-align: center; }
    .dl-empty-icon { width: 72px; height: 72px; margin: 0 auto 1rem; border-radius: 50%; background: #f1f5f9; color: var(--ct-gray-400); display: inline-flex; align-items: center; justify-content: center; font-size: 1.75rem; }
    .dl-empty h3 { font-size: 1.125rem; font-weight: 700; color: var(--ct-primary); margin: 0 0 0.375rem; }
    .dl-empty p { color: var(--ct-gray-500); font-size: 0.875rem; max-width: 420px; margin: 0 auto; }
</style>
@endsection

@section('scripts')
<script>
$(document).ready(function () {
    // Order links open the booking detail popup (incl. Dispatch History)
    $('body').on('click', '.popup', function () {
        var url = $(this).attr('data-url');
        if (!url) return;
        $.ajax({ type: 'GET', url: url, success: function (data) {
            $('#default_modal .modal-dialog').html(data);
            $('#default_modal').modal('show');
        }});
    });
});
</script>
@endsection
