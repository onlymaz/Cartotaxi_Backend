@extends('layouts.modern')

@section('title')
    <title>Packages | {{ config('app.name', 'Laravel') }}</title>
@stop

@section('content')
<div class="ct-page-header">
    <div>
        <h1 class="ct-page-title">Packages</h1>
        <p class="ct-page-subtitle">Manage delivery package tiers, weights, and pricing</p>
    </div>
    <div class="ct-page-actions">
        <button type="button" class="ct-btn ct-btn-primary open-pkg-modal" data-url="{{ route('packages.create') }}" data-mode="create">
            <i class="fas fa-plus"></i> Add Package
        </button>
    </div>
</div>

<div class="ct-stats-grid" style="margin-bottom:1.5rem;">
    <div class="ct-stat-card ct-stat-card-accent">
        <div class="ct-stat-label">Total packages</div>
        <div class="ct-stat-value">{{ $packages->total() }}</div>
        <div class="ct-stat-meta">All tiers</div>
    </div>
    <div class="ct-stat-card ct-stat-card-success">
        <div class="ct-stat-label">Active</div>
        <div class="ct-stat-value">{{ App\Models\Package::where('is_active',1)->count() }}</div>
        <div class="ct-stat-meta">Available to customers</div>
    </div>
    <div class="ct-stat-card ct-stat-card-info">
        <div class="ct-stat-label">Avg fixed price</div>
        <div class="ct-stat-value">&euro;{{ number_format((float)App\Models\Package::avg('fixed_price'),2) }}</div>
        <div class="ct-stat-meta">Across all packages</div>
    </div>
    <div class="ct-stat-card ct-stat-card-warning">
        <div class="ct-stat-label">Avg /km</div>
        <div class="ct-stat-value">&euro;{{ number_format((float)App\Models\Package::avg('per_km_charges'),3) }}</div>
        <div class="ct-stat-meta">Per kilometre charge</div>
    </div>
</div>

<div class="ct-card">
    @if($packages->count() > 0)
        <div class="pkg-table-wrap">
            <table class="pkg-table">
                <thead>
                    <tr>
                        <th style="width:60px;">ID</th>
                        <th>Name</th>
                        <th style="width:100px;">Weight</th>
                        <th style="width:80px;">Unit</th>
                        <th style="width:120px;">Fixed Price</th>
                        <th style="width:130px;">Per km</th>
                        <th style="width:90px;">Status</th>
                        <th style="width:110px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($packages as $pkg)
                        <tr>
                            <td class="pkg-id">#{{ $pkg->id }}</td>
                            <td>
                                <div class="pkg-name-row">
                                    <div class="pkg-icon"><i class="fas fa-box"></i></div>
                                    <span class="pkg-name-text">{{ $pkg->name }}</span>
                                </div>
                            </td>
                            <td class="pkg-val">{{ $pkg->weight }}</td>
                            <td><span class="pkg-unit">{{ $pkg->unit }}</span></td>
                            <td class="pkg-price">&euro;{{ number_format((float)$pkg->fixed_price, 2) }}</td>
                            <td class="pkg-price">&euro;{{ number_format((float)$pkg->per_km_charges, 3) }}</td>
                            <td>
                                @if($pkg->is_active)
                                    <span class="pkg-status active"><i class="fas fa-check-circle"></i> Active</span>
                                @else
                                    <span class="pkg-status inactive"><i class="fas fa-pause-circle"></i> Inactive</span>
                                @endif
                            </td>
                            <td>
                                <button type="button"
                                        class="ct-btn ct-btn-outline ct-btn-sm open-pkg-modal"
                                        data-url="{{ route('packages.edit', $pkg->id) }}"
                                        data-mode="edit">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pkg-pagination">{{ $packages->links() }}</div>
    @else
        <div class="pkg-empty">
            <div class="pkg-empty-icon"><i class="fas fa-box-open"></i></div>
            <h3>No packages yet</h3>
            <p>Add your first delivery package tier to get started.</p>
            <button type="button" class="ct-btn ct-btn-primary open-pkg-modal" data-url="{{ route('packages.create') }}" data-mode="create" style="margin-top:1rem;">
                <i class="fas fa-plus"></i> Add First Package
            </button>
        </div>
    @endif
</div>

{{-- Package modal --}}
<div id="pkg_modal" class="pkg-modal-overlay">
    <div class="pkg-modal">
        <div class="pkg-modal-head">
            <h4 id="pkg_modal_title"><i class="fas fa-box"></i> Package</h4>
            <button type="button" class="pkg-modal-close"><i class="fas fa-times"></i></button>
        </div>
        <div class="pkg-modal-body" id="pkg_modal_body">
            <div class="pkg-modal-loading"><i class="fas fa-spinner fa-spin"></i></div>
        </div>
    </div>
</div>

<style>
    .pkg-table-wrap { overflow-x: auto; }
    .pkg-table { width: 100%; border-collapse: collapse; font-size: 0.8125rem; }
    .pkg-table thead th {
        background: var(--ct-gray-50); text-align: left; padding: 0.75rem 1rem;
        font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.05em;
        color: var(--ct-gray-600); font-weight: 600; border-bottom: 1px solid var(--ct-gray-200);
    }
    .pkg-table tbody td { padding: 0.875rem 1rem; border-bottom: 1px solid var(--ct-gray-100); vertical-align: middle; }
    .pkg-table tbody tr:last-child td { border-bottom: none; }
    .pkg-table tbody tr:hover td { background: var(--ct-gray-50); }
    .pkg-id { font-weight: 600; color: var(--ct-primary); }
    .pkg-name-row { display: flex; align-items: center; gap: 0.625rem; }
    .pkg-icon { width: 32px; height: 32px; border-radius: 8px; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 0.875rem; flex-shrink: 0; }
    .pkg-name-text { font-weight: 600; color: var(--ct-gray-900); }
    .pkg-val { color: var(--ct-gray-700); font-weight: 500; }
    .pkg-unit { display: inline-block; padding: 2px 8px; background: var(--ct-gray-100); color: var(--ct-gray-700); border-radius: 6px; font-size: 0.75rem; font-weight: 600; }
    .pkg-price { font-weight: 700; color: var(--ct-primary); }
    .pkg-status { display: inline-flex; align-items: center; gap: 0.3rem; padding: 3px 10px; border-radius: 999px; font-size: 0.6875rem; font-weight: 600; }
    .pkg-status.active { background: #dcfce7; color: #15803d; }
    .pkg-status.inactive { background: #f1f5f9; color: #64748b; }
    .pkg-pagination { padding: 1rem 1.25rem; border-top: 1px solid var(--ct-gray-200); display: flex; justify-content: flex-end; }
    .pkg-pagination svg { width: 14px; height: 14px; }
    .pkg-pagination nav[role="navigation"] { display: inline-flex; align-items: center; gap: 4px; }
    .pkg-pagination span[aria-disabled],.pkg-pagination a[rel],.pkg-pagination span[aria-current] {
        display: inline-flex; align-items: center; justify-content: center; min-width: 34px; height: 34px;
        padding: 0 10px; border-radius: 8px; border: 1px solid var(--ct-gray-200);
        background: var(--ct-white); color: var(--ct-gray-700); font-size: 0.8125rem; font-weight: 500; text-decoration: none;
    }
    .pkg-pagination a[rel]:hover { background: var(--ct-gray-50); }
    .pkg-pagination span[aria-current] { background: var(--ct-primary); color: #fff; border-color: var(--ct-primary); }
    .pkg-pagination span[aria-disabled] { color: var(--ct-gray-400); background: var(--ct-gray-50); }
    .pkg-empty { padding: 4rem 2rem; text-align: center; }
    .pkg-empty-icon { width: 72px; height: 72px; margin: 0 auto 1rem; border-radius: 50%; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; }
    .pkg-empty h3 { font-size: 1.125rem; font-weight: 700; color: var(--ct-primary); margin: 0 0 0.375rem; }
    .pkg-empty p { color: var(--ct-gray-500); font-size: 0.875rem; }
    .pkg-modal-overlay {
        position: fixed; inset: 0; background: rgba(15,23,42,.45); z-index: 100;
        display: flex; align-items: center; justify-content: center; padding: 1rem;
        opacity: 0; pointer-events: none; transition: opacity .2s ease;
    }
    .pkg-modal-overlay.open { opacity: 1; pointer-events: auto; }
    .pkg-modal {
        background: var(--ct-white); border-radius: 16px; width: 100%; max-width: 500px;
        box-shadow: 0 24px 64px rgba(15,23,42,.18); transform: translateY(20px); transition: transform .2s ease;
    }
    .pkg-modal-overlay.open .pkg-modal { transform: translateY(0); }
    .pkg-modal-head { display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem; border-bottom: 1px solid var(--ct-gray-200); }
    .pkg-modal-head h4 { font-size: 1rem; font-weight: 700; color: var(--ct-primary); margin: 0; display: flex; align-items: center; gap: 0.5rem; }
    .pkg-modal-close { background: none; border: none; cursor: pointer; color: var(--ct-gray-500); font-size: 1rem; padding: 4px 8px; border-radius: 6px; }
    .pkg-modal-close:hover { background: var(--ct-gray-100); }
    .pkg-modal-body { padding: 1.5rem; }
    .pkg-modal-loading { display: flex; align-items: center; justify-content: center; padding: 3rem; color: var(--ct-gray-400); font-size: 1.5rem; }
    /* Form inside modal */
    .pkg-modal-body .form-group { margin-bottom: 1rem; }
    .pkg-modal-body .form-group label { display: block; font-size: 0.8125rem; font-weight: 600; color: var(--ct-gray-700); margin-bottom: 0.375rem; }
    .pkg-modal-body .form-control {
        width: 100%; padding: 0.55rem 0.75rem; border: 1px solid var(--ct-gray-200);
        border-radius: 8px; font-size: 0.875rem; color: var(--ct-gray-800); box-sizing: border-box;
    }
    .pkg-modal-body .form-control:focus { outline: none; border-color: var(--ct-accent); box-shadow: 0 0 0 3px rgba(132,204,22,.15); }
    .pkg-form-actions { display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid var(--ct-gray-200); }
    span.input-error { display: block; font-size: 0.75rem; color: #ef4444; margin-top: 4px; }
</style>
@endsection

@section('scripts')
{!! $html->scripts() !!}
<script>
(function () {
    var overlay = document.getElementById('pkg_modal');
    var body    = document.getElementById('pkg_modal_body');
    var titleEl = document.getElementById('pkg_modal_title');

    function openModal(url, mode) {
        body.innerHTML = '<div class="pkg-modal-loading"><i class="fas fa-spinner fa-spin"></i></div>';
        titleEl.innerHTML = '<i class="fas fa-box"></i> ' + (mode === 'edit' ? 'Edit Package' : 'Add Package');
        overlay.classList.add('open');
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r){ return r.text(); })
            .then(function(html){
                var parser = new DOMParser();
                var doc = parser.parseFromString(html, 'text/html');
                var inner = doc.querySelector('.modal-body') || doc.body;
                body.innerHTML = inner.innerHTML +
                    '<div class="pkg-form-actions">' +
                    '<button type="button" class="ct-btn ct-btn-outline pkg-cancel-btn">Cancel</button>' +
                    '<button type="button" class="ct-btn ct-btn-primary pkg-submit-btn">Save Package</button>' +
                    '</div>';
            });
    }

    document.querySelectorAll('.open-pkg-modal').forEach(function(btn){
        btn.addEventListener('click', function(){ openModal(this.dataset.url, this.dataset.mode); });
    });

    overlay.addEventListener('click', function(e){ if(e.target===this) this.classList.remove('open'); });
    overlay.querySelector('.pkg-modal-close').addEventListener('click', function(){ overlay.classList.remove('open'); });

    document.addEventListener('click', function(e){
        if (e.target && e.target.classList.contains('pkg-cancel-btn')) { overlay.classList.remove('open'); }
        if (e.target && e.target.classList.contains('pkg-submit-btn')) {
            var form = body.querySelector('form');
            if (!form) return;
            var isUpdate = form.classList.contains('ajax-form-update');
            var url = form.getAttribute('data-url') || form.action;
            var fd = new FormData(form);
            fd.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            if (isUpdate) fd.append('_method', 'PATCH');
            document.querySelectorAll('span.input-error').forEach(function(s){s.remove();});
            e.target.disabled = true; e.target.textContent = 'Saving…';
            fetch(url, { method: 'POST', body: fd })
                .then(function(r){ return r.json(); })
                .then(function(data){
                    if (data.success) { overlay.classList.remove('open'); window.location.reload(); }
                    else { e.target.disabled = false; e.target.textContent = 'Save Package'; }
                })
                .catch(function(){ e.target.disabled = false; e.target.textContent = 'Save Package'; });
        }
    });
})();
</script>
@endsection
