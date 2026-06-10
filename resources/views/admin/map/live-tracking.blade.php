@extends('layouts.modern')

@section('title')
    <title>Live Tracking | {{ config('app.name', 'Laravel') }}</title>
@endsection

@section('content')
<div class="ct-page-header" style="margin-bottom:.75rem;">
    <div>
        <h1 class="ct-page-title">Live Tracking</h1>
        <p class="ct-page-subtitle">Real-time rider positions and active order routes</p>
    </div>
</div>

<div style="display: flex; height: calc(100vh - 210px); min-height: 500px; gap: 1rem; position: relative;">
    <!-- Mobile Menu Toggle -->
    <button id="mobileMenuToggle" style="display: none; position: absolute; top: 1rem; left: 1rem; z-index: 1001; background: white; border: none; border-radius: 0.5rem; padding: 0.75rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1); cursor: pointer;">
        <i class="fas fa-bars" style="font-size: 1.25rem; color: var(--ct-gray-700);"></i>
    </button>
    
    <!-- Left Sidebar - Rider List & Controls -->
    <div id="sidebar" style="width: 350px; background: white; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; flex-direction: column; transition: transform 0.3s ease;">
        <!-- Header -->
        <div style="padding: 1.5rem; border-bottom: 1px solid var(--ct-gray-200);">
            <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--ct-gray-900); margin: 0 0 1rem 0;">
                <i class="fas fa-users" style="color: var(--ct-accent); margin-right: 0.5rem;"></i>
                Active Riders
            </h2>
            
            <!-- Search Bar -->
            <div style="margin-bottom: 1rem;">
                <div style="position: relative;">
                    <input type="text" id="riderSearch" class="lt-input" placeholder="Search riders or orders..." style="width: 100%; padding-left: 2.5rem;">
                    <i class="fas fa-search" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--ct-gray-400);"></i>
                    <button id="clearSearch" style="position: absolute; right: 0.5rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--ct-gray-400); cursor: pointer; display: none; padding: 0.25rem;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            
            <!-- Filters -->
            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--ct-gray-600); margin-bottom: 0.25rem; text-transform: uppercase;">Status Filter</label>
                    <select id="statusFilter" class="lt-select" style="width: 100%;">
                        <option value="all">All Riders</option>
                        <option value="active">With Active Orders</option>
                        <option value="available">Available</option>
                        <option value="offline">Offline</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--ct-gray-600); margin-bottom: 0.25rem; text-transform: uppercase;">Order Status</label>
                    <select id="orderStatusFilter" class="lt-select" style="width: 100%;">
                        <option value="all">All Orders</option>
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="picking">Picking</option>
                        <option value="picked_up">Picked Up</option>
                        <option value="on_way">On The Way</option>
                        <option value="accident">Accident</option>
                        <option value="not_received">Not Received</option>
                        <option value="refused">Refused</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancel">Cancelled</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--ct-gray-600); margin-bottom: 0.25rem; text-transform: uppercase;">Last Seen</label>
                    <select id="lastSeenFilter" class="lt-select" style="width: 100%;">
                        <option value="all">All Time</option>
                        <option value="5">Last 5 minutes</option>
                        <option value="15">Last 15 minutes</option>
                        <option value="30">Last 30 minutes</option>
                        <option value="60">Last 1 hour</option>
                        <option value="240">Last 4 hours</option>
                        <option value="1440">Last 24 hours</option>
                    </select>
                </div>
                <button id="refreshMap" type="button" class="ct-btn ct-btn-primary" style="width: 100%;">
                    <i class="fas fa-sync-alt"></i> Refresh Map
                </button>
                <div style="margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid var(--ct-gray-200);">
                    <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--ct-gray-600); margin-bottom: 0.25rem; text-transform: uppercase;">Auto Refresh</label>
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                        <input type="checkbox" id="autoRefreshToggle" checked style="width: auto;">
                        <label for="autoRefreshToggle" style="font-size: 0.875rem; margin: 0; cursor: pointer;">Enabled</label>
                    </div>
                    <select id="refreshInterval" class="lt-select" style="width: 100%;">
                        <option value="1000">Every 1 second</option>
                        <option value="3000" selected>Every 3 seconds</option>
                        <option value="5000">Every 5 seconds</option>
                        <option value="10000">Every 10 seconds</option>
                        <option value="30000">Every 30 seconds</option>
                        <option value="60000">Every 1 minute</option>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Rider List -->
        <div id="riderList" style="flex: 1; overflow-y: auto; padding: 1rem;">
            <div style="text-align: center; padding: 2rem; color: var(--ct-gray-500);">
                <i class="fas fa-spinner fa-spin" style="font-size: 2rem; margin-bottom: 1rem;"></i>
                <p>Loading riders...</p>
            </div>
        </div>
    </div>
    
    <!-- Mobile Overlay -->
    <div id="mobileOverlay" class="mobile-overlay"></div>
    
    <!-- Main Map Area -->
    <div id="mapContainer" class="map-container" style="flex: 1; position: relative; background: white; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
        <!-- Map Controls -->
        <div id="mapControls" style="position: absolute; top: 1rem; right: 1rem; z-index: 1000; display: flex; flex-direction: column; gap: 0.5rem; pointer-events: auto;">
            <button id="centerMap" type="button" class="ct-btn ct-btn-outline" style="padding: 0.75rem; min-width: auto; cursor: pointer; pointer-events: auto;" title="Center Map">
                <i class="fas fa-crosshairs"></i>
            </button>
            <button id="toggleTraffic" type="button" class="ct-btn ct-btn-outline" style="padding: 0.75rem; min-width: auto; cursor: pointer; pointer-events: auto;" title="Toggle Traffic">
                <i class="fas fa-traffic-light"></i>
            </button>
            <button id="toggleSatellite" type="button" class="ct-btn ct-btn-outline" style="padding: 0.75rem; min-width: auto; cursor: pointer; pointer-events: auto;" title="Toggle Satellite View">
                <i class="fas fa-globe"></i>
            </button>
            <button id="toggleTrails" type="button" class="ct-btn ct-btn-outline active" style="padding: 0.75rem; min-width: auto; cursor: pointer; pointer-events: auto;" title="Toggle Movement Trails">
                <i class="fas fa-route"></i>
            </button>
            <button id="toggleClustering" type="button" class="ct-btn ct-btn-outline active" style="padding: 0.75rem; min-width: auto; cursor: pointer; pointer-events: auto;" title="Toggle Marker Clustering">
                <i class="fas fa-layer-group"></i>
            </button>
            <button id="toggleFullscreen" type="button" class="ct-btn ct-btn-outline" style="padding: 0.75rem; min-width: auto; cursor: pointer; pointer-events: auto;" title="Toggle Fullscreen (F11)">
                <i class="fas fa-expand"></i>
            </button>
            <button id="showLegend" type="button" class="ct-btn ct-btn-outline" style="padding: 0.75rem; min-width: auto; cursor: pointer; pointer-events: auto;" title="Show Map Legend">
                <i class="fas fa-info-circle"></i>
            </button>
            <button id="stopFollowing" type="button" class="ct-btn ct-btn-danger" style="padding: 0.75rem; min-width: auto; display: none; cursor: pointer; pointer-events: auto;" title="Stop Following Rider">
                <i class="fas fa-stop"></i>
            </button>
        </div>
        
        <!-- Map Legend -->
        <div id="mapLegend" style="position: absolute; bottom: 1rem; left: 1rem; z-index: 1000; background: white; padding: 1rem; border-radius: 0.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1); min-width: 200px; display: none;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                <h3 style="font-size: 0.875rem; font-weight: 700; margin: 0;">Map Legend</h3>
                <button id="closeLegend" style="background: none; border: none; color: var(--ct-gray-400); cursor: pointer; padding: 0.25rem;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div style="display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.75rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div style="width: 12px; height: 12px; border-radius: 50%; background: #10b981; border: 2px solid white;"></div>
                    <span>Available Rider</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div style="width: 12px; height: 12px; border-radius: 50%; background: #f59e0b; border: 2px solid white;"></div>
                    <span>Rider on Order</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div style="width: 12px; height: 12px; border-radius: 50%; background: #3b82f6; border: 2px solid white;"></div>
                    <span>Search Match</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div style="width: 12px; height: 12px; border-radius: 50%; background: #9ca3af; border: 2px solid white;"></div>
                    <span>Offline Rider</span>
                </div>
                <div style="margin-top: 0.5rem; padding-top: 0.5rem; border-top: 1px solid var(--ct-gray-200);">
                    <div style="font-weight: 600; margin-bottom: 0.25rem;">Routes</div>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <div style="width: 30px; height: 4px; background: #f59e0b;"></div>
                        <span>Active Order Route</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <div style="width: 30px; height: 3px; background: #3b82f6;"></div>
                        <span>Movement Trail</span>
                    </div>
                </div>
                <div style="margin-top: 0.5rem; padding-top: 0.5rem; border-top: 1px solid var(--ct-gray-200);">
                    <div style="font-weight: 600; margin-bottom: 0.25rem;">Keyboard Shortcuts</div>
                    <div style="font-size: 0.7rem; color: var(--ct-gray-600);">
                        <div><kbd style="background: #f3f4f6; padding: 0.125rem 0.25rem; border-radius: 0.25rem;">C</kbd> Center Map</div>
                        <div><kbd style="background: #f3f4f6; padding: 0.125rem 0.25rem; border-radius: 0.25rem;">T</kbd> Traffic</div>
                        <div><kbd style="background: #f3f4f6; padding: 0.125rem 0.25rem; border-radius: 0.25rem;">S</kbd> Satellite</div>
                        <div><kbd style="background: #f3f4f6; padding: 0.125rem 0.25rem; border-radius: 0.25rem;">R</kbd> Refresh</div>
                        <div><kbd style="background: #f3f4f6; padding: 0.125rem 0.25rem; border-radius: 0.25rem;">F</kbd> Trails</div>
                        <div><kbd style="background: #f3f4f6; padding: 0.125rem 0.25rem; border-radius: 0.25rem;">Esc</kbd> Close Info</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Map Stats -->
        <div id="mapStats" style="position: absolute; top: 1rem; left: 1rem; z-index: 1000; background: white; padding: 1rem; border-radius: 0.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1); min-width: 200px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                <span style="font-size: 0.875rem; color: var(--ct-gray-600);">Active Riders</span>
                <span id="activeRidersCount" style="font-weight: 700; color: var(--ct-accent);">0</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                <span style="font-size: 0.875rem; color: var(--ct-gray-600);">Active Orders</span>
                <span id="activeOrdersCount" style="font-weight: 700; color: #10b981;">0</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 0.875rem; color: var(--ct-gray-600);">Last Update</span>
                <span id="lastUpdate" style="font-size: 0.75rem; color: var(--ct-gray-500);">--:--</span>
            </div>
        </div>
        
        <!-- Map Container -->
        <div id="liveTrackingMap" style="width: 100%; height: 100%; min-height: 400px;"></div>
    </div>
</div>
@endsection

@section('styles')
<style>
/* ct-theme form controls for live tracking sidebar */
.lt-input {
    width: 100%; padding: 0.5rem 0.75rem;
    border: 1px solid var(--ct-gray-200); border-radius: 8px;
    font-size: 0.875rem; color: var(--ct-gray-800);
    background: var(--ct-gray-50); box-sizing: border-box;
    transition: border-color .15s, box-shadow .15s;
}
.lt-input:focus { outline: none; border-color: var(--ct-accent); box-shadow: 0 0 0 3px rgba(132,204,22,.15); background: var(--ct-white); }
.lt-select {
    width: 100%; padding: 0.5rem 0.75rem;
    border: 1px solid var(--ct-gray-200); border-radius: 8px;
    font-size: 0.875rem; color: var(--ct-gray-800);
    background: var(--ct-gray-50); box-sizing: border-box;
    appearance: none; cursor: pointer;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%236b7280'%3E%3Cpath fill-rule='evenodd' d='M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 0.6rem center; background-size: 1rem;
}
.lt-select:focus { outline: none; border-color: var(--ct-accent); box-shadow: 0 0 0 3px rgba(132,204,22,.15); }
.ct-btn-danger { background: #ef4444; color: #fff; border-color: #ef4444; }
.ct-btn-danger:hover { background: #dc2626; }

.rider-card {
    padding: 1rem;
    border: 1px solid var(--ct-gray-200);
    border-radius: 0.5rem;
    margin-bottom: 0.75rem;
    cursor: pointer;
    transition: all 0.2s;
    background: white;
}

.rider-card:hover {
    border-color: var(--ct-accent);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.rider-card.active {
    border-color: var(--ct-accent);
    background: rgba(255, 149, 0, 0.05);
}

.rider-card.search-match {
    border-color: #3b82f6;
    background: rgba(59, 130, 246, 0.05);
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
}

.rider-card-header {
    display: flex; 
    align-items: center; 
    gap: 0.75rem; 
    margin-bottom: 0.5rem;
}

.rider-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--ct-accent);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 0.875rem;
}

.rider-info {
    flex: 1;
}

.rider-name {
    font-weight: 600;
    color: var(--ct-gray-900);
    font-size: 0.875rem;
}

.rider-status {
    font-size: 0.75rem;
    color: var(--ct-gray-500);
}

.rider-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
}

.status-online { background: rgba(16, 185, 129, 0.1); color: #047857; }
.status-offline { background: rgba(107, 114, 128, 0.1); color: #374151; }
.status-busy { background: rgba(245, 158, 11, 0.1); color: #b45309; }

.order-info {
    margin-top: 0.5rem;
    padding-top: 0.5rem;
    border-top: 1px solid var(--ct-gray-100);
    font-size: 0.75rem;
    color: var(--ct-gray-600);
}

.order-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.7rem;
    font-weight: 600;
    margin-top: 0.25rem;
}

.order-pending { background: rgba(245, 158, 11, 0.1); color: #b45309; }
.order-picking { background: rgba(59, 130, 246, 0.1); color: #1d4ed8; }
.order-on-way { background: rgba(16, 185, 129, 0.1); color: #047857; }

.map-container {
    width: 100%;
    height: 100%;
    min-height: 400px;
}

#liveTrackingMap {
    width: 100%;
    height: 100%;
    min-height: 400px;
}

@media (max-width: 768px) {
    #sidebar {
        position: fixed;
        left: 0;
        top: 0;
        bottom: 0;
        width: 300px !important;
        z-index: 1000;
        transform: translateX(-100%);
        box-shadow: 2px 0 8px rgba(0,0,0,0.2);
    }
    
    #sidebar.mobile-open {
        transform: translateX(0);
    }
    
    #mobileMenuToggle {
        display: block !important;
    }
    
    .map-container {
        width: 100% !important;
        height: 100% !important;
        min-height: 400px !important;
        margin-left: 0 !important;
    }
    
    #liveTrackingMap {
        width: 100% !important;
        height: 100% !important;
        min-height: 400px !important;
    }
    
    #mapStats {
        min-width: 150px !important;
        padding: 0.75rem !important;
        font-size: 0.75rem !important;
    }
    
    #mapControls {
        flex-direction: column;
        gap: 0.25rem;
    }
    
    #mapControls button {
        padding: 0.5rem !important;
        min-width: 40px !important;
    }
}

.mobile-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 999;
}

.mobile-overlay.active {
    display: block;
}
</style>
@endsection

@section('scripts')
<script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-database-compat.js"></script>
<script src="https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js"></script>
<script>
// ============================================
// COMPLETE REBUILD - CLEAN IMPLEMENTATION
// ============================================

console.log('🗺️ Live Tracking Map - Starting Fresh Build');

// ========== GLOBAL VARIABLES ==========
let map = null;
let markers = {};
let routes = {};
let infoWindows = {};
let riderPaths = {};
let updateInterval = null;
let directionsService = null;
let directionsRenderer = null;
let trafficLayer = null;
let markerCluster = null;
let currentRiders = [];
let isMapReady = false;
let mapCenter = { lat: 48.203231, lng: 16.3667583 };
let mapZoom = 12;
let mapTypeId = 'roadmap';
let autoRefreshEnabled = true;
let refreshInterval = 3000;
let trailsEnabled = true;
let clusteringEnabled = true;
let followingRiderId = null;
let riderHistory = {}; // Store rider movement history for trails
let riderETAs = {}; // Store route ETA information
let riderSpeeds = {}; // Store rider speeds
let riderHeadings = {}; // Store rider headings
let openInfoWindowId = null; // Track which info window is currently open

// Firebase Config
const firebaseConfig = {
    apiKey: "{{ config('firebase.api_key') }}",
    authDomain: "ultt-ce8f2.firebaseapp.com",
    databaseURL: "https://ultt-ce8f2.firebaseio.com",
    projectId: "ultt-ce8f2",
    storageBucket: "ultt-ce8f2.appspot.com",
    messagingSenderId: "1027654555881",
    appId: "1:1027654555881:web:646826f82459642ab5f878"
};

// Initialize Firebase
if (typeof firebase !== 'undefined' && !firebase.apps.length) {
    firebase.initializeApp(firebaseConfig);
}

// ========== MAP INITIALIZATION ==========
function initializeMap() {
    console.log('🔵 Initializing Google Map...');
    
        const mapElement = document.getElementById('liveTrackingMap');
        if (!mapElement) {
        console.error('❌ Map element not found');
        return false;
    }
    
    // Ensure element has dimensions
    mapElement.style.width = '100%';
    mapElement.style.height = '100%';
    mapElement.style.minHeight = '400px';
    
    // Check if Google Maps is loaded
    if (typeof google === 'undefined' || typeof google.maps === 'undefined' || typeof google.maps.Map === 'undefined') {
            console.error('❌ Google Maps API not loaded');
        return false;
        }
        
    try {
        // Create map
        map = new google.maps.Map(mapElement, {
            center: mapCenter,
            zoom: mapZoom,
            mapTypeId: mapTypeId,
            disableDefaultUI: false,
            zoomControl: true,
            mapTypeControl: true,
            scaleControl: true,
            streetViewControl: false,
            fullscreenControl: true
        });
        
        // Initialize directions service
        directionsService = new google.maps.DirectionsService();
        directionsRenderer = new google.maps.DirectionsRenderer({
            suppressMarkers: true,
            polylineOptions: {
                strokeColor: '#FF9500',
                strokeOpacity: 0.8,
                strokeWeight: 6
            }
        });
        
        // Add traffic layer
            trafficLayer = new google.maps.TrafficLayer();
        
        // Initialize marker clusterer (will be set up when markers are added)
        markerCluster = null;
        
        console.log('✅ Map initialized successfully');
        
        // Wait for map to be ready
        google.maps.event.addListenerOnce(map, 'idle', function() {
            isMapReady = true;
            console.log('✅ Map is ready');
            loadRiders();
            startAutoRefresh();
        });
        
        return true;
                    } catch (error) {
        console.error('❌ Error creating map:', error);
        return false;
    }
}

// ========== LOAD RIDERS ==========
function loadRiders() {
    console.log('📡 Loading riders from server...');
    
    $.ajax({
        url: '{{ route("map.ajax") }}',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success && response.riders) {
                console.log('✅ Loaded', response.riders.length, 'riders');
                currentRiders = response.riders;
                updateMapMarkers(response.riders);
                updateRiderList(response.riders);
                updateStats(response.stats);
            }
        },
        error: function(xhr, status, error) {
            console.error('❌ Error loading riders:', error);
                }
            });
        }

// ========== UPDATE MAP MARKERS ==========
function updateMapMarkers(riders) {
    if (!map || !isMapReady) return;
    
    // Store previous positions for trails
    const previousPositions = {};
    Object.keys(markers).forEach(function(riderId) {
        if (markers[riderId] && markers[riderId].getPosition) {
            const pos = markers[riderId].getPosition();
            previousPositions[riderId] = { lat: pos.lat(), lng: pos.lng() };
        }
    });
    
    // Track which info window is open before clearing
    const wasInfoWindowOpen = openInfoWindowId !== null;
    const openRiderId = openInfoWindowId;
    
    // Clear existing markers
    Object.values(markers).forEach(function(marker) {
        if (marker && marker.setMap) {
            marker.setMap(null);
        }
    });
    markers = {};
    
    // Clear info windows but remember which one was open
    Object.values(infoWindows).forEach(function(iw) {
        if (iw && iw.close) {
            iw.close();
        }
    });
    infoWindows = {};
    openInfoWindowId = null;
    
    // Clear routes (but keep history for trails)
    Object.values(routes).forEach(function(route) {
        if (route) {
            if (route.setMap) {
                route.setMap(null);
            } else if (route.renderer && route.renderer.setMap) {
                route.renderer.setMap(null);
            } else if (route.polyline && route.polyline.setMap) {
                route.polyline.setMap(null);
            }
        }
    });
    routes = {};
    
    // Update rider history for trails and calculate speed/heading
    riders.forEach(function(rider) {
        if (!rider.lat || !rider.long) return;
        
        const lat = parseFloat(rider.lat);
        const lng = parseFloat(rider.long || rider.lng);
        
        if (isNaN(lat) || isNaN(lng)) return;
        
        // Add to history for trails
        if (!riderHistory[rider.id]) {
            riderHistory[rider.id] = [];
        }
        
        const currentPos = { lat: lat, lng: lng, timestamp: Date.now() };
        const prevPos = previousPositions[rider.id];
        
        // Only add if position changed
        if (!prevPos || prevPos.lat !== lat || prevPos.lng !== lng) {
            riderHistory[rider.id].push(currentPos);
            
            // Keep only last 50 points
            if (riderHistory[rider.id].length > 50) {
                riderHistory[rider.id].shift();
            }
            
            // Update speed and heading
            if (prevPos && typeof google !== 'undefined' && google.maps && google.maps.geometry) {
                updateRiderSpeedAndHeading(rider.id, currentPos, prevPos);
            }
        }
    });
    
    // Create markers for each rider
    riders.forEach(function(rider) {
        if (!rider.lat || !rider.long) return;
        
        const lat = parseFloat(rider.lat);
        const lng = parseFloat(rider.long || rider.lng);
        
        if (isNaN(lat) || isNaN(lng)) return;
        
        const hasOrder = rider.active_order !== null;
        const markerColor = hasOrder ? '#f59e0b' : '#10b981';
        
        // Create marker
        const marker = new google.maps.Marker({
            position: { lat: lat, lng: lng },
            map: map,
            title: rider.first_name + ' ' + rider.last_name,
            icon: {
                path: google.maps.SymbolPath.CIRCLE,
                scale: 10,
                fillColor: markerColor,
                fillOpacity: 1,
                strokeColor: '#ffffff',
                strokeWeight: 2
            }
        });
        
        // Create or reuse info window
        let infoWindow = infoWindows[rider.id];
        if (!infoWindow) {
            const infoContent = createInfoWindowContent(rider);
            infoWindow = new google.maps.InfoWindow({
                content: infoContent
            });
            infoWindows[rider.id] = infoWindow;
            
            // Track when info window closes
            google.maps.event.addListener(infoWindow, 'closeclick', function() {
                if (openInfoWindowId == rider.id) {
                    openInfoWindowId = null;
        }
    });
}

        marker.addListener('click', function() {
            // Close all other info windows
            Object.values(infoWindows).forEach(function(iw) {
                if (iw && iw !== infoWindow && iw.close) {
                    iw.close();
                }
            });
            
            // Open this info window
            infoWindow.setContent(createInfoWindowContent(rider));
            infoWindow.open(map, marker);
            openInfoWindowId = rider.id;
        });
        
        // Reopen info window if it was open before update
        if (wasInfoWindowOpen && openRiderId == rider.id) {
                setTimeout(function() {
                infoWindow.setContent(createInfoWindowContent(rider));
                infoWindow.open(map, marker);
                openInfoWindowId = rider.id;
            }, 100);
        }
        
        markers[rider.id] = marker;
        
        // Draw movement trail if enabled
        if (trailsEnabled && riderHistory[rider.id] && riderHistory[rider.id].length > 1) {
            drawMovementTrail(rider.id, riderHistory[rider.id]);
        }
        
        // Draw route if rider has active order (delay to ensure marker is created first)
        if (hasOrder && rider.active_order) {
            setTimeout(function() {
                drawRouteForRider(rider.id, rider.active_order);
            }, 200);
        }
    });
    
    // Update marker clustering
    updateMarkerClustering();
    
    // Follow rider if enabled
    if (followingRiderId && markers[followingRiderId]) {
        map.setCenter(markers[followingRiderId].getPosition());
        map.setZoom(15);
        $('#stopFollowing').show();
                    } else {
        $('#stopFollowing').hide();
    }
    
    console.log('✅ Updated', Object.keys(markers).length, 'markers on map');
}

// ========== DRAW MOVEMENT TRAIL ==========
function drawMovementTrail(riderId, history) {
    if (!map || !history || history.length < 2) return;
    
    // Remove old trail
    if (riderPaths[riderId] && riderPaths[riderId].setMap) {
            riderPaths[riderId].setMap(null);
    }
    
    // Create path from history
    const path = history.map(function(point) {
        return new google.maps.LatLng(point.lat, point.lng);
    });
        
        // Create polyline for trail
        const trail = new google.maps.Polyline({
            path: path,
            geodesic: true,
            strokeColor: '#3b82f6',
            strokeOpacity: 0.6,
            strokeWeight: 3,
        map: trailsEnabled ? map : null
        });
        
        riderPaths[riderId] = trail;
}

// ========== UPDATE MARKER CLUSTERING ==========
function updateMarkerClustering() {
    if (!map) return;
    
    // Clear existing cluster
    if (markerCluster && markerCluster.clearMarkers) {
        markerCluster.clearMarkers();
        markerCluster = null;
    }
    
    if (clusteringEnabled && typeof markerClusterer !== 'undefined') {
        const markerArray = Object.values(markers).filter(function(m) { return m !== null; });
        if (markerArray.length > 0) {
    try {
        markerCluster = new markerClusterer.MarkerClusterer({
            map: map,
                    markers: markerArray
                });
            } catch (e) {
                console.warn('⚠️ Marker clustering failed:', e);
            }
        }
    } else if (!clusteringEnabled) {
        // Show all markers on map when clustering is disabled
        Object.values(markers).forEach(function(marker) {
            if (marker) {
                marker.setMap(map);
            }
        });
    }
}

// ========== CREATE INFO WINDOW CONTENT ==========
function createInfoWindowContent(rider) {
    const fullName = (rider.first_name || '') + ' ' + (rider.last_name || '');
    const hasOrder = rider.active_order !== null;
    const status = hasOrder ? 'On Order' : 'Available';
    const isFollowing = followingRiderId == rider.id;
    
    // Get speed and heading
    const speed = riderSpeeds[rider.id] || 0;
    const heading = riderHeadings[rider.id] || null;
    const speedColor = speed > 60 ? '#ef4444' : (speed > 40 ? '#f59e0b' : '#10b981');
    const speedDisplay = speed > 0 ? Math.round(speed) + ' km/h' : '0 km/h';
    const headingDisplay = heading !== null ? Math.round(heading) + '°' : 'N/A';
    
    // Get ETA data if available
    const etaData = riderETAs[rider.id] || null;
    
    // Check if ride has started (order status indicates rider is on the way)
    const orderStatus = hasOrder && rider.active_order ? rider.active_order.order_status : null;
    const rideStarted = orderStatus && ['on_way', 'picked_up', 'picking'].includes(orderStatus);
    
    const etaDisplay = hasOrder && rider.active_order ? `
        <div style="margin-top: 0.5rem; padding: 0.75rem; background: linear-gradient(135deg, rgba(59, 130, 246, 0.15) 0%, rgba(16, 185, 129, 0.15) 100%); border-radius: 0.5rem; border: 2px solid rgba(59, 130, 246, 0.3);">
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                <i class="fas fa-route" style="color: #3b82f6; font-size: 1rem;"></i>
                <span style="font-weight: 700; color: #1e40af; font-size: 0.875rem;">ROUTE INFORMATION</span>
            </div>
            ${etaData ? `
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; padding: 0.5rem; background: white; border-radius: 0.375rem;">
                <span style="font-size: 0.875rem; color: #6b7280; display: flex; align-items: center; gap: 0.25rem;">
                    <i class="fas fa-ruler-horizontal" style="color: #3b82f6;"></i> Distance:
                </span>
                <span style="font-weight: 700; color: #1d4ed8; font-size: 1rem;">${etaData.distance || 'N/A'}</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; padding: 0.5rem; background: white; border-radius: 0.375rem;">
                <span style="font-size: 0.875rem; color: #6b7280; display: flex; align-items: center; gap: 0.25rem;">
                    <i class="fas fa-clock" style="color: #f59e0b;"></i> Travel Time:
                </span>
                <span style="font-weight: 700; color: #b45309; font-size: 1rem;">${etaData.duration || 'N/A'}</span>
            </div>
            ` : ''}
            ${rideStarted && etaData && etaData.expectedDelivery ? `
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 0.375rem; color: white;">
                <span style="font-size: 0.875rem; display: flex; align-items: center; gap: 0.25rem; font-weight: 600;">
                    <i class="fas fa-calendar-check"></i> Expected Delivery:
                </span>
                <span style="font-weight: 700; font-size: 1.1rem;">${etaData.expectedDelivery}</span>
            </div>
            ` : !rideStarted ? `
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 0.375rem; color: white;">
                <span style="font-size: 0.875rem; display: flex; align-items: center; gap: 0.25rem; font-weight: 600;">
                    <i class="fas fa-hourglass-half"></i> Status:
                </span>
                <span style="font-weight: 700; font-size: 1rem;">Waiting for rider to start the ride</span>
            </div>
            ` : ''}
        </div>
    ` : '';
    
    let orderDetails = '';
    if (hasOrder && rider.active_order) {
        const order = rider.active_order;
        orderDetails = `
            <div style="margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid #e5e7eb;">
                <div style="font-weight: 600; margin-bottom: 0.5rem;">Active Order</div>
                <div style="font-size: 0.875rem; color: #6b7280;">
                    <div><strong>Booking ID:</strong> ${order.booking_id || 'N/A'}</div>
                    <div><strong>Status:</strong> ${order.order_status || 'N/A'}</div>
                    ${order.start_location ? `<div><strong>From:</strong> ${order.start_location}</div>` : ''}
                    ${order.end_location ? `<div><strong>To:</strong> ${order.end_location}</div>` : ''}
                    ${order.customer_name ? `<div><strong>Customer:</strong> ${order.customer_name}</div>` : ''}
                </div>
                ${etaDisplay}
            </div>
        `;
    }
    
    // Speed and heading info
    const speedInfo = `
        <div style="margin-top: 0.5rem; padding: 0.5rem; background: rgba(16, 185, 129, 0.1); border-radius: 0.375rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                <span style="font-size: 0.75rem; color: #6b7280;"><i class="fas fa-tachometer-alt"></i> Speed:</span>
                <span style="font-weight: 600; color: ${speedColor};">${speedDisplay}</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 0.75rem; color: #6b7280;"><i class="fas fa-compass"></i> Heading:</span>
                <span style="font-weight: 600; color: #1d4ed8;">${headingDisplay}</span>
            </div>
        </div>
    `;
    
    const followButton = `
        <div style="margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid #e5e7eb;">
            <button onclick="toggleFollowRider(${rider.id})" style="width: 100%; padding: 0.5rem; background: ${isFollowing ? '#ef4444' : '#3b82f6'}; color: white; border: none; border-radius: 0.375rem; cursor: pointer; font-weight: 600; margin-bottom: 0.5rem;">
                <i class="fas fa-${isFollowing ? 'stop' : 'location-arrow'}"></i>
                ${isFollowing ? 'Stop Following' : 'Follow Rider'}
            </button>
            ${hasOrder ? `
            <button onclick="forceDrawRoute(${rider.id})" style="width: 100%; padding: 0.5rem; background: #f59e0b; color: white; border: none; border-radius: 0.375rem; cursor: pointer; font-weight: 600;">
                <i class="fas fa-route"></i> Show Route
            </button>
            ` : ''}
        </div>
    `;
    
    return `
        <div style="min-width: 200px; max-width: 350px;">
            <div style="font-weight: 700; font-size: 1rem; margin-bottom: 0.5rem;">${fullName.trim() || rider.email || 'Rider'}</div>
            <div style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.5rem;">
                <i class="fas fa-circle" style="color: ${hasOrder ? '#f59e0b' : '#10b981'}; font-size: 0.5rem;"></i>
                ${status}
            </div>
            ${rider.phone_number ? `<div style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.5rem;"><i class="fas fa-phone"></i> ${rider.phone_number}</div>` : ''}
            ${rider.lat && (rider.long || rider.lng) ? `<div style="font-size: 0.75rem; color: #9ca3af; margin-bottom: 0.5rem;"><i class="fas fa-map-marker-alt"></i> ${parseFloat(rider.lat).toFixed(6)}, ${parseFloat(rider.long || rider.lng).toFixed(6)}</div>` : ''}
            ${speedInfo}
            ${orderDetails}
            ${followButton}
        </div>
    `;
}

// ========== DRAW ROUTE FOR RIDER ==========
function drawRouteForRider(riderId, order) {
    console.log(`🔵 drawRoute called for rider ${riderId}`, order);
    
    // Remove existing route if any
    if (routes[riderId]) {
        console.log('Removing existing route...');
        if (routes[riderId].setMap) {
            routes[riderId].setMap(null);
        } else if (routes[riderId].renderer && routes[riderId].renderer.setMap) {
            routes[riderId].renderer.setMap(null);
        } else if (routes[riderId].polyline && routes[riderId].polyline.setMap) {
            routes[riderId].polyline.setMap(null);
        }
        delete routes[riderId];
    }
    
    // Check if we have coordinates (support both 'lng' and 'long' formats)
    const startLng = order.start_lng || order.start_long;
    const endLng = order.end_lng || order.end_long;
    const hasCoordinates = order.start_lat && startLng && order.end_lat && endLng;
    const hasAddresses = order.start_location && order.end_location;
    
    console.log(`Route data check: hasCoordinates=${hasCoordinates}, hasAddresses=${hasAddresses}`, {
        start_lat: order.start_lat,
        start_lng: startLng,
        end_lat: order.end_lat,
        end_lng: endLng
    });
    
    if (!hasCoordinates && !hasAddresses) {
        console.warn(`⚠️ No coordinates or addresses for rider ${riderId}, using simple route`);
        drawSimpleRoute(riderId, order);
        return;
    }
    
    // Get rider's current position
    const rider = currentRiders.find(r => r.id == riderId);
    const riderPos = rider && rider.lat && (rider.long || rider.lng) 
        ? { lat: parseFloat(rider.lat), lng: parseFloat(rider.long || rider.lng) }
        : null;
    
    console.log(`Rider position:`, riderPos);
    
    // Determine origin (rider's current position or start location)
    let origin;
    if (riderPos) {
        origin = riderPos;
        console.log('Using rider current position as origin');
    } else if (hasCoordinates) {
        origin = `${order.start_lat},${startLng}`;
        console.log('Using start coordinates as origin');
    } else {
        origin = order.start_location;
        console.log('Using start location address as origin');
    }
    
    // Determine destination
    let destination;
    if (hasCoordinates) {
        destination = `${order.end_lat},${endLng}`;
        console.log('Using end coordinates as destination');
    } else {
        destination = order.end_location;
        console.log('Using end location address as destination');
    }
    
    // Validate origin and destination
    if (!origin || !destination) {
        console.error(`❌ Invalid route parameters for rider ${riderId}: origin=${origin}, destination=${destination}`);
        drawSimpleRoute(riderId, order);
        return;
    }
    
    console.log(`🚀 Drawing route for rider ${riderId} from ${JSON.stringify(origin)} to ${JSON.stringify(destination)}`);
    
    // Check if directionsService is available
    if (!directionsService) {
        console.warn('Directions Service not available, using simple route');
        drawSimpleRoute(riderId, order);
        return;
    }
    
    // Use Directions Service to get route FIRST, then create renderer
    directionsService.route({
        origin: origin,
        destination: destination,
        travelMode: google.maps.TravelMode.DRIVING,
        optimizeWaypoints: false,
        avoidHighways: false,
        avoidTolls: false
    }, function(result, status) {
        console.log(`Directions API response for rider ${riderId}: status=${status}`, result);
        
        // Handle specific error cases FIRST - silently fallback to simple route
        if (status !== 'OK') {
            // Log to console only, no user notifications
            if (status === 'REQUEST_DENIED') {
                if (!window.directionsApiErrorShown) {
                    console.info('ℹ️ Directions API not enabled. Using simple routes (straight lines).');
                    console.info('   To enable turn-by-turn routes, enable Directions API in Google Cloud Console.');
                    window.directionsApiErrorShown = true;
                }
            } else if (status === 'OVER_QUERY_LIMIT') {
                console.warn('⚠️ Directions API quota exceeded. Using simple routes.');
            } else {
                // Silently handle other errors
                console.debug(`Directions API ${status} - Using simple route`);
            }
            
            // Fallback to simple route for all errors (silently, no notifications)
            drawSimpleRoute(riderId, order);
            return;
        }
        
        if (result && result.routes && result.routes.length > 0) {
            try {
                // Create directions renderer AFTER getting successful result
                const routeRenderer = new google.maps.DirectionsRenderer({
                    map: map,
                    directions: result,
                    suppressMarkers: true, // We'll use custom waypoint markers
                    polylineOptions: {
                        strokeColor: '#f59e0b',
                        strokeWeight: 6, // Thicker line for better visibility
                        strokeOpacity: 0.8
                    },
                    preserveViewport: false // Auto-fit the route in view
                });
                
                // Explicitly set the map to ensure route is visible
                routeRenderer.setMap(map);
                routes[riderId] = routeRenderer;
                console.log(`✅ Route successfully drawn for rider ${riderId}`);
            
            // Calculate and store ETA and distance
            const route = result.routes[0];
            if (route && route.legs && route.legs.length > 0) {
                const leg = route.legs[0];
                const distance = leg.distance ? leg.distance.text : 'N/A';
                const duration = leg.duration ? leg.duration.text : 'N/A';
                
                // Calculate expected delivery time
                const now = new Date();
                const durationSeconds = leg.duration ? leg.duration.value : 0;
                const expectedDelivery = new Date(now.getTime() + durationSeconds * 1000);
                const deliveryTimeStr = expectedDelivery.toLocaleTimeString('en-US', { 
                    hour: '2-digit', 
                    minute: '2-digit',
                    hour12: true 
                });
                
                riderETAs[riderId] = {
                    distance: distance,
                    duration: duration,
                        expectedDelivery: deliveryTimeStr
                    };
                
                    // Update info window if it's open (only if it's the currently open one)
                    const rider = currentRiders.find(r => r.id == riderId);
                    if (rider && infoWindows[riderId] && infoWindows[riderId].getMap() && openInfoWindowId == riderId) {
                        // Use setTimeout to prevent flickering
                        setTimeout(function() {
                            if (infoWindows[riderId] && infoWindows[riderId].getMap()) {
                        infoWindows[riderId].setContent(createInfoWindowContent(rider));
                    }
                        }, 100);
                }
            }
            } catch (error) {
                console.error(`❌ Error creating route renderer for rider ${riderId}:`, error);
                drawSimpleRoute(riderId, order);
            }
        } else {
            console.warn(`⚠️ No routes in result for rider ${riderId}`);
            drawSimpleRoute(riderId, order);
        }
    });
}

// ========== DRAW SIMPLE ROUTE (FALLBACK) ==========
function drawSimpleRoute(riderId, order) {
    try {
        console.log(`🔵 drawSimpleRoute called for rider ${riderId}`, order);
        
        // Remove existing route
        if (routes[riderId]) {
            if (routes[riderId].setMap) {
                routes[riderId].setMap(null);
            } else if (routes[riderId].renderer && routes[riderId].renderer.setMap) {
                routes[riderId].renderer.setMap(null);
            } else if (routes[riderId].polyline && routes[riderId].polyline.setMap) {
                routes[riderId].polyline.setMap(null);
            }
            delete routes[riderId];
        }
        
        // Get rider's current position
        const rider = currentRiders.find(r => r.id == riderId);
        const riderPos = rider && rider.lat && (rider.long || rider.lng) 
            ? { lat: parseFloat(rider.lat), lng: parseFloat(rider.long || rider.lng) }
            : null;
        
        console.log(`Rider position for simple route:`, riderPos);
        
        // Try to get coordinates from order if available (support both 'lng' and 'long' formats)
        const startLng = order.start_lng || order.start_long;
        const endLng = order.end_lng || order.end_long;
        const hasCoordinates = order.start_lat && startLng && order.end_lat && endLng;
        
        if (!hasCoordinates) {
            console.error(`❌ No coordinates available for simple route:`, {
                start_lat: order.start_lat,
                start_lng: startLng,
                end_lat: order.end_lat,
                end_lng: endLng
            });
            return;
        }
        
        // Proceed with route drawing
        const routePath = [];
        
        // Start from rider's current position if available, otherwise use start location
        if (riderPos) {
            routePath.push(riderPos);
            console.log('Using rider position as route start');
        } else {
            const startPoint = { lat: parseFloat(order.start_lat), lng: parseFloat(startLng) };
            routePath.push(startPoint);
            console.log('Using order start location as route start');
        }
        
        // End at destination
        const endPoint = { lat: parseFloat(order.end_lat), lng: parseFloat(endLng) };
        routePath.push(endPoint);
        
        console.log(`Route path:`, routePath);
        
        const route = new google.maps.Polyline({
            path: routePath,
            geodesic: true,
            strokeColor: '#f59e0b',
            strokeOpacity: 0.8,
            strokeWeight: 6, // Make it more visible
            map: map,
            zIndex: 500,
            visible: true
        });
        
        routes[riderId] = route;
        console.log(`✅ Simple route drawn for rider ${riderId} with ${routePath.length} points`);
    } catch (error) {
        console.error(`❌ Error drawing simple route for rider ${riderId}:`, error);
    }
}

// ========== UPDATE RIDER LIST ==========
function updateRiderList(riders) {
    const listContainer = $('#riderList');
    
    if (riders.length === 0) {
        listContainer.html('<div style="text-align: center; padding: 2rem; color: var(--ct-gray-500);">No riders found</div>');
        return;
    }
    
    let html = '';
    riders.forEach(function(rider) {
        const hasOrder = rider.active_order !== null;
        const statusText = hasOrder ? 'On Order' : 'Available';
        const statusClass = hasOrder ? 'status-busy' : 'status-online';
        
        html += '<div class="rider-card" data-rider-id="' + rider.id + '">';
        html += '<div class="rider-card-header">';
        html += '<div class="rider-avatar">' + (rider.first_name ? rider.first_name.charAt(0) : 'R') + '</div>';
        html += '<div class="rider-info">';
        html += '<div class="rider-name">' + rider.first_name + ' ' + rider.last_name + '</div>';
        html += '<div class="rider-status">';
        html += '<span class="rider-status-badge ' + statusClass + '">' + statusText + '</span>';
        html += '</div>';
        html += '</div>';
        html += '</div>';
        
        if (hasOrder && rider.active_order) {
            html += '<div class="order-info">';
            html += '<div><strong>Order:</strong> ' + (rider.active_order.booking_id || 'N/A') + '</div>';
            html += '<div><strong>Status:</strong> ' + (rider.active_order.order_status || 'N/A') + '</div>';
            html += '</div>';
        }
        
        html += '</div>';
    });
    
    listContainer.html(html);
    
    // Add click handlers
    $('.rider-card').off('click').on('click', function() {
        const riderId = parseInt($(this).data('rider-id'));
        if (markers[riderId]) {
            // Remove active class from all cards
            $('.rider-card').removeClass('active');
            // Add active class to clicked card
            $(this).addClass('active');
            
            // Center map on rider
            const position = markers[riderId].getPosition();
            map.setCenter(position);
            map.setZoom(15);
            
            // Open info window
            if (infoWindows[riderId]) {
                // Close all other info windows
                Object.values(infoWindows).forEach(function(iw) {
                    if (iw && iw !== infoWindows[riderId] && iw.close) {
                        iw.close();
                    }
                });
                
                // Update content and open
                const rider = currentRiders.find(function(r) { return r.id == riderId; });
                if (rider) {
                    infoWindows[riderId].setContent(createInfoWindowContent(rider));
                }
                infoWindows[riderId].open(map, markers[riderId]);
                openInfoWindowId = riderId;
            }
            
            // Enable following
            followingRiderId = riderId;
            $('#stopFollowing').show();
        }
    });
}

// ========== UPDATE STATS ==========
function updateStats(stats) {
    if (stats) {
    $('#activeRidersCount').text(stats.active_riders || 0);
    $('#activeOrdersCount').text(stats.active_orders || 0);
        $('#lastUpdate').text(new Date().toLocaleTimeString());
    }
}

// ========== AUTO REFRESH ==========
function startAutoRefresh() {
    if (updateInterval) {
        clearInterval(updateInterval);
    }
    
    if (autoRefreshEnabled) {
        updateInterval = setInterval(function() {
            loadRiders();
        }, refreshInterval);
    }
}

// ========== NOTIFICATION ==========
function showNotification(type, message) {
    const colors = {
        success: { bg: 'rgba(16, 185, 129, 0.1)', border: '#10b981', text: '#047857' },
        info: { bg: 'rgba(59, 130, 246, 0.1)', border: '#3b82f6', text: '#1d4ed8' },
        error: { bg: 'rgba(239, 68, 68, 0.1)', border: '#ef4444', text: '#b91c1c' }
    };
    const color = colors[type] || colors.info;
    const icon = type === 'success' ? 'fa-check-circle' : (type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle');
    
    const notification = $('<div style="position: fixed; top: 1rem; right: 1rem; z-index: 10000; padding: 1rem 1.5rem; background: ' + color.bg + '; border: 1px solid ' + color.border + '; border-radius: 0.75rem; display: flex; align-items: center; gap: 0.75rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">' +
        '<i class="fas ' + icon + '" style="color: ' + color.text + ';"></i>' +
        '<span style="color: ' + color.text + '; font-weight: 500;">' + message + '</span>' +
        '</div>');
    
    $('body').append(notification);
    setTimeout(function() {
        notification.fadeOut(300, function() {
            $(this).remove();
        });
    }, 3000);
}

// ========== EVENT LISTENERS ==========
function setupEventListeners() {
    // Refresh button
    $('#refreshMap').on('click', function() {
        loadRiders();
    });
    
    // Auto refresh toggle
    $('#autoRefreshToggle').on('change', function() {
        autoRefreshEnabled = $(this).is(':checked');
        startAutoRefresh();
    });
    
    // Refresh interval change
    $('#refreshInterval').on('change', function() {
        refreshInterval = parseInt($(this).val());
        startAutoRefresh();
    });
    
    // Center map
    $('#centerMap').off('click').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Center map clicked');
        if (map) {
            map.setCenter(mapCenter);
            map.setZoom(mapZoom);
            showNotification('info', 'Map centered');
        }
    });
    
    // Toggle traffic
    $('#toggleTraffic').off('click').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Toggle traffic clicked');
        if (trafficLayer && map) {
            const isVisible = trafficLayer.getMap() !== null;
            trafficLayer.setMap(isVisible ? null : map);
            $(this).toggleClass('active', !isVisible);
            showNotification('info', isVisible ? 'Traffic layer hidden' : 'Traffic layer shown');
        }
    });
    
    // Toggle satellite
    $('#toggleSatellite').off('click').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Toggle satellite clicked');
        if (map) {
            mapTypeId = mapTypeId === 'roadmap' ? 'satellite' : 'roadmap';
            map.setMapTypeId(mapTypeId);
            $(this).toggleClass('active', mapTypeId === 'satellite');
            showNotification('info', mapTypeId === 'satellite' ? 'Satellite view enabled' : 'Road map view enabled');
        }
    });
    
    // Toggle trails
    $('#toggleTrails').off('click').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Toggle trails clicked');
        trailsEnabled = !trailsEnabled;
        $(this).toggleClass('active', trailsEnabled);
        
        // Show/hide all trails
        Object.keys(riderPaths).forEach(function(riderId) {
            if (riderPaths[riderId] && riderPaths[riderId].setMap) {
                riderPaths[riderId].setMap(trailsEnabled ? map : null);
            }
        });
        showNotification('info', trailsEnabled ? 'Movement trails enabled' : 'Movement trails disabled');
    });
    
    // Toggle clustering
    $('#toggleClustering').off('click').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Toggle clustering clicked');
        clusteringEnabled = !clusteringEnabled;
        $(this).toggleClass('active', clusteringEnabled);
        updateMarkerClustering();
        showNotification('info', clusteringEnabled ? 'Marker clustering enabled' : 'Marker clustering disabled');
    });
    
    // Toggle fullscreen
    $('#toggleFullscreen').off('click').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Toggle fullscreen clicked');
        const mapContainer = document.getElementById('mapContainer');
        if (!document.fullscreenElement && !document.webkitFullscreenElement && !document.mozFullScreenElement && !document.msFullscreenElement) {
            if (mapContainer.requestFullscreen) {
                mapContainer.requestFullscreen();
            } else if (mapContainer.webkitRequestFullscreen) {
                mapContainer.webkitRequestFullscreen();
            } else if (mapContainer.mozRequestFullScreen) {
                mapContainer.mozRequestFullScreen();
            } else if (mapContainer.msRequestFullscreen) {
                mapContainer.msRequestFullscreen();
            }
            
            $(this).html('<i class="fas fa-compress"></i>');
            setTimeout(function() {
                if (map) google.maps.event.trigger(map, 'resize');
            }, 100);
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            } else if (document.mozCancelFullScreen) {
                document.mozCancelFullScreen();
            } else if (document.msExitFullscreen) {
                document.msExitFullscreen();
            }
            
            $('#toggleFullscreen').html('<i class="fas fa-expand"></i>');
            setTimeout(function() {
                if (map) google.maps.event.trigger(map, 'resize');
            }, 100);
        }
    });
    
    // Stop following
    $('#stopFollowing').off('click').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        followingRiderId = null;
        $(this).hide();
        showNotification('info', 'Stopped following rider');
    });
    
    // Show legend
    $('#showLegend').off('click').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Show legend clicked');
        $('#mapLegend').toggle();
    });
    
    $('#closeLegend').off('click').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $('#mapLegend').hide();
    });
    
    // Search functionality
    $('#riderSearch').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        if (searchTerm.length > 0) {
            $('#clearSearch').show();
            filterRiders(searchTerm);
        } else {
            $('#clearSearch').hide();
            filterRiders('');
        }
    });
    
    $('#clearSearch').on('click', function() {
        $('#riderSearch').val('');
        $(this).hide();
        filterRiders('');
    });
    
    // Filter changes
    $('#statusFilter, #orderStatusFilter, #lastSeenFilter').on('change', function() {
        applyFilters();
    });
    
    // Keyboard shortcuts
    $(document).on('keydown', function(e) {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
        
        switch(e.key.toLowerCase()) {
            case 'c':
                if (map) {
                    map.setCenter(mapCenter);
                    map.setZoom(mapZoom);
                }
                break;
            case 't':
                $('#toggleTraffic').click();
                break;
            case 's':
                $('#toggleSatellite').click();
                break;
            case 'r':
                loadRiders();
                break;
            case 'f':
                $('#toggleTrails').click();
                break;
        }
    });
}

// ========== FILTER RIDERS ==========
function filterRiders(searchTerm) {
    $('.rider-card').each(function() {
        const card = $(this);
        const text = card.text().toLowerCase();
        const matches = searchTerm === '' || text.includes(searchTerm);
        
        card.toggle(matches);
        
        if (matches && searchTerm !== '') {
            card.addClass('search-match');
            const riderId = parseInt(card.data('rider-id'));
        if (markers[riderId]) {
                // Highlight marker
                markers[riderId].setIcon({
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 12,
                    fillColor: '#3b82f6',
                    fillOpacity: 1,
                    strokeColor: '#ffffff',
                    strokeWeight: 2
                });
            }
        } else {
            card.removeClass('search-match');
            const riderId = parseInt(card.data('rider-id'));
            if (markers[riderId] && currentRiders.find(function(r) { return r.id == riderId; })) {
                const rider = currentRiders.find(function(r) { return r.id == riderId; });
                const hasOrder = rider && rider.active_order !== null;
                const markerColor = hasOrder ? '#f59e0b' : '#10b981';
                markers[riderId].setIcon({
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 10,
                    fillColor: markerColor,
                    fillOpacity: 1,
                    strokeColor: '#ffffff',
                    strokeWeight: 2
                });
            }
        }
    });
}

// ========== APPLY FILTERS ==========
function applyFilters() {
    const statusFilter = $('#statusFilter').val();
    const orderStatusFilter = $('#orderStatusFilter').val();
    const lastSeenFilter = $('#lastSeenFilter').val();
    
    const filteredRiders = currentRiders.filter(function(rider) {
        // Status filter
        if (statusFilter === 'active' && !rider.active_order) return false;
        if (statusFilter === 'available' && rider.active_order) return false;
        if (statusFilter === 'offline' && rider.IsActive) return false;
        
        // Order status filter
        if (orderStatusFilter !== 'all' && (!rider.active_order || rider.active_order.order_status !== orderStatusFilter)) {
            return false;
        }
        
        // Last seen filter (would need timestamp data)
        // For now, skip this filter
        
        return true;
    });
    
    updateRiderList(filteredRiders);
    updateMapMarkers(filteredRiders);
}

// ========== MAIN INITIALIZATION ==========
$(document).ready(function() {
    console.log('📄 Document ready');
    
    // Setup event listeners immediately (buttons exist in DOM)
    setupEventListeners();
    
    // Wait for Google Maps to load
    function checkAndInit() {
        if (typeof google !== 'undefined' && typeof google.maps !== 'undefined' && typeof google.maps.Map !== 'undefined') {
            console.log('✅ Google Maps is available');
            if (!map) {
                initializeMap();
            }
            // Re-setup event listeners after map is ready
            setupEventListeners();
            return true;
        }
        return false;
    }
    
    // Try immediately
    if (checkAndInit()) {
        return;
    }
    
    // Poll for Google Maps
    let attempts = 0;
    const maxAttempts = 50;
    const checkTimer = setInterval(function() {
        attempts++;
        if (checkAndInit()) {
            clearInterval(checkTimer);
        } else if (attempts >= maxAttempts) {
            clearInterval(checkTimer);
            console.error('❌ Google Maps failed to load after 25 seconds');
            showNotification('error', 'Google Maps failed to load. Please check your API key.');
        }
    }, 500);

    // Cleanup on page unload
    $(window).on('beforeunload', function() {
        if (updateInterval) {
            clearInterval(updateInterval);
        }
    });
});

// ========== FOLLOW RIDER FUNCTION ==========
function toggleFollowRider(riderId) {
    if (followingRiderId == riderId) {
        followingRiderId = null;
        $('#stopFollowing').hide();
        showNotification('info', 'Stopped following rider');
    } else {
        followingRiderId = riderId;
        $('#stopFollowing').show();
        if (markers[riderId]) {
            const position = markers[riderId].getPosition();
            map.setCenter(position);
                map.setZoom(15);
            }
        showNotification('success', 'Following rider');
        
        // Update info window only if it's currently open
        const rider = currentRiders.find(r => r.id == riderId);
        if (rider && infoWindows[riderId] && infoWindows[riderId].getMap() && openInfoWindowId == riderId) {
            setTimeout(function() {
                if (infoWindows[riderId] && infoWindows[riderId].getMap()) {
                    infoWindows[riderId].setContent(createInfoWindowContent(rider));
                }
            }, 100);
        }
    }
}

// ========== FORCE DRAW ROUTE FUNCTION ==========
window.forceDrawRoute = function(riderId) {
    const rider = currentRiders.find(r => r.id == riderId);
    if (rider && rider.active_order) {
        console.log('🚀 Force drawing route for rider', riderId);
        drawRouteForRider(riderId, rider.active_order);
        showNotification('info', 'Route displayed');
    }
};

// ========== UPDATE RIDER SPEED AND HEADING ==========
function updateRiderSpeedAndHeading(riderId, currentPos, previousPos) {
    if (!previousPos || !currentPos) {
        riderSpeeds[riderId] = 0;
        riderHeadings[riderId] = null;
        return;
    }
    
    // Check if geometry library is available
    if (typeof google === 'undefined' || !google.maps || !google.maps.geometry || !google.maps.geometry.spherical) {
        // Fallback calculation without geometry library
        const R = 6371; // Earth's radius in km
        const dLat = (currentPos.lat - previousPos.lat) * Math.PI / 180;
        const dLng = (currentPos.lng - previousPos.lng) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                  Math.cos(previousPos.lat * Math.PI / 180) * Math.cos(currentPos.lat * Math.PI / 180) *
                  Math.sin(dLng/2) * Math.sin(dLng/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        const distance = R * c; // km
        
        const timeDiff = 3; // seconds
        const speed = (distance / timeDiff) * 3600; // km/h
        riderSpeeds[riderId] = speed;
        
        // Calculate heading (bearing)
        const y = Math.sin(dLng) * Math.cos(currentPos.lat * Math.PI / 180);
        const x = Math.cos(previousPos.lat * Math.PI / 180) * Math.sin(currentPos.lat * Math.PI / 180) -
                  Math.sin(previousPos.lat * Math.PI / 180) * Math.cos(currentPos.lat * Math.PI / 180) * Math.cos(dLng);
        const heading = (Math.atan2(y, x) * 180 / Math.PI + 360) % 360;
        riderHeadings[riderId] = heading;
        return;
    }
    
    // Use Google Maps geometry library if available
    const distance = google.maps.geometry.spherical.computeDistanceBetween(
        new google.maps.LatLng(previousPos.lat, previousPos.lng),
        new google.maps.LatLng(currentPos.lat, currentPos.lng)
    ) / 1000; // Convert to km
    
    // Calculate time difference (assuming updates every 3 seconds)
    const timeDiff = 3; // seconds
    const speed = (distance / timeDiff) * 3600; // km/h
    
    riderSpeeds[riderId] = speed;
    
    // Calculate heading
    const heading = google.maps.geometry.spherical.computeHeading(
        new google.maps.LatLng(previousPos.lat, previousPos.lng),
        new google.maps.LatLng(currentPos.lat, currentPos.lng)
    );
    
    riderHeadings[riderId] = heading;
}

// Update speed/heading when markers update
function updateSpeedAndHeadingForRiders(riders) {
    riders.forEach(function(rider) {
        if (rider.lat && rider.long) {
            const currentPos = {
                lat: parseFloat(rider.lat),
                lng: parseFloat(rider.long || rider.lng)
            };
            
            // Get previous position from history
            if (riderHistory[rider.id] && riderHistory[rider.id].length > 1) {
                const previousPos = riderHistory[rider.id][riderHistory[rider.id].length - 2];
                updateRiderSpeedAndHeading(rider.id, currentPos, previousPos);
        }
    }
});
}
</script>
@endsection

