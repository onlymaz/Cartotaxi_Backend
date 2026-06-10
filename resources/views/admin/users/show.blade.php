@extends('layouts.modern')

@section('title')
    <title>{{ $user->full_name }} | {{ config('app.name', 'Laravel') }}</title>
@stop

@section('content')
<div class="ct-page-header">
    <div>
        <h1 class="ct-page-title">{{ $user->full_name }}</h1>
        <p class="ct-page-subtitle">{{ ucfirst(optional($user->role)->name ?? 'user') }} profile &amp; order history</p>
    </div>
    <div class="ct-page-actions">
        <a href="{{ route('users.index') }}" class="ct-btn ct-btn-outline"><i class="fas fa-arrow-left"></i> Back to Users</a>
        <button type="button" class="ct-btn {{ $user->IsActive ? 'ct-btn-outline' : 'ct-btn-primary' }}" id="toggle-active"
                data-id="{{ $user->id }}" data-value="{{ $user->IsActive ? 0 : 1 }}" data-url="{{ route('users.change_status') }}">
            @if($user->IsActive)
                <i class="fas fa-lock"></i> Disable Account
            @else
                <i class="fas fa-lock-open"></i> Enable Account
            @endif
        </button>
    </div>
</div>

<div class="profile-grid">
    {{-- Identity card --}}
    <div class="ct-card profile-card">
        <div class="profile-id">
            <div class="profile-avatar">
                @if($user->profile_image)
                    <img src="{{ asset('storage/'.$user->profile_image) }}" alt="{{ $user->full_name }}">
                @else
                    {{ strtoupper(substr($user->first_name ?? '?',0,1).substr($user->last_name ?? '',0,1)) }}
                @endif
            </div>
            <div>
                <div class="profile-name">{{ $user->full_name }}</div>
                <div class="profile-role">{{ ucfirst(optional($user->role)->name ?? '—') }}</div>
            </div>
        </div>

        <div class="profile-badges">
            @if($user->IsActive)
                <span class="pbadge ok"><i class="fas fa-check-circle"></i> Active</span>
            @else
                <span class="pbadge bad"><i class="fas fa-lock"></i> Disabled / Locked</span>
            @endif
            @if($user->confirmed)
                <span class="pbadge ok"><i class="fas fa-user-check"></i> Verified</span>
            @else
                <span class="pbadge warn"><i class="fas fa-user-clock"></i> Unverified</span>
            @endif
            <span class="pbadge neutral"><i class="fas fa-wallet"></i> {{ $user->isWeekly ? 'Weekly billing' : 'Per-order billing' }}</span>
        </div>

        <dl class="profile-facts">
            <div><dt>Email</dt><dd><a href="mailto:{{ $user->email }}">{{ $user->email }}</a></dd></div>
            <div><dt>Phone</dt><dd>{{ $user->phone_number ?: '—' }}</dd></div>
            @if($isRider)
                <div><dt>Car number</dt><dd>{{ $user->car_number ?: '—' }}</dd></div>
            @endif
            @if($user->company_name)
                <div><dt>Company</dt><dd>{{ $user->company_name }}@if($user->vat_number) · VAT {{ $user->vat_number }}@endif</dd></div>
            @endif
            <div><dt>Language</dt><dd>{{ strtoupper($user->lang ?: '—') }}</dd></div>
            <div><dt>Signed up via</dt><dd>{{ ucfirst($user->provider ?: '—') }} ({{ $user->device ?: '—' }})</dd></div>
            <div><dt>Joined</dt><dd>{{ optional($user->created_at)->format('M d, Y') ?? '—' }}</dd></div>
            <div><dt>Last login</dt><dd>{{ $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->format('M d, Y H:i') : 'Never' }}</dd></div>
        </dl>
    </div>

    {{-- Order stats + history --}}
    <div class="profile-main">
        <div class="pstats">
            <div class="ct-card pstat"><div class="pstat-num">{{ $orderStats['total'] }}</div><div class="pstat-label">{{ $isRider ? 'Orders delivered for' : 'Orders placed' }}</div></div>
            <div class="ct-card pstat"><div class="pstat-num ok">{{ $orderStats['delivered'] }}</div><div class="pstat-label">Completed</div></div>
            <div class="ct-card pstat"><div class="pstat-num bad">{{ $orderStats['cancelled'] }}</div><div class="pstat-label">Cancelled / refused</div></div>
            <div class="ct-card pstat"><div class="pstat-num">${{ number_format((float) $orderStats['spent'], 2) }}</div><div class="pstat-label">{{ $isRider ? 'Revenue delivered' : 'Total spent' }}</div></div>
        </div>

        <div class="ct-card">
            <div class="phist-head"><h3>Order history</h3></div>
            @if($orders->count())
                <div class="phist-scroll">
                    <table class="phist-table">
                        <thead>
                            <tr>
                                <th>Booking</th>
                                <th>Route</th>
                                <th>{{ $isRider ? 'Customer' : 'Rider' }}</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                @php $other = $isRider ? $order->user : $order->rider; @endphp
                                <tr>
                                    <td class="mono">{{ $order->booking_id ?: '#'.$order->id }}</td>
                                    <td class="route" title="{{ $order->start_location }} → {{ $order->end_location }}">
                                        {{ \Illuminate\Support\Str::limit($order->start_location, 28) }}
                                        <i class="fas fa-long-arrow-alt-right"></i>
                                        {{ \Illuminate\Support\Str::limit($order->end_location, 28) }}
                                    </td>
                                    <td>{{ $other ? trim($other->first_name.' '.$other->last_name) : '—' }}</td>
                                    <td class="mono">${{ number_format((float) $order->total_amount, 2) }}</td>
                                    <td><span class="status-pill st-{{ $order->order_status }}">{{ str_replace('_',' ', ucfirst($order->order_status)) }}</span></td>
                                    <td>{{ optional($order->created_at)->format('M d, Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="phist-pag">{{ $orders->links() }}</div>
            @else
                <div class="phist-empty">No orders yet for this {{ $isRider ? 'rider' : 'customer' }}.</div>
            @endif
        </div>
    </div>
</div>

<style>
    .profile-grid { display: grid; grid-template-columns: 340px 1fr; gap: 1.25rem; align-items: start; }
    @media (max-width: 991px) { .profile-grid { grid-template-columns: 1fr; } }
    .profile-card { padding: 1.5rem; }
    .profile-id { display: flex; align-items: center; gap: 0.875rem; margin-bottom: 1rem; }
    .profile-avatar { width: 56px; height: 56px; border-radius: 50%; background: linear-gradient(135deg, var(--ct-accent), #65a30d); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:1.125rem; overflow:hidden; flex-shrink:0; }
    .profile-avatar img { width:100%; height:100%; object-fit:cover; }
    .profile-name { font-size: 1.0625rem; font-weight: 700; color: var(--ct-gray-900); }
    .profile-role { font-size: 0.8125rem; color: var(--ct-gray-500); }
    .profile-badges { display:flex; flex-wrap:wrap; gap:0.5rem; margin-bottom:1.125rem; }
    .pbadge { display:inline-flex; align-items:center; gap:0.35rem; padding:3px 10px; border-radius:999px; font-size:0.75rem; font-weight:600; }
    .pbadge.ok { background:#dcfce7; color:#15803d; }
    .pbadge.bad { background:#fee2e2; color:#b91c1c; }
    .pbadge.warn { background:#fef9c3; color:#a16207; }
    .pbadge.neutral { background:var(--ct-gray-100); color:var(--ct-gray-600); }
    .profile-facts { margin:0; }
    .profile-facts > div { display:flex; justify-content:space-between; gap:1rem; padding:0.5rem 0; border-bottom:1px solid var(--ct-gray-100); }
    .profile-facts > div:last-child { border-bottom:none; }
    .profile-facts dt { font-size:0.75rem; font-weight:600; color:var(--ct-gray-400); text-transform:uppercase; letter-spacing:0.04em; }
    .profile-facts dd { margin:0; font-size:0.8438rem; color:var(--ct-gray-800); text-align:right; word-break:break-word; }
    .pstats { display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; margin-bottom:1.25rem; }
    @media (max-width: 767px) { .pstats { grid-template-columns:repeat(2,1fr); } }
    .pstat { padding:1rem 1.25rem; }
    .pstat-num { font-size:1.375rem; font-weight:700; color:var(--ct-gray-900); }
    .pstat-num.ok { color:#15803d; } .pstat-num.bad { color:#b91c1c; }
    .pstat-label { font-size:0.75rem; color:var(--ct-gray-500); }
    .phist-head { padding:1rem 1.25rem; border-bottom:1px solid var(--ct-gray-200); }
    .phist-head h3 { margin:0; font-size:0.9375rem; font-weight:700; color:var(--ct-gray-900); }
    .phist-scroll { overflow-x:auto; }
    .phist-table { width:100%; border-collapse:collapse; font-size:0.8125rem; }
    .phist-table th { text-align:left; padding:0.625rem 1.25rem; font-size:0.6875rem; text-transform:uppercase; letter-spacing:0.04em; color:var(--ct-gray-400); border-bottom:1px solid var(--ct-gray-200); white-space:nowrap; }
    .phist-table td { padding:0.625rem 1.25rem; border-bottom:1px solid var(--ct-gray-100); color:var(--ct-gray-700); vertical-align:top; }
    .phist-table tr:last-child td { border-bottom:none; }
    .mono { font-family:ui-monospace, SFMono-Regular, Menlo, monospace; font-size:0.78rem; }
    .route i { color:var(--ct-gray-400); margin:0 0.25rem; }
    .status-pill { display:inline-block; padding:2px 9px; border-radius:999px; font-size:0.6875rem; font-weight:600; background:var(--ct-gray-100); color:var(--ct-gray-600); white-space:nowrap; }
    .st-delivered { background:#dcfce7; color:#15803d; }
    .st-pending { background:#fef9c3; color:#a16207; }
    .st-processing, .st-picking { background:#e0f2fe; color:#0369a1; }
    .st-cancel, .st-refused, .st-accident, .st-not_received { background:#fee2e2; color:#b91c1c; }
    .phist-pag { padding:0.875rem 1.25rem; border-top:1px solid var(--ct-gray-200); display:flex; justify-content:flex-end; }
    .phist-pag svg { width:14px; height:14px; }
    .phist-empty { padding:2.5rem; text-align:center; color:var(--ct-gray-500); font-size:0.875rem; }
</style>

<script>
    document.getElementById('toggle-active').addEventListener('click', function () {
        var btn = this;
        var label = btn.dataset.value === '1' ? 'enable' : 'disable';
        if (!confirm('Are you sure you want to ' + label + ' this account?')) return;
        btn.disabled = true;
        fetch(btn.dataset.url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ id: btn.dataset.id, value: btn.dataset.value })
        }).then(function (r) {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            window.location.reload();
        }).catch(function (e) {
            btn.disabled = false;
            alert('Could not update account status: ' + e.message);
        });
    });
</script>
@endsection
