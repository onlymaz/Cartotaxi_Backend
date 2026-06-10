@extends('layouts.cargotaxi')

@section('title')
    <title>Dashboard | {{ config('app.name', 'DispatchPro') }}</title>
@endsection

@section('breadcrumb')
    <span class="nova-breadcrumb-current">Dashboard</span>
@endsection

@section('content')
<!-- Welcome Section -->
<div class="nova-card mb-6" style="background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%); border: none;">
    <div class="nova-card-body" style="padding: 2rem;">
        <div class="flex items-center justify-between">
            <div>
                <h1 style="font-size: 1.75rem; font-weight: 700; color: white; margin-bottom: 0.5rem;">
                    Welcome back, {{ auth()->user()->first_name }}! 👋
                </h1>
                <p style="color: rgba(255,255,255,0.8); font-size: 1rem;">
                    Track your deliveries and manage your bookings from here.
                </p>
            </div>
            <a href="{{ route('customer.bookings') }}" class="nova-btn" style="background: white; color: var(--color-primary); font-weight: 600;">
                <i class="fas fa-plus"></i> New Booking
            </a>
        </div>
    </div>
</div>

<!-- Date Range Search -->
<div class="nova-card mb-6" style="padding: 1.5rem; background: white;">
    <form id="dateRangeForm" method="GET" action="{{ route('customer.dashboard') }}" style="display: flex; align-items: flex-end; gap: 1rem; flex-wrap: wrap;">
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

<!-- Stats Grid -->
<div class="nova-stats-grid mb-6" style="grid-template-columns: repeat(4, 1fr);">
    <!-- Active Orders -->
    <div class="nova-stat-card info">
        <div class="nova-stat-header">
            <div class="nova-stat-icon">
                <i class="fas fa-shipping-fast"></i>
            </div>
        </div>
        <div class="nova-stat-value">{{ $order_processing + $order_pending + $order_picking + $order_picked_up + $order_on_way }}</div>
        <div class="nova-stat-label">Active Orders</div>
    </div>
    
    <!-- Delivered -->
    <div class="nova-stat-card success">
        <div class="nova-stat-header">
            <div class="nova-stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
        <div class="nova-stat-value">{{ $order_delivered }}</div>
        <div class="nova-stat-label">Delivered</div>
    </div>
    
    <!-- Cancelled -->
    <div class="nova-stat-card danger">
        <div class="nova-stat-header">
            <div class="nova-stat-icon">
                <i class="fas fa-times-circle"></i>
            </div>
        </div>
        <div class="nova-stat-value">{{ $order_cancel }}</div>
        <div class="nova-stat-label">Cancelled</div>
    </div>
    
    <!-- Refused -->
    <div class="nova-stat-card warning">
        <div class="nova-stat-header">
            <div class="nova-stat-icon">
                <i class="fas fa-ban"></i>
            </div>
        </div>
        <div class="nova-stat-value">{{ $order_refused }}</div>
        <div class="nova-stat-label">Refused</div>
    </div>
</div>

<!-- Order Status Breakdown -->
<div class="nova-card mb-6">
    <div class="nova-card-header">
        <h3 class="nova-card-title">Order Status Breakdown</h3>
    </div>
    <div class="nova-card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <!-- Pending -->
            <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: var(--color-gray-50); border-radius: 0.75rem;">
                <div style="width: 48px; height: 48px; background: rgba(245, 158, 11, 0.1); border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; color: #f59e0b;">
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--color-gray-900);">{{ $order_pending }}</div>
                    <div style="font-size: 0.8125rem; color: var(--color-gray-500);">Pending</div>
                </div>
            </div>
            
            <!-- Processing -->
            <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: var(--color-gray-50); border-radius: 0.75rem;">
                <div style="width: 48px; height: 48px; background: rgba(59, 130, 246, 0.1); border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; color: #3b82f6;">
                    <i class="fas fa-cog fa-spin"></i>
                </div>
                <div>
                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--color-gray-900);">{{ $order_processing }}</div>
                    <div style="font-size: 0.8125rem; color: var(--color-gray-500);">Processing</div>
                </div>
            </div>
            
            <!-- Picking -->
            <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: var(--color-gray-50); border-radius: 0.75rem;">
                <div style="width: 48px; height: 48px; background: rgba(139, 92, 246, 0.1); border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; color: #8b5cf6;">
                    <i class="fas fa-hand-paper"></i>
                </div>
                <div>
                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--color-gray-900);">{{ $order_picking }}</div>
                    <div style="font-size: 0.8125rem; color: var(--color-gray-500);">Picking</div>
                </div>
            </div>
            
            <!-- Picked Up -->
            <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: var(--color-gray-50); border-radius: 0.75rem;">
                <div style="width: 48px; height: 48px; background: rgba(16, 185, 129, 0.1); border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; color: #10b981;">
                    <i class="fas fa-box"></i>
                </div>
                <div>
                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--color-gray-900);">{{ $order_picked_up }}</div>
                    <div style="font-size: 0.8125rem; color: var(--color-gray-500);">Picked Up</div>
                </div>
            </div>
            
            <!-- On The Way -->
            <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: var(--color-gray-50); border-radius: 0.75rem;">
                <div style="width: 48px; height: 48px; background: rgba(34, 197, 94, 0.1); border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; color: #22c55e;">
                    <i class="fas fa-truck"></i>
                </div>
                <div>
                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--color-gray-900);">{{ $order_on_way }}</div>
                    <div style="font-size: 0.8125rem; color: var(--color-gray-500);">On The Way</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="nova-card">
    <div class="nova-card-header">
        <h3 class="nova-card-title">Quick Actions</h3>
    </div>
    <div class="nova-card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
            <!-- New Booking -->
            <a href="{{ route('customer.bookings') }}" style="text-decoration: none;">
                <div style="padding: 1.5rem; background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%); border-radius: 1rem; color: white; transition: transform 0.2s, box-shadow 0.2s;">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="width: 56px; height: 56px; background: rgba(255,255,255,0.2); border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                            <i class="fas fa-plus"></i>
                        </div>
                        <div>
                            <div style="font-size: 1.125rem; font-weight: 600;">New Booking</div>
                            <div style="font-size: 0.875rem; opacity: 0.8;">Create a new delivery order</div>
                        </div>
                    </div>
                </div>
            </a>
            
            <!-- My Bookings -->
            <a href="{{ route('customer.mybookings') }}" style="text-decoration: none;">
                <div style="padding: 1.5rem; background: var(--color-gray-50); border: 1px solid var(--color-gray-200); border-radius: 1rem; transition: transform 0.2s, box-shadow 0.2s;">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="width: 56px; height: 56px; background: rgba(59, 130, 246, 0.1); border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: #3b82f6;">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <div>
                            <div style="font-size: 1.125rem; font-weight: 600; color: var(--color-gray-900);">{{ __('messages.my_bookings') }}</div>
                            <div style="font-size: 0.875rem; color: var(--color-gray-500);">View all your orders</div>
                        </div>
                    </div>
                </div>
            </a>
            
            <!-- Request Helper -->
            <a href="{{ route('helper.index') }}" style="text-decoration: none;">
                <div style="padding: 1.5rem; background: var(--color-gray-50); border: 1px solid var(--color-gray-200); border-radius: 1rem; transition: transform 0.2s, box-shadow 0.2s;">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="width: 56px; height: 56px; background: rgba(245, 158, 11, 0.1); border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: #f59e0b;">
                            <i class="fas fa-hands-helping"></i>
                        </div>
                        <div>
                            <div style="font-size: 1.125rem; font-weight: 600; color: var(--color-gray-900);">Request Helper</div>
                            <div style="font-size: 0.875rem; color: var(--color-gray-500);">Get extra help for your move</div>
                        </div>
                    </div>
                </div>
            </a>
            
            <!-- Profile -->
            <a href="{{ route('settings.profile_view', auth()->user()->id) }}" style="text-decoration: none;">
                <div style="padding: 1.5rem; background: var(--color-gray-50); border: 1px solid var(--color-gray-200); border-radius: 1rem; transition: transform 0.2s, box-shadow 0.2s;">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="width: 56px; height: 56px; background: rgba(139, 92, 246, 0.1); border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: #8b5cf6;">
                            <i class="fas fa-user-cog"></i>
                        </div>
                        <div>
                            <div style="font-size: 1.125rem; font-weight: 600; color: var(--color-gray-900);">{{ __('messages.profile') }}</div>
                            <div style="font-size: 0.875rem; color: var(--color-gray-500);">Manage your account</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
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
            window.location.href = '{{ route("customer.dashboard") }}';
        });
    });
</script>
@endsection

@section('styles')
<style>
    /* Hover effects for action cards */
    .nova-card-body a > div:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1);
    }
    
    /* Responsive grid */
    @media (max-width: 1024px) {
        .nova-stats-grid[style*="repeat(4, 1fr)"] {
            grid-template-columns: repeat(2, 1fr) !important;
        }
    }
    
    @media (max-width: 640px) {
        .nova-stats-grid[style*="repeat(4, 1fr)"] {
            grid-template-columns: 1fr !important;
        }
    }
</style>
@endsection

