@extends('layouts.modern')

@section('title')
    <title>Users | {{ config('app.name', 'Laravel') }}</title>
@stop

@section('content')
<div class="ct-page-header">
    <div>
        <h1 class="ct-page-title">Users</h1>
        <p class="ct-page-subtitle">Manage customers, riders, and admin accounts</p>
    </div>
    <div class="ct-page-actions">
        <button class="ct-btn ct-btn-outline Reload">
            <i class="fas fa-sync-alt"></i> Reload
        </button>
        <a href="javascript:void(0);" class="ct-btn ct-btn-primary popup" data-url="{{ route('users.create') }}">
            <i class="fas fa-plus"></i> Add User
        </a>
    </div>
</div>

<div class="ct-stats-grid" style="margin-bottom:1.5rem;">
    <div class="ct-stat-card ct-stat-card-accent">
        <div class="ct-stat-label">Total Users</div>
        <div class="ct-stat-value">{{ $stats['total'] }}</div>
        <div class="ct-stat-meta">All accounts</div>
    </div>
    <div class="ct-stat-card ct-stat-card-info">
        <div class="ct-stat-label">Customers</div>
        <div class="ct-stat-value">{{ $stats['customers'] }}</div>
        <div class="ct-stat-meta">Role: customer</div>
    </div>
    <div class="ct-stat-card ct-stat-card-warning">
        <div class="ct-stat-label">Riders</div>
        <div class="ct-stat-value">{{ $stats['riders'] }}</div>
        <div class="ct-stat-meta">Role: rider</div>
    </div>
    <div class="ct-stat-card ct-stat-card-success">
        <div class="ct-stat-label">Verified</div>
        <div class="ct-stat-value">{{ $stats['verified'] }}</div>
        <div class="ct-stat-meta">Confirmed accounts</div>
    </div>
</div>

<div class="ct-card">
    {{-- Filter bar --}}
    <div class="usr-filter-bar">
        <button id="usr-filter-toggle" class="ct-btn ct-btn-outline ct-btn-sm">
            <i class="fas fa-filter"></i> Filter by Type
        </button>
        <div class="usr-type-pills" id="usr-type-pills">
            <label class="usr-pill">
                <input type="radio" name="user" value="regular_user">
                <span>Regular Users</span>
            </label>
            <label class="usr-pill">
                <input type="radio" name="user" value="weekly_user">
                <span>Weekly Users</span>
            </label>
            <label class="usr-pill active-pill">
                <input type="radio" name="user" value="all" checked>
                <span>All</span>
            </label>
        </div>
    </div>

    {{-- DataTable --}}
    <div class="usr-table-wrap">
        {!! $html->table(['class' => 'usr-datatable', 'id' => 'users'], true) !!}
    </div>
</div>

<style>
    .usr-filter-bar { display: flex; align-items: center; gap: 1rem; padding: 0.875rem 1.25rem; border-bottom: 1px solid var(--ct-gray-100); background: var(--ct-gray-50); flex-wrap: wrap; }
    .usr-type-pills { display: flex; gap: 0.5rem; flex-wrap: wrap; }
    .usr-pill { display: flex; align-items: center; cursor: pointer; }
    .usr-pill input { position: absolute; opacity: 0; width: 0; }
    .usr-pill span { padding: 4px 12px; border: 1px solid var(--ct-gray-200); border-radius: 999px; font-size: 0.75rem; font-weight: 600; color: var(--ct-gray-600); background: var(--ct-white); transition: all .15s; cursor: pointer; }
    .usr-pill input:checked + span { background: var(--ct-primary); color: #fff; border-color: var(--ct-primary); }
    .usr-pill span:hover { border-color: var(--ct-accent); color: var(--ct-primary); }

    .usr-table-wrap { overflow-x: auto; }

    table.usr-datatable { width: 100% !important; border-collapse: collapse !important; font-size: 0.8125rem; }
    table.usr-datatable thead th {
        background: var(--ct-gray-50); text-align: left; padding: 0.75rem 1rem;
        font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.05em;
        color: var(--ct-gray-600); font-weight: 600; border-bottom: 1px solid var(--ct-gray-200);
        white-space: nowrap;
    }
    table.usr-datatable tbody td { padding: 0.875rem 1rem; border-bottom: 1px solid var(--ct-gray-100); vertical-align: middle; color: var(--ct-gray-800); }
    table.usr-datatable tbody tr:last-child td { border-bottom: none; }
    table.usr-datatable tbody tr:hover td { background: var(--ct-gray-50); }
    table.usr-datatable.no-footer { border-bottom: none; }

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
</style>
@endsection

@section('scripts')
    {!! $html->scripts() !!}
    <script>
    $(document).ready(function () {
        // Reload
        $('.Reload').on('click', function () {
            if (window.LaravelDataTables && window.LaravelDataTables['users']) {
                window.LaravelDataTables['users'].ajax.reload(null, false);
            } else {
                location.reload();
            }
        });

        // User type filter pills
        $('input[name=user]').on('change', function () {
            var userType = $(this).val();
            $.ajax({ type: 'GET', url: '{{ route('users.index') }}', data: { userType: userType },
                success: function () {
                    window.LaravelDataTables['users'].draw();
                }
            });
        });

        // Popup modal
        $('body').on('click', '.popup', function () {
            var url  = $(this).attr('data-url');
            var type = $(this).attr('data-type');
            $.ajax({ type: 'GET', url: url, success: function (data) {
                $('#default_modal .modal-dialog').html(data);
                if (type === 'small') $('#default_modal .modal-dialog').removeClass('modal-lg');
                $('#default_modal').modal('show');
            }});
        });

        // Active/inactive toggle
        $('body').on('change', '.is_active', function () {
            var url   = $(this).attr('data-url');
            var value = $(this).attr('data-value');
            var id    = $(this).attr('data-id');
            $.ajax({ type: 'POST', url: url, data: { '_token': $('meta[name=csrf-token]').attr('content'), value, id },
                success: function () {
                    window.LaravelDataTables['users'].ajax.reload(null, false);
                }
            });
        });

        // Update user form
        $('body').on('submit', '.ajax-form-update', function (event) {
            event.preventDefault();
            var url = $(this).attr('data-url');
            var formData = new FormData($(this)[0]);
            $('span.input-error').remove();
            formData.append('_token', $('meta[name=csrf-token]').attr('content'));
            formData.append('_method', 'PATCH');
            $.ajax({ type: 'POST', url: url, data: formData, processData: false, contentType: false,
                success: function (data) {
                    if (data.success) { window.LaravelDataTables['users'].ajax.reload(null, false); $('#default_modal').modal('toggle'); toast.success('Updated successfully'); }
                },
                error: function (response) {
                    toast.error('Please resolve following errors');
                    var errors = JSON.parse(response.responseText).errors;
                    $.each(errors, function (name, error) {
                        if ($('span.text-danger.' + name).length === 0)
                            $('.form-control[name="' + name + '"]').after('<span class="input-error ' + name + '">' + error + '</span>');
                    });
                }
            });
        });

        // Create user
        $('body').on('submit', '.create_user', function (event) {
            event.preventDefault();
            var fileInput = document.getElementById('file-upload-input');
            var formData  = new FormData($(this)[0]);
            $('span.input-error').remove();
            if (fileInput) formData.append('file', fileInput.files[0]);
            formData.append('_token', $('meta[name=csrf-token]').attr('content'));
            $.ajax({ type: 'POST', url: $(this).attr('data-url'), data: formData, processData: false, contentType: false,
                success: function (data) {
                    if (data.success) { window.LaravelDataTables['users'].ajax.reload(null, false); $('#default_modal').modal('toggle'); toast.success('User created successfully'); }
                },
                error: function (response) {
                    toast.error('Please resolve following errors');
                    var errors = JSON.parse(response.responseText).errors;
                    $.each(errors, function (name, error) {
                        $('.form-control[name="' + name + '"]').after('<span class="input-error ' + name + '">' + error + '</span>');
                    });
                }
            });
        });

        // Edit user
        $('body').on('submit', '.edit_user', function (event) {
            event.preventDefault();
            var fileInput = document.getElementById('file-upload-input');
            var formData  = new FormData($(this)[0]);
            var weekly = $('input[name=payment_weekly_status]').prop('checked') ? 1 : 0;
            $('span.input-error').remove();
            if (fileInput) formData.append('file', fileInput.files[0]);
            formData.append('_token', $('meta[name=csrf-token]').attr('content'));
            formData.append('_method', 'PATCH');
            formData.append('weekly', weekly);
            $.ajax({ type: 'POST', url: $(this).attr('data-url'), data: formData, processData: false, contentType: false,
                success: function (data) {
                    if (data.success) { window.LaravelDataTables['users'].ajax.reload(null, false); $('#default_modal').modal('toggle'); toast.success('User updated successfully'); }
                },
                error: function (response) {
                    toast.error('Please resolve following errors');
                    var errors = JSON.parse(response.responseText).errors;
                    $.each(errors, function (name, error) {
                        if ($('span.text-danger.' + name).length === 0)
                            $('.form-control[name="' + name + '"]').after('<span class="input-error ' + name + '">' + error + '</span>');
                    });
                }
            });
        });

        // Delete user
        $('body').on('submit', '.delete_user', function (event) {
            event.preventDefault();
            var formData = new FormData();
            formData.append('_token', $('meta[name=csrf-token]').attr('content'));
            formData.append('_method', 'DELETE');
            $.ajax({ type: 'POST', url: $(this).attr('data-url'), data: formData, processData: false, contentType: false,
                success: function (data) {
                    if (data.success) { window.LaravelDataTables['users'].ajax.reload(null, false); $('#default_modal').modal('toggle'); toast.success('User deleted successfully'); }
                }
            });
        });
    });
    </script>
@endsection
