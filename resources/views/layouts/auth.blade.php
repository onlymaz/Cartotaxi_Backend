<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @yield('title')
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#EEF2FF',
                            100: '#E0E7FF',
                            500: '#6366F1',
                            600: '#4F46E5',
                            700: '#4338CA',
                        }
                    }
                }
            }
        }
    </script>
    
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
        }
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        .pattern-dots {
            background-image: radial-gradient(rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 20px 20px;
        }
    </style>
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen flex">
        <!-- Left Side - Branding/Illustration -->
        <div class="hidden lg:flex lg:w-1/2 gradient-bg pattern-dots relative overflow-hidden">
            <div class="absolute inset-0 flex flex-col items-center justify-center p-12">
                <!-- Logo -->
                <div class="mb-8 animate-float">
                    <div class="w-24 h-24 bg-white rounded-2xl shadow-2xl flex items-center justify-center">
                        <img src="{{ url('images/logo-round.png') }}" alt="Logo" class="w-16 h-16">
                    </div>
                </div>
                
                <!-- Title -->
                <h1 class="text-4xl font-bold text-white text-center mb-4">{{ config('app.name', 'CargoTaxi') }}</h1>
                <p class="text-xl text-white/80 text-center mb-12 max-w-md">
                    Fast, reliable delivery services at your fingertips. Connect with drivers and manage your shipments effortlessly.
                </p>
                
                <!-- Feature Icons -->
                <div class="grid grid-cols-3 gap-8 max-w-md">
                    <div class="text-center">
                        <div class="w-14 h-14 bg-white/20 rounded-xl flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-shipping-fast text-2xl text-white"></i>
                        </div>
                        <p class="text-white/90 text-sm font-medium">Fast Delivery</p>
                    </div>
                    <div class="text-center">
                        <div class="w-14 h-14 bg-white/20 rounded-xl flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-map-marker-alt text-2xl text-white"></i>
                        </div>
                        <p class="text-white/90 text-sm font-medium">Live Tracking</p>
                    </div>
                    <div class="text-center">
                        <div class="w-14 h-14 bg-white/20 rounded-xl flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-shield-alt text-2xl text-white"></i>
                        </div>
                        <p class="text-white/90 text-sm font-medium">Secure</p>
                    </div>
                </div>
                
                <!-- Decorative Elements -->
                <div class="absolute -bottom-32 -left-32 w-64 h-64 bg-white/10 rounded-full"></div>
                <div class="absolute -top-16 -right-16 w-48 h-48 bg-white/10 rounded-full"></div>
            </div>
        </div>
        
        <!-- Right Side - Auth Form -->
        <div class="w-full lg:w-1/2 flex flex-col">
            <!-- Mobile Header -->
            <div class="lg:hidden bg-gradient-to-r from-indigo-600 to-purple-600 py-6 px-4">
                <div class="flex items-center justify-center">
                    <img src="{{ url('images/logo-round.png') }}" alt="Logo" class="w-12 h-12 mr-3">
                    <h1 class="text-2xl font-bold text-white">{{ config('app.name', 'CargoTaxi') }}</h1>
                </div>
            </div>
            
            <!-- Auth Content -->
            <div class="flex-1 flex items-center justify-center p-8 bg-gray-50">
                <div class="w-full max-w-md">
                    @yield('content')
                </div>
            </div>
            
            <!-- Footer -->
            <div class="py-4 px-8 bg-white border-t border-gray-100 text-center">
                <p class="text-sm text-gray-500">
                    © {{ date('Y') }} {{ config('app.name', 'CargoTaxi') }}. All rights reserved.
                </p>
            </div>
        </div>
    </div>
    
    @yield('scripts')
</body>
</html>
