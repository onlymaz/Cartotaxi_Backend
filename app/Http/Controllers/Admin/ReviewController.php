<?php

namespace App\Http\Controllers\Admin;

use App\Datatables\DataColumn;
use App\Datatables\Datatable;
use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\CityDistrict;
use App\Models\Country;
use App\Models\Review;
use App\Models\ReviewRating;
use App\Models\ReviewType;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Html\Builder;
use Yajra\DataTables\Html\Button;
use App\Models\Order;


class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request,Builder $builder)
    {
        if ($request->ajax()){
            $custom_columns =   [
                'Rating'    =>  'admin.reviews.rating',
                'rider_name' => 'admin.reviews.rider_name'
            ];
            $users  =   Review::from(get_table_name(Review::class).' as r')
                ->join(get_table_name(ReviewRating::class).' as rr','rr.review_id','=','r.id')
                ->join(get_table_name(ReviewType::class).' as rt','rt.id','=','rr.review_type_id')
                ->join(get_table_name(Order::class).' as o','o.id','=','rr.order_id')
                ->join(get_table_name(User::class).' as u','u.id','o.customer_id')
                ->join(get_table_name(User::class).' as rider','rider.id','=','o.rider_id')
                ->select('r.id','rr.rating','rr.comments','r.created_at','u.first_name','o.id as order_id',
                    'rider.first_name as rider_name',
                    'rider.last_name',
                    'o.start_time as order_start_time',
                    'o.end_time as order_end_time'

                )
                ->where('r.types','customer')->orderBy('r.id','DESC');
            return Datatable::create($users,$custom_columns);
        }
        $columns         =   [
            [
                'data' =>  'id',
                'name'  =>  'r.id',
                'label' =>  trans('messages.request_id'),
            ],
            [
                'data' =>  'order_id',
                'name'  =>  'o.id',
                'label' =>  "Order ID",
            ],
            
            [
                'data' =>  'rider_name',
                'name'  =>  'rider_name',
                'label' =>  "Rider Name",
            ],
            [
                'data' =>  'order_start_time',
                'name'  =>  'o.start_time',
                'label' =>  "Start Time",
            ],
            [
                'data' =>  'order_end_time',
                'name'  =>  'o.order_end_time',
                'label' =>  "End Time",
            ],

            [
                'data' =>  'first_name',
                'name'  =>  'u.first_name',
                'label' =>  trans('messages.user_name'),
            ],
            [
                'data' =>  'Rating',
                'name'  =>  'Rating',
                'label' =>  trans('messages.rating'),
            ],
            [
                'data' =>  'created_at',
                'name'  =>  'r.created_at',
                'label' =>  trans('messages.date_and_time'),
            ],
            [
                'data' =>  'comments',
                'name'  =>  'rr.comments',
                'label' =>  'Remark',
            ]

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

        $title   = 'User Reviews';
        $reviews = \App\Models\Review::with(['user', 'reviewRatings.reviewType', 'reviewRatings.order.rider'])
            ->where('types', 'customer')->orderByDesc('id')->paginate(20);
        return view('admin.reviews.index', compact('html', 'title', 'reviews'));
    }


    public function riders(Request $request, Builder $builder)
    {
        if ($request->ajax()) {
            $custom_columns = [
                'Rating' => 'admin.reviews.rating',
            ];
            $reviews = Review::from(get_table_name(Review::class) . ' as r')
                ->join(get_table_name(User::class) . ' as rider', 'rider.id', '=', 'r.user_id')
                ->join(get_table_name(ReviewRating::class) . ' as rr', 'rr.review_id', '=', 'r.id')
                ->join(get_table_name(ReviewType::class) . ' as rt', 'rt.id', '=', 'rr.review_type_id')
                ->join(get_table_name(Order::class) . ' as o', 'o.id', '=', 'rr.order_id')
                ->join(get_table_name(User::class) . ' as customer', 'customer.id', '=', 'o.customer_id')
                ->select(
                    'r.id',
                    'rr.order_id',
                    'rr.rating',
                    'rr.comments',
                    'r.created_at',
                    'rider.first_name as rider_first_name',
                    'rider.last_name as rider_last_name',
                    'customer.first_name as customer_first_name',
                    'customer.last_name as customer_last_name',
                    'o.start_time as order_start_time',
                    'o.end_time as order_end_time'
                )
                ->where('r.types', 'rider')
                ->orderBy('r.id', 'DESC');

            return Datatable::create($reviews, $custom_columns);
        }

        $columns = [
            ['data' => 'id',                   'name' => 'r.id',                   'label' => trans('messages.request_id')],
            ['data' => 'order_id',             'name' => 'rr.order_id',            'label' => 'Order ID'],
            ['data' => 'rider_first_name',     'name' => 'rider.first_name',       'label' => 'Rider Name'],
            ['data' => 'customer_first_name',  'name' => 'customer.first_name',    'label' => 'Customer Name'],
            ['data' => 'Rating',               'name' => 'Rating',                 'label' => trans('messages.rating')],
            ['data' => 'order_start_time',     'name' => 'o.start_time',           'label' => 'Order Start'],
            ['data' => 'order_end_time',       'name' => 'o.end_time',             'label' => 'Order End'],
            ['data' => 'comments',             'name' => 'rr.comments',            'label' => trans('messages.comments')],
            ['data' => 'created_at',           'name' => 'r.created_at',           'label' => trans('messages.date_and_time')],
        ];

        $columns_data = [];
        foreach ($columns as $column) {
            $columns_data[] = DataColumn::add_new($column, true);
        }

        $builder->pageLength(50);
        $html = $builder->columns($columns_data)
            ->dom('Bfrtip')
            ->buttons(
                Button::make('copy'),
                Button::make('excel'),
                Button::make('csv'),
                Button::make('pdf')
            );

        $title   = 'Rider Reviews';
        $reviews = \App\Models\Review::with(['user', 'reviewRatings.reviewType', 'reviewRatings.order.user'])
            ->where('types', 'rider')->orderByDesc('id')->paginate(20);
        return view('admin.reviews.index', compact('html', 'title', 'reviews'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
