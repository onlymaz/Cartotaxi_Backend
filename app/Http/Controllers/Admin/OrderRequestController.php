<?php

namespace App\Http\Controllers\Admin;

use App\Datatables\DataColumn;
use App\Datatables\Datatable;
use App\Http\Controllers\Controller;
use App\Http\Requests\ChangeOrderStatusRequest;
use App\Models\Gateway;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Html\Builder;
use Yajra\DataTables\Html\Button;
use App\Utilities\FireBaseMessaging;
use App\Models\HelperOrder;
use App\Models\Notification;
use App\Models\Helper;
use App\Utilities\UserHelper;
use App\Mail\UpdateStatus;
use App\Jobs\SendUpdateStatus;
use App\Mail\ConfirmOrder;
use Str;
use App\Jobs\ConfirmOrderJob;
use Auth;
use App\Utilities\FireBaseRealTimeDatabase;
use App\Scopes\OrderFilter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OrderRequestController extends Controller
{
    public function __construct()
    {
        // Don't use authorizeResource here - we'll handle authorization manually in each method
        // This allows us to bypass authorization for admin users in the show method
    }

    public function index(Request $request, Builder $builder, OrderFilter $filters)
    {
        // Determine action - if status is provided in URL, use 'all' action to show all orders
        $statusParam = $request->get('status');
        if ($statusParam && in_array($statusParam, ['pending', 'processing', 'picking', 'on_way', 'delivered', 'cancel', 'accident'])) {
            $action = 'all'; // Override action when specific status is requested
        } else {
        $action = $request->get('action', 'pickup');
        }
        $filter = $request->get('statusFilter', $statusParam ?: 'all');
        $orderFilter = $request->get('orderFilterType', 'all');

        if ($request->ajax()) {
            try {
            $custom_columns = [
                    'SerialNumber'  => 'admin.requests.serial-number',
                    'CustomerName'  => 'admin.requests.customer-name',
                    'RiderName'     => 'admin.requests.rider-name',
                    'BookingID'     => 'admin.requests.booking-id',
                'DateTime'      => 'admin.requests.date-time',
                    'PaymentMethod' => 'admin.requests.payment-method',
                'Status'        => 'admin.requests.status',
                    'Action'        => 'admin.requests.actions',
            ];

                // Build query with joins - use select() with array to avoid DataTables subquery issues
                $orders = Order::query()
                ->join('users as u', 'u.id', '=', 'orders.customer_id')
                ->leftJoin('users as r', 'r.id', '=', 'orders.rider_id')
                ->leftJoin('payments as p', 'p.order_id', '=', 'orders.id')
                ->leftJoin('gateways as g', 'g.id', '=', 'p.gateway_id')
                    ->leftJoin('pivot_helper_order as hp', 'hp.order_id', '=', 'orders.id')
                    ->select([
                        'orders.id as id',
                        'orders.booking_id',
                        'orders.rider_id',
                        'orders.order_status',
                        'orders.start_location',
                        'orders.end_location',
                        'orders.total_amount',
                        'orders.picked_time',
                        'u.first_name as customer_first_name',
                        'u.last_name as customer_last_name',
                        'r.first_name as rider_first_name',
                        'r.last_name as rider_last_name',
                        'p.status as payment_status',
                        'g.name as gateway_name',
                        'orders.created_at',
                        'orders.updated_at',
                        'hp.id as helper_order_id',
                        'hp.order_id as HelperOrderId'
                    ]);

                // Apply status filter first (before OrderFilter to avoid conflicts)
                if ($request->has('status') && $request->status != 'all') {
                    $orders->where('orders.order_status', $request->status);
                }

                // Apply payment status filter
                if ($request->has('payment_status') && $request->payment_status != 'all') {
                    $orders->where('p.status', $request->payment_status);
                }

                // Apply payment method filter
                if ($request->has('payment_method') && $request->payment_method != 'all' && $request->payment_method != '') {
                    $orders->where('g.name', $request->payment_method);
                }

                // Apply customer name filter
                if ($request->has('customer_name') && $request->customer_name != '') {
                    $customerName = $request->customer_name;
                    $orders->where(function($query) use ($customerName) {
                        $query->where('u.first_name', 'like', '%' . $customerName . '%')
                              ->orWhere('u.last_name', 'like', '%' . $customerName . '%')
                              ->orWhereRaw("CONCAT(u.first_name, ' ', u.last_name) LIKE ?", ['%' . $customerName . '%']);
                    });
                }

                // Apply rider name filter
                if ($request->has('rider_name') && $request->rider_name != '') {
                    $riderName = $request->rider_name;
                    $orders->where(function($query) use ($riderName) {
                        $query->where('r.first_name', 'like', '%' . $riderName . '%')
                              ->orWhere('r.last_name', 'like', '%' . $riderName . '%')
                              ->orWhereRaw("CONCAT(r.first_name, ' ', r.last_name) LIKE ?", ['%' . $riderName . '%']);
                    });
                }

                // Apply booking ID filter
                if ($request->has('booking_id') && $request->booking_id != '') {
                    $orders->where('orders.booking_id', 'like', '%' . $request->booking_id . '%');
                }

                // Apply date filters
                if ($request->has('date_from') && $request->date_from) {
                    $orders->whereDate('orders.created_at', '>=', Carbon::parse($request->date_from));
                }

                if ($request->has('date_to') && $request->date_to) {
                    $orders->whereDate('orders.created_at', '<=', Carbon::parse($request->date_to));
                }

                // Apply OrderFilter only if action is not 'all' and no specific status is requested
                if ($action != 'all' && !$request->has('status')) {
                    // Apply helper filter
                    if ($request->has('orderFilterType') && $request->orderFilterType != 'all') {
                        if ($request->orderFilterType == 'withouthelper') {
                            $orders->whereNull('hp.order_id');
                        } elseif ($request->orderFilterType == 'withhelper') {
                            $orders->whereNotNull('hp.order_id');
                        }
                    }

                    // Apply action-based filters
                    if ($action == 'pickup') {
                        $orders->whereNotIn('orders.order_status', [Order::STATUS_ACCIDENT, Order::STATUS_REFUSED, Order::STATUS_DELIVERED, Order::STATUS_CANCEL])
                            ->orderBy('orders.picked_time', 'asc');
                    } elseif ($action == 'schedule') {
                        $orders->whereDate('orders.picked_time', '>', Carbon::now())
                            ->orderBy('orders.picked_time', 'asc');
                    } elseif ($action == 'history') {
                        $orders->whereIn('orders.order_status', [Order::STATUS_ACCIDENT, Order::STATUS_REFUSED, Order::STATUS_DELIVERED, Order::STATUS_CANCEL])
                            ->orderBy('orders.updated_at', 'desc');
                    }
                } else {
                    // Apply helper filter even when action is 'all'
                    if ($request->has('orderFilterType') && $request->orderFilterType != 'all') {
                        if ($request->orderFilterType == 'withouthelper') {
                            $orders->whereNull('hp.order_id');
                        } elseif ($request->orderFilterType == 'withhelper') {
                            $orders->whereNotNull('hp.order_id');
                        }
                    }
                    // Default ordering when action is 'all'
                    $orders->orderBy('orders.created_at', 'desc');
                }

                // Apply statusFilter if provided and status is not already set
                if (!$request->has('status') && $request->has('statusFilter') && $request->statusFilter != 'all') {
                    $orders->where('orders.order_status', $request->statusFilter);
                }

                // Pass query builder directly to DataTables (don't call ->get())
                return Datatable::create($orders, $custom_columns);
            } catch (\Exception $e) {
                \Log::error('DataTables AJAX Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
                return response()->json([
                    'draw' => intval($request->get('draw')),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                    'error' => 'An error occurred while loading data. Please try again.'
                ]);
            }
        }

        // Build columns with clear, descriptive labels
        $columns_data = [];
        
        $columns = [
            ['data' => 'SerialNumber', 'name' => 'SerialNumber', 'label' => 'Serial Number', 'orderable' => false, 'searchable' => false],
            ['data' => 'CustomerName', 'name' => 'CustomerName', 'label' => 'Customer Name', 'orderable' => true, 'searchable' => true],
            ['data' => 'RiderName', 'name' => 'RiderName', 'label' => 'Rider Name', 'orderable' => true, 'searchable' => true],
            ['data' => 'BookingID', 'name' => 'orders.booking_id', 'label' => 'Booking ID', 'orderable' => true, 'searchable' => true],
            ['data' => 'DateTime', 'name' => 'orders.created_at', 'label' => 'Date and Time', 'orderable' => true, 'searchable' => false],
            ['data' => 'PaymentMethod', 'name' => 'g.name', 'label' => 'Payment Method', 'orderable' => true, 'searchable' => true],
            ['data' => 'Status', 'name' => 'orders.order_status', 'label' => 'Order Status', 'orderable' => true, 'searchable' => true],
            ['data' => 'Action', 'name' => 'Action', 'label' => 'Actions', 'orderable' => false, 'searchable' => false],
        ];
        
        // Convert to DataColumn format for proper header display
        foreach ($columns as $column) {
            $isSearchable = isset($column['searchable']) ? $column['searchable'] : true;
            $isOrderable = isset($column['orderable']) ? $column['orderable'] : true;
            $columns_data[] = DataColumn::add_new($column, $isSearchable, $isOrderable);
        }

        $html = $builder->columns($columns_data)
            ->dom('Bfrtip')
            ->pageLength(10) // Show 10 records per page
            ->lengthMenu([10, 25, 50, 100]) // Allow users to choose: 10, 25, 50, or 100 records per page
            ->buttons(
                Button::make('copy'),
                Button::make('excel'),
                Button::make('csv'),
                Button::make('pdf')
            );

        // Calculate stats for nova view
        $stats = [
            'total' => Order::count(),
            'pending' => Order::where('order_status', 'pending')->count(),
            'processing' => Order::whereIn('order_status', ['processing', 'picking', 'on_way'])->count(),
            'delivered' => Order::where('order_status', 'delivered')->count(),
            'cancelled' => Order::where('order_status', 'cancel')->count(),
        ];

        return view('admin.requests.index', compact('html', 'action', 'filter', 'orderFilter', 'stats'));
    }

    public function create()
    {
        return view('admin.requests.show');
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        try {
            // Eager load all relationships to prevent N+1 queries
            $order = Order::with([
                'user',           // Customer relationship
                'rider',          // Rider relationship  
                'package',        // Package relationship
                'orderSubTrip',   // Order sub trip relationship
                'payment.gateway' // Payment and gateway relationship
            ])->findOrFail($id);
            
            // Allow admin users (role_id == 1) to always view orders
            // For other users, check authorization via policy
            $user = Auth::user();
            if ($user && $user->role_id != 1) {
                // Check authorization for non-admin users
                if (!$user->hasRole('admin') && $user->id !== $order->customer_id && $user->id !== $order->rider_id) {
                    if (request()->ajax() || request()->wantsJson()) {
                        return response('<div style="padding: 2rem; text-align: center; color: #ef4444;"><i class="fas fa-exclamation-circle" style="font-size: 2rem; margin-bottom: 1rem;"></i><p>You are not authorized to view this order.</p></div>', 403)->header('Content-Type', 'text/html');
                    }
                    abort(403);
                }
            }
            // Admin users (role_id == 1) can always view, so skip authorization check
            
            $helper = HelperOrder::with('helper')->where('order_id', $order->id)->first();
        if(!$helper){
                $helper = [];
            }
            
            $html = view('admin.requests.request-detail-popup', compact('order', 'helper'))->render();
            
            // Return HTML string for AJAX requests
            if (request()->ajax() || request()->wantsJson()) {
                return response($html, 200)->header('Content-Type', 'text/html');
            }
            
            return view('admin.requests.request-detail-popup', compact('order', 'helper'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('Order not found: ' . $id);
            $errorHtml = '<div style="padding: 2rem; text-align: center; color: #ef4444;"><i class="fas fa-exclamation-circle" style="font-size: 2rem; margin-bottom: 1rem;"></i><p>Order not found.</p></div>';
            if (request()->ajax() || request()->wantsJson()) {
                return response($errorHtml, 404)->header('Content-Type', 'text/html');
            }
            return $errorHtml;
        } catch (\Exception $e) {
            \Log::error('Error in show method: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            $errorHtml = '<div style="padding: 2rem; text-align: center; color: #ef4444;"><i class="fas fa-exclamation-circle" style="font-size: 2rem; margin-bottom: 1rem;"></i><p>An error occurred while loading the order. Please try again.</p></div>';
            if (request()->ajax() || request()->wantsJson()) {
                return response($errorHtml, 500)->header('Content-Type', 'text/html');
            }
            return $errorHtml;
        }
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }

    public function change_status_popup($id)
    {
        try {
            $order = Order::findOrFail($id);
            return view('admin.requests.change_status_popup', compact('order'));
        } catch (\Exception $e) {
            \Log::error('Error in change_status_popup method: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            \Log::error('Error in change_status_popup: ' . $e->getMessage());
            return '<div style="padding: 2rem; text-align: center; color: #ef4444;"><i class="fas fa-exclamation-circle" style="font-size: 2rem; margin-bottom: 1rem;"></i><p>An error occurred. Please try again.</p></div>';
        }
    }

    public function change_status(ChangeOrderStatusRequest $request, $id)
    {
        try {
            $order = Order::findOrFail($id);
            
            // Allow admin users (role_id == 1) to always update orders
            // For other users, check authorization
            $user = Auth::user();
            if ($user->role_id != 1) {
                // Check authorization for non-admin users
                if (!$user->hasRole('admin') && $user->id !== $order->rider_id) {
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'You are not authorized to update this order.'
                        ], 403);
                    }
                    abort(403);
                }
            }
            // Admin users (role_id == 1) can always update, so skip authorization check

        $start_time = $order->start_time;
        $end_time = $order->end_time;
        $customer_id = $order->customer_id;

        if ($request->order_status == Order::STATUS_PICKING) {
            $start_time = now();
        }

        if (in_array($request->order_status, [Order::STATUS_DELIVERED, Order::STATUS_REFUSED, Order::STATUS_NOT_RECEIVED])) {
            $end_time = now();
        }

        $order->update([
            'order_status' => $request->order_status,
            'start_time'  =>  $start_time,
            'end_time'  =>  $end_time,
        ]);

        if ($request->order_status == Order::STATUS_DELIVERED) {
            if ($request->signature) {
                $image_64 = $request->signature;
                $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];
                $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
                $image = str_replace($replace, '', $image_64);
                $image = str_replace(' ', '+', $image);
                $imageName = Str::random(10) . '.' . $extension;
                Storage::disk('public')->put('signatures/' . $imageName, base64_decode($image));

                if ($request->reciver_name && $request->reciver_address) {
                    $order->update([
                        'reciver_name' => $request->reciver_name,
                        'reciver_address' => $request->reciver_address,
                    ]);
                }
                $order->update([
                    'sign' => 'signatures/' . $imageName
                ]);
            }

            Payment::where('order_id', $order->id)->update([
                'status' => "completed"
            ]);
        }

        // Try to send notifications, but don't fail if Firebase is not configured
        try {
        $fcm_token = User::where('id', $customer_id)->first();

            if ($fcm_token) {
        $messages = UserHelper::orderStatus($request->order_status);
        if ($request->order_status == Order::STATUS_PROCESSING) {
                    try {
                        if ($fcm_token->fcm_token) {
            FireBaseMessaging::send_notification($fcm_token->fcm_token, 'Your Order has been Confirmed', 'Confirm Order', $order);
                        }
                        if ($fcm_token->fcm_web_token) {
            FireBaseMessaging::send_notification($fcm_token->fcm_web_token, 'Your Order has been Confirmed', 'Confirm Order', $order);
                        }
            ConfirmOrderJob::dispatch($fcm_token, new ConfirmOrder($fcm_token, $order->id));
                    } catch (\Exception $e) {
                        \Log::warning('Firebase notification failed: ' . $e->getMessage());
                    }

            Notification::create([
                'user_id'  =>  auth()->id(),
                'user_to_notify'  => $fcm_token->id,
                'notifications_text'  => "Order Id :" . $order->id . ' Update Status : ' . $order->order_status,
            ]);
        } else {
            $role = "Rider";
            if (Auth::user()->role_id == 1) {
                $role = "Admin";
            }
                    try {
                        if ($fcm_token->fcm_token) {
            FireBaseMessaging::send_notification(
                $fcm_token->fcm_token,
                "Order Id :" . $order->id,
                $role . ' Update Status : ' . str_replace("_", " ", $messages),
                $order
            );
                        }
                        if ($fcm_token->fcm_web_token) {
            FireBaseMessaging::send_notification(
                $fcm_token->fcm_web_token,
                "Order Id :" . $order->id,
                $role . ' Update Status : ' . str_replace("_", " ", $messages),
                $order
            );
                        }
            SendUpdateStatus::dispatch(
                $fcm_token,
                new UpdateStatus(
                    $fcm_token,
                    $order->id,
                    str_replace("_", " ", $messages),
                    $role
                ),
                str_replace("_", " ", $messages),
                $role
            );
                    } catch (\Exception $e) {
                        \Log::warning('Firebase notification failed: ' . $e->getMessage());
                    }

            Notification::create([
                'user_id'  =>  auth()->id(),
                'user_to_notify'  => $fcm_token->id,
                'notifications_text'  => "Order Id :" . $order->id . ' Update Status : ' . $order->order_status,
            ]);
            $role_id = 1;
                    $adminUsers = User::where('role_id', $role_id)->get();

                    foreach ($adminUsers as $single) {
                        if ($single) {
                    Notification::create([
                        'user_id'  =>  auth()->id(),
                        'user_to_notify'  =>  $single->id,
                        'notifications_text'  => "Order Id :" . $order->id . ' Update Status : ' . $order->order_status,
                    ]);
            }
                    }
                }
            }
        } catch (\Exception $e) {
            // Log notification errors but don't fail the status update
            \Log::warning('Notification error (non-critical): ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'order_status' => $order->order_status
        ]);
        } catch (\Exception $e) {
            \Log::error('Error in change_status method: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update status. Please try again.'
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to update status. Please try again.');
        }
    }
    public function liveMap()
    {
        return view('admin.map.live-tracking');
    }

    public function liveMapAjax()
    {
        try {
            // Get all riders (role_id = 2) with their locations
            // Validate coordinates are numeric and within valid ranges
            $riders = User::where('role_id', 2)
                ->whereNotNull('lat')
                ->whereNotNull('long')
                ->where('lat', '!=', '')
                ->where('long', '!=', '')
                ->select('id', 'first_name', 'last_name', 'email', 'phone_number', 'profile_image', 'lat', 'long', 'IsActive')
                ->get()
                ->filter(function($rider) {
                    // Validate coordinates are valid numbers and within range
                    $lat = is_numeric($rider->lat) ? (float)$rider->lat : null;
                    $lng = is_numeric($rider->long) ? (float)$rider->long : null;
                    
                    if ($lat === null || $lng === null) {
                        return false;
                    }
                    
                    // Validate latitude is between -90 and 90
                    if ($lat < -90 || $lat > 90) {
                        return false;
                    }
                    
                    // Validate longitude is between -180 and 180
                    if ($lng < -180 || $lng > 180) {
                        return false;
                    }
                    
                    return true;
                })
                ->values(); // Re-index array after filtering
            
            // Get active orders for each rider
            $activeOrders = Order::whereIn('rider_id', $riders->pluck('id'))
                ->whereNotIn('order_status', ['delivered', 'cancel', 'refused'])
                ->with(['user', 'orderSubTrip'])
                ->get()
                ->keyBy('rider_id');
            
            // Combine rider data with their active orders
            $ridersWithOrders = $riders->map(function($rider) use ($activeOrders) {
                $riderData = $rider->toArray();
                $order = $activeOrders->get($rider->id);
                
                // If rider has active order, add order details
                if ($order) {
                    // Get first order sub trip for route coordinates
                    $firstTrip = $order->orderSubTrip->first();
                    
                    $riderData['active_order'] = [
                        'id' => $order->id,
                        'booking_id' => $order->booking_id,
                        'order_status' => $order->order_status,
                        'start_location' => $order->start_location,
                        'end_location' => $order->end_location,
                        'start_lat' => $firstTrip ? $firstTrip->start_lat : null,
                        'start_lng' => $firstTrip ? $firstTrip->start_long : null, // Backend uses 'start_long', frontend expects 'start_lng'
                        'start_long' => $firstTrip ? $firstTrip->start_long : null, // Also include 'start_long' for compatibility
                        'end_lat' => $firstTrip ? $firstTrip->end_lat : null,
                        'end_lng' => $firstTrip ? $firstTrip->end_long : null, // Backend uses 'end_long', frontend expects 'end_lng'
                        'end_long' => $firstTrip ? $firstTrip->end_long : null, // Also include 'end_long' for compatibility
                        'customer_name' => $order->user ? ($order->user->first_name . ' ' . $order->user->last_name) : 'N/A',
                    ];
                } else {
                    $riderData['active_order'] = null;
                }
                
                return $riderData;
            });
            
            // Calculate stats
            $stats = [
                'active_riders' => $riders->count(),
                'active_orders' => $activeOrders->count(),
                'available_riders' => $riders->count() - $activeOrders->count(),
            ];
            
            return response()->json([
                'success' => true,
                'riders' => $ridersWithOrders,
                'stats' => $stats,
                'timestamp' => now()->toIso8601String() // Add timestamp for debugging
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            \Log::error('Error in liveMapAjax: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading rider data',
                'riders' => [],
                'stats' => [
                    'active_riders' => 0,
                    'active_orders' => 0,
                    'available_riders' => 0,
                ]
            ], 500);
        }
    }
}
