@extends('layouts.modern')
@section('title')
    <title> {{__('messages.customer_dashboard')}} | {{ config('app.name', 'Laravel') }}</title>
@stop
@section('content')

<div class="p-6 bg-gray-50 min-h-screen">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">{{__('messages.dashboard')}}</h1>
            <p class="text-gray-500 mt-1">Welcome back, <span class="text-indigo-600 font-semibold">{{ auth()->user()->first_name }}</span></p>
        </div>
        <div class="mt-4 md:mt-0">
            <a href="{{ route('customer.bookings') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 px-5 rounded-lg shadow-sm transition-all flex items-center">
                <i class="fas fa-plus mr-2"></i> New Booking
            </a>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Completed Bookings -->
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">{{__('messages.booking_completed')}}</p>
                    <h3 class="text-3xl font-bold text-gray-900">{!! $order_delivered !!}</h3>
                </div>
                <div class="bg-green-50 text-green-600 p-3 rounded-xl group-hover:bg-green-100 transition-colors">
                    <i class="fas fa-check-circle text-2xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-green-500 flex items-center">
                    <i class="fas fa-arrow-up mr-1"></i> Delivered
                </span>
            </div>
        </div>

        <!-- Processing -->
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">{{__('messages.booking_process')}}</p>
                    <h3 class="text-3xl font-bold text-gray-900">{!! $order_processing !!}</h3>
                </div>
                <div class="bg-blue-50 text-blue-600 p-3 rounded-xl group-hover:bg-blue-100 transition-colors">
                    <i class="fas fa-cog fa-spin text-2xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-blue-500 flex items-center">
                    <i class="fas fa-sync-alt mr-1"></i> In Progress
                </span>
            </div>
        </div>

        <!-- Pending -->
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">{{__('messages.booking_pending')}}</p>
                    <h3 class="text-3xl font-bold text-gray-900">{!! $order_pending !!}</h3>
                </div>
                <div class="bg-yellow-50 text-yellow-600 p-3 rounded-xl group-hover:bg-yellow-100 transition-colors">
                    <i class="fas fa-clock text-2xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-yellow-500 flex items-center">
                    <i class="fas fa-hourglass-half mr-1"></i> Awaiting
                </span>
            </div>
        </div>

        <!-- Picking -->
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">{{__('messages.booking_picking')}}</p>
                    <h3 class="text-3xl font-bold text-gray-900">{!! $order_picking !!}</h3>
                </div>
                <div class="bg-indigo-50 text-indigo-600 p-3 rounded-xl group-hover:bg-indigo-100 transition-colors">
                    <i class="fas fa-hand-paper text-2xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-indigo-500 flex items-center">
                    <i class="fas fa-box mr-1"></i> Started
                </span>
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
                    <h3 class="text-3xl font-bold text-gray-900">{!! $order_picked_up !!}</h3>
                </div>
                <div class="bg-purple-50 text-purple-600 p-3 rounded-xl group-hover:bg-purple-100 transition-colors">
                    <i class="fas fa-box-open text-2xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-purple-500 flex items-center">
                    <i class="fas fa-truck-loading mr-1"></i> Collected
                </span>
            </div>
        </div>

        <!-- On The Way -->
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">{{__('messages.bookings_on_the_way')}}</p>
                    <h3 class="text-3xl font-bold text-gray-900">{!! $order_on_way !!}</h3>
                </div>
                <div class="bg-cyan-50 text-cyan-600 p-3 rounded-xl group-hover:bg-cyan-100 transition-colors">
                    <i class="fas fa-shipping-fast text-2xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-cyan-500 flex items-center">
                    <i class="fas fa-route mr-1"></i> In Transit
                </span>
            </div>
        </div>

        <!-- Cancelled -->
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">{{__('messages.canceled_bookings')}}</p>
                    <h3 class="text-3xl font-bold text-gray-900">{!! $order_cancel !!}</h3>
                </div>
                <div class="bg-red-50 text-red-600 p-3 rounded-xl group-hover:bg-red-100 transition-colors">
                    <i class="fas fa-times-circle text-2xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-red-500 flex items-center">
                    <i class="fas fa-ban mr-1"></i> Cancelled
                </span>
            </div>
        </div>

        <!-- Refused -->
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">Refused Booking</p>
                    <h3 class="text-3xl font-bold text-gray-900">{!! $order_refused !!}</h3>
                </div>
                <div class="bg-orange-50 text-orange-600 p-3 rounded-xl group-hover:bg-orange-100 transition-colors">
                    <i class="fas fa-exclamation-triangle text-2xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-orange-500 flex items-center">
                    <i class="fas fa-undo mr-1"></i> Returned
                </span>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-6">Quick Actions</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="{{ route('customer.bookings') }}" class="flex items-center p-4 bg-indigo-50 rounded-xl hover:bg-indigo-100 transition-colors group">
                <div class="bg-indigo-600 text-white p-3 rounded-lg mr-4 group-hover:scale-110 transition-transform">
                    <i class="fas fa-plus"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">New Booking</h3>
                    <p class="text-sm text-gray-500">Create a delivery</p>
                </div>
            </a>
            <a href="{{ route('customer.mybookings') }}" class="flex items-center p-4 bg-green-50 rounded-xl hover:bg-green-100 transition-colors group">
                <div class="bg-green-600 text-white p-3 rounded-lg mr-4 group-hover:scale-110 transition-transform">
                    <i class="fas fa-list"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">My Bookings</h3>
                    <p class="text-sm text-gray-500">View all orders</p>
                </div>
            </a>
            <a href="{{ route('settings.profile_view', auth()->user()->id) }}" class="flex items-center p-4 bg-purple-50 rounded-xl hover:bg-purple-100 transition-colors group">
                <div class="bg-purple-600 text-white p-3 rounded-lg mr-4 group-hover:scale-110 transition-transform">
                    <i class="fas fa-user-cog"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">Profile</h3>
                    <p class="text-sm text-gray-500">Manage account</p>
                </div>
            </a>
            <a href="{{ route('helper.index') }}" class="flex items-center p-4 bg-yellow-50 rounded-xl hover:bg-yellow-100 transition-colors group">
                <div class="bg-yellow-600 text-white p-3 rounded-lg mr-4 group-hover:scale-110 transition-transform">
                    <i class="fas fa-hands-helping"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">Helpers</h3>
                    <p class="text-sm text-gray-500">Book assistance</p>
                </div>
            </a>
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
