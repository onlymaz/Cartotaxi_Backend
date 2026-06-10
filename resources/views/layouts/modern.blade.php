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
    
    <!-- jQuery (load first) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    
    <!-- Google Maps -->
    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&libraries=geometry,drawing,places"></script>
    
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    
    <!-- CargoTaxi Theme -->
    <link rel="stylesheet" href="{{ url('css/cargotaxi-theme.css') }}?v={{ time() }}">
    
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">

    <script>
        function showLoader() {
            $('#bodyContent .loader-layout').show();
        }
        function hideLoader() {
            $('#bodyContent .loader-layout').hide();
        }
    </script>
    
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
                    <div class="ct-user-card">
                        @if(auth()->user()->profile_image)
                            <img src="{{ url(auth()->user()->profile_image) }}" alt="Avatar" class="ct-user-avatar">
                        @else
                            <div class="ct-user-avatar" style="background: #84cc16; display: flex; align-items: center; justify-content: center; color: #1e293b; font-weight: 700;">
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
                                <a href="{{ url('locale/en') }}" class="ct-dropdown-item">🇬🇧 English</a>
                                <a href="{{ url('locale/de') }}" class="ct-dropdown-item">🇩🇪 Deutsch</a>
                            </div>
                                </div>

                        <!-- Notifications -->
                        @if(auth()->user()->role_id == 1 || auth()->user()->role_id == 3)
                        <div class="ct-dropdown">
                            <button class="ct-header-btn" id="notifBtn" onclick="this.parentElement.classList.toggle('open')">
                                <i class="fas fa-bell"></i>
                            </button>
                            <div class="ct-dropdown-menu" style="width: 300px;">
                                <div style="padding: 0.75rem; font-weight: 600; border-bottom: 1px solid #e2e8f0;">Notifications</div>
                                <div id="notifList" style="max-height: 300px; overflow-y: auto; padding: 1rem; text-align: center; color: #94a3b8;">
                                    <i class="fas fa-bell-slash" style="font-size: 1.5rem;"></i>
                                    <p style="margin: 0.5rem 0 0;">No notifications</p>
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
                                    <div class="ct-avatar ct-avatar-sm" style="background: #84cc16; display: flex; align-items: center; justify-content: center; color: #1e293b; font-weight: 700; font-size: 0.75rem;">
                                        {{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}
                                    </div>
                                @endif
                            </button>
                            <div class="ct-dropdown-menu">
                                <div style="padding: 0.75rem; border-bottom: 1px solid #e2e8f0;">
                                    <div style="font-weight: 600;">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</div>
                                    <div style="font-size: 0.75rem; color: #64748b;">{{ auth()->user()->email }}</div>
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
                    
                    @yield('content')
                </main>
            </div>
            
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        @else
            @yield('content')
        @endauth
    </div>
    
    <!-- Modal -->
    <div id="default_modal" class="ct-modal-overlay">
        <div class="ct-modal" style="max-width: 800px;">
            <div class="ct-modal-header">
                <h3 class="ct-modal-title">Details</h3>
                <button class="ct-modal-close" data-dismiss="modal" onclick="$('#default_modal').removeClass('open'); document.body.style.overflow='';">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="ct-modal-body modal-dialog modal-lg"></div>
</div>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    {{-- js/bootstrap.min.js removed: the file doesn't exist (404 on every
         page) and this layout ships its own $.fn.modal shim below. --}}
    
    <script>
        // Mobile menu
        document.getElementById('menuToggle')?.addEventListener('click', () => {
            document.getElementById('sidebar').classList.toggle('open');
        });
        
        // Close dropdowns
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.ct-dropdown')) {
                document.querySelectorAll('.ct-dropdown.open').forEach(el => el.classList.remove('open'));
            }
        });
        
        // Modal functions
        $.fn.modal = function(action) {
            if (action === 'show') {
                $('#default_modal').addClass('open');
                document.body.style.overflow = 'hidden';
            } else if (action === 'toggle' || action === 'hide') {
                $('#default_modal').removeClass('open');
                document.body.style.overflow = '';
            }
            return this;
        };
        
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
        
        // Toast notifications
        var toast = {
            success: function(msg) { showToast('success', msg); },
            error: function(msg) { showToast('error', msg); }
        };
        
        function showToast(type, msg) {
            var colors = {
                success: { bg: 'rgba(16, 185, 129, 0.1)', border: '#10b981', text: '#047857' },
                error: { bg: 'rgba(239, 68, 68, 0.1)', border: '#ef4444', text: '#b91c1c' }
            };
            var c = colors[type] || colors.success;
            var icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
            var el = $('<div style="position:fixed;top:1rem;right:1rem;z-index:9999;padding:1rem 1.5rem;background:'+c.bg+';border:1px solid '+c.border+';border-radius:0.75rem;display:flex;align-items:center;gap:0.75rem;animation:slideDown 0.3s ease;"><i class="fas '+icon+'" style="color:'+c.text+';"></i><span style="color:'+c.text+';font-weight:500;">'+msg+'</span></div>');
            $('body').append(el);
            setTimeout(() => el.fadeOut(300, function() { $(this).remove(); }), 3000);
        }
    </script>
    
    <style>
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Override old Tailwind classes with CargoTaxi colors */
        .bg-indigo-600, .bg-indigo-500 { background: #84cc16 !important; }
        .bg-indigo-700, .hover\:bg-indigo-700:hover { background: #65d419 !important; }
        .bg-green-600, .bg-green-500 { background: #10b981 !important; }
        .bg-green-700, .hover\:bg-green-700:hover { background: #059669 !important; }
        .text-indigo-600, .text-indigo-500 { color: #84cc16 !important; }
        .border-indigo-500, .border-indigo-600 { border-color: #84cc16 !important; }
        .ring-indigo-500, .focus\:ring-indigo-500:focus { --tw-ring-color: rgba(132, 204, 22, 0.3) !important; }
        .bg-indigo-50 { background: rgba(132, 204, 22, 0.1) !important; }
        
        /* Fix button colors */
        .btn-success, a.btn-success { background: #84cc16 !important; border-color: #84cc16 !important; color: #1e293b !important; }
        .btn-success:hover { background: #65d419 !important; border-color: #65d419 !important; }
        
        /* Card styling */
        .card { background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; }
        .card-header { padding: 1rem 1.5rem; border-bottom: 1px solid #e2e8f0; background: #f8fafc; border-radius: 12px 12px 0 0; }
        .card-body { padding: 1.5rem; }
        
        /* Table styling */
        .table { width: 100%; border-collapse: collapse; }
        .table thead th { padding: 0.875rem 1rem; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: #64748b; background: #f8fafc; border-bottom: 1px solid #e2e8f0; text-align: left; }
        .table tbody td { padding: 1rem; border-bottom: 1px solid #f1f5f9; }
        .table tbody tr:hover { background: #f8fafc; }
        
        /* Modal fix */
        .modal.fade.show, #default_modal.open { display: flex !important; }
        .modal-dialog { margin: 0; }
    </style>
    
    @yield('scripts')
</body>
</html>
