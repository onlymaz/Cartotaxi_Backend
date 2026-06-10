<?php

namespace App\Console\Commands;

use App\Models\AvoidDuplicate;
use App\Mail\NegativeFeedback;
use App\Models\Order;
use App\Models\ReviewRating;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class RiderReviewConfirmation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rider:reviews';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify Admin for Negative Review by customer';

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
        $start_week     =   Carbon::parse(date('Y-m-d'))->startOfWeek()->format('Y-m-d'); /* current week start date */
        $end_of_week    =   Carbon::parse(date('Y-m-d'))->endOfWeek()->format('Y-m-d');/* current week end date */
        $reviews = Order::with(['rider', 'reviewRatings'])
            ->where('order_status', 'completed')
            ->whereHas('reviewRatings', function ($query) use ($start_week, $end_of_week) {
                $query->where('rating', 1)
                    ->whereBetween('created_at', [$start_week, $end_of_week]);
            })
            ->get()
            ->map(function ($order) {
                $order->rating_count = $order->reviewRatings->where('rating', 1)->count();
                return $order;
            })
            ->filter(function ($order) {
                return $order->rating_count > 2;
            });


        foreach ($reviews as $review){
            $avoid_duplicate    =   AvoidDuplicate::where('rider_id',$review->rider_id)
                /*->where('order_id',$review->order_id)*/
                ->where('rating_count',$review->rating_count)
                ->where('start_week',$start_week)
                ->where('end_week',$end_of_week)
                ->first();
            if (!$avoid_duplicate){
                AvoidDuplicate::create( [
                    'order_id'      =>  $review->order_id,
                    'rider_id'      =>  $review->rider_id,
                    'rating_count'  =>  $review->rating_count,
                    'start_week'    =>  $start_week,
                    'end_week'      =>  $end_of_week
                ]);
                Mail::send(new NegativeFeedback($review));
                $this->info('Mail sent');
            }
            else
            {
                $this->info($review->order_id." ");
            }
        }
    }
}
