@extends('layouts.modern')

@section('title')
    <title>Bookings | {{ config('app.name', 'Laravel') }}</title>
@stop

@section('content')
<div class="ct-page-header">
    <div>
        <h1 class="ct-page-title">Bookings</h1>
        <p class="ct-page-subtitle">Manage all delivery orders and their statuses</p>
    </div>
    <div class="ct-page-actions">
        <button class="ct-btn ct-btn-outline Reload">
            <i class="fas fa-sync-alt"></i> Reload
        </button>
        <a href="{{ route('dispatcher.index', ['action' => 'add']) }}" class="ct-btn ct-btn-primary">
            <i class="fas fa-plus"></i> New Booking
        </a>
    </div>
</div>

<div class="ct-stats-grid" style="margin-bottom:1.5rem;">
    <div class="ct-stat-card ct-stat-card-accent">
        <div class="ct-stat-label">Total</div>
        <div class="ct-stat-value">{{ $stats['total'] }}</div>
        <div class="ct-stat-meta">All orders</div>
    </div>
    <div class="ct-stat-card ct-stat-card-warning">
        <div class="ct-stat-label">Pending</div>
        <div class="ct-stat-value">{{ $stats['pending'] }}</div>
        <div class="ct-stat-meta">Awaiting pickup</div>
    </div>
    <div class="ct-stat-card ct-stat-card-info">
        <div class="ct-stat-label">In Progress</div>
        <div class="ct-stat-value">{{ $stats['processing'] }}</div>
        <div class="ct-stat-meta">Active deliveries</div>
    </div>
    <div class="ct-stat-card ct-stat-card-success">
        <div class="ct-stat-label">Delivered</div>
        <div class="ct-stat-value">{{ $stats['delivered'] }}</div>
        <div class="ct-stat-meta">Completed</div>
    </div>
</div>

<div class="ct-card">
    {{-- Tab bar --}}
    <div class="bk-tabs">
        <a href="{{ route('bookings.index', ['action' => 'pickup']) }}"
           class="bk-tab {{ $action == 'pickup' ? 'active' : '' }}">
            <i class="fas fa-bolt"></i> Pickup Now
        </a>
        <a href="{{ route('bookings.index', ['action' => 'schedule']) }}"
           class="bk-tab {{ $action == 'schedule' ? 'active' : '' }}">
            <i class="fas fa-calendar-alt"></i> Scheduled
        </a>
        <a href="{{ route('bookings.index', ['action' => 'history']) }}"
           class="bk-tab {{ $action == 'history' ? 'active' : '' }}">
            <i class="fas fa-history"></i> History
        </a>
        <a href="{{ route('bookings.index', ['action' => 'all']) }}"
           class="bk-tab {{ $action == 'all' ? 'active' : '' }}">
            <i class="fas fa-list"></i> All Orders
        </a>
    </div>

    {{-- Filter bar --}}
    <div class="bk-filter-bar">
        <button id="bk-filter-toggle" class="ct-btn ct-btn-outline ct-btn-sm">
            <i class="fas fa-filter"></i> Filters
            @if($filter != 'all')<span class="bk-filter-dot"></span>@endif
        </button>
        @if($filter != 'all')
            <span class="bk-active-filter">
                {{ ucfirst(str_replace('_', ' ', $filter)) }}
                <a href="{{ route('bookings.index', ['action' => $action]) }}"><i class="fas fa-times"></i></a>
            </span>
        @endif
    </div>

    {{-- Filter panel --}}
    <div class="bk-filter-panel" id="bk-filter-panel" style="display:none;">
        <form class="bk-filter-form" action="{{ url('bookings') }}" method="get">
            <input type="hidden" name="action" value="{{ $action }}">
            <div class="bk-filter-fields">
                <div class="bk-filter-field">
                    <label>Order Status</label>
                    <select name="statusFilter" class="formFilter bk-select">
                        <option {{ $filter == 'all' ? 'selected' : '' }} value="all">All Statuses</option>
                        @if($action == 'pickup')
                            <option {{ $filter == 'pending' ? 'selected' : '' }} value="pending">Pending</option>
                            <option {{ $filter == 'picking' ? 'selected' : '' }} value="picking">Started</option>
                            <option {{ $filter == 'picked_up' ? 'selected' : '' }} value="picked_up">Picked Up</option>
                            <option {{ $filter == 'on_way' ? 'selected' : '' }} value="on_way">On the Way</option>
                        @elseif($action == 'schedule')
                            <option {{ $filter == 'picking' ? 'selected' : '' }} value="picking">Processing</option>
                            <option {{ $filter == 'on_way' ? 'selected' : '' }} value="on_way">On the Way</option>
                            <option {{ $filter == 'picked_up' ? 'selected' : '' }} value="picked_up">Picked Up</option>
                        @elseif($action == 'history')
                            <option {{ $filter == 'delivered' ? 'selected' : '' }} value="delivered">Delivered</option>
                            <option {{ $filter == 'refused' ? 'selected' : '' }} value="refused">Refused</option>
                            <option {{ $filter == 'accident' ? 'selected' : '' }} value="accident">Accident</option>
                            <option {{ $filter == 'cancel' ? 'selected' : '' }} value="cancel">Cancelled</option>
                        @else
                            <option value="pending">Pending</option>
                            <option value="picking">Started</option>
                            <option value="on_way">On the Way</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancel">Cancelled</option>
                        @endif
                    </select>
                </div>
                @if($action == 'schedule')
                <div class="bk-filter-field">
                    <label>Booking Type</label>
                    <select name="orderFilterType" class="filterFormHelper bk-select">
                        <option {{ $orderFilter == 'all' ? 'selected' : '' }} value="all">All Types</option>
                        <option {{ $orderFilter == 'withouthelper' ? 'selected' : '' }} value="withouthelper">Without Helper</option>
                        <option {{ $orderFilter == 'withhelper' ? 'selected' : '' }} value="withhelper">With Helper</option>
                    </select>
                </div>
                @endif
            </div>
        </form>
    </div>

    {{-- DataTable --}}
    <div class="bk-table-wrap">
        {!! $html->table(['class' => 'bk-datatable', 'id' => 'requests'], true) !!}
    </div>
</div>

<style>
    .bk-tabs { display: flex; border-bottom: 1px solid var(--ct-gray-200); padding: 0 1rem; }
    .bk-tab { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.875rem 1rem; font-size: 0.875rem; font-weight: 500; color: var(--ct-gray-500); border-bottom: 2px solid transparent; text-decoration: none; transition: color .15s, border-color .15s; white-space: nowrap; }
    .bk-tab:hover { color: var(--ct-gray-800); }
    .bk-tab.active { color: var(--ct-primary); border-bottom-color: var(--ct-accent); font-weight: 600; }
    .bk-tab i { font-size: 0.75rem; }

    .bk-filter-bar { display: flex; align-items: center; gap: 0.75rem; padding: 0.875rem 1.25rem; border-bottom: 1px solid var(--ct-gray-100); background: var(--ct-gray-50); }
    .bk-filter-dot { display: inline-block; width: 7px; height: 7px; border-radius: 50%; background: var(--ct-accent); margin-left: 4px; }
    .bk-active-filter { display: inline-flex; align-items: center; gap: 0.4rem; padding: 3px 10px; background: #e0e7ff; color: #4338ca; border-radius: 999px; font-size: 0.75rem; font-weight: 600; }
    .bk-active-filter a { color: #4338ca; margin-left: 2px; }

    .bk-filter-panel { padding: 1rem 1.25rem; border-bottom: 1px solid var(--ct-gray-100); background: var(--ct-white); }
    .bk-filter-fields { display: flex; flex-wrap: wrap; gap: 1rem; }
    .bk-filter-field { display: flex; flex-direction: column; gap: 0.375rem; min-width: 180px; }
    .bk-filter-field label { font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; color: var(--ct-gray-500); }
    .bk-select { padding: 0.45rem 0.75rem; border: 1px solid var(--ct-gray-200); border-radius: 8px; font-size: 0.875rem; color: var(--ct-gray-800); background: var(--ct-white); }
    .bk-select:focus { outline: none; border-color: var(--ct-accent); box-shadow: 0 0 0 3px rgba(132,204,22,.15); }

    .bk-table-wrap { overflow-x: auto; }

    /* DataTable overrides to match ct-theme */
    table.bk-datatable { width: 100% !important; border-collapse: collapse !important; font-size: 0.8125rem; }
    table.bk-datatable thead th {
        background: var(--ct-gray-50); text-align: left; padding: 0.75rem 1rem;
        font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.05em;
        color: var(--ct-gray-600); font-weight: 600; border-bottom: 1px solid var(--ct-gray-200);
        white-space: nowrap;
    }
    table.bk-datatable tbody td { padding: 0.875rem 1rem; border-bottom: 1px solid var(--ct-gray-100); vertical-align: middle; color: var(--ct-gray-800); }
    table.bk-datatable tbody tr:last-child td { border-bottom: none; }
    table.bk-datatable tbody tr:hover td { background: var(--ct-gray-50); }
    table.bk-datatable.no-footer { border-bottom: none; }
    .hasChild td { background: #fffbeb !important; }
    .hasChild:hover td { background: #fef3c7 !important; }

    .dataTables_wrapper { padding: 0; }
    .dataTables_length, .dataTables_filter, .dataTables_info, .dataTables_paginate {
        padding: 0.875rem 1rem; font-size: 0.8125rem; color: var(--ct-gray-600);
    }
    .dataTables_length { border-bottom: 1px solid var(--ct-gray-100); }
    .dataTables_length select { padding: 0.3rem 0.6rem; border: 1px solid var(--ct-gray-200); border-radius: 6px; font-size: 0.8125rem; }
    .dataTables_filter { border-bottom: 1px solid var(--ct-gray-100); text-align: right; }
    .dataTables_filter input { padding: 0.4rem 0.75rem; border: 1px solid var(--ct-gray-200); border-radius: 8px; font-size: 0.8125rem; margin-left: 0.5rem; }
    .dataTables_filter input:focus { outline: none; border-color: var(--ct-accent); box-shadow: 0 0 0 3px rgba(132,204,22,.15); }
    .dataTables_info, .dataTables_paginate { border-top: 1px solid var(--ct-gray-200); }
    .dataTables_paginate { text-align: right; }
    .dataTables_paginate .paginate_button {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 32px; height: 32px; padding: 0 8px;
        border: 1px solid var(--ct-gray-200); border-radius: 6px;
        background: var(--ct-white); color: var(--ct-gray-700); font-size: 0.8125rem; cursor: pointer;
        margin: 0 2px; text-decoration: none; transition: background .15s;
    }
    .dataTables_paginate .paginate_button:hover { background: var(--ct-gray-50); }
    .dataTables_paginate .paginate_button.current { background: var(--ct-primary); color: #fff; border-color: var(--ct-primary); }
    .dataTables_paginate .paginate_button.disabled { opacity: .4; cursor: not-allowed; }
    div.dataTables_wrapper div.dataTables_length, div.dataTables_wrapper div.dataTables_filter { display: flex; align-items: center; }
</style>
@endsection

@section('scripts')
    {!! $html->scripts() !!}
    <script>
    $(document).ready(function () {
        // Filter toggle
        $('#bk-filter-toggle').on('click', function () {
            $('#bk-filter-panel').slideToggle(150);
        });

        // Auto-submit on filter change
        $('body').on('change', '.formFilter', function () {
            var status = $(".formFilter option:selected").val();
            window.location.href = "{!! url('bookings?action='.$action) !!}" + '&statusFilter=' + status;
        });
        $('body').on('change', '.filterFormHelper', function () {
            var statusOrder = $(".filterFormHelper option:selected").val();
            window.location.href = "{!! url('bookings?action='.$action) !!}" + '&statusFilter={{ $filter }}&orderFilterType=' + statusOrder;
        });

        // Reload button
        $('.Reload').on('click', function () {
            if (window.LaravelDataTables && window.LaravelDataTables['requests']) {
                window.LaravelDataTables['requests'].ajax.reload(null, false);
            } else {
                location.reload();
            }
        });

        // Popup modal
        $('body').on('click', '.popup', function () {
            var elem = $(this);
            var url  = elem.attr('data-url');
            var type = elem.attr('data-type');
            $.ajax({ type: 'GET', url: url, success: function (data) {
                $('#default_modal .modal-dialog').html(data);
                if (type === 'delete') $('#default_modal .modal-dialog').removeClass('modal-lg');
                if (type === 'view')   $('#default_modal .modal-dialog').removeClass('modal-lg').addClass('modal-xl');
                $('#default_modal').modal('show');
            }});
        });

        // Ajax form update
        $('body').on('submit', '.ajax-form-update', function (event) {
            event.preventDefault();
            var url = $(this).attr('data-url');
            var formData = new FormData($(this)[0]);
            $('span.input-error').remove();
            formData.append('_token', $('meta[name=csrf-token]').attr('content'));
            formData.append('_method', 'PATCH');
            $.ajax({ type: 'POST', url: url, data: formData, processData: false, contentType: false,
                success: function (data) {
                    if (data.success) {
                        window.LaravelDataTables['requests'].ajax.reload(null, false);
                        $('#default_modal').modal('toggle');
                        toast.success('Updated successfully');
                    }
                },
                error: function (response) {
                    toast.error('Please resolve following errors');
                    var errors = JSON.parse(response.responseText).errors;
                    $.each(errors, function (name, error) {
                        $('.form-control[name="' + name + '"]').after('<span class="input-error text-red-500 text-sm ' + name + '">' + error + '</span>');
                    });
                }
            });
        });

        // Highlight helper orders after table draw
        $('body').on('draw.dt', '#requests', function () {
            $('#requests .helper .helper-id').closest('tr').addClass('hasChild');
        });
    });
    </script>
@endsection
