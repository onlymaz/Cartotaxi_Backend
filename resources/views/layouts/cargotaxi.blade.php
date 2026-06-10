<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $settings = \App\Models\SiteSetting::find(1);
        $faviconUrl = $settings && $settings->site_icon ? url($settings->site_icon) : url('images/favico.ico');
    @endphp
    <link rel="shortcut icon" href="{{ $faviconUrl }}" sizes="32x32">
    <link rel="icon" type="image/png" href="{{ $faviconUrl }}" sizes="32x32">
    <link rel="icon" type="image/png" href="{{ $faviconUrl }}" sizes="16x16">
    <link rel="apple-touch-icon" href="{{ $faviconUrl }}">
    <meta name="msapplication-TileImage" content="{{ $faviconUrl }}">
    
    @yield('title')
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- Cargo Taxi Theme -->
    <link rel="stylesheet" href="{{ url('css/cargotaxi-theme.css') }}?v={{ time() }}">
    
    <!-- jQuery UI -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    
    <!-- Nova class aliases for compatibility -->
    <style>
        /* Nova to CargoTaxi class aliases */
        .nova-page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
        .nova-page-title { font-size: 1.75rem; font-weight: 700; color: var(--ct-gray-900); }
        .nova-page-subtitle { color: var(--ct-gray-500); margin-top: 0.25rem; }
        .nova-page-actions { display: flex; gap: 0.75rem; flex-wrap: wrap; }
        
        .nova-btn { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.625rem 1rem; font-size: 0.875rem; font-weight: 500; border-radius: 0.5rem; cursor: pointer; transition: all 0.2s; border: none; }
        .nova-btn-primary { background: var(--ct-accent); color: var(--ct-primary); }
        .nova-btn-primary:hover { background: var(--ct-accent-hover); }
        .nova-btn-secondary { background: white; color: var(--ct-gray-700); border: 1px solid var(--ct-gray-300); }
        .nova-btn-secondary:hover { background: var(--ct-gray-50); }
        .nova-btn-ghost { background: transparent; color: var(--ct-gray-600); }
        .nova-btn-ghost:hover { background: var(--ct-gray-100); }
        .nova-btn-sm { padding: 0.5rem 0.75rem; font-size: 0.8125rem; }
        
        .nova-card { background: white; border-radius: 0.75rem; border: 1px solid var(--ct-gray-200); overflow: hidden; }
        .nova-card-header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.5rem; border-bottom: 1px solid var(--ct-gray-100); }
        .nova-card-title { font-size: 1rem; font-weight: 600; }
        .nova-card-body { padding: 1.5rem; }
        
        .nova-stats-grid { display: grid; gap: 1.5rem; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); }
        .nova-stat-card { background: white; border-radius: 0.75rem; padding: 1.5rem; border: 1px solid var(--ct-gray-200); border-top: 3px solid var(--stat-color, var(--ct-accent)); }
        .nova-stat-card.warning { --stat-color: #f59e0b; }
        .nova-stat-card.info { --stat-color: #3b82f6; }
        .nova-stat-card.success { --stat-color: #22c55e; }
        .nova-stat-card.danger { --stat-color: #ef4444; }
        .nova-stat-icon { width: 48px; height: 48px; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; background: var(--stat-bg, rgba(255,149,0,0.1)); color: var(--stat-color, var(--ct-accent)); }
        .nova-stat-value { font-size: 1.75rem; font-weight: 700; margin: 0.5rem 0 0.25rem; }
        .nova-stat-label { color: var(--ct-gray-500); font-size: 0.875rem; }
        
        .nova-table-wrapper { overflow-x: auto; }
        .nova-table { width: 100%; border-collapse: collapse; }
        .nova-table thead th { padding: 0.75rem 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: var(--ct-gray-500); background: var(--ct-gray-50); border-bottom: 1px solid var(--ct-gray-200); }
        .nova-table tbody td { padding: 1rem; border-bottom: 1px solid var(--ct-gray-100); }
        .nova-table tbody tr:hover { background: var(--ct-gray-50); }
        
        .nova-input, .nova-select { width: 100%; height: 42px; padding: 0 1rem; border: 1px solid var(--ct-gray-300); border-radius: 0.5rem; font-size: 0.875rem; background: white; transition: all 0.2s; }
        .nova-input:focus, .nova-select:focus { outline: none; border-color: var(--ct-accent); box-shadow: 0 0 0 3px rgba(255,149,0,0.1); }
        .nova-label { display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem; color: var(--ct-gray-700); }
        
        .mb-6 { margin-bottom: 1.5rem; }
        .flex { display: flex; }
        .flex-wrap { flex-wrap: wrap; }
        .items-center { align-items: center; }
        .gap-2 { gap: 0.5rem; }
        .gap-4 { gap: 1rem; }
        
        /* DataTable override */
        .dataTables_wrapper .dataTables_filter input { border: 1px solid var(--ct-gray-300); border-radius: 0.5rem; padding: 0.5rem 1rem; }
        .dataTables_wrapper .dataTables_filter input:focus { outline: none; border-color: var(--ct-accent); box-shadow: 0 0 0 3px rgba(255,149,0,0.1); }
    </style>
    
    @yield('styles')
</head>
<body>
    <div class="ct-app">
        @auth
            <!-- Sidebar -->
            <aside class="ct-sidebar" id="sidebar">
                <div class="ct-sidebar-header">
                    <a href="{{ auth()->user()->role_id == 1 ? route('admin.dashboard') : (auth()->user()->role_id == 3 ? route('customer.dashboard') : route('rider.dashboard')) }}" class="ct-logo" style="text-decoration: none; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;">
                        @include('components.cargotaxi-logo', ['size' => 90, 'compact' => true])
                    </a>
                </div>
                
                <nav class="ct-nav">
                    @if(auth()->user()->role_id == 1)
                        @include('sidebars.ct-admin')
                    @elseif(auth()->user()->role_id == 3)
                        @include('sidebars.ct-customer')
                    @else
                        @include('sidebars.ct-rider')
                    @endif
                </nav>
                
                <div class="ct-sidebar-footer">
                    <div class="ct-user-card" onclick="document.getElementById('userDropdown').classList.toggle('open')">
                        @if(auth()->user()->profile_image)
                            <img src="{{ url(auth()->user()->profile_image) }}" alt="Avatar" class="ct-user-avatar">
                        @else
                            <div class="ct-user-avatar" style="background: var(--ct-accent); display: flex; align-items: center; justify-content: center; color: var(--ct-primary); font-weight: 700;">
                                {{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}
                            </div>
                        @endif
                        <div class="ct-user-info">
                            <div class="ct-user-name">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</div>
                            <div class="ct-user-role">
                                @if(auth()->user()->role_id == 1) Administrator
                                @elseif(auth()->user()->role_id == 2) Driver
                                @else Customer
                                @endif
                            </div>
                        </div>
                        <i class="fas fa-chevron-up" style="color: var(--ct-gray-500); font-size: 0.75rem;"></i>
                    </div>
                    <div class="ct-dropdown" id="userDropdown">
                        <div class="ct-dropdown-menu" style="bottom: 100%; top: auto; margin-bottom: 0.5rem; left: 0; right: 0;">
                            <a href="{{ route('settings.profile_view', auth()->user()->id) }}" class="ct-dropdown-item">
                                <i class="fas fa-user"></i> Profile
                            </a>
                            <a href="{{ route('settings.change_password', auth()->user()->id) }}" class="ct-dropdown-item">
                                <i class="fas fa-key"></i> Change Password
                            </a>
                            <div class="ct-dropdown-divider"></div>
                            <a href="{{ route('logout') }}" class="ct-dropdown-item danger"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </aside>
            
            <!-- Main -->
            <div class="ct-main">
                <!-- Header -->
                <header class="ct-header">
                    <div class="ct-header-left">
                        <button class="ct-menu-btn" id="menuToggle">
                            <i class="fas fa-bars"></i>
                        </button>
                        <div class="ct-breadcrumb">
                            @yield('breadcrumb')
                        </div>
                    </div>
                    
                    <div class="ct-header-center">
                        <div class="ct-search">
                            <i class="fas fa-search ct-search-icon"></i>
                            <input type="text" class="ct-search-input" placeholder="Search...">
                        </div>
                    </div>
                    
                    <div class="ct-header-right">
                        <!-- Language -->
                        <div class="ct-dropdown">
                            <button class="ct-header-btn" onclick="this.parentElement.classList.toggle('open')">
                                <i class="fas fa-globe"></i>
                            </button>
                            <div class="ct-dropdown-menu">
                                <a href="{{ url('locale/en') }}" class="ct-dropdown-item">
                                    🇬🇧 English
                                </a>
                                <a href="{{ url('locale/de') }}" class="ct-dropdown-item">
                                    🇩🇪 Deutsch
                                </a>
                            </div>
                        </div>
                        
                        <!-- Notifications -->
                        @if(auth()->user()->role_id == 1 || auth()->user()->role_id == 3)
                        <div class="ct-dropdown">
                            <button class="ct-header-btn" onclick="this.parentElement.classList.toggle('open')">
                                <i class="fas fa-bell"></i>
                                <span class="ct-badge" id="notifCount" style="display: none;">0</span>
                            </button>
                            <div class="ct-dropdown-menu" style="width: 300px;">
                                <div style="padding: 0.75rem; font-weight: 600; border-bottom: 1px solid var(--ct-gray-100);">Notifications</div>
                                <div id="notifList" style="max-height: 300px; overflow-y: auto;">
                                    <div style="padding: 2rem; text-align: center; color: var(--ct-gray-400);">
                                        <i class="fas fa-bell-slash" style="font-size: 1.5rem; margin-bottom: 0.5rem;"></i>
                                        <p style="margin: 0;">No notifications</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        <!-- User -->
                        <div class="ct-dropdown">
                            <button class="ct-header-btn" onclick="this.parentElement.classList.toggle('open')" style="width: auto; padding: 0 0.5rem;">
                                @if(auth()->user()->profile_image)
                                    <img src="{{ url(auth()->user()->profile_image) }}" class="ct-avatar ct-avatar-sm">
                                @else
                                    <div class="ct-avatar ct-avatar-sm" style="background: var(--ct-accent); display: flex; align-items: center; justify-content: center; color: var(--ct-primary); font-weight: 700; font-size: 0.75rem;">
                                        {{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}
                                    </div>
                                @endif
                            </button>
                            <div class="ct-dropdown-menu">
                                <div style="padding: 0.75rem; border-bottom: 1px solid var(--ct-gray-100);">
                                    <div style="font-weight: 600;">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</div>
                                    <div style="font-size: 0.75rem; color: var(--ct-gray-500);">{{ auth()->user()->email }}</div>
                                </div>
                                <a href="{{ route('settings.profile_view', auth()->user()->id) }}" class="ct-dropdown-item">
                                    <i class="fas fa-user"></i> Profile
                                </a>
                                <a href="{{ route('settings.change_password', auth()->user()->id) }}" class="ct-dropdown-item">
                                    <i class="fas fa-key"></i> Change Password
                                </a>
                                <div class="ct-dropdown-divider"></div>
                                <a href="{{ route('logout') }}" class="ct-dropdown-item danger"
                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </header>
                
                <!-- Content -->
                <main class="ct-content">
                    @if(session('success'))
                        <div class="ct-alert ct-alert-success">
                            <i class="fas fa-check-circle ct-alert-icon"></i>
                            <div>{{ session('success') }}</div>
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="ct-alert ct-alert-danger">
                            <i class="fas fa-exclamation-circle ct-alert-icon"></i>
                            <div>{{ session('error') }}</div>
                        </div>
                    @endif
                    
                    @if($errors->any())
                        <div class="ct-alert ct-alert-danger">
                            <i class="fas fa-exclamation-circle ct-alert-icon"></i>
                            <div>
                                @foreach($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    @yield('content')
                </main>
            </div>
            
            <!-- Logout Form -->
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
            
            <script>
            // Ensure logout functionality works
            document.addEventListener('DOMContentLoaded', function() {
                // Handle logout clicks
                document.querySelectorAll('a[href="{{ route('logout') }}"]').forEach(function(link) {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        var form = document.getElementById('logout-form');
                        if (form) {
                            form.submit();
                        } else {
                            console.error('Logout form not found');
                            // Fallback: redirect to logout route
                            window.location.href = '{{ route('logout') }}';
                        }
                    });
                });
            });
            </script>
        @else
            @yield('content')
        @endauth
    </div>
    
    <!-- Modal -->
    <div class="ct-modal-overlay" id="ctModal">
        <div class="ct-modal">
            <div class="ct-modal-header">
                <h3 class="ct-modal-title" id="modalTitle">Modal</h3>
                <button class="ct-modal-close" onclick="closeModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="ct-modal-body" id="modalBody"></div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&libraries=geometry,drawing,places"></script>
    
    <script>
        // Mobile menu toggle
        document.getElementById('menuToggle')?.addEventListener('click', () => {
            document.getElementById('sidebar').classList.toggle('open');
        });
        
        // Close dropdowns on outside click
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.ct-dropdown')) {
                document.querySelectorAll('.ct-dropdown.open').forEach(el => el.classList.remove('open'));
            }
            if (!e.target.closest('.ct-user-card') && !e.target.closest('#userDropdown')) {
                document.getElementById('userDropdown')?.classList.remove('open');
            }
        });
        
        // Modal functions
        function openModal(title, content) {
            document.getElementById('modalTitle').textContent = title;
            document.getElementById('modalBody').innerHTML = content;
            document.getElementById('ctModal').classList.add('open');
            document.body.style.overflow = 'hidden';
            
            // Execute any scripts in the loaded content
            var scripts = document.getElementById('modalBody').querySelectorAll('script');
            scripts.forEach(function(oldScript) {
                var newScript = document.createElement('script');
                Array.from(oldScript.attributes).forEach(function(attr) {
                    newScript.setAttribute(attr.name, attr.value);
                });
                newScript.appendChild(document.createTextNode(oldScript.innerHTML));
                oldScript.parentNode.replaceChild(newScript, oldScript);
            });
            
            // Trigger custom event for modal content loaded
            if (typeof jQuery !== 'undefined') {
                jQuery(document).trigger('modalContentLoaded');
            }
        }
        
        function closeModal() {
            document.getElementById('ctModal').classList.remove('open');
            document.body.style.overflow = '';
        }
        
        document.getElementById('ctModal')?.addEventListener('click', (e) => {
            if (e.target === e.currentTarget) closeModal();
        });
        
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeModal();
        });
        
        // Legacy popup support - disabled, handled in page-specific scripts
        // $(document).on('click', '.popup', function() {
        //     const url = $(this).attr('data-url');
        //     const type = $(this).attr('data-type');
        //     $.get(url, (data) => openModal(type === 'view' ? 'Details' : 'Action', data));
        // });
        
        // DataTable defaults
        if ($.fn.DataTable) {
            $.extend($.fn.dataTable.defaults, {
                language: {
                    search: "",
                    searchPlaceholder: "Search...",
                    paginate: {
                        previous: '<i class="fas fa-chevron-left"></i>',
                        next: '<i class="fas fa-chevron-right"></i>'
                    }
                }
            });
        }
    </script>
    
    @yield('scripts')
</body>
</html>

