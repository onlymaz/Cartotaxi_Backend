@extends('layouts.modern')

@section('title')
    <title>Service Zones | {{ config('app.name', 'Laravel') }}</title>
@stop

@section('content')
<div class="ct-page-header">
    <div>
        <h1 class="ct-page-title">Service Zones</h1>
        <p class="ct-page-subtitle">Draw and manage delivery zones used for rider dispatch</p>
    </div>
    <div class="ct-page-actions">
        <a href="{{ route('districts.index') }}" class="ct-btn ct-btn-outline">
            <i class="fas fa-sync-alt"></i> Refresh
        </a>
    </div>
</div>

<div class="ct-card">
    <div class="zone-toolbar">
        <div class="zone-search">
            <i class="fas fa-search"></i>
            <input type="text" id="zoneSearch" placeholder="Search by zone name…">
        </div>
        <span class="zone-meta">{{ $districts->total() }} {{ Str::plural('zone', $districts->total()) }}</span>
    </div>

    @if($districts->count() > 0)
        <div class="zone-table-wrap">
            <table class="zone-table">
                <thead>
                    <tr>
                        <th style="width:60px;">ID</th>
                        <th>Zone Name</th>
                        <th>City</th>
                        <th style="width:100px;">Status</th>
                        <th style="width:100px;">Polygon</th>
                        <th style="width:130px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($districts as $d)
                        <tr class="zone-row" data-search="{{ strtolower($d->district_name ?? '') }}">
                            <td class="zone-id">#{{ $d->id }}</td>
                            <td>
                                <div class="zone-name">
                                    <div class="zone-dot {{ $d->IsActive ? 'active' : '' }}"></div>
                                    {{ $d->district_name ?? '—' }}
                                </div>
                            </td>
                            <td class="zone-city">{{ optional($d->city)->city_name ?? '—' }}</td>
                            <td>
                                <label class="zone-toggle">
                                    <input type="checkbox"
                                           class="is_active"
                                           data-url="{{ route('districts.change_status') }}"
                                           data-id="{{ $d->id }}"
                                           data-value="{{ $d->IsActive }}"
                                           {{ $d->IsActive ? 'checked' : '' }}>
                                    <span class="zone-toggle-track"></span>
                                </label>
                            </td>
                            <td>
                                @if($d->polygons)
                                    <span class="zone-has-polygon"><i class="fas fa-draw-polygon"></i> Set</span>
                                @else
                                    <span class="zone-no-polygon">Not set</span>
                                @endif
                            </td>
                            <td>
                                <button class="add_polygons ct-btn ct-btn-outline ct-btn-sm" row="{{ $d->id }}">
                                    <i class="fas fa-edit"></i> Edit Zone
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="zone-pagination">
            {{ $districts->links() }}
        </div>
    @else
        <div class="zone-empty">
            <div class="zone-empty-icon"><i class="fas fa-draw-polygon"></i></div>
            <h3>No service zones yet</h3>
            <p>Zones define the delivery areas. Add zones so riders can be dispatched correctly.</p>
        </div>
    @endif
</div>

{{-- Polygon edit modal --}}
<div id="zone_modal" class="zone-modal-overlay">
    <div class="zone-modal">
        <div class="zone-modal-head">
            <h4><i class="fas fa-draw-polygon"></i> Edit Zone Polygon</h4>
            <button type="button" class="zone-modal-close"><i class="fas fa-times"></i></button>
        </div>
        <div class="zone-modal-body" id="zone_modal_body">
            <div class="zone-modal-loading">
                <i class="fas fa-spinner fa-spin"></i> Loading map…
            </div>
        </div>
    </div>
</div>

<style>
    .zone-toolbar {
        display: flex; align-items: center; justify-content: space-between;
        padding: 1rem 1.25rem; border-bottom: 1px solid var(--ct-gray-200); gap: 1rem;
    }
    .zone-search { position: relative; min-width: 260px; }
    .zone-search i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--ct-gray-400); font-size: 0.8125rem; }
    .zone-search input {
        width: 100%; padding: 0.55rem 0.75rem 0.55rem 2.25rem;
        border: 1px solid var(--ct-gray-200); border-radius: 8px;
        font-size: 0.8125rem; color: var(--ct-gray-800);
    }
    .zone-search input:focus { outline: none; border-color: var(--ct-accent); box-shadow: 0 0 0 3px rgba(132,204,22,.15); }
    .zone-meta { font-size: 0.75rem; color: var(--ct-gray-500); font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; }
    .zone-table-wrap { overflow-x: auto; }
    .zone-table { width: 100%; border-collapse: collapse; font-size: 0.8125rem; }
    .zone-table thead th {
        background: var(--ct-gray-50); text-align: left; padding: 0.75rem 1rem;
        font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.05em;
        color: var(--ct-gray-600); font-weight: 600; border-bottom: 1px solid var(--ct-gray-200); white-space: nowrap;
    }
    .zone-table tbody td { padding: 0.875rem 1rem; border-bottom: 1px solid var(--ct-gray-100); vertical-align: middle; }
    .zone-table tbody tr:last-child td { border-bottom: none; }
    .zone-table tbody tr:hover td { background: var(--ct-gray-50); }
    .zone-row.is-hidden { display: none; }
    .zone-id { font-weight: 600; color: var(--ct-primary); }
    .zone-name { display: flex; align-items: center; gap: 0.5rem; font-weight: 600; }
    .zone-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--ct-gray-300); flex-shrink: 0; }
    .zone-dot.active { background: #22c55e; }
    .zone-city { color: var(--ct-gray-600); }
    .zone-has-polygon { display: inline-flex; align-items: center; gap: 0.375rem; padding: 3px 10px; background: #dcfce7; color: #15803d; border-radius: 999px; font-size: 0.6875rem; font-weight: 600; }
    .zone-no-polygon { font-size: 0.75rem; color: var(--ct-gray-400); }
    .zone-toggle { position: relative; display: inline-block; width: 36px; height: 20px; cursor: pointer; }
    .zone-toggle input { opacity: 0; width: 0; height: 0; }
    .zone-toggle-track {
        position: absolute; top: 0; left: 0; right: 0; bottom: 0;
        background: var(--ct-gray-300); border-radius: 999px; transition: .2s;
    }
    .zone-toggle-track::before {
        content: ''; position: absolute; left: 3px; top: 3px;
        width: 14px; height: 14px; background: #fff; border-radius: 50%; transition: .2s;
    }
    .zone-toggle input:checked + .zone-toggle-track { background: var(--ct-accent); }
    .zone-toggle input:checked + .zone-toggle-track::before { transform: translateX(16px); }
    .zone-pagination { padding: 1rem 1.25rem; border-top: 1px solid var(--ct-gray-200); display: flex; justify-content: flex-end; }
    .zone-pagination svg { width: 14px; height: 14px; }
    .zone-pagination nav[role="navigation"] { display: inline-flex; align-items: center; gap: 4px; }
    .zone-pagination span[aria-disabled], .zone-pagination a[rel], .zone-pagination span[aria-current] {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 34px; height: 34px; padding: 0 10px; border-radius: 8px;
        border: 1px solid var(--ct-gray-200); background: var(--ct-white);
        color: var(--ct-gray-700); font-size: 0.8125rem; font-weight: 500; text-decoration: none;
    }
    .zone-pagination a[rel]:hover { background: var(--ct-gray-50); }
    .zone-pagination span[aria-current] { background: var(--ct-primary); color: #fff; border-color: var(--ct-primary); }
    .zone-pagination span[aria-disabled] { color: var(--ct-gray-400); background: var(--ct-gray-50); }
    .zone-empty { padding: 4rem 2rem; text-align: center; }
    .zone-empty-icon { width: 72px; height: 72px; margin: 0 auto 1rem; border-radius: 50%; background: #f1f5f9; color: var(--ct-gray-400); display: inline-flex; align-items: center; justify-content: center; font-size: 1.75rem; }
    .zone-empty h3 { font-size: 1.125rem; font-weight: 700; color: var(--ct-primary); margin: 0 0 0.375rem; }
    .zone-empty p { color: var(--ct-gray-500); font-size: 0.875rem; max-width: 400px; margin: 0 auto; }

    .zone-modal-overlay {
        position: fixed; inset: 0; background: rgba(15,23,42,.45); z-index: 100;
        display: flex; align-items: center; justify-content: center; padding: 1rem;
        opacity: 0; pointer-events: none; transition: opacity .2s ease;
    }
    .zone-modal-overlay.open { opacity: 1; pointer-events: auto; }
    .zone-modal {
        background: var(--ct-white); border-radius: 16px; width: 100%; max-width: 780px;
        box-shadow: 0 24px 64px rgba(15,23,42,.18); overflow: hidden;
        transform: translateY(20px); transition: transform .2s ease;
    }
    .zone-modal-overlay.open .zone-modal { transform: translateY(0); }
    .zone-modal-head {
        display: flex; align-items: center; justify-content: space-between;
        padding: 1rem 1.25rem; border-bottom: 1px solid var(--ct-gray-200); gap: 1rem;
    }
    .zone-modal-head h4 { font-size: 1rem; font-weight: 700; color: var(--ct-primary); margin: 0; display: flex; align-items: center; gap: 0.5rem; }
    .zone-modal-close { background: none; border: none; cursor: pointer; color: var(--ct-gray-500); font-size: 1rem; padding: 4px 8px; border-radius: 6px; }
    .zone-modal-close:hover { background: var(--ct-gray-100); color: var(--ct-primary); }
    .zone-modal-body { padding: 1.25rem; min-height: 300px; }
    .zone-modal-loading { display: flex; align-items: center; justify-content: center; gap: 0.5rem; color: var(--ct-gray-500); padding: 4rem; font-size: 0.875rem; }
</style>
@endsection

@section('scripts')
{!! $html->scripts() !!}
<script>
(function () {
    var searchInput = document.getElementById('zoneSearch');
    var rows = document.querySelectorAll('.zone-row');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            var q = this.value.toLowerCase().trim();
            rows.forEach(function (r) {
                r.classList.toggle('is-hidden', q !== '' && (r.getAttribute('data-search') || '').indexOf(q) === -1);
            });
        });
    }

    document.querySelectorAll('.is_active').forEach(function (cb) {
        cb.addEventListener('change', function () {
            var url   = this.getAttribute('data-url');
            var id    = this.getAttribute('data-id');
            var value = this.checked ? 1 : 0;
            this.setAttribute('data-value', value);
            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ id: id, value: value })
            });
        });
    });

    var overlay = document.getElementById('zone_modal');
    var body    = document.getElementById('zone_modal_body');

    document.querySelectorAll('.add_polygons').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = this.getAttribute('row');
            body.innerHTML = '<div class="zone-modal-loading"><i class="fas fa-spinner fa-spin"></i> Loading map…</div>';
            overlay.classList.add('open');
            // X-Requested-With makes $request->ajax() true so the controller
            // returns the modal partial instead of redirecting to the index.
            fetch('{{ route("districts.create") }}?id=' + id, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.text(); })
                .then(function (html) { body.innerHTML = html; if (typeof initialize === 'function') initialize(); })
                .catch(function () { body.innerHTML = '<p style="color:#ef4444;padding:2rem;text-align:center">Failed to load map.</p>'; });
        });
    });

    if (overlay) {
        overlay.addEventListener('click', function (e) { if (e.target === this) this.classList.remove('open'); });
        document.querySelector('.zone-modal-close').addEventListener('click', function () { overlay.classList.remove('open'); });
    }

    document.addEventListener('click', function (e) {
        if (e.target && e.target.classList.contains('buttonSubmitDistrict')) {
            var district_id = document.querySelector('input[name="district_id"]').value;
            var polygons    = document.querySelector('input[name="polygons"]').value;
            var _token      = document.querySelector('input[name="_token"]').value;
            var btn         = e.target;
            btn.textContent = 'Please Wait…';
            fetch(btn.closest('form').action, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ district_id, polygons, _token })
            }).then(function () {
                overlay.classList.remove('open');
                window.location.reload();
            });
        }
    });
})();
</script>
<script>
    // Zone polygon editor. google.maps.drawing.DrawingManager was removed from
    // the Maps JS API (v3.65), so drawing is done with plain editable Polygons:
    // click the map to add vertices, drag vertices/midpoints to adjust,
    // right-click a vertex to remove it.
    var zonePolygon = null;

    function pointsToText() {
        var inp = document.querySelector('input[name="polygons"]');
        if (!inp) return;
        if (!zonePolygon || zonePolygon.getPath().getLength() === 0) { inp.value = ''; return; }
        inp.value = JSON.stringify(zonePolygon.getPath().getArray().map(function (p) { return p.toJSON(); }));
        if (zonePolygon.getPath().getLength() >= 3) {
            document.querySelectorAll('.buttonSubmitDistrict').forEach(function (b) { b.classList.remove('disabled'); });
        }
    }

    function bindPathEvents(path) {
        ['set_at', 'insert_at', 'remove_at'].forEach(function (ev) {
            google.maps.event.addListener(path, ev, pointsToText);
        });
    }

    function makeZonePolygon(map, paths) {
        var pg = new google.maps.Polygon({
            paths: paths, strokeColor: '#1E90FF', strokeOpacity: 0.8, strokeWeight: 2,
            fillColor: '#1E90FF', fillOpacity: 0.35, editable: true
        });
        pg.setMap(map);
        bindPathEvents(pg.getPath());
        google.maps.event.addListener(pg, 'rightclick', function (e) {
            if (e.vertex !== undefined && pg.getPath().getLength() > 3) pg.getPath().removeAt(e.vertex);
        });
        return pg;
    }

    function initialize() {
        var input = document.querySelector('input[name="polygons"]');
        var existing = null;
        if (input && input.value) { try { existing = JSON.parse(input.value); } catch (e) {} }

        var center = { lat: 48.203231, lng: 16.3667583 };
        if (existing && existing.length) center = { lat: existing[0].lat, lng: existing[0].lng };

        var map = new google.maps.Map(document.getElementById('map'), {
            zoom: existing ? 8 : 11, center: center, mapTypeId: 'roadmap',
            disableDefaultUI: true, zoomControl: true
        });

        zonePolygon = null;
        if (existing && existing.length) {
            zonePolygon = makeZonePolygon(map, existing);
            // fit the view to the zone
            var bounds = new google.maps.LatLngBounds();
            existing.forEach(function (p) { bounds.extend(p); });
            map.fitBounds(bounds);
        }

        // click to draw: first click creates the polygon, further clicks add vertices
        google.maps.event.addListener(map, 'click', function (e) {
            if (!zonePolygon) {
                zonePolygon = makeZonePolygon(map, [e.latLng]);
            } else {
                zonePolygon.getPath().push(e.latLng);
            }
            pointsToText();
        });

        var db = document.getElementById('delete-button');
        if (db) {
            db.classList.remove('disabled');
            db.addEventListener('click', function () {
                if (zonePolygon) { zonePolygon.setMap(null); zonePolygon = null; }
                pointsToText();
                document.querySelectorAll('.buttonSubmitDistrict').forEach(function (b) { b.classList.add('disabled'); });
            });
        }
        pointsToText();
    }
</script>
@endsection
