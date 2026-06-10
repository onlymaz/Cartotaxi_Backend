<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>{{ $title }} — {{ $generatedAt }}</title>
<style>
    * { box-sizing: border-box; }
    body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
        color: #1e293b;
        background: #fff;
        margin: 0;
        padding: 32px 40px;
        font-size: 12px;
        line-height: 1.45;
    }
    .print-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        border-bottom: 2px solid #1e293b;
        padding-bottom: 14px;
        margin-bottom: 20px;
    }
    .brand {
        font-size: 18px;
        font-weight: 700;
        letter-spacing: -0.01em;
    }
    .brand small {
        display: block;
        font-weight: 500;
        color: #64748b;
        font-size: 11px;
        margin-top: 2px;
        letter-spacing: 0;
    }
    .meta {
        text-align: right;
        font-size: 11px;
        color: #475569;
    }
    .meta strong {
        display: block;
        font-size: 14px;
        color: #1e293b;
        margin-bottom: 2px;
    }
    .summary {
        display: flex;
        gap: 10px;
        margin-bottom: 18px;
    }
    .summary-item {
        flex: 1;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px 12px;
    }
    .summary-item .label {
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
        margin-bottom: 4px;
    }
    .summary-item .value {
        font-size: 16px;
        font-weight: 700;
        color: #1e293b;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 11px;
    }
    thead th {
        background: #1e293b;
        color: #fff;
        text-align: left;
        padding: 9px 10px;
        font-weight: 600;
        font-size: 10.5px;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    tbody td {
        padding: 8px 10px;
        border-bottom: 1px solid #e2e8f0;
        vertical-align: top;
    }
    tbody tr:nth-child(even) td {
        background: #f8fafc;
    }
    .order-id {
        font-weight: 600;
        color: #1e293b;
        white-space: nowrap;
    }
    .muted { color: #94a3b8; }
    .addr {
        max-width: 180px;
        word-break: break-word;
    }
    .amount {
        font-weight: 600;
        color: #0f172a;
        white-space: nowrap;
    }
    .status {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 999px;
        font-size: 10px;
        font-weight: 600;
        text-transform: capitalize;
        background: #e2e8f0;
        color: #334155;
    }
    .status.delivered { background: #dcfce7; color: #15803d; }
    .status.on_way    { background: #dbeafe; color: #1d4ed8; }
    .status.picked_up { background: #e0e7ff; color: #4338ca; }
    .status.picking   { background: #fef9c3; color: #a16207; }
    .status.cancel    { background: #fee2e2; color: #b91c1c; }
    .status.pending   { background: #f1f5f9; color: #475569; }
    .empty {
        text-align: center;
        padding: 60px 20px;
        color: #94a3b8;
        border: 1px dashed #cbd5e1;
        border-radius: 10px;
    }
    .foot {
        margin-top: 20px;
        padding-top: 12px;
        border-top: 1px solid #e2e8f0;
        font-size: 10px;
        color: #94a3b8;
        display: flex;
        justify-content: space-between;
    }
    .print-btn {
        position: fixed;
        top: 16px;
        right: 16px;
        background: #1e293b;
        color: #fff;
        border: none;
        padding: 10px 18px;
        font-size: 13px;
        font-weight: 600;
        border-radius: 8px;
        cursor: pointer;
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.18);
    }
    .print-btn:hover { background: #0f172a; }
    @media print {
        body { padding: 18mm; }
        .print-btn { display: none; }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
    }
</style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">Print / Save as PDF</button>

    <div class="print-head">
        <div class="brand">
            {{ config('app.name', 'CargoTaxi') }}
            <small>Dispatcher Report — {{ $title }}</small>
        </div>
        <div class="meta">
            <strong>{{ $title }}</strong>
            Generated {{ $generatedAt }}<br>
            Total records: {{ count($orders) }}
        </div>
    </div>

    @php
        $totalRevenue = 0;
        $totalDistance = 0;
        foreach ($orders as $o) {
            $totalRevenue += (float) $o->total_amount;
            $totalDistance += (float) $o->total_meter;
        }
    @endphp

    <div class="summary">
        <div class="summary-item">
            <div class="label">Orders</div>
            <div class="value">{{ count($orders) }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Combined amount</div>
            <div class="value">&euro;{{ number_format($totalRevenue, 2) }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Total distance</div>
            <div class="value">{{ number_format($totalDistance / 1000, 2) }} km</div>
        </div>
    </div>

    @if(count($orders) > 0)
        <table>
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Rider</th>
                    <th>Pickup</th>
                    <th>Drop-off</th>
                    <th>Amount</th>
                    <th>Distance</th>
                    <th>Status</th>
                    <th>Picked</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $o)
                    @php
                        $customer = trim(($o->first_name ?? '') . ' ' . ($o->last_name ?? ''));
                        $rider = trim(($o->rider_first_name ?? '') . ' ' . ($o->rider_last_name ?? ''));
                        $statusKey = $o->order_status ?? 'pending';
                    @endphp
                    <tr>
                        <td class="order-id">#{{ $o->id }}</td>
                        <td>{{ $customer !== '' ? $customer : '—' }}</td>
                        <td>{!! $rider !== '' ? e($rider) : '<span class="muted">—</span>' !!}</td>
                        <td class="addr">{{ $o->start_location }}</td>
                        <td class="addr">{{ $o->end_location }}</td>
                        <td class="amount">&euro;{{ number_format((float) $o->total_amount, 2) }}</td>
                        <td>{{ number_format(((float) $o->total_meter) / 1000, 2) }} km</td>
                        <td><span class="status {{ $statusKey }}">{{ str_replace('_',' ', $statusKey) }}</span></td>
                        <td>{{ $o->picked_time ? \Carbon\Carbon::parse($o->picked_time)->format('M d, H:i') : '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="empty">No records to show for this report.</div>
    @endif

    <div class="foot">
        <span>{{ config('app.name', 'CargoTaxi') }} &middot; Dispatcher Report</span>
        <span>Page 1</span>
    </div>

    <script>
        window.addEventListener('load', function () {
            setTimeout(function () { window.print(); }, 300);
        });
    </script>
</body>
</html>
