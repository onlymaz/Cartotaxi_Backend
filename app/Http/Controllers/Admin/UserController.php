<?php

namespace App\Http\Controllers\Admin;

use App\Datatables\DataColumn;
use App\Datatables\Datatable;
use App\Http\Controllers\Controller;
use App\Http\Requests\RiderRequest;
use App\Http\Requests\UserRequest;
use App\Models\Order;
use App\Models\Notification;
use App\Models\Role;
use App\Models\User;
use App\Utilities\FireBaseRealTimeDatabase;
use App\Utilities\UserHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Html\Builder;
use Yajra\DataTables\Html\Button;
use App\Utilities\FireBaseMessaging;
use App\Mail\AssignRider;
use App\Jobs\SendAssignRider;
use App\Jobs\SendConfirmationEmail;
use App\Scopes\UserFilter;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(User::class, 'user');
    }

    public function index(Request $request, Builder $builder, UserFilter $filters)
    {
        if ($request->ajax()) {
            $custom_columns =   [
                'Action'    =>  'admin.users.actions',
                'Image'     =>  'admin.users.image',
                'isWeekly'  => 'admin.users.isWeekly',
                'Status'  =>  'admin.users.IsActive',
            ];
            
            $users = User::filter($filters)
                ->join('roles as r', 'r.id', '=', 'users.role_id')
                ->select(
                    'users.first_name',
                    'users.last_name',
                    'users.email',
                    'users.phone_number',
                    'users.id',
                    'users.profile_image',
                    'users.role_id',
                    'users.IsActive',
                    'users.isWeekly',
                    'r.name as rider_name'
                );

            return Datatable::create($users, $custom_columns);
        }

        // Column `name`s must match the ajax query above: the users table is
        // not aliased (the old `u.` prefix broke default search and sorting).
        $columns = [
            ['data' => 'Image', 'name' => 'Image', 'label' => trans('messages.profile_photo')],
            ['data' => 'first_name', 'name' => 'users.first_name', 'label' => trans('messages.first_name')],
            ['data' => 'last_name', 'name' => 'users.last_name', 'label' => trans('messages.last_name')],
            ['data' => 'email', 'name' => 'users.email', 'label' => trans('messages.email')],
            ['data' => 'rider_name', 'name' => 'r.name', 'label' => trans('messages.user_type')],
            ['data' => 'phone_number', 'name' => 'users.phone_number', 'label' => trans('messages.phone')],
            ['data' => 'Status', 'name' => 'Status', 'label' => trans('messages.status')],
            ['data' => 'isWeekly', 'name' => 'isWeekly', 'label' => 'Payment Type'],
            ['data' => 'Action', 'name' => 'Action', 'label' => trans('messages.action')],
        ];

        $html = $builder->columns($columns)
            // Full URL so ?type=customer|rider|admin survives into the ajax
            // request that actually filters the table (see UserFilter).
            ->ajax(url()->full())
            ->dom('Bfrtip')
            ->buttons(
                Button::make('copy'),
                Button::make('excel'),
                Button::make('csv'),
                Button::make('pdf')
            );
        
        $request->session()->forget('userType');

        // Calculate stats for the nova view
        $stats = [
            'total' => User::count(),
            'customers' => User::where('role_id', 3)->count(),
            'riders' => User::where('role_id', 2)->count(),
            'verified' => User::where('confirmed', 1)->count(),
        ];

        return view('admin.users.index', compact('html', 'stats'));
    }

    public function create(Request $request)
    {
        $roles  =   Role::get();
        if ($request->ajax()){
            return view('admin.users.create_modal',compact('roles'));
        }

        return redirect()->route('users.index');
    }

    public function store(UserRequest $request)
    {
        $imagePath = null;
        if ($request->hasFile('profile_image')) {
            $imagePath = $request->file('profile_image')->store('profile-images', 'public');
        }

        $rawCode = (string) random_int(100000, 999999);

        // Admin can set role_id explicitly. UserRequest already validates the
        // value (`required|exists:roles,id` etc) — see app/Http/Requests/UserRequest.php.
        // forceCreate bypasses $fillable, which intentionally excludes role_id.
        $user = User::forceCreate([
            'first_name'                   => $request->first_name,
            'last_name'                    => $request->last_name,
            'email'                        => $request->email,
            'phone_number'                 => $request->phone_number,
            'password'                     => Hash::make($request->password),
            'role_id'                      => (int) $request->role_id,
            'profile_image'                => $imagePath,
            'confirmed'                    => 0,
            'IsActive'                     => 1,
            'device'                       => 'web',
            'provider'                     => 'web',
            'lat'                          => '',
            'long'                         => '',
            'confirmation_code'            => Hash::make($rawCode),
            'confirmation_code_expires_at' => now()->addMinutes(30),
        ]);

        $object         =   UserHelper::user_array($user);
        $reference  =   'users/'.$user->id;
        FireBaseRealTimeDatabase::StoreData($reference,$object);
        $this->dispatch(new SendConfirmationEmail($user, $rawCode));

        return response()->json([
            'success'=>true
        ]);
    }

    /**
     * Admin profile page for a user: identity, account state (active/locked,
     * verified), and the full order history with totals. For riders the
     * history lists orders they delivered; for customers, orders they placed.
     * Authorization runs via authorizeResource + UserPolicy::view.
     */
    public function show(User $user)
    {
        $user->load('role');
        $isRider = optional($user->role)->name === 'rider';

        $ordersQuery = Order::with($isRider ? 'user' : 'rider')
            ->where($isRider ? 'rider_id' : 'customer_id', $user->id)
            ->orderByDesc('id');

        $orderStats = [
            'total'     => (clone $ordersQuery)->count(),
            'delivered' => (clone $ordersQuery)->where('order_status', Order::STATUS_DELIVERED)->count(),
            'cancelled' => (clone $ordersQuery)->whereIn('order_status', [Order::STATUS_CANCEL, Order::STATUS_REFUSED, Order::STATUS_NOT_RECEIVED])->count(),
            'spent'     => (clone $ordersQuery)->where('order_status', Order::STATUS_DELIVERED)->sum('total_amount'),
        ];

        $orders = $ordersQuery->paginate(15)->withQueryString();

        return view('admin.users.show', compact('user', 'orders', 'orderStats', 'isRider'));
    }

    public function edit(Request $request, $id)
    {
        $user   =   User::find($id);
        $roles  =   Role::get();

        if ($request->ajax()){
            return view('admin.users.edit_modal',compact('roles','user'));
        }
        return redirect()->route('users.index');
    }

    public function update(UserRequest $request, $id)
    {
        $user   =   User::find($id);
        $imagePath = $user->profile_image;

        if ($request->hasFile('profile_image')) {
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }
            $imagePath = $request->file('profile_image')->store('profile-images', 'public');
        }

        // role_id is not in $fillable, so we set it directly. Other fields are
        // assigned via forceFill so $fillable cannot be the source of bypass.
        $user->forceFill([
            'first_name'    => $request->first_name,
            'last_name'     => $request->last_name,
            'email'         => $request->email,
            'phone_number'  => $request->phone_number,
            'role_id'       => (int) $request->role_id,
            'profile_image' => $imagePath,
            'isWeekly'      => $request->weekly,
        ]);
        // Only rotate the password when the admin actually supplied one;
        // otherwise leave the existing hash untouched.
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->save();
        $object         =   UserHelper::user_array($user);
        $reference  =   'users/'.$user->id;
        FireBaseRealTimeDatabase::StoreData($reference,$object);
        return response()->json([
            'success'=>true
        ]);
    }

    public function destroy($id)
    {
        return response()->json([
            'success'=>true
        ]);
    }

    public function delete_modal(Request $request,$id)
    {
        $user   =   User::find($id);
        if ($request->ajax()){
            return view('admin.users.delete_modal',compact('user'));
        }
        return redirect()->route('users.index');
    }

    public function change_status(Request $request)
    {
        $user   =   User::find($request->id);

        $user->update([
            'IsActive'  =>  $request->value
        ]);

        return response()->json([
            'status'    =>  true
        ]);
    }

    public function RiderPopup($id)
    {
        $order      =   Order::find($id);
        $riders     =   User::with('order')
            ->where('role_id',2)
            ->where('IsActive',1)
            ->get();
        return view('admin.users.rider_modal',compact('riders','order'));
    }

    public function RiderOrderSave(RiderRequest $request,$id)
    {
        try {
            $order  =   Order::findOrFail($id);
            $order->update([
                'rider_id'  =>  $request->rider_id,
                'order_status'=>'processing'
            ]);
            $rider      =   User::where('id',$request->rider_id)->first();
            $customer   =   User::find($order->customer_id);
            $customer_body  =   "Your Booking Order has been assigned to"." ".$rider->first_name ." ".$rider->last_name;

            // Try to send notifications, but don't fail if Firebase is not configured
            try {
                if ($rider && $rider->fcm_token) {
                    FireBaseMessaging::send_notification($rider->fcm_token,"Admin Assigned you a ride","Assigned Order");
                }
                if ($customer && $customer->fcm_token) {
                    FireBaseMessaging::send_notification($customer->fcm_token,$customer_body,"Assigned Order");
                }
                if ($customer && $customer->fcm_web_token) {
                    FireBaseMessaging::send_notification($customer->fcm_web_token,$customer_body,"Assigned Order");
                }
                if ($rider && $rider->fcm_web_token) {
                    FireBaseMessaging::send_notification($rider->fcm_web_token,"Admin Assigned you a ride","Assigned Order");
                }
            } catch (\Exception $e) {
                \Log::warning('Firebase notification failed in RiderOrderSave: ' . $e->getMessage());
            }

        Notification::create([
            'user_id'  =>  auth()->user()->id,
            'user_to_notify'  => $order->customer_id,
            'notifications_text'  =>  "Admin Assigned you a rider: " ." Rider id :".$rider->id. " Order id : " .$order->id,
        ]);

        Notification::create([
            'user_id'  =>  auth()->user()->id,
            'user_to_notify'  => $rider->id,
            'notifications_text'  =>  "Admin Assigned you a ride: "." Order id :" .$order->id,
        ]);

        $role_id = 1;
        $fcm_token = User::where('role_id',$role_id)->get();

        foreach ($fcm_token as $single) {
            if($single)  
        Notification::create([
            'user_id'  =>  auth()->user()->id,
            'user_to_notify'  => $single->id,
            'notifications_text'  => auth()->user()->name." Assigned a ride Rider id: ".$rider->id." Order id :" .$order->id,
        ]); 
        }

            try {
                SendAssignRider::dispatch($customer, new AssignRider($customer,$order->id,$rider),$rider);
                SendAssignRider::dispatch($rider, new AssignRider($rider,$order->id,$customer),$customer);
            } catch (\Exception $e) {
                \Log::warning('Email dispatch failed in RiderOrderSave: ' . $e->getMessage());
            }

            // Return JSON response for AJAX requests
            return response()->json([
                'success' => true,
                'message' => 'Rider assigned successfully',
                'type' => 'search',
                'url' => route('dispatcher.create')
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in RiderOrderSave: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to assign rider: ' . $e->getMessage()
                ], 500);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign rider: ' . $e->getMessage()
            ], 500);
        }
    }
}