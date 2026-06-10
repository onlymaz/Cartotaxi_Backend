<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderAssign extends Model
{
    /**
     * Dispatch offer lifecycle:
     *  pending   — offered to the rider, waiting for an answer
     *  assign    — rider accepted the ride
     *  rejected  — rider declined
     *  expired   — rider didn't answer within the offer timeout
     *  cancelled — offer voided (e.g. admin assigned the ride manually)
     *  deleted   — legacy name for expired offers (pre-audit-log rows)
     */
    const STATUS_PENDING   = 'pending';
    const STATUS_ACCEPTED  = 'assign';
    const STATUS_REJECTED  = 'rejected';
    const STATUS_EXPIRED   = 'expired';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'order_id', 'rider_id', 'attempt', 'distance_km',
        'assign_status', 'responded_at', 'note',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
        'distance_km'  => 'float',
    ];

    public function rider()
    {
        return $this->belongsTo(User::class, 'rider_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
