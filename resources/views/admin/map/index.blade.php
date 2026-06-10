@extends('layouts.modern')
@section('title')
    <title>Live Rider Map | {{ config('app.name', 'Laravel') }}</title>
@stop

@section('content')
<div class="ct-page-header">
    <div>
        <h1 class="ct-page-title">Live Rider Map</h1>
        <p class="ct-page-subtitle">Real-time rider locations via Firebase</p>
    </div>
    <div class="ct-page-actions">
        <button id="center-btn" class="ct-btn ct-btn-outline">
            <i class="fas fa-crosshairs"></i> Center Map
        </button>
        <span class="lm-badge" id="rider-count-badge">
            <i class="fas fa-circle" style="color:#22c55e;font-size:.5rem;"></i>
            <span id="rider-count">0</span> riders online
        </span>
    </div>
</div>

<div class="ct-card lm-card">
    <div id="live_map"></div>
    <div class="lm-legend">
        <div class="lm-legend-row"><span class="lm-dot" style="background:#3b82f6;"></span> Rider</div>
    </div>
</div>

<style>
    .lm-badge { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.4rem 0.875rem; background: #dcfce7; color: #15803d; border-radius: 999px; font-size: 0.8125rem; font-weight: 600; }
    .lm-card { padding: 0; overflow: hidden; position: relative; }
    #live_map { width: 100%; height: calc(100vh - 200px); min-height: 500px; }
    .lm-legend { position: absolute; bottom: 1rem; left: 1rem; background: var(--ct-white); border-radius: 10px; padding: 0.75rem 1rem; box-shadow: 0 2px 8px rgba(0,0,0,.1); font-size: 0.8125rem; }
    .lm-legend-row { display: flex; align-items: center; gap: 0.5rem; color: var(--ct-gray-700); font-weight: 500; }
    .lm-dot { width: 11px; height: 11px; border-radius: 50%; display: inline-block; border: 2px solid #fff; box-shadow: 0 0 0 1px rgba(0,0,0,.1); }
</style>
@endsection

@section('scripts')
<script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-database-compat.js"></script>
<script>
var map;
var markers = [];
var mapCenter = { lat: 48.203231, lng: 16.3667583 };

var firebaseConfig = {
    apiKey: "{{ config('firebase.api_key') }}",
    authDomain: "ultt-ce8f2.firebaseapp.com",
    databaseURL: "https://ultt-ce8f2.firebaseio.com",
    projectId: "ultt-ce8f2",
    storageBucket: "ultt-ce8f2.appspot.com",
    messagingSenderId: "1027654555881",
    appId: "1:1027654555881:web:646826f82459642ab5f878"
};

if (!firebase.apps.length) {
    firebase.initializeApp(firebaseConfig);
}

var dbRef = firebase.database().ref();

$(document).ready(function () {
    initMap();

    $('#center-btn').on('click', function () {
        if (map) { map.setCenter(mapCenter); map.setZoom(12); }
    });
});

function initMap() {
    map = new google.maps.Map(document.getElementById('live_map'), {
        zoom: 12,
        center: mapCenter,
        mapTypeId: 'roadmap',
        streetViewControl: false
    });

    RealTimeMap();
}

function RealTimeMap() {
    dbRef.child('users/').on('value', function (snapshot) {
        var riders = snapshot.val();
        updateMarkers(riders);
    });
}

function updateMarkers(riders) {
    markers.forEach(function (m) { m.setMap(null); });
    markers = [];

    if (!riders) {
        $('#rider-count').text(0);
        return;
    }

    var count = 0;
    Object.keys(riders).forEach(function (key) {
        var rider = riders[key];
        if (rider && rider.role_id == 2 && rider.lat && rider.long) {
            var marker = new google.maps.Marker({
                position: { lat: parseFloat(rider.lat), lng: parseFloat(rider.long) },
                title: rider.email || 'Rider',
                map: map,
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 9,
                    fillColor: '#3b82f6',
                    fillOpacity: 1,
                    strokeColor: '#ffffff',
                    strokeWeight: 2
                }
            });

            var infoWindow = new google.maps.InfoWindow({
                content: '<div style="font-size:.875rem;font-weight:600;padding:4px 8px;">' +
                         '<i class="fas fa-motorcycle" style="color:#3b82f6;margin-right:6px;"></i>' +
                         (rider.email || 'Rider') + '</div>'
            });
            marker.addListener('click', function () { infoWindow.open(map, marker); });

            markers.push(marker);
            count++;
        }
    });

    $('#rider-count').text(count);
}
</script>
@endsection
