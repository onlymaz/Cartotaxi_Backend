@extends('layouts.cargotaxi')

@section('title')
    <title>Driver Dashboard | {{ config('app.name', 'DispatchPro') }}</title>
@endsection

@section('breadcrumb')
    <span class="nova-breadcrumb-current">Dashboard</span>
@endsection

@section('content')
<!-- Welcome Section -->
<div class="nova-card mb-6" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border: none;">
    <div class="nova-card-body" style="padding: 2rem;">
        <div class="flex items-center justify-between">
            <div>
                <h1 style="font-size: 1.75rem; font-weight: 700; color: white; margin-bottom: 0.5rem;">
                    Hello, {{ auth()->user()->first_name }}! 🚚
                </h1>
                <p style="color: rgba(255,255,255,0.7); font-size: 1rem;">
                    Here's your delivery overview for today.
                </p>
            </div>
            <div style="text-align: right;">
                <div style="font-size: 0.875rem; color: rgba(255,255,255,0.6);">Today's Date</div>
                <div style="font-size: 1.25rem; font-weight: 600; color: white;">{{ now()->format('D, M d, Y') }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Date Range Search -->
<div class="nova-card mb-6" style="padding: 1.5rem; background: white;">
    <form id="dateRangeForm" method="GET" action="{{ route('rider.dashboard') }}" style="display: flex; align-items: flex-end; gap: 1rem; flex-wrap: wrap;">
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

<!-- Primary Stats -->
<div class="nova-stats-grid mb-6" style="grid-template-columns: repeat(4, 1fr);">
    <!-- Active Deliveries -->
    <div class="nova-stat-card nova-stat-featured" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);">
        <div class="nova-stat-header">
            <div class="nova-stat-icon">
                <i class="fas fa-truck-loading"></i>
            </div>
        </div>
        <div class="nova-stat-value">{{ $stats['order_processing'] + $stats['order_pending'] + $stats['order_picking'] + $stats['order_picked_up'] + $stats['order_on_way'] }}</div>
        <div class="nova-stat-label">Active Deliveries</div>
    </div>
    
    <!-- Completed Today -->
    <div class="nova-stat-card success">
        <div class="nova-stat-header">
            <div class="nova-stat-icon">
                <i class="fas fa-check-double"></i>
            </div>
        </div>
        <div class="nova-stat-value">{{ $stats['order_delivered'] }}</div>
        <div class="nova-stat-label">Delivered</div>
    </div>
    
    <!-- Cancelled -->
    <div class="nova-stat-card danger">
        <div class="nova-stat-header">
            <div class="nova-stat-icon">
                <i class="fas fa-times-circle"></i>
            </div>
        </div>
        <div class="nova-stat-value">{{ $stats['order_cancel'] }}</div>
        <div class="nova-stat-label">Cancelled</div>
    </div>
    
    <!-- Refused -->
    <div class="nova-stat-card warning">
        <div class="nova-stat-header">
            <div class="nova-stat-icon">
                <i class="fas fa-hand-paper"></i>
            </div>
        </div>
        <div class="nova-stat-value">{{ $stats['refused'] }}</div>
        <div class="nova-stat-label">Refused</div>
    </div>
</div>

<!-- Delivery Pipeline -->
<div class="nova-card mb-6">
    <div class="nova-card-header">
        <h3 class="nova-card-title">Delivery Pipeline</h3>
        <a href="{{ route('rider.bookings') }}" class="nova-btn nova-btn-ghost nova-btn-sm">
            View All <i class="fas fa-arrow-right" style="margin-left: 0.5rem;"></i>
        </a>
    </div>
    <div class="nova-card-body">
        <!-- Pipeline visualization -->
        <div style="display: flex; gap: 0.5rem; margin-bottom: 2rem; flex-wrap: wrap;">
            @php
                $total = $stats['order_pending'] + $stats['order_processing'] + $stats['order_picking'] + $stats['order_picked_up'] + $stats['order_on_way'] + $stats['order_delivered'];
                $total = $total > 0 ? $total : 1;
            @endphp
            
            <div style="flex: {{ $stats['order_pending'] / $total * 100 }}%; min-width: 40px; height: 8px; background: #f59e0b; border-radius: 4px 0 0 4px;" title="Pending: {{ $stats['order_pending'] }}"></div>
            <div style="flex: {{ $stats['order_processing'] / $total * 100 }}%; min-width: 40px; height: 8px; background: #3b82f6;" title="Processing: {{ $stats['order_processing'] }}"></div>
            <div style="flex: {{ $stats['order_picking'] / $total * 100 }}%; min-width: 40px; height: 8px; background: #8b5cf6;" title="Picking: {{ $stats['order_picking'] }}"></div>
            <div style="flex: {{ $stats['order_picked_up'] / $total * 100 }}%; min-width: 40px; height: 8px; background: #06b6d4;" title="Picked Up: {{ $stats['order_picked_up'] }}"></div>
            <div style="flex: {{ $stats['order_on_way'] / $total * 100 }}%; min-width: 40px; height: 8px; background: #10b981;" title="On Way: {{ $stats['order_on_way'] }}"></div>
            <div style="flex: {{ $stats['order_delivered'] / $total * 100 }}%; min-width: 40px; height: 8px; background: #22c55e; border-radius: 0 4px 4px 0;" title="Delivered: {{ $stats['order_delivered'] }}"></div>
        </div>
        
        <!-- Pipeline cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
            <div style="text-align: center; padding: 1rem; background: rgba(245, 158, 11, 0.1); border-radius: 0.75rem;">
                <div style="width: 40px; height: 40px; background: #f59e0b; border-radius: 50%; margin: 0 auto 0.75rem; display: flex; align-items: center; justify-content: center; color: white;">
                    <i class="fas fa-clock"></i>
                </div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--color-gray-900);">{{ $stats['order_pending'] }}</div>
                <div style="font-size: 0.75rem; color: var(--color-gray-500);">Pending</div>
            </div>
            
            <div style="text-align: center; padding: 1rem; background: rgba(59, 130, 246, 0.1); border-radius: 0.75rem;">
                <div style="width: 40px; height: 40px; background: #3b82f6; border-radius: 50%; margin: 0 auto 0.75rem; display: flex; align-items: center; justify-content: center; color: white;">
                    <i class="fas fa-cog"></i>
                </div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--color-gray-900);">{{ $stats['order_processing'] }}</div>
                <div style="font-size: 0.75rem; color: var(--color-gray-500);">Processing</div>
            </div>
            
            <div style="text-align: center; padding: 1rem; background: rgba(139, 92, 246, 0.1); border-radius: 0.75rem;">
                <div style="width: 40px; height: 40px; background: #8b5cf6; border-radius: 50%; margin: 0 auto 0.75rem; display: flex; align-items: center; justify-content: center; color: white;">
                    <i class="fas fa-hand-paper"></i>
                </div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--color-gray-900);">{{ $stats['order_picking'] }}</div>
                <div style="font-size: 0.75rem; color: var(--color-gray-500);">Picking</div>
            </div>
            
            <div style="text-align: center; padding: 1rem; background: rgba(6, 182, 212, 0.1); border-radius: 0.75rem;">
                <div style="width: 40px; height: 40px; background: #06b6d4; border-radius: 50%; margin: 0 auto 0.75rem; display: flex; align-items: center; justify-content: center; color: white;">
                    <i class="fas fa-box"></i>
                </div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--color-gray-900);">{{ $stats['order_picked_up'] }}</div>
                <div style="font-size: 0.75rem; color: var(--color-gray-500);">Picked Up</div>
            </div>
            
            <div style="text-align: center; padding: 1rem; background: rgba(16, 185, 129, 0.1); border-radius: 0.75rem;">
                <div style="width: 40px; height: 40px; background: #10b981; border-radius: 50%; margin: 0 auto 0.75rem; display: flex; align-items: center; justify-content: center; color: white;">
                    <i class="fas fa-truck"></i>
                </div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--color-gray-900);">{{ $stats['order_on_way'] }}</div>
                <div style="font-size: 0.75rem; color: var(--color-gray-500);">On Way</div>
            </div>
            
            <div style="text-align: center; padding: 1rem; background: rgba(34, 197, 94, 0.1); border-radius: 0.75rem;">
                <div style="width: 40px; height: 40px; background: #22c55e; border-radius: 50%; margin: 0 auto 0.75rem; display: flex; align-items: center; justify-content: center; color: white;">
                    <i class="fas fa-check"></i>
                </div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--color-gray-900);">{{ $stats['order_delivered'] }}</div>
                <div style="font-size: 0.75rem; color: var(--color-gray-500);">Delivered</div>
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
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
            <!-- Today's Pickups -->
            <a href="{{ route('rider.bookings', ['action' => 'pickup']) }}" style="text-decoration: none;">
                <div style="padding: 1.5rem; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); border-radius: 1rem; color: white;">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="width: 56px; height: 56px; background: rgba(255,255,255,0.2); border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                            <i class="fas fa-truck-pickup"></i>
                        </div>
                        <div>
                            <div style="font-size: 1.125rem; font-weight: 600;">Today's Pickups</div>
                            <div style="font-size: 0.875rem; opacity: 0.8;">View active deliveries</div>
                        </div>
                    </div>
                </div>
            </a>
            
            <!-- Scheduled -->
            <a href="{{ route('rider.bookings', ['action' => 'schedule']) }}" style="text-decoration: none;">
                <div style="padding: 1.5rem; background: var(--color-gray-50); border: 1px solid var(--color-gray-200); border-radius: 1rem;">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="width: 56px; height: 56px; background: rgba(245, 158, 11, 0.1); border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: #f59e0b;">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div>
                            <div style="font-size: 1.125rem; font-weight: 600; color: var(--color-gray-900);">Scheduled Deliveries</div>
                            <div style="font-size: 0.875rem; color: var(--color-gray-500);">Future pickups</div>
                        </div>
                    </div>
                </div>
            </a>
            
            <!-- Delivery History -->
            <a href="{{ route('rider.bookings', ['action' => 'history']) }}" style="text-decoration: none;">
                <div style="padding: 1.5rem; background: var(--color-gray-50); border: 1px solid var(--color-gray-200); border-radius: 1rem;">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="width: 56px; height: 56px; background: rgba(34, 197, 94, 0.1); border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: #22c55e;">
                            <i class="fas fa-history"></i>
                        </div>
                        <div>
                            <div style="font-size: 1.125rem; font-weight: 600; color: var(--color-gray-900);">Delivery History</div>
                            <div style="font-size: 0.875rem; color: var(--color-gray-500);">Past deliveries</div>
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
            window.location.href = '{{ route("rider.dashboard") }}';
        });
    });
</script>
@endsection

@section('styles')
<style>
    /* Hover effects */
    .nova-card-body a > div:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1);
        transition: all 0.2s;
    }
    
    /* Responsive */
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

