@extends('layouts.modern')
@section('title')
    <title>Dispatcher | {{ config('app.name', 'Laravel') }}</title>
@stop

@php
    $unassignedCount = \App\Models\Order::where('order_status', 'pending')
        ->where(function ($q) { $q->where('is_assign', 0)->orWhere('rider_id', 0); })
        ->count();
    $assignedCount = \App\Models\Order::where('rider_id', '!=', 0)
        ->whereIn('order_status', ['processing', 'picking', 'pickup', 'picked_up', 'on_way'])
        ->count();
    $deliveredCount = \App\Models\Order::where('order_status', 'delivered')->count();
    $cancelledCount = \App\Models\Order::where('order_status', 'cancel')->count();
@endphp

@section('content')
    {{-- Page Header --}}
    <div class="ct-page-header">
        <div>
            <h1 class="ct-page-title">Dispatcher</h1>
            <p class="ct-page-subtitle">Assign orders to available riders and monitor delivery status in real time.</p>
        </div>
        <div class="ct-page-actions">
            <a href="javascript:void(0);" type="add" url="{{ route('dispatcher.create') }}"
               class="tabset-link ct-btn ct-btn-primary">
                <i class="fas fa-plus"></i>
                New Order
            </a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="ct-stats-grid ct-mb-6">
        <div class="ct-stat-card accent">
            <div class="ct-stat-header">
                <div>
                    <div class="ct-stat-value" style="font-variant-numeric: tabular-nums;">{{ $unassignedCount }}</div>
                    <div class="ct-stat-label">Unassigned · Awaiting rider</div>
                </div>
                <div class="ct-stat-icon"><i class="fas fa-hourglass-half"></i></div>
            </div>
        </div>
        <div class="ct-stat-card info">
            <div class="ct-stat-header">
                <div>
                    <div class="ct-stat-value" style="font-variant-numeric: tabular-nums;">{{ $assignedCount }}</div>
                    <div class="ct-stat-label">Assigned · In progress</div>
                </div>
                <div class="ct-stat-icon"><i class="fas fa-route"></i></div>
            </div>
        </div>
        <div class="ct-stat-card success">
            <div class="ct-stat-header">
                <div>
                    <div class="ct-stat-value" style="font-variant-numeric: tabular-nums;">{{ $deliveredCount }}</div>
                    <div class="ct-stat-label">Delivered · Completed</div>
                </div>
                <div class="ct-stat-icon"><i class="fas fa-circle-check"></i></div>
            </div>
        </div>
        <div class="ct-stat-card danger">
            <div class="ct-stat-header">
                <div>
                    <div class="ct-stat-value" style="font-variant-numeric: tabular-nums;">{{ $cancelledCount }}</div>
                    <div class="ct-stat-label">Cancelled · Not completed</div>
                </div>
                <div class="ct-stat-icon"><i class="fas fa-ban"></i></div>
            </div>
        </div>
    </div>

    {{-- Tab panel --}}
    <div class="ct-card">
        <ul class="Content dispatcher-tabs">
            <li class="active">
                <a href="javascript:void(0);" type="search" url="{{ route('dispatcher.create') }}"
                   class="tabset-link dispatcher-tab">
                    <i class="fas fa-hourglass-half"></i>
                    <span>Unassigned</span>
                    <span class="dispatcher-tab-count">{{ $unassignedCount }}</span>
                </a>
            </li>
            <li>
                <a href="javascript:void(0);" type="assigned" url="{{ route('dispatcher.create') }}"
                   class="tabset-link dispatcher-tab">
                    <i class="fas fa-route"></i>
                    <span>Assigned</span>
                    <span class="dispatcher-tab-count">{{ $assignedCount }}</span>
                </a>
            </li>
            <li>
                <a href="javascript:void(0);" type="cancel" url="{{ route('dispatcher.create') }}"
                   class="tabset-link dispatcher-tab">
                    <i class="fas fa-ban"></i>
                    <span>Cancelled</span>
                    <span class="dispatcher-tab-count">{{ $cancelledCount }}</span>
                </a>
            </li>
        </ul>

        <div class="dispatcher-panel">
            <div id="bodyContent" class="dispatcher-content-wrap">
                <div class="loader-layout dispatcher-loader" style="display: none;">
                    <div class="dispatcher-loader-inner">
                        <div class="dispatcher-spinner"></div>
                        <p>Loading orders…</p>
                    </div>
                </div>
                <div class="BodyContent">
                    @include('admin.dispatcher.search')
                </div>
            </div>
        </div>
    </div>

    <style>
        /* ============ Dispatcher page ============ */
        ul.dispatcher-tabs {
            display: flex;
            gap: 0.25rem;
            padding: 0 1.5rem;
            margin: 0;
            list-style: none;
            border-bottom: 1px solid var(--ct-gray-100);
            background: var(--ct-white);
            overflow-x: auto;
        }
        ul.dispatcher-tabs > li {
            display: inline-flex;
            margin: 0;
            padding: 0;
            list-style: none;
        }
        .dispatcher-tab {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--ct-gray-500);
            border-bottom: 2px solid transparent;
            text-decoration: none;
            white-space: nowrap;
            transition: all 0.15s ease;
        }
        .dispatcher-tab:hover {
            color: var(--ct-gray-800);
            text-decoration: none;
        }
        ul.dispatcher-tabs > li.active .dispatcher-tab {
            color: var(--ct-primary);
            border-bottom-color: var(--ct-accent);
        }
        .dispatcher-tab i {
            font-size: 0.875rem;
        }
        .dispatcher-tab-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 22px;
            height: 20px;
            padding: 0 0.4rem;
            border-radius: 999px;
            font-size: 0.6875rem;
            font-weight: 700;
            color: var(--ct-gray-600);
            background: var(--ct-gray-100);
            font-variant-numeric: tabular-nums;
        }
        ul.dispatcher-tabs > li.active .dispatcher-tab-count {
            color: var(--ct-primary);
            background: rgba(132, 204, 22, 0.18);
        }
        .dispatcher-panel {
            padding: 1.5rem;
            background: var(--ct-gray-50);
        }
        .dispatcher-content-wrap {
            position: relative;
            min-height: 400px;
        }
        .dispatcher-loader {
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0.85);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            border-radius: var(--ct-radius-lg);
        }
        .dispatcher-loader-inner { text-align: center; }
        .dispatcher-loader-inner p {
            margin: 0.75rem 0 0;
            font-size: 0.875rem;
            color: var(--ct-gray-600);
            font-weight: 500;
        }
        .dispatcher-spinner {
            width: 36px;
            height: 36px;
            border: 3px solid var(--ct-gray-200);
            border-top-color: var(--ct-accent);
            border-radius: 50%;
            margin: 0 auto;
            animation: dispatcher-spin 0.8s linear infinite;
        }
        @keyframes dispatcher-spin {
            to { transform: rotate(360deg); }
        }

        /* ============ Order list ============ */
        .dispatcher-section-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }
        .dispatcher-section-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--ct-gray-900);
            margin: 0;
        }
        .dispatcher-section-meta {
            font-size: 0.8125rem;
            color: var(--ct-gray-500);
        }
        .dispatcher-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        .order-card {
            background: var(--ct-white);
            border: 1px solid var(--ct-gray-200);
            border-radius: var(--ct-radius-lg);
            transition: box-shadow 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
            overflow: hidden;
        }
        .order-card:hover {
            box-shadow: var(--ct-shadow-lg);
            border-color: var(--ct-gray-300);
            transform: translateY(-1px);
        }
        .order-card.highlight {
            border-color: var(--ct-accent);
            box-shadow: 0 0 0 3px rgba(132, 204, 22, 0.15);
        }
        .order-card-body { padding: 1.25rem; }
        .order-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .order-identity {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            min-width: 0;
        }
        .order-avatar {
            width: 42px;
            height: 42px;
            border-radius: 999px;
            background: linear-gradient(135deg, var(--ct-primary), var(--ct-gray-700));
            color: var(--ct-white);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
            flex-shrink: 0;
        }
        .order-name {
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--ct-gray-900);
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .order-tags {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.375rem;
            margin-top: 0.25rem;
        }
        .order-id {
            font-family: 'SFMono-Regular', ui-monospace, monospace;
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--ct-gray-600);
            background: var(--ct-gray-100);
            padding: 0.125rem 0.5rem;
            border-radius: 6px;
        }

        /* Route timeline */
        .order-route {
            position: relative;
            padding-left: 1.75rem;
            margin-bottom: 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        .order-route::before {
            content: '';
            position: absolute;
            left: 0.625rem;
            top: 0.5rem;
            bottom: 0.5rem;
            width: 2px;
            background: linear-gradient(to bottom, var(--ct-accent) 0%, var(--ct-accent) 45%, var(--ct-gray-300) 55%, var(--ct-danger) 100%);
            border-radius: 2px;
        }
        .route-step { position: relative; }
        .route-dot {
            position: absolute;
            left: -1.75rem;
            top: 0.1rem;
            width: 20px;
            height: 20px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 2px solid var(--ct-white);
            box-shadow: 0 0 0 2px var(--ct-gray-200);
        }
        .route-dot.from { background: rgba(132, 204, 22, 0.2); }
        .route-dot.from::after {
            content: '';
            width: 7px; height: 7px;
            border-radius: 999px;
            background: var(--ct-accent);
        }
        .route-dot.to { background: rgba(239, 68, 68, 0.15); }
        .route-dot.to::after {
            content: '';
            width: 7px; height: 7px;
            border-radius: 999px;
            background: var(--ct-danger);
        }
        .route-label {
            font-size: 0.6875rem;
            font-weight: 600;
            color: var(--ct-gray-500);
            letter-spacing: 0.04em;
            text-transform: uppercase;
            margin: 0;
        }
        .route-address {
            font-size: 0.875rem;
            color: var(--ct-gray-900);
            margin: 0.125rem 0 0;
            line-height: 1.4;
        }

        /* Metrics row */
        .order-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            flex-wrap: wrap;
            padding-top: 0.875rem;
            border-top: 1px solid var(--ct-gray-100);
        }
        .order-metrics {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            flex-wrap: wrap;
            font-size: 0.8125rem;
        }
        .order-metric {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            color: var(--ct-gray-700);
        }
        .order-metric i { font-size: 0.75rem; color: var(--ct-gray-500); }
        .order-metric.amount { color: var(--ct-gray-900); font-weight: 600; }
        .order-metric.amount i { color: var(--ct-accent); }
        .order-metric.distance i { color: var(--ct-info); }
        .order-metric.time i { color: var(--ct-secondary); }

        .btn-map {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.4rem 0.75rem;
            border-radius: 8px;
            border: 1px solid var(--ct-gray-200);
            background: var(--ct-white);
            color: var(--ct-gray-700);
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .btn-map:hover {
            background: var(--ct-gray-50);
            border-color: var(--ct-gray-300);
            color: var(--ct-primary);
        }

        .export-menu { position: relative; display: inline-block; }
        .export-dropdown {
            position: absolute;
            top: calc(100% + 6px);
            right: 0;
            min-width: 200px;
            background: var(--ct-white);
            border: 1px solid var(--ct-gray-200);
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.12);
            padding: 6px;
            display: none;
            z-index: 40;
        }
        .export-menu.open .export-dropdown { display: block; }
        .export-item {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            padding: 0.55rem 0.75rem;
            border-radius: 8px;
            font-size: 0.8125rem;
            font-weight: 500;
            color: var(--ct-gray-700);
            text-decoration: none;
            transition: background 0.12s ease;
        }
        .export-item:hover {
            background: var(--ct-gray-50);
            color: var(--ct-primary);
            text-decoration: none;
        }
        .export-item i {
            width: 18px;
            text-align: center;
            color: var(--ct-gray-500);
            font-size: 0.875rem;
        }
        .export-item:hover i { color: var(--ct-accent); }

        .btn-assign {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.5rem 0.875rem;
            border-radius: 8px;
            background: var(--ct-accent);
            color: var(--ct-primary);
            font-size: 0.8125rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.15s ease;
            flex-shrink: 0;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }
        .btn-assign:hover {
            background: var(--ct-accent-hover);
            color: var(--ct-primary);
            box-shadow: 0 2px 8px var(--ct-accent-glow);
            transform: translateY(-1px);
        }
        .btn-rider-details {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.4rem 0.75rem;
            border-radius: 8px;
            border: 1px solid var(--ct-gray-200);
            background: var(--ct-gray-50);
            color: var(--ct-gray-700);
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s ease;
            flex-shrink: 0;
        }
        .btn-rider-details:hover {
            background: var(--ct-white);
            color: var(--ct-primary);
            border-color: var(--ct-gray-300);
        }

        .map-container {
            margin-top: 0.875rem;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid var(--ct-gray-200);
            background: var(--ct-gray-100);
        }
        .show_map {
            height: 360px !important;
            width: 100%;
        }

        /* Status badges */
        .ord-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.125rem 0.5rem;
            border-radius: 6px;
            font-size: 0.6875rem;
            font-weight: 600;
            letter-spacing: 0.01em;
        }
        .ord-badge-pending    { background: rgba(245, 158, 11, 0.12); color: #b45309; }
        .ord-badge-processing { background: rgba(99, 102, 241, 0.12); color: #4338ca; }
        .ord-badge-picking    { background: rgba(59, 130, 246, 0.12); color: #1d4ed8; }
        .ord-badge-pickup     { background: rgba(245, 158, 11, 0.12); color: #b45309; }
        .ord-badge-picked_up  { background: rgba(14, 165, 233, 0.12); color: #0369a1; }
        .ord-badge-on_way     { background: rgba(139, 92, 246, 0.12); color: #6d28d9; }
        .ord-badge-delivered  { background: rgba(16, 185, 129, 0.12); color: #047857; }
        .ord-badge-cancel     { background: rgba(239, 68, 68, 0.12); color: #b91c1c; }
        .ord-badge-rider      { background: rgba(30, 41, 59, 0.06); color: var(--ct-primary); }
        .ord-badge-gateway    { background: rgba(100, 116, 139, 0.12); color: var(--ct-gray-700); text-transform: uppercase; }
        .ord-badge-live       { background: rgba(16, 185, 129, 0.12); color: #047857; }
        .ord-badge i { font-size: 0.625rem; }

        /* Empty state */
        .dispatcher-empty {
            background: var(--ct-white);
            border: 1px dashed var(--ct-gray-300);
            border-radius: var(--ct-radius-lg);
            padding: 3.5rem 1.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        .dispatcher-empty-icon {
            width: 64px;
            height: 64px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        .dispatcher-empty-icon.amber { background: rgba(245, 158, 11, 0.12); color: #b45309; }
        .dispatcher-empty-icon.info  { background: rgba(59, 130, 246, 0.12); color: #1d4ed8; }
        .dispatcher-empty-icon.rose  { background: rgba(239, 68, 68, 0.12); color: #b91c1c; }
        .dispatcher-empty-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--ct-gray-900);
            margin: 0;
        }
        .dispatcher-empty-text {
            font-size: 0.8125rem;
            color: var(--ct-gray-500);
            margin: 0.25rem 0 0;
        }

        /* Filter panel */
        .filter-panel {
            background: var(--ct-white);
            border: 1px solid var(--ct-gray-200);
            border-radius: var(--ct-radius-lg);
            padding: 1rem 1.25rem;
            margin-bottom: 1rem;
        }
        .filter-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        @media (min-width: 640px) {
            .filter-grid { grid-template-columns: 1fr 1fr; }
        }
        .filter-label {
            display: block;
            font-size: 0.6875rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            color: var(--ct-gray-600);
            text-transform: uppercase;
            margin-bottom: 0.375rem;
        }
        .filter-select {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 1px solid var(--ct-gray-300);
            border-radius: 8px;
            font-size: 0.875rem;
            color: var(--ct-gray-800);
            background: var(--ct-white);
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .filter-select:focus {
            outline: none;
            border-color: var(--ct-accent);
            box-shadow: 0 0 0 3px var(--ct-accent-glow);
        }

        /* Rider modal */
        .rider-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(10, 22, 40, 0.55);
            backdrop-filter: blur(4px);
            z-index: 2000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .rider-modal-overlay.open { display: flex; }
        .rider-modal {
            background: var(--ct-white);
            border-radius: var(--ct-radius-xl);
            width: 100%;
            max-width: 440px;
            overflow: hidden;
            box-shadow: var(--ct-shadow-lg);
        }
        .rider-modal-head {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--ct-gray-100);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .rider-modal-head h4 {
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--ct-gray-900);
            margin: 0;
        }
        .rider-modal-close {
            width: 28px;
            height: 28px;
            border: none;
            background: var(--ct-gray-100);
            border-radius: 999px;
            color: var(--ct-gray-500);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s ease;
        }
        .rider-modal-close:hover { background: var(--ct-gray-200); color: var(--ct-gray-700); }
        .rider-modal-body { padding: 1.25rem; }
        .rider-row {
            display: grid;
            grid-template-columns: 110px 1fr;
            gap: 0.5rem;
            padding: 0.5rem 0;
            border-bottom: 1px solid var(--ct-gray-100);
            font-size: 0.8125rem;
        }
        .rider-row:last-of-type { border-bottom: none; }
        .rider-row-label { color: var(--ct-gray-500); font-weight: 500; }
        .rider-row-value { color: var(--ct-gray-900); }
        .rider-signature {
            margin-top: 0.75rem;
            padding-top: 0.75rem;
            border-top: 1px solid var(--ct-gray-100);
        }
        .rider-signature-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--ct-gray-600);
            margin: 0 0 0.5rem;
        }
        .rider-signature img {
            max-width: 100%;
            max-height: 180px;
            border-radius: 8px;
            border: 1px solid var(--ct-gray-200);
            background: var(--ct-gray-50);
        }
        .rider-modal-foot {
            padding: 0.875rem 1.25rem;
            border-top: 1px solid var(--ct-gray-100);
            background: var(--ct-gray-50);
            display: flex;
            justify-content: flex-end;
        }

        /* Pagination */
        .custom_pagination {
            margin-top: 1.5rem;
            display: flex;
            justify-content: center;
        }
        .custom_pagination nav {
            display: flex;
            justify-content: center;
            width: 100%;
        }
        .custom_pagination nav > div:first-child { display: none; } /* hide mobile prev/next duplicate */
        .custom_pagination nav > div.hidden { display: block !important; }
        .custom_pagination p.text-sm {
            font-size: 0.8125rem;
            color: var(--ct-gray-500);
            margin: 0;
        }
        .custom_pagination .pagination {
            display: inline-flex;
            gap: 0.25rem;
            align-items: center;
            padding: 0;
            margin: 0;
            list-style: none;
        }
        .custom_pagination .page-item .page-link,
        .custom_pagination .pagination a,
        .custom_pagination .pagination span {
            padding: 0.4rem 0.75rem;
            border-radius: 8px;
            color: var(--ct-gray-700);
            border: 1px solid var(--ct-gray-200);
            background: var(--ct-white);
            font-size: 0.8125rem;
            text-decoration: none;
            transition: all 0.15s;
            line-height: 1.2;
            min-width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .custom_pagination .pagination a:hover {
            background: var(--ct-gray-50);
            border-color: var(--ct-gray-300);
        }
        .custom_pagination .pagination .active span,
        .custom_pagination .page-item.active .page-link {
            background: var(--ct-primary);
            color: var(--ct-white);
            border-color: var(--ct-primary);
        }
        .custom_pagination .pagination .disabled span {
            color: var(--ct-gray-400);
            background: var(--ct-gray-50);
        }
        /* Laravel default tailwind pagination arrows — shrink SVGs */
        .custom_pagination svg {
            width: 14px;
            height: 14px;
            display: inline-block;
        }
        .custom_pagination nav[role="navigation"] {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }
        .custom_pagination nav[role="navigation"] > div:first-child {
            display: none; /* hide mobile-only prev/next row */
        }
        .custom_pagination nav[role="navigation"] span[aria-disabled],
        .custom_pagination nav[role="navigation"] a[rel] {
            padding: 0.4rem 0.75rem;
            border-radius: 8px;
            border: 1px solid var(--ct-gray-200);
            background: var(--ct-white);
            font-size: 0.8125rem;
            color: var(--ct-gray-700);
            margin: 0 0.125rem;
            line-height: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 34px;
            height: 34px;
        }
        .custom_pagination nav[role="navigation"] span[aria-current] {
            background: var(--ct-primary);
            color: var(--ct-white);
            border-color: var(--ct-primary);
            padding: 0.4rem 0.75rem;
            border-radius: 8px;
            font-size: 0.8125rem;
            margin: 0 0.125rem;
            line-height: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 34px;
            height: 34px;
        }
    </style>
@endsection

@section('scripts')
    {{-- jquery.datetimepicker assets removed: files don't exist (404) and
         nothing on this page calls .datetimepicker(). --}}
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    @include('admin.dispatcher.dispatcher_scripts')
    @include('customer.customer_maps_scripts')

    <script type="text/javascript">
        function getTimvalue(value) {
            $("input[name='time']").val(value);
        }
        function onChangeAjax(value) {
            $.ajax({
                url: '{!! route('getTime') !!}',
                type: 'post',
                data: {
                    date: value,
                    _token: '{!! csrf_token() !!}',
                },
                success: function (time) {
                    $("#ajaxHours").empty();
                    for (var i = 0; i < time.length; i++) {
                        $("#ajaxHours").append('<option>' + getTime(time[i]) + '</option>');
                    }
                }
            });
        }
        function getTime(seconds) {
            var leftover = seconds;
            var days = Math.floor(leftover / 86400);
            leftover = leftover - (days * 86400);
            var hours = Math.floor(leftover / 3600);
            leftover = leftover - (hours * 3600);
            var minutes = Math.floor(leftover / 60);
            leftover = leftover - (minutes * 60);
            if (hours < 10) { hours = "0" + hours; }
            if (minutes < 10) { minutes = "0" + minutes; }
            return hours + ':' + minutes;
        }

        // ── Load Leaflet + OpenStreetMap once (free, no API key needed) ──────────
        (function () {
            if (document.getElementById('leaflet-css')) return;
            var css = document.createElement('link');
            css.id  = 'leaflet-css'; css.rel = 'stylesheet';
            css.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
            document.head.appendChild(css);
            var js = document.createElement('script');
            js.id  = 'leaflet-js';
            js.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
            document.head.appendChild(js);
        })();

        window._dispatchMaps = window._dispatchMaps || {};

        // Decode Google encoded-polyline format → [[lat,lng], ...]
        function decodeGPoly(str) {
            var idx = 0, lat = 0, lng = 0, out = [], shift, res, b, dLat, dLng;
            while (idx < str.length) {
                b = null; shift = 0; res = 0;
                do { b = str.charCodeAt(idx++) - 63; res |= (b & 0x1f) << shift; shift += 5; } while (b >= 0x20);
                dLat = (res & 1) ? ~(res >> 1) : (res >> 1); shift = res = 0;
                do { b = str.charCodeAt(idx++) - 63; res |= (b & 0x1f) << shift; shift += 5; } while (b >= 0x20);
                dLng = (res & 1) ? ~(res >> 1) : (res >> 1);
                lat += dLat; lng += dLng;
                out.push([lat / 1e5, lng / 1e5]);
            }
            return out;
        }

        // Custom pin icon
        function pinIcon(label, color) {
            color = color || '#1e293b';
            return L.divIcon({
                className: '',
                html: '<div style="background:' + color + ';color:#fff;width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;border:2px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.35);">' + label + '</div>',
                iconSize: [28, 28], iconAnchor: [14, 14], popupAnchor: [0, -16]
            });
        }

        // Draw route via OSRM (free routing engine, no key needed)
        function osrmRoute(lmap, sLat, sLng, eLat, eLng, onDone) {
            var url = 'https://router.project-osrm.org/route/v1/driving/'
                    + sLng + ',' + sLat + ';' + eLng + ',' + eLat
                    + '?overview=full&geometries=geojson';
            fetch(url)
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.routes && data.routes[0]) {
                        var line = L.geoJSON(data.routes[0].geometry, {
                            style: { color: '#84cc16', weight: 5, opacity: 0.95 }
                        }).addTo(lmap);
                        lmap.fitBounds(line.getBounds(), { padding: [40, 40] });
                    } else { onDone && onDone('no-route'); }
                })
                .catch(function() { onDone && onDone('error'); });
        }

        // Geocode via server-side proxy (avoids CORS and rate-limit issues)
        function nominatim(addr, cb) {
            fetch('/geocode?address=' + encodeURIComponent(addr), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(function(r) { return r.json(); })
                .then(function(d) { cb(d && d.lat ? [d.lat, d.lng] : null); })
                .catch(function() { cb(null); });
        }

        $("body").on('click', '.view_map_button', function () {
            var flip     = $(this).closest(".flip");
            var order    = flip.find("input[name='orderid']").val();
            var mapEl    = flip.find(".show_map")[0];
            var $toggled = flip.find('.toggled_content');

            // ── Open/close detection ─────────────────────────────────────────
            // We CANNOT use .is(':visible') here: the .toggler direct handler
            // fires before this delegated handler and jQuery's slideToggle()
            // synchronously sets display:block (animation start), making the
            // element appear "open" before we've even decided what to do.
            // Solution: track state with a data attribute we own.
            var isOpen = $toggled.data('mapOpen'); // undefined/false = closed

            if (isOpen) {
                // Panel is closing — tear down and exit
                $toggled.data('mapOpen', false);
                if (window._dispatchMaps[order]) {
                    window._dispatchMaps[order].remove();
                    delete window._dispatchMaps[order];
                }
                mapEl.innerHTML = '';
                return;
            }

            // Panel is opening — mark it and continue to init the map
            $toggled.data('mapOpen', true);

            // Loading spinner shown while the 150ms animation plays + map loads
            mapEl.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100%;color:#94a3b8;gap:.5rem;font-size:.8125rem;"><i class="fas fa-spinner fa-spin"></i> Loading map…</div>';

            var pickupEls  = document.querySelectorAll('.pickup'  + order);
            var dropoffEls = document.querySelectorAll('.dropoff' + order);
            var slatEls    = document.querySelectorAll('.slat'    + order);
            var slngEls    = document.querySelectorAll('.slng'    + order);
            var latEls     = document.querySelectorAll('.lat'     + order);
            var lngEls     = document.querySelectorAll('.lng'     + order);
            var rawPolyline = flip.find("input[name='polylines']").val() || '';

            var hasPolyline = rawPolyline && rawPolyline.trim() && rawPolyline.trim() !== 'null' && rawPolyline.trim() !== '[]';
            var hasCoords   = slatEls.length && slatEls[0].value && latEls.length && latEls[0].value;
            var hasAddress  = pickupEls.length && pickupEls[0].value && dropoffEls.length && dropoffEls[0].value;

            function waitForLeaflet(cb) {
                if (window.L) { cb(); return; }
                var t = setInterval(function() { if (window.L) { clearInterval(t); cb(); } }, 80);
            }

            // Wait for the 150ms slideToggle animation to finish before calling
            // L.map() — Leaflet reads offsetWidth/Height on init, so the container
            // must be at its natural size (360px) before we initialise.
            setTimeout(function () {
                if (!$toggled.data('mapOpen')) return; // closed before timeout fired

                waitForLeaflet(function () {
                    mapEl.innerHTML = '';
                    var lmap = L.map(mapEl, { zoomControl: true }).setView([48.2082, 16.3738], 12);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                        maxZoom: 19
                    }).addTo(lmap);
                    window._dispatchMaps[order] = lmap;

                    // Force Leaflet to recalculate tile grid after the container
                    // is fully rendered at its natural 360 px height
                    lmap.invalidateSize();

                    if (hasPolyline) {
                        // ── Case 1: encoded polyline ─────────────────────────────
                        try {
                            var segs = JSON.parse(rawPolyline);
                            if (!Array.isArray(segs)) segs = [segs];
                            var allCoords = [];
                            segs.forEach(function (seg) {
                                if (!seg) return;
                                var coords = decodeGPoly(seg);
                                L.polyline(coords, { color: '#84cc16', weight: 5, opacity: 0.95 }).addTo(lmap);
                                allCoords = allCoords.concat(coords);
                            });
                            if (allCoords.length) lmap.fitBounds(allCoords, { padding: [40, 40] });
                            if (slatEls[0] && slatEls[0].value)
                                L.marker([+slatEls[0].value, +slngEls[0].value], { icon: pinIcon('P', '#22c55e') })
                                 .addTo(lmap).bindPopup(pickupEls[0] ? pickupEls[0].value : 'Pickup');
                            for (var b = 0; b < latEls.length; b++) {
                                L.marker([+latEls[b].value, +lngEls[b].value], { icon: pinIcon('D' + (b + 1), '#ef4444') })
                                 .addTo(lmap).bindPopup(dropoffEls[b] ? dropoffEls[b].value : 'Dropoff');
                            }
                        } catch (e) {}

                    } else if (hasCoords) {
                        // ── Case 2: lat/lng coordinates ──────────────────────────
                        var sLat = +slatEls[0].value, sLng = +slngEls[0].value;
                        var eLat = +latEls[latEls.length - 1].value, eLng = +lngEls[lngEls.length - 1].value;
                        var sPopup = pickupEls[0]  ? pickupEls[0].value  : 'Pickup';
                        var ePopup = dropoffEls[0] ? dropoffEls[0].value : 'Dropoff';
                        L.marker([sLat, sLng], { icon: pinIcon('P', '#22c55e') }).addTo(lmap).bindPopup(sPopup).openPopup();
                        L.marker([eLat, eLng], { icon: pinIcon('D', '#ef4444') }).addTo(lmap).bindPopup(ePopup);
                        osrmRoute(lmap, sLat, sLng, eLat, eLng, function () {
                            // fallback: straight line
                            L.polyline([[sLat, sLng], [eLat, eLng]], { color: '#84cc16', weight: 5, dashArray: '8,6' }).addTo(lmap);
                            lmap.fitBounds([[sLat, sLng], [eLat, eLng]], { padding: [40, 40] });
                        });

                    } else if (hasAddress) {
                        // ── Case 3: addresses only — geocode then route ──────────
                        var aOrigin = pickupEls[0].value;
                        var aDest   = dropoffEls[dropoffEls.length - 1].value;
                        nominatim(aOrigin, function (start) {
                            nominatim(aDest, function (end) {
                                if (start && end) {
                                    L.marker(start, { icon: pinIcon('P', '#22c55e') }).addTo(lmap).bindPopup(aOrigin).openPopup();
                                    L.marker(end,   { icon: pinIcon('D', '#ef4444') }).addTo(lmap).bindPopup(aDest);
                                    osrmRoute(lmap, start[0], start[1], end[0], end[1], function () {
                                        L.polyline([start, end], { color: '#84cc16', weight: 5, dashArray: '8,6' }).addTo(lmap);
                                        lmap.fitBounds([start, end], { padding: [40, 40] });
                                    });
                                } else if (start) {
                                    lmap.setView(start, 14);
                                    L.marker(start, { icon: pinIcon('P', '#22c55e') }).addTo(lmap).bindPopup(aOrigin).openPopup();
                                } else {
                                    lmap.setView([48.2082, 16.3738], 12);
                                }
                            });
                        });

                    } else {
                        lmap.remove(); delete window._dispatchMaps[order];
                        mapEl.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100%;flex-direction:column;gap:.5rem;color:#94a3b8;font-size:.875rem;"><i class="fas fa-map" style="font-size:2rem;"></i><span>No location data for this order</span></div>';
                    }
                });
            }, 200); // wait for the 150ms slideToggle animation + paint buffer
        });

        $("body").on('click', '#filter', function () {
            $('.filterDetail').slideToggle(150);
        });

        $('body').on('click', '.export-toggle', function (e) {
            e.stopPropagation();
            var $menu = $(this).closest('.export-menu');
            $('.export-menu').not($menu).removeClass('open');
            $menu.toggleClass('open');
        });
        $(document).on('click', function () {
            $('.export-menu').removeClass('open');
        });
    </script>
@endsection
