@extends('layouts.modern')

@section('title')
    <title>Reports | {{ config('app.name', 'Laravel') }}</title>
@stop

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
        'refused'    => 'Refused',
        'accident'   => 'Accident',
    ];
    $statusColors = [
        'pending'    => '#f59e0b',
        'processing' => '#6366f1',
        'picking'    => '#3b82f6',
        'pickup'     => '#f59e0b',
        'picked_up'  => '#0ea5e9',
        'on_way'     => '#8b5cf6',
        'delivered'  => '#10b981',
        'cancel'     => '#ef4444',
        'refused'    => '#64748b',
        'accident'   => '#dc2626',
    ];
    $maxStatusTotal = $statusBreakdown->max('total') ?: 1;
@endphp

@section('content')
    {{-- Page Header --}}
    <div class="ct-page-header">
        <div>
            <h1 class="ct-page-title">Reports</h1>
            <p class="ct-page-subtitle">Operational insights across orders, revenue, and rider performance.</p>
        </div>
        <div class="ct-page-actions">
            <form method="GET" action="{{ route('admin.reports') }}" class="reports-range-form">
                <div class="reports-range-tabs">
                    <button type="submit" name="range" value="today"
                            class="reports-range-btn {{ $rangeKey === 'today' ? 'active' : '' }}">Today</button>
                    <button type="submit" name="range" value="7d"
                            class="reports-range-btn {{ $rangeKey === '7d' ? 'active' : '' }}">7 days</button>
                    <button type="submit" name="range" value="30d"
                            class="reports-range-btn {{ $rangeKey === '30d' ? 'active' : '' }}">30 days</button>
                    <label class="reports-range-btn reports-range-custom {{ $rangeKey === 'custom' ? 'active' : '' }}">
                        <i class="far fa-calendar-alt"></i>
                        Custom
                        <input type="hidden" name="range" value="custom">
                        <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" onchange="this.form.submit()">
                        <span>–</span>
                        <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" onchange="this.form.submit()">
                    </label>
                </div>
            </form>
        </div>
    </div>

    <p class="reports-range-caption">
        Showing data from <strong>{{ $from->format('M d, Y') }}</strong> to <strong>{{ $to->format('M d, Y') }}</strong>
    </p>

    {{-- KPI Stats --}}
    <div class="ct-stats-grid ct-mb-6">
        <div class="ct-stat-card info">
            <div class="ct-stat-header">
                <div>
                    <div class="ct-stat-value" style="font-variant-numeric: tabular-nums;">{{ number_format($totalOrders) }}</div>
                    <div class="ct-stat-label">Total Orders</div>
                </div>
                <div class="ct-stat-icon"><i class="fas fa-boxes"></i></div>
            </div>
        </div>
        <div class="ct-stat-card accent">
            <div class="ct-stat-header">
                <div>
                    <div class="ct-stat-value" style="font-variant-numeric: tabular-nums;">&euro;{{ number_format($totalRevenue, 2) }}</div>
                    <div class="ct-stat-label">Revenue (billable)</div>
                </div>
                <div class="ct-stat-icon"><i class="fas fa-euro-sign"></i></div>
            </div>
        </div>
        <div class="ct-stat-card success">
            <div class="ct-stat-header">
                <div>
                    <div class="ct-stat-value" style="font-variant-numeric: tabular-nums;">{{ number_format($deliveredCount) }}</div>
                    <div class="ct-stat-label">Delivered</div>
                </div>
                <div class="ct-stat-icon"><i class="fas fa-circle-check"></i></div>
            </div>
        </div>
        <div class="ct-stat-card primary">
            <div class="ct-stat-header">
                <div>
                    <div class="ct-stat-value" style="font-variant-numeric: tabular-nums;">{{ number_format($activeRiders) }}</div>
                    <div class="ct-stat-label">Active Riders</div>
                </div>
                <div class="ct-stat-icon"><i class="fas fa-user-tie"></i></div>
            </div>
        </div>
    </div>

    {{-- Charts & breakdowns --}}
    <div class="reports-grid">
        <div class="ct-card reports-revenue">
            <div class="reports-card-head">
                <div>
                    <h3 class="reports-card-title">Revenue Trend</h3>
                    <p class="reports-card-sub">Billable orders over selected range</p>
                </div>
                <span class="ord-badge" style="background: rgba(132, 204, 22, 0.12); color: #5a7e0d;">
                    <i class="fas fa-chart-line"></i>
                    {{ count($revenueTrend['labels']) }} days
                </span>
            </div>
            <div class="reports-chart-wrap">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <div class="ct-card reports-status">
            <div class="reports-card-head">
                <div>
                    <h3 class="reports-card-title">Orders by Status</h3>
                    <p class="reports-card-sub">Distribution across {{ number_format($totalOrders) }} orders</p>
                </div>
            </div>
            @if($statusBreakdown->count() > 0)
                <div class="status-list">
                    @foreach($statusBreakdown as $row)
                        @php
                            $label = $statusLabels[$row->order_status] ?? ucfirst($row->order_status);
                            $color = $statusColors[$row->order_status] ?? '#64748b';
                            $pct   = $maxStatusTotal > 0 ? ($row->total / $maxStatusTotal) * 100 : 0;
                            $share = $totalOrders > 0 ? ($row->total / $totalOrders) * 100 : 0;
                        @endphp
                        <div class="status-row">
                            <div class="status-row-head">
                                <div class="status-row-label">
                                    <span class="status-dot" style="background: {{ $color }};"></span>
                                    <span>{{ $label }}</span>
                                </div>
                                <div class="status-row-meta">
                                    <span class="status-count">{{ number_format($row->total) }}</span>
                                    <span class="status-share">{{ number_format($share, 1) }}%</span>
                                </div>
                            </div>
                            <div class="status-bar">
                                <div class="status-bar-fill" style="width: {{ $pct }}%; background: {{ $color }};"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="reports-empty">
                    <i class="fas fa-inbox"></i>
                    <p>No orders in this range yet.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Top riders --}}
    <div class="ct-card reports-riders ct-mt-6">
        <div class="reports-card-head">
            <div>
                <h3 class="reports-card-title">Top Riders</h3>
                <p class="reports-card-sub">Ranked by completed deliveries in range</p>
            </div>
        </div>
        @if($topRiders->count() > 0)
            <div class="reports-table-wrap">
                <table class="reports-table">
                    <thead>
                        <tr>
                            <th style="width: 42px;">#</th>
                            <th>Rider</th>
                            <th class="col-num">Completed</th>
                            <th class="col-num">Revenue</th>
                            <th class="col-bar">Share</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $topCount = $topRiders->max('completed_orders') ?: 1; @endphp
                        @foreach($topRiders as $idx => $rider)
                            @php $pct = ($rider->completed_orders / $topCount) * 100; @endphp
                            <tr>
                                <td>
                                    <span class="rider-rank rank-{{ $idx + 1 }}">{{ $idx + 1 }}</span>
                                </td>
                                <td>
                                    <div class="rider-cell">
                                        <div class="rider-avatar">
                                            {{ strtoupper(substr($rider->first_name, 0, 1) . substr($rider->last_name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="rider-name">{{ $rider->first_name }} {{ $rider->last_name }}</div>
                                            <div class="rider-email">{{ $rider->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="col-num"><strong>{{ number_format($rider->completed_orders) }}</strong></td>
                                <td class="col-num">&euro;{{ number_format($rider->revenue, 2) }}</td>
                                <td class="col-bar">
                                    <div class="rider-bar"><div class="rider-bar-fill" style="width: {{ $pct }}%;"></div></div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="reports-empty">
                <i class="fas fa-user-slash"></i>
                <p>No rider deliveries in this range.</p>
            </div>
        @endif
    </div>

    <style>
        /* Range controls */
        .reports-range-form { display: inline-flex; }
        .reports-range-tabs {
            display: inline-flex;
            padding: 4px;
            border-radius: 10px;
            background: var(--ct-gray-100);
            gap: 2px;
        }
        .reports-range-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.45rem 0.9rem;
            border: none;
            background: transparent;
            color: var(--ct-gray-600);
            font-size: 0.8125rem;
            font-weight: 600;
            border-radius: 7px;
            cursor: pointer;
            transition: all 0.15s;
        }
        .reports-range-btn:hover { color: var(--ct-primary); }
        .reports-range-btn.active {
            background: var(--ct-white);
            color: var(--ct-primary);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }
        .reports-range-custom input[type="date"] {
            border: none;
            background: transparent;
            color: inherit;
            font: inherit;
            padding: 0;
            min-width: 110px;
            cursor: pointer;
        }
        .reports-range-custom input[type="date"]:focus { outline: none; }
        .reports-range-custom span { margin: 0 0.25rem; color: var(--ct-gray-400); }

        .reports-range-caption {
            font-size: 0.8125rem;
            color: var(--ct-gray-500);
            margin: 0 0 1.25rem;
        }
        .reports-range-caption strong { color: var(--ct-gray-800); font-weight: 600; }

        /* Grid */
        .reports-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.25rem;
        }
        @media (min-width: 1024px) {
            .reports-grid { grid-template-columns: 1.6fr 1fr; }
        }

        /* Card heads */
        .reports-card-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--ct-gray-100);
        }
        .reports-card-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--ct-gray-900);
            margin: 0;
        }
        .reports-card-sub {
            font-size: 0.8125rem;
            color: var(--ct-gray-500);
            margin: 0.2rem 0 0;
        }

        /* Revenue chart */
        .reports-chart-wrap {
            padding: 1.5rem;
            height: 320px;
        }
        .reports-chart-wrap canvas { max-width: 100%; height: 100% !important; }

        /* Status list */
        .status-list {
            padding: 1rem 1.5rem 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .status-row { font-size: 0.8125rem; }
        .status-row-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.45rem;
        }
        .status-row-label {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--ct-gray-800);
            font-weight: 500;
        }
        .status-dot {
            width: 10px; height: 10px;
            border-radius: 999px;
            display: inline-block;
        }
        .status-row-meta {
            display: inline-flex;
            align-items: baseline;
            gap: 0.5rem;
        }
        .status-count {
            font-weight: 700;
            color: var(--ct-gray-900);
            font-variant-numeric: tabular-nums;
        }
        .status-share {
            font-size: 0.75rem;
            color: var(--ct-gray-500);
            font-variant-numeric: tabular-nums;
        }
        .status-bar {
            height: 8px;
            background: var(--ct-gray-100);
            border-radius: 999px;
            overflow: hidden;
        }
        .status-bar-fill {
            height: 100%;
            border-radius: 999px;
            transition: width 0.3s ease;
        }

        /* Top riders table */
        .reports-table-wrap {
            padding: 0.5rem 0.5rem 1rem;
            overflow-x: auto;
        }
        .reports-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 0.875rem;
        }
        .reports-table thead th {
            text-align: left;
            padding: 0.75rem 1rem;
            font-size: 0.6875rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: var(--ct-gray-500);
            border-bottom: 1px solid var(--ct-gray-100);
        }
        .reports-table tbody td {
            padding: 0.9rem 1rem;
            border-bottom: 1px solid var(--ct-gray-100);
            color: var(--ct-gray-800);
            vertical-align: middle;
        }
        .reports-table tbody tr:last-child td { border-bottom: none; }
        .reports-table .col-num { text-align: right; font-variant-numeric: tabular-nums; }
        .reports-table .col-bar { width: 160px; }

        .rider-rank {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px; height: 28px;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 700;
            background: var(--ct-gray-100);
            color: var(--ct-gray-700);
        }
        .rider-rank.rank-1 { background: rgba(245, 158, 11, 0.18); color: #b45309; }
        .rider-rank.rank-2 { background: rgba(100, 116, 139, 0.18); color: #475569; }
        .rider-rank.rank-3 { background: rgba(180, 83, 9, 0.12); color: #92400e; }

        .rider-cell {
            display: flex;
            align-items: center;
            gap: 0.65rem;
        }
        .rider-avatar {
            width: 34px; height: 34px;
            border-radius: 999px;
            background: linear-gradient(135deg, var(--ct-primary), var(--ct-gray-700));
            color: var(--ct-white);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 600;
            flex-shrink: 0;
        }
        .rider-name { font-weight: 600; color: var(--ct-gray-900); }
        .rider-email {
            font-size: 0.75rem;
            color: var(--ct-gray-500);
        }
        .rider-bar {
            height: 6px;
            background: var(--ct-gray-100);
            border-radius: 999px;
            overflow: hidden;
        }
        .rider-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--ct-accent), #65d419);
            border-radius: 999px;
        }

        /* Empty states */
        .reports-empty {
            padding: 3rem 1.5rem;
            text-align: center;
            color: var(--ct-gray-500);
        }
        .reports-empty i {
            font-size: 1.75rem;
            color: var(--ct-gray-400);
            margin-bottom: 0.5rem;
            display: block;
        }
        .reports-empty p { margin: 0; font-size: 0.875rem; }

        .ord-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.25rem 0.6rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
    </style>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            const labels = @json($revenueTrend['labels']);
            const revenue = @json($revenueTrend['revenueData']);
            const orders = @json($revenueTrend['ordersData']);

            const ctx = document.getElementById('revenueChart');
            if (!ctx) return;

            const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 280);
            gradient.addColorStop(0, 'rgba(132, 204, 22, 0.35)');
            gradient.addColorStop(1, 'rgba(132, 204, 22, 0)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Revenue (€)',
                            data: revenue,
                            borderColor: '#84cc16',
                            backgroundColor: gradient,
                            tension: 0.35,
                            fill: true,
                            pointRadius: 0,
                            pointHoverRadius: 5,
                            pointBackgroundColor: '#84cc16',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            borderWidth: 2.5,
                            yAxisID: 'y',
                        },
                        {
                            label: 'Orders',
                            data: orders,
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.08)',
                            borderDash: [4, 4],
                            tension: 0.35,
                            pointRadius: 0,
                            pointHoverRadius: 4,
                            borderWidth: 2,
                            yAxisID: 'y1',
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 10,
                                boxHeight: 10,
                                usePointStyle: true,
                                pointStyle: 'circle',
                                font: { size: 12, family: 'Inter, system-ui, sans-serif' },
                                color: '#475569',
                                padding: 16,
                            }
                        },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleColor: '#fff',
                            bodyColor: '#cbd5e1',
                            padding: 10,
                            cornerRadius: 8,
                            titleFont: { size: 12, family: 'Inter, system-ui, sans-serif' },
                            bodyFont: { size: 12, family: 'Inter, system-ui, sans-serif' },
                            callbacks: {
                                label: function (ctx) {
                                    if (ctx.dataset.label === 'Revenue (€)') {
                                        return ' €' + Number(ctx.parsed.y).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                    }
                                    return ' ' + ctx.parsed.y + ' orders';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { color: '#94a3b8', font: { size: 11, family: 'Inter, system-ui, sans-serif' } },
                        },
                        y: {
                            position: 'left',
                            grid: { color: '#f1f5f9' },
                            ticks: {
                                color: '#94a3b8',
                                font: { size: 11, family: 'Inter, system-ui, sans-serif' },
                                callback: function (v) { return '€' + v; }
                            },
                        },
                        y1: {
                            position: 'right',
                            grid: { drawOnChartArea: false },
                            ticks: {
                                color: '#94a3b8',
                                font: { size: 11, family: 'Inter, system-ui, sans-serif' },
                                precision: 0,
                            },
                        }
                    }
                }
            });
        })();
    </script>
@endsection
