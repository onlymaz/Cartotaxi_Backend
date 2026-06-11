<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Gateway;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Stripe\StripeClient;

/**
 * Server side of the iOS StripePaymentIntentService contract:
 *
 *  POST payments/stripe/payment-intent          booking params + idempotency_key
 *      -> creates the booking (same path as booking-store/v2) and a Stripe
 *         PaymentIntent for its server-side amount, returns client_secret.
 *  POST payments/stripe/payment-intent/confirm  order_id + payment_intent_id
 *      -> verifies the intent really succeeded at Stripe before promoting
 *         the payment row; the client's word is never trusted.
 *  POST payments/stripe/payment-intent/cancel   order_id + payment_intent_id
 *      -> best-effort cancel of an abandoned intent.
 */
class StripePaymentIntentController extends Controller
{
    public function create(Request $request, BookingController $bookings)
    {
        $stripe = $this->client();
        if ($stripe instanceof \Illuminate\Http\JsonResponse) {
            return $stripe;
        }

        // Create the booking exactly like booking-store/v2 would.
        try {
            $storeRequest = app(StoreBookingRequest::class);
        } catch (ValidationException $e) {
            return response()->json([
                'status'   => false,
                'messages' => implode(' ', collect($e->errors())->flatten()->all()),
            ], 422);
        }

        $storeResponse = $bookings->store($storeRequest);
        $payload = $storeResponse->getData(true);

        if (empty($payload['status']) || empty($payload['data']['id'])) {
            return $storeResponse;
        }

        $order = Order::findOrFail((int) $payload['data']['id']);

        try {
            $intent = $stripe->paymentIntents->create([
                'amount'   => (int) round(((float) $order->total_amount) * 100),
                'currency' => strtolower(config('app.currency', 'EUR')),
                'metadata' => [
                    'order_id'    => (string) $order->id,
                    'booking_id'  => (string) $order->booking_id,
                    'customer_id' => (string) $order->customer_id,
                    'platform'    => (string) $request->input('platform', 'ios'),
                ],
                'automatic_payment_methods' => ['enabled' => true],
            ], array_filter([
                'idempotency_key' => $request->input('idempotency_key'),
            ]));
        } catch (\Throwable $e) {
            \Log::error('Stripe intent creation failed for order ' . $order->id . ': ' . $e->getMessage());
            return response()->json([
                'status'   => false,
                'messages' => 'Unable to start the card payment. Please try again.',
            ], 502);
        }

        Payment::where('order_id', $order->id)->update([
            'gateway_id'     => $this->stripeGatewayId(),
            'transaction_id' => $intent->id,
        ]);

        return response()->json([
            'status'   => true,
            'messages' => 'Payment intent created',
            'data'     => [
                'booking_id'        => $order->id,
                'order_id'          => $order->id,
                'payment_intent_id' => $intent->id,
                'client_secret'     => $intent->client_secret,
                'publishable_key'   => config('services.stripe.key'),
            ],
        ]);
    }

    public function confirm(Request $request)
    {
        $stripe = $this->client();
        if ($stripe instanceof \Illuminate\Http\JsonResponse) {
            return $stripe;
        }

        $request->validate([
            'order_id'          => 'required|numeric',
            'payment_intent_id' => 'required|string',
        ]);

        $order = Order::find((int) $request->order_id);
        if (!$order || (int) $order->customer_id !== (int) $request->user()->id) {
            return response()->json(['status' => false, 'messages' => 'Order not found'], 404);
        }

        try {
            $intent = $stripe->paymentIntents->retrieve($request->payment_intent_id);
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'messages' => 'Payment not found at provider'], 404);
        }

        if (($intent->metadata['order_id'] ?? null) !== (string) $order->id) {
            return response()->json(['status' => false, 'messages' => 'Payment does not belong to this order'], 422);
        }

        if ($intent->status !== 'succeeded') {
            return response()->json([
                'status'   => false,
                'messages' => 'Payment not completed (status: ' . $intent->status . ')',
            ], 402);
        }

        Payment::where('order_id', $order->id)->update([
            'gateway_id'     => $this->stripeGatewayId(),
            'transaction_id' => $intent->id,
            'status'         => 'completed',
        ]);

        return response()->json([
            'status'   => true,
            'messages' => 'Payment confirmed',
            'data'     => ['order_id' => $order->id, 'payment_status' => 'completed'],
        ]);
    }

    public function cancel(Request $request)
    {
        $stripe = $this->client();
        if ($stripe instanceof \Illuminate\Http\JsonResponse) {
            return $stripe;
        }

        $request->validate([
            'payment_intent_id' => 'required|string',
        ]);

        try {
            $stripe->paymentIntents->cancel($request->payment_intent_id);
        } catch (\Throwable $e) {
            // Best-effort: an already-confirmed or expired intent can't be
            // cancelled; the confirm endpoint remains the source of truth.
        }

        return response()->json(['status' => true, 'messages' => 'Payment cancelled']);
    }

    /** @return StripeClient|\Illuminate\Http\JsonResponse */
    private function client()
    {
        $secret = config('services.stripe.secret');
        if (empty($secret)) {
            return response()->json([
                'status'   => false,
                'messages' => 'Card payments are not configured on this server.',
            ], 503);
        }

        return new StripeClient($secret);
    }

    private function stripeGatewayId(): int
    {
        return (int) Gateway::firstOrCreate(['name' => 'stripe'])->id;
    }
}
