<?php

namespace App\Http\Controllers\Rider;

use App\Datatables\DataColumn;
use App\Datatables\Datatable;
use App\Http\Controllers\Controller;
use App\Models\Gateway;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Html\Builder;
use Yajra\DataTables\Html\Button;

class DashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        $user_id = auth()->user()->id;
        
        // Build base query
        $orderQuery = Order::where('rider_id', $user_id);
        
        // Apply date filters if provided
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();
            $orderQuery->whereBetween('created_at', [$start, $end]);
        }
        
        // Get counts with date filters
        $stats['order_processing']   = (clone $orderQuery)->where('order_status','processing')->count();
        $stats['order_pending']      = (clone $orderQuery)->where('order_status','pending')->count();
        $stats['order_picking']       = (clone $orderQuery)->where('order_status','picking')->count();
        $stats['order_picked_up']    = (clone $orderQuery)->where('order_status','picked_up')->count();
        $stats['order_on_way']       = (clone $orderQuery)->where('order_status','on_way')->count();
        $stats['order_delivered']    = (clone $orderQuery)->where('order_status','delivered')->count();
        $stats['order_cancel']       = (clone $orderQuery)->where('order_status','cancel')->count();
        $stats['refused']            = (clone $orderQuery)->where('order_status','refused')->count();
        
        return view('rider.nova-dashboard',compact('stats'));
    }

    public function bookings(Request $request,Builder $builder)
    {
        if (!empty($request->action)){
            $action =   $request->action;
        }else{
            $action =   'pickup';
        }
        if ($request->ajax()){
            $custom_columns =   [
                'Action'    =>  'admin.requests.actions',
                'DateTime'    =>  'admin.requests.date-time',
                'Status'    =>  'admin.requests.status',
            ];
            $users  =   Order::from(get_table_name(Order::class).' as o')
                ->join(get_table_name(User::class).' as u','u.id','o.customer_id')
                ->select('o.id','o.booking_id','o.rider_id','o.order_status','o.start_location','o.end_location','o.total_amount','o.picked_time','u.first_name')
                ->where('o.rider_id',auth()->user()->id);

            if ($action=='pickup'){
                    // $users  =   $users->whereDate('o.updated_at', Carbon::today());
                    $users  =   $users->whereNotIn('o.order_status', ["delivered",'refused','cancel','accident','not_received'] );
            }elseif($action=='schedule'){
                $users  =   $users->whereDate('o.picked_time','>', Carbon::today());
            }elseif ($action=='history'){
                    $users  =   $users->whereIn('o.order_status',["delivered",'refused','cancel','accident','not_received']);
                // $users  =   $users->whereDate('o.picked_time','<', Carbon::today());
            }

            $users->orderBy('o.updated_at','desc')->get();
            
            return Datatable::create($users,$custom_columns);
        }
        $columns         =   [

            [
                'data' =>  'booking_id',
                'name'  =>  'o.booking_id',
                'label' =>  'Booking ID'
            ],
            [
                 'data' =>  'id',
                'name'  =>  'o.id',
                'label' =>  'Order Id',
            ],
            [
                'data' =>  'first_name',
                'name'  =>  'u.first_name',
                'label' =>  'Customer Name'
            ],
            [
                'data' =>  'DateTime',
                'name'  =>  'DateTime',
                'label' =>  'Date & Time'
            ],
            [
                'data' =>  'Status',
                'name'  =>  'Status',
                'label' =>  'Booking Status'
            ],
            [
                'data' =>  'Action',
                'name'  =>  'Action',
                'label' =>  'Action'
            ],
        ];
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

        return view('rider.index',compact('html','action'));
    }

}
