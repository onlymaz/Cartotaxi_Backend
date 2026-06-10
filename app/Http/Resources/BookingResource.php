<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Order;
use App\Models\OrderSubTrip;
use Carbon\Carbon;

class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $user = auth()->user();
        $is_past = $this->picked_time < Carbon::today() || in_array($this->order_status, ['delivered', 'cancel', 'not_received', 'accident', 'refused']);
        
        $name = '';
        if ($user->role_id == 3) {
            $name = optional($this->rider)->first_name . ' ' . optional($this->rider)->last_name;
        } else {
            $name = optional($this->user)->first_name . ' ' . optional($this->user)->last_name;
        }

        return [
            'order_id'       => $this->id,
            'name'           => $name,
            'rider_id'       => $this->rider_id ?? 0,
            'total_distance' => Order::total_km($this->id),
            'total_cost'     => $this->total_amount ?? 0,
            'booking_id'     => $this->booking_id ?? '',
            'helper_id'      => optional($this->helperOrder->first())->helper_id ?? '',
            'package_name'   => optional($this->package)->name ?? '',
            'order_status'   => $this->order_status ?? '',
            'start_location' => $this->start_location ?? '',
            'picked_time'    => $this->picked_time ?? '',
            'ride_start'     => $this->start_time ? utc_time(strtotime($this->start_time)) : '',
            'end_ride'       => $this->end_time ? utc_time(strtotime($this->end_time)) : '',
            'is_past'        => $is_past,
            'phone_number'   => optional($this->user)->phone_number ?? '',
            'payment_status' => optional($this->payment)->status ?? '',
            'gateway_name'   => optional(optional($this->payment)->gateway)->name ?? '',
            'customer_id'    => $this->customer_id ?? '',
            'map_image'      => $this->map_image ?? '',
            'location'       => OrderSubTrip::locationObject($this->id),
        ];
    }
}
