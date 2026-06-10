<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DispatcherRequest;
use App\Http\Requests\DispatcherRequset;
use Illuminate\Support\Facades\Storage;
use App\Utilities\FireBaseMessaging;
use App\Models\CityDistrict;
use App\Models\OrderSubTrip;
use Illuminate\Http\Request;
use App\Models\OrderStatus;
use App\Models\Gateway;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Order;
use Carbon\Carbon;
use App\Models\User;
use Geocoder;
use Str;
use App\Mail\StoreBooking;
use App\Jobs\CreateBooking;
use App\Models\Helper;
use App\Models\HelperOrder;
use App\Models\HelperFee;
use App\Models\Notification;
use Auth;




class DispatcherController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $type   =   '';
        if ($request->action){
            $type   =   $request->action;
        }
        $url    =   route('dispatcher.create');
        $orders = Order::from(get_table_name(Order::class).' as o')
            ->join(get_table_name(User::class).' as u','u.id','o.customer_id')
            ->leftJoin(get_table_name(User::class).' as r','r.id','o.rider_id')
            ->where('o.rider_id',0)->select(
                'o.id',
                'o.rider_id',
                'o.start_location',
                'o.end_location',
                'o.total_amount',
                'o.picked_time',
                'u.first_name',
                'u.last_name',
                'r.first_name as rider_name',
                'o.map_image as map_image',
                'o.total_meter'
             )
            ->groupBy('o.id')
            ->orderBy('o.id','desc')
            ->paginate(5);
        return view('admin.dispatcher.index',compact('orders','type','url'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $orders = Order::from(get_table_name(Order::class).' as o')
            ->join(get_table_name(User::class).' as u','u.id','o.customer_id')
            ->leftJoin(get_table_name(User::class).' as r','r.id','o.rider_id')
            ->leftJoin(get_table_name(HelperOrder::class).' as hp','o.id','hp.order_id')
            ->select('o.map_image as map_image','o.id','o.start_location','o.end_location','o.total_amount','o.picked_time','u.first_name','u.last_name','r.first_name as rider_name','o.total_meter','o.order_status')
        ->groupBy('o.id');
        if ($request->ajax()){

            if (!empty($request['action'])){
                $view_data =    'search';
                if ($request['action'] == 'search'){
                    $view_data  =   'search';
                    $orders =   $orders->where('o.rider_id',0)->orderBy('o.id','desc');
                    $orders =   $orders->paginate(5);
                    return view('admin.dispatcher.'.$view_data,compact('orders'));
                }
                if ($request['action'] == 'assigned'){
                    $view_data  =   'assigned';
                        if(isset($request->orderFilterType) && $request->orderFilterType!='all'){
                            $orders =   $orders->where('o.order_status',$request->orderFilterType);
                        }
                        if(isset($request->statusFilter) && $request->statusFilter!='all'){
                            if($request->statusFilter=='withouthelper'){
                                $orders =   $orders->whereNull('hp.order_id');
                            }elseif($request->statusFilter=='withhelper'){
                                $orders =   $orders->whereNotNull('hp.order_id');
                            }
                        }
                        $orders =   $orders->where('o.rider_id','!=',0)->orderBy('o.id','desc');
                        $orders =   $orders->paginate(8);
                    return view('admin.dispatcher.'.$view_data,compact('orders'));
                }
                if ($request['action'] == 'cancel'){
                    $view_data  =   'cancelled';
                    $orders =   $orders->where('o.order_status','cancel')->orderBy('o.id','desc');
                    $orders =   $orders->paginate(8);
                    return view('admin.dispatcher.'.$view_data,compact('orders'));
                }
                if ($request['action'] == 'add'){
                    $view_data  =   'create';
                    $price      =   env('PACKAGE_PRICE');
                    $currency   =   config('app.currency_symbol');
                    $districts  =   CityDistrict::select('id','polygons')
                        ->where('polygons','<>','')
                        ->where('IsActive',1)
                        ->get();
                    $polygons = [];
                    foreach($districts as $row){
                        $polygons[$row->id] = $row->polygons;
                    }
                    $packages   = Package::select('id','name','per_km_charges')->get();
                    $fee=HelperFee::first();
                    return view('admin.dispatcher.'.$view_data,compact('price','currency','polygons','packages','fee'));
                }
            }
            $orders =   $orders->paginate(3);
            return redirect()->route('dispatcher.index',compact('orders'));
        }
        $orders =   $orders->paginate(3);
        return redirect()->route('dispatcher.index',compact('orders'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(DispatcherRequest $request)
    {
        // $image_64 = $request->map_data_url; //your base64 encoded data
        // $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf
        // $replace = substr($image_64, 0, strpos($image_64, ',')+1); 
        // $image = str_replace($replace, '', $image_64); 
        // $image = str_replace(' ', '+', $image); 
        // $imageName = Str::random(10).'.'.$extension;
        // \File::put(public_path(). '/uploads/media/' . $imageName, base64_decode($image));
        $data   =   [
            'booking_id'        =>  Order::CreateRandomBookingID(),
            'customer_id'       =>  $request['customer_id'],
            'package_id'        =>  $request['select_package'],
            'rider_id'          =>  0,
            'start_district_id' =>  $request['start_address_district_id'],
            'end_district_id'   =>  $request['end_address_district_id'],
            'name'              =>  '',
            'description'       =>  $request['description'],
            'start_location'    =>  $request['mid'][0],
            'end_location'      =>  $request['end_location'],
            'picked_time'       =>  (
                                        ($request['helper']=="With") ? 
                                        $request->start." ".$request->time : 
                                        $request['picked_date']." ".$request['picked_time']
                                    ),
            'fixed_price'       =>  $request['unit_price'],
            'per_km_charges'    =>  $request['per_km_charges'],
            'total_amount'      =>  $request['total_amount'],
            'total_meter'       =>  $request['total_km_meter'],
            'total_second'      =>  $request['total_time_sec'],
            'map_image'         =>  $request['encodeString'],//url("/uploads/media/".$imageName),
            'order_status'      =>  'pending',
            /*'start_time'        =>  date('Y-m-d H:i:s')*/
        ];
        $order = Order::create($data);
        if($request['mid']){
            for($i=0;$i<=Count($request['mid'])-1;$i++){
                if(Count($request['mid']) ==1){
                    $endLocation=$request['end_location'];
                    $end_district_id=$request['end_address_district_id'];
                }elseif(Count($request['mid'])-1 == $i){
                    $endLocation=$request['end_location'];
                    $end_district_id=$request['end_address_district_id'];
                }else{
                    $endLocation=$request['mid'][$i+1];
                    $end_district_id=$request['district_ids'][$i+1];
                }
                $OrderSubTripData=[
                    "order_id"              =>  $order->id,
                    'start_district_id'     =>  $request['district_ids'][$i],
                    'end_district_id'       =>  $end_district_id,
                    "start_location"        =>  $request['mid'][$i],
                    "end_location"          =>  $endLocation,
                    'start_lat'             =>  $this->findLocation($request['mid'][$i])['lat'],
                    'start_long'            =>  $this->findLocation($request['mid'][$i])['lng'],
                    'end_lat'               =>  $this->findLocation($endLocation)['lat'],
                    'end_long'              =>  $this->findLocation($endLocation)['lng'],
                    "total_amount"          =>  $request['total_amount_fee'],
                    "total_meter"           =>  $request['total_km_meter'],
                    "total_second"          =>  $request['total_time_sec'],
                    'created_at'            =>  date('Y-m-d H:i:s',strtotime($request->picked_time)),
                    'updated_at'            =>  date('Y-m-d H:i:s',strtotime($request->picked_time)),
                ];  
                OrderSubTrip::insert($OrderSubTripData);
            }
        }


        $data   =   [
            'order_id'  =>  $order->id,
            'order_status'  =>  'pending',
            'comments'  =>  '',
        ];
        $order_status   =   OrderStatus::create($data);
        $data       =   [
            'order_id'      =>  $order->id,
            'customer_id'   =>  $request['customer_id'],
            'gateway_id'    =>  1,
            'amount'        =>  $request['total_amount'],
            'transactions'  =>  '',
            'response'      =>  '',
            'status'        =>  'pending'
        ];
        Payment::create($data);

        if($request['helper']=='With'){        
            $helper= new Helper;
            $helper->user_id=$request['customer_id'];
            $helper->total_helper=$request['total_helper'];
            $helper->payment_method=1;
            $helper->price=$request['helperPayment'];
            $helper->start_time= $request['picked_date']." ".$request['picked_time'];
            $helper->end_time=$request['end'];
            $helper->address=$request['end_location'];
            $helper->save();

            $data = [
                'order_id'=>$order->id,
                'helper_id'=>$helper->id
            ];
            HelperOrder::insert($data);

        }




        //Admin Notification
        $customer_name=User::where('id',$request['customer_id'])->first();
        $admins=User::where('role_id','1')->get();
        foreach ($admins as $admin) {
            FireBaseMessaging::send_notification(
                $admin->fcm_web_token,
                "Customer Booking",
                "Customer Create a new Booking-Name: ".$customer_name->first_name." ".$customer_name->last_name." Booking-Id: ".$order->id." "
            );
            CreateBooking::dispatch($admin, new StoreBooking($admin,$order->id));

        Notification::create([
            'user_id'  =>  auth()->user()->id,
            'user_to_notify'  => $admin->id,
            'notifications_text'  =>  "Customer Create a new Booking-Name: ".$customer_name->first_name." ".$customer_name->last_name." Booking-Id: ".$order->id." ",
        ]); 

        }
            CreateBooking::dispatch($customer_name, new StoreBooking($customer_name,$order->id));

        Notification::create([
            'user_id'  =>  auth()->user()->id,
            'user_to_notify'  => $request['customer_id'],
            'notifications_text'  =>  "Your Order has been Placed Booking-Id: ".$order->id
        ]);   
        return response()->json([
            'success'       => true,
            'messages'      => "Order Created Successfully",
            'data'          => $order
        ]);
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
    public function getfcm_token(Request $request){
        User::where('id',$request->user_id)->update(
            [
                'fcm_web_token'=> $request->fcm_token
            ]
        );
        return json_encode(array('message' => 'Fcm Web Token Updated', 'status' => '200'));
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

    /**
     * Server-side Nominatim geocoding proxy — avoids browser CORS/rate-limit issues.
     * Returns JSON: {"lat": float, "lng": float} or {"error": "..."}
     */
    public function geocode(Request $request)
    {
        $address = trim($request->input('address', ''));
        if (!$address) {
            return response()->json(['error' => 'No address provided'], 400);
        }

        $url = 'https://nominatim.openstreetmap.org/search?'
             . http_build_query([
                 'q'              => $address,
                 'format'         => 'json',
                 'limit'          => 1,
                 'addressdetails' => 0,
             ]);

        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'User-Agent'      => 'CargoTaxiDispatcher/1.0 (admin@cargotaxi.at)',
                'Accept-Language' => 'en',
            ])->timeout(8)->get($url);

            $data = $response->json();

            if (!empty($data[0])) {
                return response()->json([
                    'lat' => (float) $data[0]['lat'],
                    'lng' => (float) $data[0]['lon'],
                ]);
            }

            return response()->json(['error' => 'Not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getCustomers(Request $request){
        $term = $request['term'];
        $users = User::where('role_id',3)
            ->where('first_name','LIKE',$term."%")
            /*->where('last_name','LIKE',$term."%")*/
            ->get();
        $items =    [];
        foreach($users as $row){
            $items[] =   [
                'id'    => $row->id,
                'label' => $row->first_name." ".$row->last_name,
                'value' => $row->first_name." ".$row->last_name
            ];
        }
        return response()->json($items);
    }
    public function SelectPaymentGateway(Request $request){
        // $order_id   =   $request['order_id'];
        $gateways   =   Gateway::where('isActive', 1)->get();
        // $order      =   Order::find($order_id);
        return view('admin.dispatcher.gateways',compact('gateways'));
    }

    public function CreatePaymentTransaction(Request $request){

        $order_id           =   $request['order_id'];
        $transaction_id     =   $request['transaction_id'];
        $response           =   $request['response'];

        $data       =   [
            'gateway_id'    =>  $request['gateway_id'],
            'transactions'  =>  $transaction_id,
            'response'      =>  $response,
            'status'        =>  (($request['gateway_id'] ==1)?'pending':'completed')
        ];
        $payment    =   Payment::where('order_id', $order_id)->first();
        $payment->update($data);
        $payment    =   Payment::where('order_id', $order_id)->first();
        $order      =   Order::where('id', $order_id)->first();
        $order->update([
            'order_status'  => 'processing'
        ]);
        return view('customer.payment_success',compact('payment','order'));
    }
    public function findLocation($location)
    {
        // Hardcoded Google Maps key was a P0 secret-in-source-code leak.
        // Now reads from config (which reads from env), so the key rotates
        // with deployment and cannot be exfiltrated by code-only repo access.
        \Geocoder::setApiKey(config('services.google.maps_api_key'));
        return \Geocoder::getCoordinatesForAddress($location);
    }

    public function riderDetail(Request $req)
    {
        $RiderDetail = Order::with('rider','user')->where('id',$req->id)->first();
        return response()->json($RiderDetail);
    }

    private function exportQuery(string $type)
    {
        $query = Order::from(get_table_name(Order::class).' as o')
            ->join(get_table_name(User::class).' as u','u.id','o.customer_id')
            ->leftJoin(get_table_name(User::class).' as r','r.id','o.rider_id')
            ->select(
                'o.id',
                'o.rider_id',
                'o.start_location',
                'o.end_location',
                'o.total_amount',
                'o.total_meter',
                'o.picked_time',
                'o.order_status',
                'o.created_at',
                'u.first_name',
                'u.last_name',
                'r.first_name as rider_first_name',
                'r.last_name as rider_last_name'
            )
            ->groupBy('o.id')
            ->orderBy('o.id','desc');

        if ($type === 'assigned') {
            return $query->where('o.rider_id','!=',0)->where('o.order_status','!=','cancel');
        }
        if ($type === 'cancel' || $type === 'cancelled') {
            return $query->where('o.order_status','cancel');
        }
        return $query->where('o.rider_id',0);
    }

    private function tabLabel(string $type): string
    {
        return [
            'assigned'  => 'Assigned Orders',
            'cancel'    => 'Cancelled Orders',
            'cancelled' => 'Cancelled Orders',
            'search'    => 'Unassigned Orders',
        ][$type] ?? 'Dispatcher Orders';
    }

    public function exportCsv($type)
    {
        $type = in_array($type, ['search','assigned','cancel','cancelled']) ? $type : 'search';
        $orders = $this->exportQuery($type)->get();

        $filename = 'dispatcher-' . ($type === 'cancel' ? 'cancelled' : $type) . '-' . date('Ymd-His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma'              => 'no-cache',
            'Expires'             => '0',
        ];

        $callback = function () use ($orders) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, [
                'Order ID','Customer','Rider','Pickup','Drop-off',
                'Amount','Distance (km)','Status','Picked Time','Created At'
            ]);
            foreach ($orders as $o) {
                $customer = trim(($o->first_name ?? '') . ' ' . ($o->last_name ?? ''));
                $rider    = trim(($o->rider_first_name ?? '') . ' ' . ($o->rider_last_name ?? ''));
                fputcsv($out, [
                    '#' . $o->id,
                    $customer,
                    $rider !== '' ? $rider : '—',
                    $o->start_location,
                    $o->end_location,
                    number_format((float) $o->total_amount, 2, '.', ''),
                    number_format(((float) $o->total_meter) / 1000, 2, '.', ''),
                    ucfirst(str_replace('_',' ', $o->order_status ?? '')),
                    $o->picked_time,
                    (string) $o->created_at,
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf($type)
    {
        $type = in_array($type, ['search','assigned','cancel','cancelled']) ? $type : 'search';
        $orders = $this->exportQuery($type)->get();
        $title  = $this->tabLabel($type);
        $generatedAt = now()->format('M d, Y H:i');

        return view('admin.dispatcher.export_print', compact('orders','title','generatedAt','type'));
    }
}
