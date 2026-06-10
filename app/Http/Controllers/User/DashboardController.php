<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\CityDistrict;
use App\Models\Order;
use App\Models\Package;
use App\Models\HelperOrder;
use App\Models\Helper;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Gateway;
use App\Models\ReviewRating;
use App\Models\Payment;
use App\Models\Review;
use App\Models\OrderSubTrip;
use App\Http\Resources\BookingResource;
use Response;
use Lang;
use Session;
class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function dashboard(Request $request){    
        /*'pending','processing','picking','picked_up','on_way','accident','not_received','refused','delivered','cancel'*/
        $user_id = auth()->user()->id;
        
        // Build base query
        $orderQuery = Order::where('customer_id', $user_id);
        
        // Apply date filters if provided
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();
            $orderQuery->whereBetween('created_at', [$start, $end]);
        }
        
        // Get counts with date filters
        $order_processing   = (clone $orderQuery)->where('order_status','processing')->count();
        $order_pending      = (clone $orderQuery)->where('order_status','pending')->count();
        $order_picking      = (clone $orderQuery)->where('order_status','picking')->count();
        $order_picked_up    = (clone $orderQuery)->where('order_status','picked_up')->count();
        $order_on_way       = (clone $orderQuery)->where('order_status','on_way')->count();
        $order_delivered    = (clone $orderQuery)->where('order_status','delivered')->count();
        $order_cancel       = (clone $orderQuery)->where('order_status','cancel')->count();
        $order_refused      = (clone $orderQuery)->where('order_status','refused')->count();

        return view('customer.nova-dashboard',
            compact('order_cancel','order_delivered',
                'order_on_way','order_picked_up','order_picking','order_pending','order_processing','order_refused'
            ));
    }
    public function index()
    {
        $view_data  =   'create';
        $price      =   0;
        $currency   =   env('CURRENCY');
        $districts  =   CityDistrict::select('id','polygons')
            ->where('polygons','<>','')
            ->where('IsActive',1)
            ->get();
        $polygons = [];
        foreach($districts as $row){
            $polygons[$row->id] = $row->polygons;
        }
        $packages   = Package::select('id','name','per_km_charges')->get();
        return view('customer.booking',compact('price','currency','polygons','packages'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function MyBookings(Request $request)
    {
        $user = auth()->user();

        $bookings = Order::with(['package', 'user', 'payment.gateway', 'rider', 'helperOrder'])
            ->where(function ($query) use ($user) {
                if ($user->role_id == 3) {
                    $query->where('customer_id', $user->id);
                } else {
                    $query->where('rider_id', $user->id);
                }
            })
            ->orderBy('updated_at', 'DESC')
            ->paginate(4);

        return view('customer.mybookings', [
            'bookings' => BookingResource::collection($bookings),
            'data' => [
                'bookings' => [
                    'links' => $this->getPaginationLinks($bookings),
                    'list' => BookingResource::collection($bookings),
                ]
            ]
        ]);
    }

    private function getPaginationLinks($paginator)
    {
        return [
            'current_page' => $paginator->currentPage(),
            'first_page_url' => $paginator->url(1),
            'from' => $paginator->firstItem(),
            'last_page' => $paginator->lastPage(),
            'last_page_url' => $paginator->url($paginator->lastPage()),
            'next_page_url' => $paginator->nextPageUrl(),
            'per_page' => $paginator->perPage(),
            'prev_page_url' => $paginator->previousPageUrl(),
            'to' => $paginator->lastItem(),
            'total' => $paginator->total(),
        ];
    }
    
    public function create()
    {
        //
    }
    public function ajaxGetPackagePrice(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:packages,id'
        ]);

        $package = Package::where('id',$request->id)->first('fixed_price');
        return Response::json( ['price' => $package->fixed_price] );
    }
    public function HelperInfo(Request $req)
    {
        $req->validate([
            'id' => 'required|integer|exists:helpers,id'
        ]);
        $HelperInfo = Helper::where('id',$req->id)->get();
        return Response::json($HelperInfo);
    }
    
    public function RiderDetail(Request $req)
    {
        $req->validate([
            'id' => 'required|integer|exists:orders,id'
        ]);
        $RiderDetail = Order::with('rider','user')->where('id',$req->id)->where('customer_id', auth()->id())->first();
        if(!$RiderDetail) {
            return Response::json(['error' => 'Not authorized.'], 403);
        }
        return Response::json($RiderDetail);
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

    public function RatingRider(Request $request)
    {
        $request->validate([
            'oid' => 'required|integer|exists:orders,id',
            'rating' => 'required|integer|min:1|max:5',
            'comments' => 'nullable|string|max:255',
        ]);

        $order = Order::where('id', $request->oid)->where('customer_id', auth()->id())->first();

        if(!$order) {
            return response()->json(['error' => 'Not authorized.'], 403);
        }

        $reviewid = Review::create([
            'user_id'  =>  auth()->user()->id,
            'order_id'  => $request->oid,
            'types'  =>"Customer",
        ]);  
        ReviewRating::create([
            'user_id'  =>  auth()->user()->id,
            'order_id'  => $request->oid,
            'review_id' => $reviewid->id,
            'review_type_id' => 2,
            'rating' => $request->rating,
            'comments'  =>$request->comments,
        ]); 
        return true;

    }
}
