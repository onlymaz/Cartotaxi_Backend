<?php

namespace App\Models;

use App\Scopes\Filterable;
use App\Models\User;
use Faker\Factory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use Filterable;

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_PICKING = 'picking';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_REFUSED = 'refused';
    const STATUS_NOT_RECEIVED = 'not_received';
    const STATUS_ACCIDENT = 'accident';
    const STATUS_CANCEL = 'cancel';

    /**
     * Mass-assignable fields. Money/identity/state fields are deliberately
     * excluded:
     *   - customer_id, rider_id, booking_id  (server-controlled identity)
     *   - fixed_price, total_amount          (server-computed; never client input)
     *   - order_status                       (workflow state; transitions only)
     *   - is_assign                          (assignment workflow flag)
     *
     * Legitimate writers MUST use Order::forceCreate() / $order->forceFill()
     * for those, so a future controller bug that does $order->update($request->all())
     * cannot be coerced into rewriting another customer's bill.
     */
    protected $fillable = [
        'package_id',
        'start_district_id', 'end_district_id', 'name', 'description',
        'start_location', 'end_location', 'picked_time',
        'per_km_charges', 'total_meter', 'total_second',
        'map_image', 'sign', 'start_time', 'end_time',
        'reciver_name', 'reciver_address', 'image_name',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'customer_id', 'id');
    }

    public function rider()
    {
        return $this->belongsTo(User::class, 'rider_id', 'id');
    }
    /**
     * Generate a candidate booking ID. We use the framework's CSPRNG via
     * Str::random rather than the previous sha1(uniqid(mt_rand())) chain —
     * mt_rand is not cryptographically secure and the wrapping was
     * deterministic enough to risk collisions on burst traffic.
     */
    public function unique_code(): string
    {
        return 'BRN' . strtoupper(\Illuminate\Support\Str::random(10));
    }

    /**
     * Generate a unique booking ID. The previous implementation recursed on
     * collision and had a TOCTOU race: between SELECT COUNT and INSERT, two
     * concurrent requests could both observe "no duplicate" and then write
     * identical IDs.
     *
     * The migration `2026_04_25_220548_security_and_performance_hardening`
     * adds a UNIQUE index on orders.booking_id; this method now relies on
     * that index as the source of truth and only uses the SELECT pre-check
     * as a soft optimisation. The real guarantee is the DB constraint.
     *
     * @return string
     * @throws \RuntimeException if no unique ID is found after $maxAttempts.
     */
    public static function CreateRandomBookingID(): string
    {
        $self        = new self();
        $maxAttempts = 8;

        for ($i = 0; $i < $maxAttempts; $i++) {
            $code = $self->unique_code();
            // Probabilistic: BRN + 10 base62 chars ≈ 62^10 ≈ 8.4e17 distinct
            // codes, so collisions are vanishingly rare. The pre-check just
            // saves a round-trip to the INSERT path on the unlikely hit.
            if (!self::where('booking_id', $code)->exists()) {
                return $code;
            }
        }

        // If we somehow generated $maxAttempts duplicates in a row, surface
        // the failure rather than recurse forever.
        throw new \RuntimeException('Failed to generate a unique booking ID');
    }
    public function payment(){
        return $this->hasOne(Payment::class,'order_id','id');
    }
    public function orderSubTrip(){
        return $this->hasMany(OrderSubTrip::class, 'order_id','id');
    }
    public function helperOrder(){
        return $this->hasMany(HelperOrder::class, 'order_id','id');
    }
    public function package(){
        return $this->belongsTo(Package::class,'package_id','id');
    }

    public function reviewRatings()
    {
        return $this->hasMany(ReviewRating::class);
    }

    public static function total_km($id){
        return Order::where('id',$id)->first(['total_meter'])->total_meter/1000;
    }
    public function getSignAttribute($value){
        return url($value);
    }
}
