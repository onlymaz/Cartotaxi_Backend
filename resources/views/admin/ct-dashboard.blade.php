@extends('layouts.cargotaxi')

@section('title')
    <title>Dashboard | Cargo Taxi</title>
@endsection

@section('breadcrumb')
    <span class="ct-breadcrumb-current">Dashboard</span>
@endsection

@section('content')
<!-- Page Header -->
<div class="ct-page-header">
    <div>
        <h1 class="ct-page-title">Welcome back, {{ auth()->user()->first_name }}! 👋</h1>
        <p class="ct-page-subtitle">Here's what's happening with your business today.</p>
    </div>
    <div class="ct-page-actions">
        <a href="{{ route('dispatcher.index') }}" class="ct-btn ct-btn-primary">
            <i class="fas fa-plus"></i> New Booking
        </a>
    </div>
</div>

<!-- Date Range Search -->
<div class="ct-card ct-mb-6" style="padding: 1.5rem; background: white;">
    <form id="dateRangeForm" method="GET" action="{{ route('admin.dashboard') }}" style="display: flex; align-items: flex-end; gap: 1rem; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 200px;">
            <label class="nova-label" style="margin-bottom: 0.5rem; display: block; font-size: 0.875rem; font-weight: 500; color: #475569;">Start Date</label>
            <input type="text" name="start_date" id="start_date" class="nova-input datepicker" placeholder="Enter Start Date" value="{{ request('start_date') }}" autocomplete="off" style="width: 100%; height: 42px;">
        </div>
        <div style="flex: 1; min-width: 200px;">
            <label class="nova-label" style="margin-bottom: 0.5rem; display: block; font-size: 0.875rem; font-weight: 500; color: #475569;">End Date</label>
            <input type="text" name="end_date" id="end_date" class="nova-input datepicker" placeholder="Enter End Date" value="{{ request('end_date') }}" autocomplete="off" style="width: 100%; height: 42px;">
        </div>
        <div style="display: flex; gap: 0.75rem; align-items: flex-end;">
            <button type="submit" class="nova-btn nova-btn-primary" style="height: 42px; white-space: nowrap; padding: 0 1.25rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-search"></i> Submit
            </button>
            <button type="button" id="clearDates" class="nova-btn nova-btn-secondary" style="height: 42px; white-space: nowrap; padding: 0 1.25rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-times"></i> Clear
            </button>
        </div>
    </form>
</div>

<!-- Main Stats -->
<div class="ct-stats-grid ct-mb-6">
    <!-- Revenue -->
    <div class="ct-stat-card" style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); --stat-color: #84cc16;">
        <div class="ct-stat-header">
            <div class="ct-stat-icon" style="background: rgba(132, 204, 22, 0.2); color: #84cc16;">
                <i class="fas fa-euro-sign"></i>
            </div>
        </div>
        <div class="ct-stat-value" style="color: white;">{{ env('CURRENCY_SYMBOL', '€') }}{{ number_format((float)$stats['revenue'], 2) }}</div>
        <div class="ct-stat-label" style="color: rgba(255,255,255,0.7);">Total Revenue</div>
    </div>
    
    <!-- Customers -->
    <a href="{{ route('users.index', ['type' => 'customer']) }}" style="text-decoration: none;">
        <div class="ct-stat-card" style="--stat-color: #84cc16; --stat-bg: rgba(132, 204, 22, 0.1);">
            <div class="ct-stat-header">
                <div class="ct-stat-icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div class="ct-stat-value">{{ number_format($stats['total_customers']) }}</div>
            <div class="ct-stat-label">Total Customers</div>
        </div>
    </a>
    
    <!-- Drivers -->
    <a href="{{ route('users.index', ['type' => 'rider']) }}" style="text-decoration: none;">
        <div class="ct-stat-card info">
            <div class="ct-stat-header">
                <div class="ct-stat-icon">
                    <i class="fas fa-truck"></i>
                </div>
            </div>
            <div class="ct-stat-value">{{ number_format($stats['total_riders']) }}</div>
            <div class="ct-stat-label">Total Drivers</div>
        </div>
    </a>
    
    <!-- Delivered -->
    <a href="{{ route('bookings.index', ['status' => 'delivered']) }}" style="text-decoration: none;">
        <div class="ct-stat-card success">
            <div class="ct-stat-header">
                <div class="ct-stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <div class="ct-stat-value">{{ number_format($stats['total_delivered']) }}</div>
            <div class="ct-stat-label">Delivered</div>
        </div>
    </a>
</div>

<!-- Secondary Stats -->
<div class="ct-grid ct-grid-4 ct-gap-4 ct-mb-6" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem;">
    <div class="ct-card" style="padding: 1.25rem;">
        <div class="ct-flex ct-items-center ct-gap-3" style="display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 44px; height: 44px; background: rgba(16, 185, 129, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #10b981;">
                <i class="fas fa-user-check"></i>
            </div>
            <div>
                <div style="font-size: 1.5rem; font-weight: 700; color: #0f172a;">{{ $stats['verified_customers'] }}</div>
                <div style="font-size: 0.8125rem; color: #64748b;">Verified</div>
            </div>
        </div>
    </div>
    
    <div class="ct-card" style="padding: 1.25rem;">
        <div class="ct-flex ct-items-center ct-gap-3" style="display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 44px; height: 44px; background: rgba(239, 68, 68, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #ef4444;">
                <i class="fas fa-user-times"></i>
            </div>
            <div>
                <div style="font-size: 1.5rem; font-weight: 700; color: #0f172a;">{{ $stats['unverified_customers'] }}</div>
                <div style="font-size: 0.8125rem; color: #64748b;">Unverified</div>
            </div>
        </div>
    </div>
    
    <div class="ct-card" style="padding: 1.25rem;">
        <div class="ct-flex ct-items-center ct-gap-3" style="display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 44px; height: 44px; background: rgba(59, 130, 246, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #3b82f6;">
                <i class="fas fa-shipping-fast"></i>
            </div>
            <div>
                <div style="font-size: 1.5rem; font-weight: 700; color: #0f172a;">{{ $stats['on_way_bookings'] }}</div>
                <div style="font-size: 0.8125rem; color: #64748b;">On The Way</div>
            </div>
        </div>
    </div>
    
    <div class="ct-card" style="padding: 1.25rem;">
        <div class="ct-flex ct-items-center ct-gap-3" style="display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 44px; height: 44px; background: rgba(239, 68, 68, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #ef4444;">
                <i class="fas fa-ban"></i>
            </div>
            <div>
                <div style="font-size: 1.5rem; font-weight: 700; color: #0f172a;">{{ $stats['total_cancelled_bookings'] }}</div>
                <div style="font-size: 0.8125rem; color: #64748b;">Cancelled</div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
    <!-- Revenue Chart -->
    <div class="ct-card">
        <div class="ct-card-header">
            <h3 class="ct-card-title">Revenue Breakdown</h3>
        </div>
        <div class="ct-card-body">
            <canvas id="revenueChart" height="280"></canvas>
        </div>
    </div>
    
    <!-- Payment Methods -->
    <div class="ct-card">
        <div class="ct-card-header">
            <h3 class="ct-card-title">Payment Methods</h3>
        </div>
        <div class="ct-card-body">
            <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                <div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span style="display: flex; align-items: center; gap: 0.5rem; font-weight: 500;">
                            <i class="fas fa-money-bill-wave" style="color: #10b981;"></i> Cash on Delivery
                        </span>
                        <span style="font-weight: 600;">€{{ number_format((float)$stats['COD'], 2) }}</span>
                    </div>
                    <div class="ct-progress"><div class="ct-progress-bar" style="width: {{ $stats['revenue'] > 0 ? ($stats['COD'] / $stats['revenue'] * 100) : 0 }}%; background: #10b981;"></div></div>
                </div>
                
                <div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span style="display: flex; align-items: center; gap: 0.5rem; font-weight: 500;">
                            <i class="fab fa-paypal" style="color: #3b82f6;"></i> PayPal
                        </span>
                        <span style="font-weight: 600;">€{{ number_format((float)$stats['paypal'], 2) }}</span>
                    </div>
                    <div class="ct-progress"><div class="ct-progress-bar" style="width: {{ $stats['revenue'] > 0 ? ($stats['paypal'] / $stats['revenue'] * 100) : 0 }}%; background: #3b82f6;"></div></div>
                </div>
                
                <div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span style="display: flex; align-items: center; gap: 0.5rem; font-weight: 500;">
                            <i class="fab fa-stripe" style="color: #6366f1;"></i> Stripe
                        </span>
                        <span style="font-weight: 600;">€{{ number_format((float)$stats['stripe'], 2) }}</span>
                    </div>
                    <div class="ct-progress"><div class="ct-progress-bar" style="width: {{ $stats['revenue'] > 0 ? ($stats['stripe'] / $stats['revenue'] * 100) : 0 }}%; background: #6366f1;"></div></div>
                </div>
                
                <div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span style="display: flex; align-items: center; gap: 0.5rem; font-weight: 500;">
                            <i class="fas fa-calendar-week" style="color: #84cc16;"></i> Weekly Invoice
                        </span>
                        <span style="font-weight: 600;">€{{ number_format((float)$stats['weekly'], 2) }}</span>
                    </div>
                    <div class="ct-progress"><div class="ct-progress-bar" style="width: {{ $stats['revenue'] > 0 ? ($stats['weekly'] / $stats['revenue'] * 100) : 0 }}%; background: #84cc16;"></div></div>
                </div>
            </div>
            
            @if($stats['pending_revenue'] > 0)
            <div class="ct-alert ct-alert-warning" style="margin-top: 1.5rem;">
                <i class="fas fa-hourglass-half ct-alert-icon"></i>
                <div><strong>€{{ number_format((float)$stats['pending_revenue'], 2) }}</strong> pending payment</div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Recent Bookings -->
<div class="ct-card">
    <div class="ct-card-header">
        <h3 class="ct-card-title">Recent Bookings</h3>
        <a href="{{ route('bookings.index') }}" class="ct-btn ct-btn-ghost ct-btn-sm">
            View All <i class="fas fa-arrow-right" style="margin-left: 0.5rem;"></i>
        </a>
    </div>
    <div class="ct-table-wrapper">
        <table class="ct-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Customer</th>
                    <th>Booking ID</th>
                    <th>Time</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $key => $order)
                <tr>
                    <td style="color: #64748b;">{{ $key + 1 }}</td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            @if($order->user && $order->user->profile_image)
                                <img src="{{ url($order->user->profile_image) }}" class="ct-avatar ct-avatar-sm">
                            @else
                                <div class="ct-avatar ct-avatar-sm" style="background: #84cc16; display: flex; align-items: center; justify-content: center; color: #1e293b; font-weight: 600; font-size: 0.75rem;">
                                    {{ $order->user ? strtoupper(substr($order->user->first_name, 0, 1)) : '?' }}
                                </div>
                            @endif
                            <span style="font-weight: 500;">{{ $order->user ? $order->user->first_name . ' ' . $order->user->last_name : 'N/A' }}</span>
                        </div>
                    </td>
                    <td>
                        <code style="background: #f1f5f9; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8125rem;">{{ $order->booking_id ?? 'N/A' }}</code>
                    </td>
                    <td style="color: #64748b; font-size: 0.875rem;">
                        <i class="far fa-clock" style="margin-right: 0.25rem;"></i>
                        {{ (new \Carbon\Carbon($order->picked_time))->diffForHumans() }}
                    </td>
                    <td>
                        @switch($order->order_status)
                            @case('pending')
                                <span class="ct-badge ct-badge-warning"><i class="fas fa-clock"></i> Pending</span>
                                @break
                            @case('processing')
                                <span class="ct-badge ct-badge-info"><i class="fas fa-cog"></i> Processing</span>
                                @break
                            @case('on_way')
                                <span class="ct-badge ct-badge-accent"><i class="fas fa-truck"></i> On Way</span>
                                @break
                            @case('delivered')
                                <span class="ct-badge ct-badge-success"><i class="fas fa-check"></i> Delivered</span>
                                @break
                            @case('cancel')
                                <span class="ct-badge ct-badge-danger"><i class="fas fa-times"></i> Cancelled</span>
                                @break
                            @default
                                <span class="ct-badge ct-badge-gray">{{ $order->order_status }}</span>
                        @endswitch
                    </td>
                    <td>
                        <button class="ct-btn ct-btn-ghost ct-btn-sm popup" data-url="{{ route('bookings.show', $order->id) }}" data-type="view">
                            <i class="fas fa-eye"></i> View
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 3rem; color: #94a3b8;">
                        <i class="fas fa-inbox" style="font-size: 2rem; margin-bottom: 0.5rem;"></i>
                        <p>No bookings found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Initialize datepickers
    $(document).ready(function() {
        $('.datepicker').datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true,
            yearRange: '2020:2030',
            showButtonPanel: true
        });
        
        // Clear dates button
        $('#clearDates').on('click', function() {
            $('#start_date').val('');
            $('#end_date').val('');
            window.location.href = '{{ route("admin.dashboard") }}';
        });
    });
    
    // Revenue Chart
    const ctx = document.getElementById('revenueChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['COD', 'PayPal', 'Stripe', 'Weekly'],
                datasets: [{
                    data: [{{ $stats['COD'] }}, {{ $stats['paypal'] }}, {{ $stats['stripe'] }}, {{ $stats['weekly'] }}],
                    backgroundColor: ['#10b981', '#3b82f6', '#6366f1', '#84cc16'],
                    borderWidth: 0,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            font: { family: "'Inter', sans-serif", size: 13 }
                        }
                    }
                }
            }
        });
    }
</script>

<style>
    @media (max-width: 1024px) {
        [style*="grid-template-columns: 1fr 1fr"],
        [style*="grid-template-columns: repeat(4, 1fr)"] {
            grid-template-columns: 1fr !important;
        }
    }
</style>
@endsection

