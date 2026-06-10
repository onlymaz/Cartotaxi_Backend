@extends('layouts.modern')

@section('title')
    <title>Helpers | {{ config('app.name', 'Laravel') }}</title>
@stop

@section('content')
<div class="ct-page-header">
    <div>
        <h1 class="ct-page-title">Helpers</h1>
        <p class="ct-page-subtitle">{{ $isAdmin ? 'Review, confirm, and manage helper requests' : 'Your helper requests' }}</p>
    </div>
    <div class="ct-page-actions">
        <a href="{{ route('helper.create') }}" class="ct-btn ct-btn-primary">
            <i class="fas fa-plus"></i>
            Add Helper
        </a>
        <a href="{{ route('helper.index') }}" class="ct-btn ct-btn-outline">
            <i class="fas fa-sync-alt"></i>
            Refresh
        </a>
    </div>
</div>

<div class="ct-stats-grid" style="margin-bottom: 1.5rem;">
    <div class="ct-stat-card ct-stat-card-accent">
        <div class="ct-stat-label">Total helpers</div>
        <div class="ct-stat-value">{{ number_format($totalHelpers) }}</div>
        <div class="ct-stat-meta">All time</div>
    </div>
    <div class="ct-stat-card ct-stat-card-warning">
        <div class="ct-stat-label">Pending</div>
        <div class="ct-stat-value">{{ number_format($pendingHelpers) }}</div>
        <div class="ct-stat-meta">Awaiting approval</div>
    </div>
    <div class="ct-stat-card ct-stat-card-success">
        <div class="ct-stat-label">Approved</div>
        <div class="ct-stat-value">{{ number_format($approvedHelpers) }}</div>
        <div class="ct-stat-meta">Confirmed helpers</div>
    </div>
    <div class="ct-stat-card ct-stat-card-info">
        <div class="ct-stat-label">Revenue</div>
        <div class="ct-stat-value">&euro;{{ number_format($totalRevenue, 2) }}</div>
        <div class="ct-stat-meta">From approved helpers</div>
    </div>
</div>

<div class="ct-card">
    <div class="helper-toolbar">
        <div class="helper-toolbar-left">
            <div class="helper-search">
                <i class="fas fa-search"></i>
                <input type="text" id="helperSearch" placeholder="Search by user, booking ID, or address…">
            </div>
            <select id="helperStatusFilter" class="helper-select">
                <option value="all">All statuses</option>
                <option value="0">Pending</option>
                <option value="1">Approved</option>
            </select>
        </div>
        <span class="helper-toolbar-meta">{{ $helpers->total() }} {{ Str::plural('record', $helpers->total()) }}</span>
    </div>

    @if($helpers->count() > 0)
        <div class="helper-table-wrap">
            <table class="helper-table">
                <thead>
                    <tr>
                        <th style="width: 64px;">ID</th>
                        <th style="width: 104px;">Booking</th>
                        @if($isAdmin)
                            <th>User</th>
                        @endif
                        <th style="width: 80px;">Helpers</th>
                        <th style="width: 90px;">Price</th>
                        <th>Address</th>
                        <th style="width: 140px;">Start</th>
                        <th style="width: 110px;">Duration</th>
                        <th style="width: 120px;">Payment</th>
                        <th style="width: 100px;">Status</th>
                        @if($isAdmin)
                            <th style="width: 110px;">Action</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($helpers as $h)
                        @php
                            $user       = $h->user;
                            $initials   = $user ? strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) : '—';
                            $fullName   = $user ? trim($user->first_name.' '.$user->last_name) : '—';
                            $bookingId  = optional(optional($h->helperOrder)->order)->booking_id ?? optional($h->helperOrder)->order_id;
                            $gateway    = $h->gateway->name ?? $h->payment_method ?? '—';
                            $statusKey  = (int) $h->status;
                        @endphp
                        <tr class="helper-row"
                            data-status="{{ $statusKey }}"
                            data-search="{{ strtolower($fullName.' '.($bookingId ?? '').' '.($h->address ?? '')) }}">
                            <td><span class="helper-id">#{{ $h->id }}</span></td>
                            <td>
                                @if($bookingId)
                                    <span class="helper-booking">{{ $bookingId }}</span>
                                @else
                                    <span class="muted">—</span>
                                @endif
                            </td>
                            @if($isAdmin)
                                <td>
                                    <div class="helper-user">
                                        <div class="helper-avatar">{{ $initials }}</div>
                                        <div class="helper-user-text">
                                            <div class="helper-user-name">{{ $fullName }}</div>
                                            @if(!empty($user->email))
                                                <div class="helper-user-sub">{{ $user->email }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            @endif
                            <td>
                                <span class="helper-count"><i class="fas fa-user-friends"></i> {{ $h->total_helper }}</span>
                            </td>
                            <td class="helper-price">&euro;{{ number_format((float) $h->price, 2) }}</td>
                            <td class="helper-addr">{{ $h->address ?? '—' }}</td>
                            <td>
                                @if($h->start_time)
                                    <div class="helper-time">
                                        {{ \Carbon\Carbon::parse($h->start_time)->format('M d, Y') }}
                                        <span class="helper-time-sub">{{ \Carbon\Carbon::parse($h->start_time)->format('H:i') }}</span>
                                    </div>
                                @else
                                    <span class="muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($h->end_time)
                                    <span class="helper-duration">{{ $h->end_time }}</span>
                                @else
                                    <span class="muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="helper-gateway">{{ $gateway }}</span>
                            </td>
                            <td>
                                @if($statusKey === 1)
                                    <span class="helper-status approved"><i class="fas fa-check-circle"></i> Approved</span>
                                @elseif($statusKey === 0)
                                    <span class="helper-status pending"><i class="fas fa-clock"></i> Pending</span>
                                @else
                                    <span class="helper-status invalid"><i class="fas fa-ban"></i> Invalid</span>
                                @endif
                            </td>
                            @if($isAdmin)
                                <td>
                                    @if($statusKey === 0)
                                        <button type="button"
                                                class="helper-confirm-btn"
                                                data-url="{{ route('helper.change_status', $h->id) }}"
                                                data-id="{{ $h->id }}">
                                            <i class="fas fa-check"></i>
                                            Confirm
                                        </button>
                                    @else
                                        <span class="muted" style="font-size: 0.75rem;">—</span>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="helper-pagination">
            {{ $helpers->links() }}
        </div>
    @else
        <div class="helper-empty">
            <div class="helper-empty-icon">
                <i class="fas fa-hands-helping"></i>
            </div>
            <h3 class="helper-empty-title">No helper requests yet</h3>
            <p class="helper-empty-text">
                {{ $isAdmin
                    ? 'Helper requests submitted by customers will appear here.'
                    : 'You haven\'t requested any helpers yet. Click "Add Helper" to create your first request.' }}
            </p>
            <a href="{{ route('helper.create') }}" class="ct-btn ct-btn-primary" style="margin-top: 1rem;">
                <i class="fas fa-plus"></i>
                Create Helper Request
            </a>
        </div>
    @endif
</div>

<style>
    .helper-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--ct-gray-200);
        flex-wrap: wrap;
    }
    .helper-toolbar-left {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
    }
    .helper-search {
        position: relative;
        min-width: 280px;
    }
    .helper-search i {
        position: absolute;
        top: 50%;
        left: 12px;
        transform: translateY(-50%);
        color: var(--ct-gray-400);
        font-size: 0.8125rem;
    }
    .helper-search input {
        width: 100%;
        padding: 0.55rem 0.75rem 0.55rem 2.25rem;
        border: 1px solid var(--ct-gray-200);
        border-radius: 8px;
        background: var(--ct-white);
        font-size: 0.8125rem;
        color: var(--ct-gray-800);
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .helper-search input:focus {
        outline: none;
        border-color: var(--ct-accent);
        box-shadow: 0 0 0 3px rgba(132, 204, 22, 0.15);
    }
    .helper-select {
        padding: 0.55rem 0.75rem;
        border: 1px solid var(--ct-gray-200);
        border-radius: 8px;
        background: var(--ct-white);
        font-size: 0.8125rem;
        color: var(--ct-gray-800);
        font-weight: 500;
        cursor: pointer;
    }
    .helper-toolbar-meta {
        font-size: 0.75rem;
        color: var(--ct-gray-500);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .helper-table-wrap {
        overflow-x: auto;
    }
    .helper-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.8125rem;
    }
    .helper-table thead th {
        background: var(--ct-gray-50);
        text-align: left;
        padding: 0.75rem 1rem;
        font-size: 0.6875rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--ct-gray-600);
        font-weight: 600;
        border-bottom: 1px solid var(--ct-gray-200);
        white-space: nowrap;
    }
    .helper-table tbody td {
        padding: 0.875rem 1rem;
        border-bottom: 1px solid var(--ct-gray-100);
        vertical-align: middle;
    }
    .helper-table tbody tr:last-child td {
        border-bottom: none;
    }
    .helper-table tbody tr.helper-row:hover {
        background: var(--ct-gray-50);
    }
    .helper-row.is-hidden { display: none; }

    .helper-id {
        font-weight: 600;
        color: var(--ct-primary);
    }
    .helper-booking {
        display: inline-block;
        padding: 2px 8px;
        background: #eef2ff;
        color: #4338ca;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
        font-family: 'SF Mono', Menlo, Monaco, monospace;
    }
    .helper-user {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        min-width: 0;
    }
    .helper-avatar {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--ct-accent), #65a30d);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 700;
        flex-shrink: 0;
    }
    .helper-user-text {
        min-width: 0;
    }
    .helper-user-name {
        font-weight: 600;
        color: var(--ct-gray-900);
        font-size: 0.8125rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        max-width: 180px;
    }
    .helper-user-sub {
        font-size: 0.6875rem;
        color: var(--ct-gray-500);
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        max-width: 180px;
    }
    .helper-count {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        font-weight: 600;
        color: var(--ct-gray-800);
    }
    .helper-count i {
        color: var(--ct-accent);
        font-size: 0.75rem;
    }
    .helper-price {
        font-weight: 700;
        color: var(--ct-primary);
        white-space: nowrap;
    }
    .helper-addr {
        color: var(--ct-gray-700);
        max-width: 220px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .helper-time {
        line-height: 1.25;
        color: var(--ct-gray-800);
        font-weight: 500;
    }
    .helper-time-sub {
        display: block;
        font-size: 0.6875rem;
        color: var(--ct-gray-500);
        font-weight: 400;
    }
    .helper-duration {
        font-weight: 600;
        color: var(--ct-gray-800);
    }
    .helper-gateway {
        display: inline-block;
        padding: 2px 10px;
        background: var(--ct-gray-100);
        color: var(--ct-gray-700);
        border-radius: 999px;
        font-size: 0.6875rem;
        font-weight: 600;
        text-transform: capitalize;
    }
    .helper-status {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 0.6875rem;
        font-weight: 600;
    }
    .helper-status i { font-size: 0.625rem; }
    .helper-status.approved { background: #dcfce7; color: #15803d; }
    .helper-status.pending { background: #fef3c7; color: #a16207; }
    .helper-status.invalid { background: #fee2e2; color: #b91c1c; }

    .helper-confirm-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.4rem 0.75rem;
        border: none;
        border-radius: 8px;
        background: var(--ct-accent);
        color: var(--ct-primary);
        font-size: 0.75rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.15s ease;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }
    .helper-confirm-btn:hover {
        background: #a3e635;
        transform: translateY(-1px);
        box-shadow: 0 3px 8px rgba(132, 204, 22, 0.35);
    }
    .helper-confirm-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }
    .muted { color: var(--ct-gray-400); }

    .helper-pagination {
        padding: 1rem 1.25rem;
        border-top: 1px solid var(--ct-gray-200);
        display: flex;
        justify-content: flex-end;
    }
    .helper-pagination nav[role="navigation"] {
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .helper-pagination svg { width: 14px; height: 14px; }
    .helper-pagination span[aria-disabled],
    .helper-pagination a[rel],
    .helper-pagination span[aria-current] {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 34px;
        height: 34px;
        padding: 0 10px;
        border-radius: 8px;
        border: 1px solid var(--ct-gray-200);
        background: var(--ct-white);
        color: var(--ct-gray-700);
        font-size: 0.8125rem;
        font-weight: 500;
        text-decoration: none;
    }
    .helper-pagination a[rel]:hover {
        background: var(--ct-gray-50);
        color: var(--ct-primary);
    }
    .helper-pagination span[aria-current] {
        background: var(--ct-primary);
        color: #fff;
        border-color: var(--ct-primary);
    }
    .helper-pagination span[aria-disabled] {
        color: var(--ct-gray-400);
        background: var(--ct-gray-50);
        cursor: not-allowed;
    }

    .helper-empty {
        padding: 4rem 2rem;
        text-align: center;
    }
    .helper-empty-icon {
        width: 72px;
        height: 72px;
        margin: 0 auto 1rem;
        border-radius: 50%;
        background: linear-gradient(135deg, #ecfccb, #d9f99d);
        color: #65a30d;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
    }
    .helper-empty-title {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--ct-primary);
        margin: 0 0 0.375rem;
    }
    .helper-empty-text {
        color: var(--ct-gray-500);
        font-size: 0.875rem;
        max-width: 420px;
        margin: 0 auto;
    }
</style>
@endsection

@section('scripts')
<script>
    (function () {
        var searchInput = document.getElementById('helperSearch');
        var statusSel   = document.getElementById('helperStatusFilter');
        var rows        = document.querySelectorAll('.helper-row');

        function applyFilter() {
            var q = (searchInput && searchInput.value || '').toLowerCase().trim();
            var s = statusSel && statusSel.value || 'all';
            rows.forEach(function (r) {
                var hay = r.getAttribute('data-search') || '';
                var rs  = r.getAttribute('data-status') || '';
                var matchText   = q === '' || hay.indexOf(q) !== -1;
                var matchStatus = s === 'all' || rs === s;
                r.classList.toggle('is-hidden', !(matchText && matchStatus));
            });
        }

        if (searchInput) searchInput.addEventListener('input', applyFilter);
        if (statusSel)   statusSel.addEventListener('change', applyFilter);

        document.querySelectorAll('.helper-confirm-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var url = btn.getAttribute('data-url');
                if (!url) return;
                if (!confirm('Confirm this helper request?')) return;
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Confirming…';

                var token = (document.querySelector('meta[name="csrf-token"]') || {}).content || '{{ csrf_token() }}';
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }).then(function () {
                    window.location.reload();
                }).catch(function () {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-check"></i> Confirm';
                    alert('Could not confirm. Please try again.');
                });
            });
        });
    })();
</script>
@endsection
