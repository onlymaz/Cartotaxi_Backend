@extends('layouts.cargotaxi')

@section('title')
    <title>Dashboard | {{ config('app.name', 'DispatchPro') }}</title>
@endsection

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Home</a>
    <span class="nova-breadcrumb-separator">/</span>
    <span class="nova-breadcrumb-current">Dashboard</span>
@endsection

@section('content')
<!-- Page Header -->
<div class="nova-page-header">
    <div>
        <h1 class="nova-page-title">Welcome back, {{ auth()->user()->first_name }}! 👋</h1>
        <p class="nova-page-subtitle">Here's what's happening with your business today.</p>
    </div>
    <div class="nova-page-actions">
        <a href="{{ route('dispatcher.index') }}" class="nova-btn nova-btn-primary">
            <i class="fas fa-plus"></i> New Booking
        </a>
    </div>
</div>

<!-- Date Filter -->
<div class="nova-card mb-6">
    <div class="nova-card-body">
        <form class="flex flex-wrap items-end gap-4" id="dateFilterForm">
            <div style="flex: 1; min-width: 200px;">
                <label class="nova-label">Start Date</label>
                <input type="text" id="datepicker1" name="from" class="nova-input" placeholder="Select start date">
            </div>
            <div style="flex: 1; min-width: 200px;">
                <label class="nova-label">End Date</label>
                <input type="text" id="datepicker2" name="to" class="nova-input" placeholder="Select end date">
            </div>
            <div>
                <button type="submit" class="nova-btn nova-btn-secondary">
                    <i class="fas fa-filter"></i> Apply Filter
                </button>
            </div>
            <div>
                <button type="button" class="nova-btn nova-btn-ghost" onclick="clearFilters()">
                    <i class="fas fa-times"></i> Clear
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Primary Stats -->
<div class="nova-stats-grid mb-6">
    <!-- Revenue Card - Featured -->
    <div class="nova-stat-card nova-stat-featured animate-slideUp">
        <div class="nova-stat-header">
            <div class="nova-stat-icon">
                <i class="fas fa-euro-sign"></i>
            </div>
            <div class="nova-stat-trend up">
                <i class="fas fa-arrow-up"></i> 12%
            </div>
        </div>
        <div class="nova-stat-value">{{ env('CURRENCY_SYMBOL', '€') }}{{ number_format((float)$stats['revenue'], 2) }}</div>
        <div class="nova-stat-label">Total Revenue</div>
    </div>
    
    <!-- Total Customers -->
    <a href="{{ route('users.index', ['type' => 'customer']) }}" style="text-decoration: none;">
        <div class="nova-stat-card primary animate-slideUp">
            <div class="nova-stat-header">
                <div class="nova-stat-icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div class="nova-stat-value">{{ number_format($stats['total_customers']) }}</div>
            <div class="nova-stat-label">{{ __('messages.total_customer') }}</div>
        </div>
    </a>
    
    <!-- Total Drivers -->
    <a href="{{ route('users.index', ['type' => 'rider']) }}" style="text-decoration: none;">
        <div class="nova-stat-card warning animate-slideUp">
            <div class="nova-stat-header">
                <div class="nova-stat-icon">
                    <i class="fas fa-motorcycle"></i>
                </div>
            </div>
            <div class="nova-stat-value">{{ number_format($stats['total_riders']) }}</div>
            <div class="nova-stat-label">{{ __('messages.total_driver') }}</div>
        </div>
    </a>
    
    <!-- Delivered -->
    <a href="{{ route('bookings.index', ['status' => 'delivered']) }}" style="text-decoration: none;">
        <div class="nova-stat-card success animate-slideUp">
            <div class="nova-stat-header">
                <div class="nova-stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <div class="nova-stat-value">{{ number_format($stats['total_delivered']) }}</div>
            <div class="nova-stat-label">{{ __('messages.total_drivery') }}</div>
        </div>
    </a>
</div>

<!-- Secondary Stats Row -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem;">
    <!-- Verified Customers -->
    <div class="nova-card" style="padding: 1.25rem;">
        <div class="flex items-center gap-3">
            <div style="width: 44px; height: 44px; background: rgba(34, 197, 94, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #22c55e;">
                <i class="fas fa-user-check"></i>
            </div>
            <div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--color-gray-900);">{{ $stats['verified_customers'] }}</div>
                <div style="font-size: 0.8125rem; color: var(--color-gray-500);">{{ __('messages.verify_customer') }}</div>
            </div>
        </div>
    </div>
    
    <!-- Unverified Customers -->
    <div class="nova-card" style="padding: 1.25rem;">
        <div class="flex items-center gap-3">
            <div style="width: 44px; height: 44px; background: rgba(239, 68, 68, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #ef4444;">
                <i class="fas fa-user-times"></i>
            </div>
            <div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--color-gray-900);">{{ $stats['unverified_customers'] }}</div>
                <div style="font-size: 0.8125rem; color: var(--color-gray-500);">{{ __('messages.unverify_customer') }}</div>
            </div>
        </div>
    </div>
    
    <!-- On Way -->
    <div class="nova-card" style="padding: 1.25rem;">
        <div class="flex items-center gap-3">
            <div style="width: 44px; height: 44px; background: rgba(59, 130, 246, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #3b82f6;">
                <i class="fas fa-shipping-fast"></i>
            </div>
            <div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--color-gray-900);">{{ $stats['on_way_bookings'] }}</div>
                <div style="font-size: 0.8125rem; color: var(--color-gray-500);">{{ __('messages.on_the_why_delivery') }}</div>
            </div>
        </div>
    </div>
    
    <!-- Cancelled -->
    <div class="nova-card" style="padding: 1.25rem;">
        <div class="flex items-center gap-3">
            <div style="width: 44px; height: 44px; background: rgba(239, 68, 68, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #ef4444;">
                <i class="fas fa-ban"></i>
            </div>
            <div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--color-gray-900);">{{ $stats['total_cancelled_bookings'] }}</div>
                <div style="font-size: 0.8125rem; color: var(--color-gray-500);">{{ __('messages.total_cancel_delivery') }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Charts and Tables Row -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
    <!-- Revenue Chart -->
    <div class="nova-card">
        <div class="nova-card-header">
            <h3 class="nova-card-title">Revenue by Payment Method</h3>
        </div>
        <div class="nova-card-body">
            <canvas id="revenueChart" height="260"></canvas>
        </div>
    </div>
    
    <!-- Payment Methods Breakdown -->
    <div class="nova-card">
        <div class="nova-card-header">
            <h3 class="nova-card-title">Payment Methods</h3>
        </div>
        <div class="nova-card-body">
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <!-- COD -->
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-money-bill-wave" style="color: #22c55e;"></i>
                            <span style="font-weight: 500;">Cash on Delivery</span>
                        </div>
                        <span style="font-weight: 600;">{{ env('CURRENCY_SYMBOL', '€') }}{{ number_format((float)$stats['COD'], 2) }}</span>
                    </div>
                    <div class="nova-progress">
                        <div class="nova-progress-bar" style="width: {{ $stats['revenue'] > 0 ? ($stats['COD'] / $stats['revenue'] * 100) : 0 }}%; background: #22c55e;"></div>
                    </div>
                </div>
                
                <!-- PayPal -->
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <div class="flex items-center gap-2">
                            <i class="fab fa-paypal" style="color: #3b82f6;"></i>
                            <span style="font-weight: 500;">PayPal</span>
                        </div>
                        <span style="font-weight: 600;">{{ env('CURRENCY_SYMBOL', '€') }}{{ number_format((float)$stats['paypal'], 2) }}</span>
                    </div>
                    <div class="nova-progress">
                        <div class="nova-progress-bar" style="width: {{ $stats['revenue'] > 0 ? ($stats['paypal'] / $stats['revenue'] * 100) : 0 }}%; background: #3b82f6;"></div>
                    </div>
                </div>
                
                <!-- Stripe -->
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <div class="flex items-center gap-2">
                            <i class="fab fa-stripe" style="color: #6366f1;"></i>
                            <span style="font-weight: 500;">Stripe</span>
                        </div>
                        <span style="font-weight: 600;">{{ env('CURRENCY_SYMBOL', '€') }}{{ number_format((float)$stats['stripe'], 2) }}</span>
                    </div>
                    <div class="nova-progress">
                        <div class="nova-progress-bar" style="width: {{ $stats['revenue'] > 0 ? ($stats['stripe'] / $stats['revenue'] * 100) : 0 }}%; background: #6366f1;"></div>
                    </div>
                </div>
                
                <!-- Weekly -->
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-calendar-week" style="color: #a855f7;"></i>
                            <span style="font-weight: 500;">Weekly Invoice</span>
                        </div>
                        <span style="font-weight: 600;">{{ env('CURRENCY_SYMBOL', '€') }}{{ number_format((float)$stats['weekly'], 2) }}</span>
                    </div>
                    <div class="nova-progress">
                        <div class="nova-progress-bar" style="width: {{ $stats['revenue'] > 0 ? ($stats['weekly'] / $stats['revenue'] * 100) : 0 }}%; background: #a855f7;"></div>
                    </div>
                </div>
            </div>
            
            <!-- Pending Revenue Alert -->
            @if($stats['pending_revenue'] > 0)
            <div class="nova-alert nova-alert-warning" style="margin-top: 1.5rem;">
                <i class="fas fa-hourglass-half nova-alert-icon"></i>
                <div class="nova-alert-content">
                    <strong>{{ env('CURRENCY_SYMBOL', '€') }}{{ number_format((float)$stats['pending_revenue'], 2) }}</strong> pending payment
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Quick Stats Row -->
<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 1.5rem;">
    <a href="{{ route('bookings.index', ['status' => 'pending']) }}" class="nova-card" style="padding: 1.25rem; text-decoration: none; transition: transform 0.2s;">
        <div class="flex items-center justify-between">
            <div>
                <div style="font-size: 0.8125rem; color: var(--color-gray-500); margin-bottom: 0.25rem;">Scheduled</div>
                <div style="font-size: 1.75rem; font-weight: 700; color: var(--color-gray-900);">{{ $stats['scheduled_bookings'] }}</div>
            </div>
            <div style="width: 48px; height: 48px; background: rgba(245, 158, 11, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #f59e0b; font-size: 1.25rem;">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </a>
    
    <a href="{{ route('bookings.index', ['status' => 'accident']) }}" class="nova-card" style="padding: 1.25rem; text-decoration: none;">
        <div class="flex items-center justify-between">
            <div>
                <div style="font-size: 0.8125rem; color: var(--color-gray-500); margin-bottom: 0.25rem;">{{ __('messages.met_with_accident') }}</div>
                <div style="font-size: 1.75rem; font-weight: 700; color: var(--color-gray-900);">{{ $stats['total_accident_bookings'] }}</div>
            </div>
            <div style="width: 48px; height: 48px; background: rgba(239, 68, 68, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #ef4444; font-size: 1.25rem;">
                <i class="fas fa-car-crash"></i>
            </div>
        </div>
    </a>
    
    <a href="{{ route('bookings.index', ['payment_status' => 'pending']) }}" class="nova-card" style="padding: 1.25rem; text-decoration: none;">
        <div class="flex items-center justify-between">
            <div>
                <div style="font-size: 0.8125rem; color: var(--color-gray-500); margin-bottom: 0.25rem;">{{ __('messages.pending_revenue') }}</div>
                <div style="font-size: 1.75rem; font-weight: 700; color: var(--color-gray-900);">{{ env('CURRENCY_SYMBOL', '€') }}{{ number_format((float)$stats['pending_revenue'], 0) }}</div>
            </div>
            <div style="width: 48px; height: 48px; background: rgba(245, 158, 11, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #f59e0b; font-size: 1.25rem;">
                <i class="fas fa-hourglass-half"></i>
            </div>
        </div>
    </a>
</div>

<!-- Recent Bookings Table -->
<div class="nova-card">
    <div class="nova-card-header">
        <h3 class="nova-card-title">{{ __('messages.recent_bookings') }}</h3>
        <a href="{{ route('bookings.index') }}" class="nova-btn nova-btn-ghost nova-btn-sm">
            View All <i class="fas fa-arrow-right" style="margin-left: 0.5rem;"></i>
        </a>
    </div>
    <div class="nova-table-wrapper">
        <table class="nova-table">
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
                    <td style="color: var(--color-gray-500);">{{ $key + 1 }}</td>
                    <td>
                        <div class="flex items-center gap-3">
                            @if($order->user && $order->user->profile_image)
                                <img src="{{ url($order->user->profile_image) }}" class="nova-avatar nova-avatar-sm">
                            @else
                                <div class="nova-avatar nova-avatar-sm" style="background: var(--color-primary-50); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-weight: 600;">
                                    {{ $order->user ? strtoupper(substr($order->user->first_name, 0, 1)) : '?' }}
                                </div>
                            @endif
                            <div>
                                <div style="font-weight: 500; color: var(--color-gray-900);">
                                    {{ $order->user ? $order->user->first_name . ' ' . $order->user->last_name : 'N/A' }}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <code style="background: var(--color-gray-100); padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8125rem;">
                            {{ $order->booking_id ?? 'N/A' }}
                        </code>
                    </td>
                    <td>
                        <div style="color: var(--color-gray-500); font-size: 0.875rem;">
                            <i class="far fa-clock" style="margin-right: 0.25rem;"></i>
                            {{ (new \Carbon\Carbon($order->picked_time))->diffForHumans() }}
                        </div>
                    </td>
                    <td>
                        @switch($order->order_status)
                            @case('pending')
                                <span class="nova-badge nova-badge-warning"><i class="fas fa-clock"></i> Pending</span>
                                @break
                            @case('processing')
                                <span class="nova-badge nova-badge-info"><i class="fas fa-cog fa-spin"></i> Processing</span>
                                @break
                            @case('picked_up')
                                <span class="nova-badge nova-badge-info"><i class="fas fa-box"></i> Picked Up</span>
                                @break
                            @case('on_way')
                                <span class="nova-badge nova-badge-primary"><i class="fas fa-truck"></i> On the Way</span>
                                @break
                            @case('delivered')
                                <span class="nova-badge nova-badge-success"><i class="fas fa-check"></i> Delivered</span>
                                @break
                            @case('cancel')
                                <span class="nova-badge nova-badge-danger"><i class="fas fa-times"></i> Cancelled</span>
                                @break
                            @case('accident')
                                <span class="nova-badge nova-badge-danger"><i class="fas fa-exclamation-triangle"></i> Accident</span>
                                @break
                            @case('refused')
                                <span class="nova-badge nova-badge-danger"><i class="fas fa-ban"></i> Refused</span>
                                @break
                            @case('picking')
                                <span class="nova-badge nova-badge-info"><i class="fas fa-hand-paper"></i> Picking</span>
                                @break
                            @default
                                <span class="nova-badge nova-badge-gray">{{ $order->order_status }}</span>
                        @endswitch
                    </td>
                    <td>
                        <button class="nova-btn nova-btn-ghost nova-btn-sm popup" data-url="{{ route('bookings.show', $order->id) }}" data-type="view">
                            <i class="fas fa-eye"></i> View
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 3rem;">
                        <div style="color: var(--color-gray-400);">
                            <i class="fas fa-inbox" style="font-size: 2.5rem; margin-bottom: 1rem;"></i>
                            <p>No bookings found</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($orders->hasPages())
    <div class="nova-card-footer">
        {{ $orders->links() }}
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    // Initialize datepickers
    $(function() {
        $("#datepicker1").datepicker({
            dateFormat: 'dd-M-yy',
            changeMonth: true,
            changeYear: true,
            onClose: function(selected) {
                if (selected) {
                    $("#datepicker2").datepicker("option", "minDate", selected);
                }
            }
        });
        
        $("#datepicker2").datepicker({
            dateFormat: 'dd-M-yy',
            changeMonth: true,
            changeYear: true,
            onClose: function(selected) {
                if (selected) {
                    $("#datepicker1").datepicker("option", "maxDate", selected);
                }
            }
        });
    });
    
    function clearFilters() {
        $('#datepicker1').val('');
        $('#datepicker2').val('');
        window.location.href = '{{ route("admin.dashboard") }}';
    }
    
    // Revenue Chart
    const ctx = document.getElementById('revenueChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['COD', 'PayPal', 'Stripe', 'Weekly'],
                datasets: [{
                    data: [
                        {{ $stats['COD'] }},
                        {{ $stats['paypal'] }},
                        {{ $stats['stripe'] }},
                        {{ $stats['weekly'] }}
                    ],
                    backgroundColor: [
                        '#22c55e',
                        '#3b82f6',
                        '#6366f1',
                        '#a855f7'
                    ],
                    borderWidth: 0,
                    hoverOffset: 10
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
                            font: {
                                family: "'Plus Jakarta Sans', sans-serif",
                                size: 13
                            }
                        }
                    }
                }
            }
        });
    }
</script>
@endsection

@section('styles')
<style>
    /* Responsive grid adjustments */
    @media (max-width: 1024px) {
        [style*="grid-template-columns: 1fr 1fr"] {
            grid-template-columns: 1fr !important;
        }
        [style*="grid-template-columns: repeat(3, 1fr)"] {
            grid-template-columns: 1fr !important;
        }
        [style*="grid-template-columns: repeat(4, 1fr)"] {
            grid-template-columns: repeat(2, 1fr) !important;
        }
    }
    
    @media (max-width: 640px) {
        [style*="grid-template-columns: repeat(2, 1fr)"] {
            grid-template-columns: 1fr !important;
        }
    }
    
    /* Hover effects for stat cards */
    .nova-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }
</style>
@endsection

