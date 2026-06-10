@extends('layouts.modern')
@section('title')
    <title> {{__('messages.driver_dashboard')}} | {{ config('app.name', 'Laravel') }}</title>
@stop
@section('content')

<div class="p-6 bg-gray-50 min-h-screen">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">{{__('messages.dashboard')}}</h1>
            <p class="text-gray-500 mt-1">Welcome back, <span class="text-indigo-600 font-semibold">{{ auth()->user()->first_name }}</span></p>
        </div>
        <div class="mt-4 md:mt-0 flex items-center space-x-3">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                <span class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                Online
            </span>
            <a href="{{ route('rider.bookings') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 px-5 rounded-lg shadow-sm transition-all flex items-center">
                <i class="fas fa-list mr-2"></i> My Deliveries
            </a>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Completed Deliveries -->
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-6 shadow-lg text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium mb-1">{{__('messages.booking_completed')}}</p>
                    <h3 class="text-4xl font-bold">{!! $stats['order_delivered'] !!}</h3>
                </div>
                <div class="bg-white bg-opacity-20 p-3 rounded-xl">
                    <i class="fas fa-check-double text-2xl"></i>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-green-400 border-opacity-30">
                <span class="text-green-100 text-sm flex items-center">
                    <i class="fas fa-trophy mr-2"></i> Successfully delivered
                </span>
            </div>
        </div>

        <!-- Processing -->
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">{{__('messages.booking_process')}}</p>
                    <h3 class="text-3xl font-bold text-gray-900">{!! $stats['order_processing'] !!}</h3>
                </div>
                <div class="bg-blue-50 text-blue-600 p-3 rounded-xl group-hover:bg-blue-100 transition-colors">
                    <i class="fas fa-cog text-2xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm text-blue-500">
                <i class="fas fa-sync-alt mr-2"></i> Being processed
            </div>
        </div>

        <!-- Pending -->
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">{{__('messages.booking_pending')}}</p>
                    <h3 class="text-3xl font-bold text-gray-900">{!! $stats['order_pending'] !!}</h3>
                </div>
                <div class="bg-yellow-50 text-yellow-600 p-3 rounded-xl group-hover:bg-yellow-100 transition-colors">
                    <i class="fas fa-clock text-2xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm text-yellow-500">
                <i class="fas fa-hourglass-half mr-2"></i> Awaiting pickup
            </div>
        </div>

        <!-- Picking -->
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">{{__('messages.booking_picking')}}</p>
                    <h3 class="text-3xl font-bold text-gray-900">{!! $stats['order_picking'] !!}</h3>
                </div>
                <div class="bg-indigo-50 text-indigo-600 p-3 rounded-xl group-hover:bg-indigo-100 transition-colors">
                    <i class="fas fa-hand-paper text-2xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm text-indigo-500">
                <i class="fas fa-box mr-2"></i> Ready for pickup
            </div>
        </div>
    </div>

    <!-- Second Row Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Picked Up -->
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">{{__('messages.booking_pickup')}}</p>
                    <h3 class="text-3xl font-bold text-gray-900">{!! $stats['order_picked_up'] !!}</h3>
                </div>
                <div class="bg-purple-50 text-purple-600 p-3 rounded-xl group-hover:bg-purple-100 transition-colors">
                    <i class="fas fa-box-open text-2xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm text-purple-500">
                <i class="fas fa-truck-loading mr-2"></i> Collected
            </div>
        </div>

        <!-- On The Way -->
        <div class="bg-gradient-to-br from-cyan-500 to-cyan-600 rounded-xl p-6 shadow-lg text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-cyan-100 text-sm font-medium mb-1">{{__('messages.bookings_on_the_way')}}</p>
                    <h3 class="text-4xl font-bold">{!! $stats['order_on_way'] !!}</h3>
                </div>
                <div class="bg-white bg-opacity-20 p-3 rounded-xl">
                    <i class="fas fa-motorcycle text-2xl"></i>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-cyan-400 border-opacity-30">
                <span class="text-cyan-100 text-sm flex items-center">
                    <i class="fas fa-route mr-2"></i> Currently delivering
                </span>
            </div>
        </div>

        <!-- Cancelled -->
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">{{__('messages.canceled_bookings')}}</p>
                    <h3 class="text-3xl font-bold text-gray-900">{!! $stats['order_cancel'] !!}</h3>
                </div>
                <div class="bg-red-50 text-red-600 p-3 rounded-xl group-hover:bg-red-100 transition-colors">
                    <i class="fas fa-times-circle text-2xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm text-red-500">
                <i class="fas fa-ban mr-2"></i> Cancelled
            </div>
        </div>

        <!-- Refused -->
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">Refused Booking</p>
                    <h3 class="text-3xl font-bold text-gray-900">{!! $stats['refused'] !!}</h3>
                </div>
                <div class="bg-orange-50 text-orange-600 p-3 rounded-xl group-hover:bg-orange-100 transition-colors">
                    <i class="fas fa-undo text-2xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm text-orange-500">
                <i class="fas fa-exclamation-triangle mr-2"></i> Returned
            </div>
        </div>
    </div>

    <!-- Quick Actions & Current Delivery -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Quick Actions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-6">Quick Actions</h2>
            <div class="grid grid-cols-2 gap-4">
                <a href="{{ route('rider.bookings') }}" class="flex flex-col items-center p-4 bg-indigo-50 rounded-xl hover:bg-indigo-100 transition-colors group text-center">
                    <div class="bg-indigo-600 text-white p-4 rounded-xl mb-3 group-hover:scale-110 transition-transform">
                        <i class="fas fa-list text-xl"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900">My Deliveries</h3>
                    <p class="text-sm text-gray-500">View assigned orders</p>
                </a>
                <a href="{{ route('settings.profile_view', auth()->user()->id) }}" class="flex flex-col items-center p-4 bg-purple-50 rounded-xl hover:bg-purple-100 transition-colors group text-center">
                    <div class="bg-purple-600 text-white p-4 rounded-xl mb-3 group-hover:scale-110 transition-transform">
                        <i class="fas fa-user-cog text-xl"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900">Profile</h3>
                    <p class="text-sm text-gray-500">Update your info</p>
                </a>
                <a href="{{ route('settings.change_password', auth()->user()->id) }}" class="flex flex-col items-center p-4 bg-green-50 rounded-xl hover:bg-green-100 transition-colors group text-center">
                    <div class="bg-green-600 text-white p-4 rounded-xl mb-3 group-hover:scale-110 transition-transform">
                        <i class="fas fa-key text-xl"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900">Password</h3>
                    <p class="text-sm text-gray-500">Change password</p>
                </a>
                <a href="{{ route('notificaitons.show') }}" class="flex flex-col items-center p-4 bg-yellow-50 rounded-xl hover:bg-yellow-100 transition-colors group text-center">
                    <div class="bg-yellow-600 text-white p-4 rounded-xl mb-3 group-hover:scale-110 transition-transform">
                        <i class="fas fa-bell text-xl"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900">Notifications</h3>
                    <p class="text-sm text-gray-500">View alerts</p>
                </a>
            </div>
        </div>

        <!-- Performance Summary -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-6">Performance Summary</h2>
            <div class="space-y-4">
                <!-- Delivery Rate -->
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-medium text-gray-600">Delivery Success Rate</span>
                        @php
                            $total = $stats['order_delivered'] + $stats['order_cancel'] + $stats['refused'];
                            $successRate = $total > 0 ? round(($stats['order_delivered'] / $total) * 100) : 0;
                        @endphp
                        <span class="text-sm font-bold text-green-600">{{ $successRate }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div class="bg-green-500 h-2.5 rounded-full transition-all duration-500" style="width: {{ $successRate }}%"></div>
                    </div>
                </div>

                <!-- Active Deliveries Progress -->
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-medium text-gray-600">Active Deliveries</span>
                        <span class="text-sm font-bold text-cyan-600">{{ $stats['order_on_way'] + $stats['order_picking'] }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div class="bg-cyan-500 h-2.5 rounded-full" style="width: {{ min(100, ($stats['order_on_way'] + $stats['order_picking']) * 10) }}%"></div>
                    </div>
                </div>

                <!-- Total Stats -->
                <div class="pt-4 mt-4 border-t border-gray-100">
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['order_delivered'] }}</p>
                            <p class="text-xs text-gray-500 uppercase tracking-wider">Delivered</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['order_on_way'] }}</p>
                            <p class="text-xs text-gray-500 uppercase tracking-wider">In Transit</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['order_pending'] }}</p>
                            <p class="text-xs text-gray-500 uppercase tracking-wider">Pending</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
    <script src="https://www.gstatic.com/firebasejs/6.3.4/firebase.js"></script>
    <script>
        $(document).ready(function(){
            const config = {
                apiKey: "{{ config('firebase.api_key') }}",
                authDomain: "{{ config('firebase.auth_domain', 'ultt-ce8f2.firebaseapp.com') }}",
                databaseURL: "{{ config('firebase.database_url', 'https://ultt-ce8f2.firebaseio.com') }}",
                projectId: "{{ config('firebase.project_id', 'ultt-ce8f2') }}",
                storageBucket: "{{ config('firebase.storage_bucket', 'ultt-ce8f2.appspot.com') }}",
                messagingSenderId: "{{ config('firebase.messaging_sender_id', '1027654555881') }}",
                appId: "{{ config('firebase.app_id', '1:1027654555881:web:646826f82459642ab5f878') }}",
                measurementId: "{{ config('firebase.measurement_id', 'G-4W6ZHFLBDK') }}"
            };
            firebase.initializeApp(config);
            const messaging = firebase.messaging();
            messaging
                .requestPermission()
                .then(function () {
                    return messaging.getToken()
                })
                .then(function(token) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.ajax({
                        url: '{{ url("save-device-token") }}',
                        type: 'POST',
                        data: {
                            fcm_token: token,
                            user_id: {{ auth()->user()->id }}
                        },
                        dataType: 'JSON',
                        success: function (response) {
                            console.log('FCM token saved');
                        },
                        error: function (err) {
                            console.log("FCM token error: " + err);
                        },
                    });
                })
                .catch(function (err) {
                    console.log("Unable to get permission to notify.", err);
                });
            messaging.onMessage(function(payload) {
                const noteTitle = payload.notification.title;
                const noteOptions = {
                    body: payload.notification.body,
                    icon: payload.notification.icon,
                };
                new Notification(noteTitle, noteOptions);
            });
        });

        // Sidebar Toggle Logic
        $(document).ready(function() {
            $('.child-toggle').on('click', function(e) {
                e.preventDefault();
                $(this).parent().toggleClass('open');
            });
            $('.nav-opener').on('click', function(e) {
                e.preventDefault();
                $('body').toggleClass('sidebar-active');
            });
        });
    </script>
@endsection
it