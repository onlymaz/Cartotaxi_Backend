<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- CargoTaxi Theme CSS -->
    <link rel="stylesheet" href="{{ url('css/cargotaxi-theme.css') }}?v={{ time() }}">
    
    <!-- jQuery UI for datepicker -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.tailwindcss.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    
    <!-- CargoTaxi Color Overrides -->
    <style>
        :root {
            --primary: #1e293b;
            --primary-light: #334155;
            --accent: #84cc16;
            --accent-hover: #65d419;
            --accent-glow: rgba(255, 149, 0, 0.3);
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #374151; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #4b5563; }
        
        /* Nova sidebar with CargoTaxi branding */
        .nova-sidebar { background: #1e293b !important; }
        .nova-sidebar-header { border-color: rgba(255,255,255,0.1) !important; }
        
        .nova-nav-link.active, .nova-nav-link:hover {
            background: rgba(255, 149, 0, 0.15) !important;
            color: #84cc16 !important;
        }
        .nova-nav-link.active::before {
            background: #84cc16 !important;
        }
        .nova-nav-link.active .nova-nav-icon, .nova-nav-link:hover .nova-nav-icon {
            color: #84cc16 !important;
        }
        
        /* Buttons */
        .nova-btn-primary { background: #84cc16 !important; color: #1e293b !important; }
        .nova-btn-primary:hover { background: #65d419 !important; }
        
        /* Stats cards accent */
        .nova-stat-card.accent { background: linear-gradient(135deg, #84cc16 0%, #65d419 100%) !important; }
        
        /* Animation */
        .nova-content > * { animation: slideUp 0.4s ease-out; }
        @keyframes slideUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        
        * { transition: background-color 0.2s ease, border-color 0.2s ease; }
    </style>
    
    @yield('styles')
</head>
<body>
    <div class="nova-wrapper">
        @auth
            <!-- Sidebar -->
            <aside class="nova-sidebar" id="sidebar">
                <!-- Logo -->
                <div class="nova-sidebar-header">
                    <a href="{{ auth()->user()->role_id == 1 ? route('admin.dashboard') : (auth()->user()->role_id == 3 ? route('customer.dashboard') : route('rider.dashboard')) }}" class="nova-logo" style="text-decoration: none; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;">
                        @include('components.cargotaxi-logo', ['size' => 90, 'compact' => true])
                    </a>
                </div>
                
                <!-- Navigation -->
                <nav class="nova-nav">
                    @if(auth()->user()->role_id == 1)
                        @include('sidebars.nova-admin-sidebar')
                    @elseif(auth()->user()->role_id == 3)
                        @include('sidebars.nova-customer-sidebar')
                    @else
                        @include('sidebars.nova-rider-sidebar')
                    @endif
                </nav>
                
                <!-- User Card -->
                <div class="nova-sidebar-footer">
                    <div class="nova-user-card">
                        @if(auth()->user()->profile_image)
                            <img src="{{ url(auth()->user()->profile_image) }}" alt="Avatar" class="nova-user-avatar">
                        @else
                            <img src="{{ url('images/avatar.jpg') }}" alt="Avatar" class="nova-user-avatar">
                        @endif
                        <div class="nova-user-info">
                            <div class="nova-user-name">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</div>
                            <div class="nova-user-role">
                                @if(auth()->user()->role_id == 1) Administrator
                                @elseif(auth()->user()->role_id == 2) Driver
                                @else Customer
                                @endif
                            </div>
                        </div>
                        <div class="nova-dropdown">
                            <button class="nova-btn-ghost nova-btn-icon" onclick="this.parentElement.classList.toggle('open')">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="nova-dropdown-menu" style="bottom: 100%; top: auto; margin-bottom: 0.5rem;">
                                <a href="{{ route('settings.profile_view', auth()->user()->id) }}" class="nova-dropdown-item">
                                    <i class="fas fa-user"></i> Profile
                                </a>
                                <a href="{{ route('settings.change_password', auth()->user()->id) }}" class="nova-dropdown-item">
                                    <i class="fas fa-key"></i> Change Password
                                </a>
                                <div class="nova-dropdown-divider"></div>
                                <a href="{{ route('logout') }}" class="nova-dropdown-item danger"
                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>
            
            <!-- Main Content -->
            <div class="nova-main">
                <!-- Header -->
                <header class="nova-header">
                    <div class="nova-header-left">
                        <button class="nova-menu-toggle" id="sidebarToggle">
                            <i class="fas fa-bars"></i>
                        </button>
                        <div class="nova-breadcrumb">
                            @yield('breadcrumb')
                        </div>
                    </div>
                    
                    <div class="nova-header-center">
                        <div class="nova-search">
                            <i class="fas fa-search nova-search-icon"></i>
                            <input type="text" class="nova-search-input" placeholder="Search bookings, users, orders...">
                        </div>
                    </div>
                    
                    <div class="nova-header-right">
                        <!-- Language Switcher -->
                        <div class="nova-dropdown">
                            <button class="nova-header-btn" onclick="this.parentElement.classList.toggle('open')">
                                <i class="fas fa-globe"></i>
                            </button>
                            <div class="nova-dropdown-menu">
                                <a href="{{ url('locale/en') }}" class="nova-dropdown-item">
                                    <img src="{{ url('uploads/flag/englishFlag.png') }}" width="20" height="20" style="border-radius: 2px;"> English
                                </a>
                                <a href="{{ url('locale/de') }}" class="nova-dropdown-item">
                                    <img src="{{ url('uploads/flag/germanFlag.jpg') }}" width="20" height="20" style="border-radius: 2px;"> German
                                </a>
                            </div>
                        </div>
                        
                        <!-- Notifications -->
                        @if(auth()->user()->role_id == 1 || auth()->user()->role_id == 3)
                        <div class="nova-dropdown">
                            <button class="nova-header-btn" id="notificationBtn" onclick="this.parentElement.classList.toggle('open'); loadNotifications();">
                                <i class="fas fa-bell"></i>
                                <span class="badge" id="notificationCount" style="display: none;">0</span>
                            </button>
                            <div class="nova-dropdown-menu" style="width: 320px; max-height: 400px; overflow-y: auto;">
                                <div style="padding: 1rem; border-bottom: 1px solid var(--color-gray-100);">
                                    <strong>Notifications</strong>
                                </div>
                                <div id="notificationList">
                                    <div style="padding: 2rem; text-align: center; color: var(--color-gray-400);">
                                        <i class="fas fa-bell-slash" style="font-size: 2rem; margin-bottom: 0.5rem;"></i>
                                        <p>No notifications</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        <!-- Theme Toggle -->
                        <button class="nova-header-btn" id="themeToggle" title="Toggle theme">
                            <i class="fas fa-moon"></i>
                        </button>
                        
                        <!-- User Menu -->
                        <div class="nova-dropdown">
                            <button class="nova-header-btn" onclick="this.parentElement.classList.toggle('open')" style="width: auto; padding: 0 0.5rem;">
                                @if(auth()->user()->profile_image)
                                    <img src="{{ url(auth()->user()->profile_image) }}" alt="Avatar" class="nova-avatar nova-avatar-sm">
                                @else
                                    <img src="{{ url('images/avatar.jpg') }}" alt="Avatar" class="nova-avatar nova-avatar-sm">
                                @endif
                            </button>
                            <div class="nova-dropdown-menu">
                                <div style="padding: 0.75rem; border-bottom: 1px solid var(--color-gray-100);">
                                    <div style="font-weight: 600; color: var(--color-gray-900);">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</div>
                                    <div style="font-size: 0.75rem; color: var(--color-gray-500);">{{ auth()->user()->email }}</div>
                                </div>
                                <a href="{{ route('settings.profile_view', auth()->user()->id) }}" class="nova-dropdown-item">
                                    <i class="fas fa-user"></i> My Profile
                                </a>
                                <a href="{{ route('settings.change_password', auth()->user()->id) }}" class="nova-dropdown-item">
                                    <i class="fas fa-key"></i> Change Password
                                </a>
                                <div class="nova-dropdown-divider"></div>
                                <a href="{{ route('logout') }}" class="nova-dropdown-item danger"
                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </header>
                
                <!-- Page Content -->
                <main class="nova-content">
                    @if(session('success'))
                        <div class="nova-alert nova-alert-success mb-4">
                            <i class="fas fa-check-circle nova-alert-icon"></i>
                            <div class="nova-alert-content">{{ session('success') }}</div>
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="nova-alert nova-alert-danger mb-4">
                            <i class="fas fa-exclamation-circle nova-alert-icon"></i>
                            <div class="nova-alert-content">{{ session('error') }}</div>
                        </div>
                    @endif
                    
                    @if($errors->any())
                        <div class="nova-alert nova-alert-danger mb-4">
                            <i class="fas fa-exclamation-circle nova-alert-icon"></i>
                            <div class="nova-alert-content">
                                <ul style="margin: 0; padding-left: 1rem;">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
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
        @else
            <!-- Guest Content -->
            @yield('content')
        @endauth
    </div>
    
    <!-- Modal Container -->
    <div id="novaModal" class="nova-modal-backdrop">
        <div class="nova-modal">
            <div class="nova-modal-header">
                <h3 class="nova-modal-title" id="modalTitle">Modal Title</h3>
                <button class="nova-modal-close" onclick="closeModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="nova-modal-body" id="modalBody">
                <!-- Modal content loaded here -->
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    
    <!-- Google Maps -->
    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&libraries=geometry,drawing,places"></script>
    
    <!-- Firebase -->
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-messaging-compat.js"></script>
    
    <script>
        // Theme Toggle
        const themeToggle = document.getElementById('themeToggle');
        const html = document.documentElement;
        
        // Check saved theme
        const savedTheme = localStorage.getItem('theme') || 'light';
        html.setAttribute('data-theme', savedTheme);
        updateThemeIcon(savedTheme);
        
        themeToggle?.addEventListener('click', () => {
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
        });
        
        function updateThemeIcon(theme) {
            const icon = themeToggle?.querySelector('i');
            if (icon) {
                icon.className = theme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
            }
        }
        
        // Sidebar Toggle
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        
        sidebarToggle?.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            document.body.classList.toggle('sidebar-active');
        });
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.nova-dropdown')) {
                document.querySelectorAll('.nova-dropdown.open').forEach(dropdown => {
                    dropdown.classList.remove('open');
                });
            }
        });
        
        // Navigation submenu toggle
        document.querySelectorAll('.nova-nav-item.has-submenu > .nova-nav-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                link.parentElement.classList.toggle('open');
            });
        });
        
        // Modal functions
        function openModal(title, content) {
            document.getElementById('modalTitle').textContent = title;
            document.getElementById('modalBody').innerHTML = content;
            document.getElementById('novaModal').classList.add('open');
            document.body.style.overflow = 'hidden';
        }
        
        function closeModal() {
            document.getElementById('novaModal').classList.remove('open');
            document.body.style.overflow = '';
        }
        
        // Close modal on backdrop click
        document.getElementById('novaModal')?.addEventListener('click', (e) => {
            if (e.target === e.currentTarget) {
                closeModal();
            }
        });
        
        // Close modal on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
        
        // Load notifications
        function loadNotifications() {
            fetch('{{ url("notificaitons") }}')
                .then(response => response.json())
                .then(data => {
                    const list = document.getElementById('notificationList');
                    const count = document.getElementById('notificationCount');
                    
                    if (data.data && data.data.length > 0) {
                        let html = '';
                        data.data.forEach(notification => {
                            html += `
                                <div class="nova-dropdown-item" style="flex-direction: column; align-items: flex-start; gap: 0.25rem;">
                                    <div style="font-size: 0.875rem;">${notification.notifications_text}</div>
                                    <div style="font-size: 0.75rem; color: var(--color-gray-400);">${notification.created_at}</div>
                                </div>
                            `;
                        });
                        list.innerHTML = html;
                        count.textContent = data.data.length;
                        count.style.display = 'flex';
                    }
                })
                .catch(err => console.log('Error loading notifications:', err));
        }
        
        // AJAX popup loader (for legacy compatibility)
        $(document).on('click', '.popup', function() {
            const url = $(this).attr('data-url');
            const type = $(this).attr('data-type');
            
            $.get(url, function(data) {
                openModal(type === 'view' ? 'Details' : 'Action', data);
            });
        });
        
        // Firebase initialization
        @if(auth()->check())
        const firebaseConfig = {
            apiKey: "{{ config('firebase.api_key') }}",
            authDomain: "{{ env('FIREBASE_AUTH_DOMAIN', 'ultt-ce8f2.firebaseapp.com') }}",
            projectId: "{{ env('FIREBASE_PROJECT_ID', 'ultt-ce8f2') }}",
            storageBucket: "{{ env('FIREBASE_STORAGE_BUCKET', 'ultt-ce8f2.appspot.com') }}",
            messagingSenderId: "{{ env('FIREBASE_MESSAGING_SENDER_ID', '1027654555881') }}",
            appId: "{{ env('FIREBASE_APP_ID', '1:1027654555881:web:646826f82459642ab5f878') }}"
        };
        
        try {
            firebase.initializeApp(firebaseConfig);
            const messaging = firebase.messaging();
            
            messaging.requestPermission()
                .then(() => messaging.getToken())
                .then(token => {
                    $.ajax({
                        url: '{{ url("save-device-token") }}',
                        type: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        data: { fcm_token: token, user_id: {{ auth()->user()->id }} }
                    });
                })
                .catch(err => console.log('FCM permission denied:', err));
            
            messaging.onMessage(payload => {
                const { title, body, icon } = payload.notification;
                new Notification(title, { body, icon });
                loadNotifications();
            });
        } catch (e) {
            console.log('Firebase init error:', e);
        }
        @endif
        
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
                },
                dom: '<"flex justify-between items-center p-4"lf>rt<"flex justify-between items-center p-4"ip>'
            });
        }
    </script>
    
    @yield('scripts')
</body>
</html>

