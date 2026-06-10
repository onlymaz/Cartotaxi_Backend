<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\OrderSubTrip;
use App\Models\OrderDropOff;
use Illuminate\Console\Command;

class OrderDropOffStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:order_drop_off_status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Order Drop off';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $orders     =   Order::where('drop_off',0)->orderBy('id','DESC')
            ->paginate(20);
        foreach ($orders as $order){
            $order_id       =   $order->id;
            $order_trips    =   OrderSubTrip::where('order_id',$order_id)->get();
            $total_count    =   count($order_trips);
            $data           =   [];
            foreach ($order_trips as $key => $trip){
                $location       =   $trip->start_location;
                if ((($total_count-1)-$key) ===0){
                    $location   =   $trip->end_location;
                    $data         =   [
                        'order_id'              =>  $order_id,
                        'location'              =>  $trip->start_location,
                        'is_end_drop_off'       =>  0,
                        'status'                =>  'pending',
                        'lat'                   =>  $trip->start_lat,
                        'lng'                   =>  $trip->start_long,
                    ];
                    OrderDropOff::create($data);
                    $data         =   [
                        'order_id'              =>  $order_id,
                        'location'              =>  $location,
                        'is_end_drop_off'       =>  1,
                        'status'                =>  'pending',
                        'lat'                   =>  $trip->end_lat,
                        'lng'                   =>  $trip->end_long,
                    ];
                    OrderDropOff::create($data);
                }
                else{
                    $data        =   [
                        'order_id'              =>  $order_id,
                        'location'              =>  $location,
                        'is_end_drop_off'       =>  0,
                        'status'                =>  'pending',
                        'lat'                   =>  $trip->start_lat,
                        'lng'                   =>  $trip->start_long,
                    ];
                    OrderDropOff::create($data);
                }
            }
            $order->update([
                'drop_off'      =>  1
            ]);
            $this->info('done : '.$order_id);
        }
    }
}
