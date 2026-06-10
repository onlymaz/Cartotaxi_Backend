<?php

namespace App\Http\Controllers\Helper;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAdminHelperRequest;
use App\Http\Requests\StoreHelperRequest;
use Illuminate\Http\Request;
use App\Models\Helper;
use App\Models\HelperOrder;
use App\Datatables\DataColumn;
use App\Datatables\Datatable;
use App\Models\Gateway;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Yajra\DataTables\Html\Builder;
use Yajra\DataTables\Html\Button;
use App\Utilities\FireBaseMessaging;
use Auth;
use App\Models\Notification;
use Illuminate\Support\Facades\Validator;
use App\Models\HelperFee;
use App\Jobs\CreateHelper;
use App\Mail\NewHelper;
use App\Jobs\HelperConfirm;
use App\Mail\HelperConfirmMail;


class HelperController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index(Request $request,Builder $builder)
    {
        $action =   $request->action;
        if ($request->ajax()){
            $custom_columns =   [
                'Action'    =>  'helper.request.actions',
                'status'    =>  'helper.request.Status',
            ];
            $users  =   Helper::with(['user', 'helperOrder.order', 'gateway'])
                        ->select(
                                'helpers.id',
                                'helpers.user_id',
                                'helpers.total_helper',
                                'helpers.payment_method',
                                'helpers.status',
                                'helpers.start_time',
                                'helpers.end_time',
                                'helpers.address',
                                'helpers.price'
                            )
                        ->orderBy('id', 'DESC');
            if(Auth::user()->role->name!="admin"){
                $users->where('user_id',Auth::user()->id);
            }

            return Datatable::create($users,$custom_columns);
        }
        if(Auth::user()->role->name=="admin"){
        $columns         =   [
            [
                'data' =>  'id',
                'name'  =>  'h.id',
                'label' =>  "Helper Id",
            ],
            [
                'data' =>  'order_id',
                'name'  =>  'ho.order_id',
                'label' =>  'Booking ID',
            ],
            [
                'data' =>  'first_name',
                'name'  =>  'u.first_name',
                'label' =>  'User',
            ],
            [
                'data' =>  'total_helper',
                'name'  =>  'h.total_helper',
                'label' =>  "Total Helper",
            ],
            [
                'data' =>  'price',
                'name'  =>  'h.price',
                'label' =>  "Price",
            ],
            [
                'data' =>  'start_time',
                'name'  =>  'h.start_time',
                'label' =>  "Start Time",
            ],
            [
                'data' =>  'end_time',
                'name'  =>  'h.end_time',
                'label' =>  "Total Hours",
            ],
            [
                'data' =>  'address',
                'name'  =>  'h.address',
                'label' =>  "Address",
            ],
            [
                'data' =>  'payment',
                'name'  =>  'g.name',
                'label' => 'Payment Method',
            ],
            [
                'data' =>  'status',
                'name'  =>  'h.status',
                'label' =>  "Status",
            ],
            [
                'data' =>  'Action',
                'name'  =>  'Action',
                'label' =>  trans('messages.action'),
            ],
        ];
        }else{
            $columns         =   [
                [
                    'data' =>  'id',
                    'name'  =>  'h.id',
                    'label' =>  "Helper Id",
                ],
                [
                    'data' =>  'order_id',
                    'name'  =>  'ho.order_id',
                    'label' =>  'Booking ID',
                ],
                [
                    'data' =>  'total_helper',
                    'name'  =>  'h.total_helper',
                    'label' =>  "Total Helper",
                ],
                [
                    'data' =>  'price',
                    'name'  =>  'h.price',
                    'label' =>  "Price",
                ],
                [
                    'data' =>  'address',
                    'name'  =>  'h.address',
                    'label' =>  "Address",
                ],
                [
                    'data' =>  'start_time',
                    'name'  =>  'h.start_time',
                    'label' =>  "Start Time",
                ],
                [
                    'data' =>  'end_time',
                    'name'  =>  'h.end_time',
                    'label' =>  "Total Hours",
                ],
                [
                    'data' =>  'payment',
                    'name'  =>  'g.name',
                    'label' => 'Payment Method',
                ],
                [
                    'data' =>  'status',
                    'name'  =>  'h.status',
                    'label' =>  "Status",
                ],
            ];
        }
        $columns_data   =   [];
        foreach ($columns as $column => $title){
            $columns_data[] = DataColumn::add_new($title,true);
        }
        $builder->pageLength(50);
        $html   =   $builder->columns($columns_data)
            ->dom('Bfrtip')
            ->buttons(
                Button::make('copy'),
                Button::make('excel'),
                Button::make('csv'),
                Button::make('pdf')
            );

        // return view('admin.requests.index',compact('html','action'));
        $isAdmin = Auth::user()->role->name === 'admin';
        $helpers = Helper::with(['user', 'helperOrder.order', 'gateway'])
            ->when(!$isAdmin, function ($q) { $q->where('user_id', Auth::id()); })
            ->orderByDesc('id')
            ->paginate(15);

        $totalHelpers    = Helper::when(!$isAdmin, fn($q) => $q->where('user_id', Auth::id()))->count();
        $pendingHelpers  = Helper::when(!$isAdmin, fn($q) => $q->where('user_id', Auth::id()))->where('status', 0)->count();
        $approvedHelpers = Helper::when(!$isAdmin, fn($q) => $q->where('user_id', Auth::id()))->where('status', 1)->count();
        $totalRevenue    = (float) Helper::when(!$isAdmin, fn($q) => $q->where('user_id', Auth::id()))->where('status', 1)->sum('price');

        return view('helper.index', compact(
            'html',
            'helpers',
            'isAdmin',
            'totalHelpers',
            'pendingHelpers',
            'approvedHelpers',
            'totalRevenue'
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $currentDate=date("h:i:s");
        $hours =getOptionsTimes($currentDate);
        $fee=HelperFee::first();
        return view('helper.create',compact('hours','fee'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreHelperRequest $request)
    {
        $helper = new Helper;
        $helper->user_id=Auth::user()->id;
        $helper->total_helper=$request->total_helper;
        $helper->start_time=$request->start." ".$request->time;
        $helper->end_time=$request->end;
        $helper->payment_method=$request->gateway;
        $helper->address=$request->address;
        $helper->price=$request->price;

        $helper->save();
        $helper_info = $request->price;
        $helpers = $request->total_helper;
        $h_hours = $request->end;
        $h_start_end_time=$request->start." ".$request->time;

        $fcm_token=User::where('id',Auth::user()->id)->first();
        $customer_fcm_token=$fcm_token;
        CreateHelper::dispatch($fcm_token,$helpers,$h_hours, $h_start_end_time, new NewHelper($fcm_token,$helper_info,$helpers,$h_hours,$h_start_end_time));
        FireBaseMessaging::send_notification(
            $fcm_token->fcm_web_token,
            "Creating New Helper",
            "Created a New Helper : ".$fcm_token->first_name." ".$fcm_token->last_name." Helper-Id: ".$helper->id." "
        );
        FireBaseMessaging::send_notification(
            $fcm_token->fcm_token,
            "Creating New Helper",
            "Created a New Helper : ".$fcm_token->first_name." ".$fcm_token->last_name." Helper-Id: ".$helper->id." "
        );

        Notification::create([
            'user_id'  =>  auth()->user()->id,
            'user_to_notify'  => $helper->user->id,
            'notifications_text'  =>"Created a New Helper : ".$helper->user->first_name." ".$helper->user->last_name." Helper-Id: ".$helper->id." ",
        ]); 

        $role_id = 1;
        $fcm_token = User::where('role_id',$role_id)->get();
        foreach ($fcm_token as $single) {
            CreateHelper::dispatch($single,$helpers,$h_hours,$h_start_end_time, new NewHelper($single,$helper_info,$helpers,$h_hours ,$h_start_end_time));
            FireBaseMessaging::send_notification(
                $single->fcm_web_token,
                "Creating New Helper",
                "Created a New Helper : ".$customer_fcm_token->first_name." ".$customer_fcm_token->last_name." Helper-Id: ".$helper->id." "
            );

        Notification::create([
            'user_id'  =>  auth()->user()->id,
            'user_to_notify'  => $single->id,
            'notifications_text'  =>"Created a New Helper : ".$helper->user->first_name." ".$helper->user->last_name." Helper-Id: ".$helper->id." ",
        ]); 
        }

    }
    public function storeAdmin(StoreAdminHelperRequest $request){
        $helper = new Helper;
        $helper->user_id=$request->customer_id;
        $helper->total_helper=$request->total_helper;
        $helper->end_time=$request->end;
        $helper->start_time=$request->start." ".$request->time;
        $helper->payment_method=$request->gateway;
        $helper->address=$request->address;
        $helper->price=$request->price;
        $helper->save();

        $helper_info = $request->price;
        $helpers = $request->total_helper;
        $h_hours = $request->end;

        $h_start_end_time=$request->start." ".$request->time;

        $fcm_token=User::where('id',$request->customer_id)->first();
        $customer_fcm_token=$fcm_token;

        if($fcm_token){
            CreateHelper::dispatch($fcm_token,$helpers,$h_hours,$h_start_end_time, new NewHelper($fcm_token,$helper_info,$helpers,$h_hours,$h_start_end_time));
            FireBaseMessaging::send_notification(
                $fcm_token->fcm_web_token,
                "Creating New Helper",
                "Created a New Helper : ".$fcm_token->first_name." ".$fcm_token->last_name." Helper-Id: ".$helper->id." "
            );
            FireBaseMessaging::send_notification(
                $fcm_token->fcm_token,
                "Creating New Helper",
                "Created a New Helper : ".$fcm_token->first_name." ".$fcm_token->last_name." Helper-Id: ".$helper->id." "
            );

        Notification::create([
            'user_id'  =>  auth()->user()->id,
            'user_to_notify'  => $request->customer_id,
            'notifications_text'  =>"Created a New Helper : ".$helper->user->first_name." ".$helper->user->last_name." Helper-Id: ".$helper->id." ",
        ]); 
        }


        $role_id = 1;
        $fcm_token = User::where('role_id',$role_id)->get();

        foreach ($fcm_token as $single) {
            if($single) 
                CreateHelper::dispatch($single,$helpers,$h_hours, $h_start_end_time,new NewHelper($single,$helper_info,$helpers,$h_hours,$h_start_end_time));
                FireBaseMessaging::send_notification(
                    $single->fcm_web_token,
                    "Creating New Helper",
                    "Created a New Helper : ".$customer_fcm_token->first_name." ".$customer_fcm_token->last_name." Helper-Id: ".$helper->id." "
                );

        Notification::create([
            'user_id'  =>  auth()->user()->id,
            'user_to_notify'  => $single->id,
            'notifications_text'  =>"Created a New Helper : ".$helper->user->first_name." ".$helper->user->last_name." Helper-Id: ".$helper->id." ",
        ]); 
            }            
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Helper  $helper
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        $fee =  HelperFee::first();
        return view('admin.helper.helper_fee' , compact('fee'));
    }

    public function storeFee(Request $request){
        $this->validate($request, [
            'fee'=>'required|numeric|min:0|gt:0',
        ],[
            'fee.required'=>'fee is required',
        ]);
        HelperFee::where('id',1)->update([
            'fee'=>$request->fee
        ]);
        return redirect()->back();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Helper  $helper
     * @return \Illuminate\Http\Response
     */
    public function edit(Helper $helper)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Helper  $helper
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Helper $helper)
    {
        
    }
    public function getTime(Request $request){
        if($request){
            if($request->date==date('Y-m-d',strtotime('+2 days'))){
                $hours =getOptionsTimes("h:i:s");
            }else{
                $hours =getOptionsTimes("00:00");

            }
            return $hours;
        }
    }
    public function change_status(Request $request ,$id){
        $helper= Helper::where('id',$id)->first();
        Helper::where('id',$id)->update([
            'status'=>1
        ]);
        if($helper){
            HelperConfirm::dispatch(
                $helper->user,
                $helper->price,
                $helper->total_helper,
                $helper->end_time,
                new HelperConfirmMail(
                    $helper->user,
                    $helper->price,
                    $helper->total_helper,
                    $helper->end_time,
                    $helper->start_time,
                    $helper->id
                ),
                $helper->start_time,
                $helper->id
            );

            FireBaseMessaging::send_notification(
                $helper->user->fcm_web_token,
                "Admin Confirm the Helper",
                "Admin Confirm the Helper : ".$helper->user->first_name." ".$helper->user->last_name." Helper-Id: ".$helper->id." "
            );
            FireBaseMessaging::send_notification(
                $helper->user->fcm_token,
                "Admin Confirm the Helper",
                "Admin Confirm the Helper : ".$helper->user->first_name." ".$helper->user->last_name." Helper-Id: ".$helper->id." "
            );

        Notification::create([
            'user_id'  =>  auth()->user()->id,
            'user_to_notify'  => $helper->user->id,
            'notifications_text'  =>"Confirm Helper : ".$helper->user->first_name." ".$helper->user->last_name." Helper-Id: ".$helper->id." ",
        ]); 
        }


        $role_id = 1;
        $fcm_token = User::where('role_id',$role_id)->get();

        foreach ($fcm_token as $single) {
            if($single) 
                HelperConfirm::dispatch(
                    $single,
                    $helper->price,
                    $helper->total_helper,
                    $helper->end_time,
                    $helper->id,
                    new HelperConfirmMail(
                        $single,
                        $helper->price,
                        $helper->total_helper,
                        $helper->end_time,
                        $helper->start_time,
                        $helper->id
                    ),
                    $helper->start_time
                );
                FireBaseMessaging::send_notification(
                    $single->fcm_web_token,
                    "You Updated Helper",
                    "You Update Helper : ".$helper->user->first_name." ".$helper->user->last_name." Helper-Id: ".$helper->id." "
                );

            Notification::create([
                'user_id'  =>  auth()->user()->id,
                'user_to_notify'  => $single->id,
                'notifications_text'  =>"Confirm Helper : ".$helper->user->first_name." ".$helper->user->last_name." Helper-Id: ".$helper->id." ",
            ]); 
            }            

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Helper  $helper
     * @return \Illuminate\Http\Response
     */
    public function destroy(Helper $helper)
    {
        //
    }
}
