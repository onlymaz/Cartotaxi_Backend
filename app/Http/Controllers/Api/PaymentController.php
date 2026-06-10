<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function payments(Request $request)
    {
        $user = $request->user();

        $payments = Payment::from(get_table_name(Payment::class) . ' as p')
            ->join(get_table_name(Order::class) . ' as o', 'o.id', 'p.order_id')
            ->where('o.customer_id', $user->id)
            ->select('p.id', 'p.amount', 'p.status', 'o.booking_id')
            ->paginate(20);

        if (!empty($payments)) {
            foreach ($payments as $payment) {
                $currencySymbol = config('app.currency_symbol');
                $data['payments']['list'][] = [
                    'id'         => $payment->id,
                    'amount'     => $currencySymbol . $payment->amount,
                    'booking_id' => $payment->booking_id,   // fixed: no currency symbol on booking ID
                    'status'     => $payment->status,
                ];
            }

            $data['payments']['links']['current_page']   = $payments->currentPage();
            $data['payments']['links']['first_page_url'] = $payments->url($payments->currentPage());
            $data['payments']['links']['from']           = $payments->firstItem();
            $data['payments']['links']['last_page']      = $payments->lastPage();
            $data['payments']['links']['last_page_url']  = $payments->url($payments->lastPage());
            $data['payments']['links']['next_page_url']  = $payments->nextPageUrl();
            $data['payments']['links']['per_page']       = $payments->perPage();
            $data['payments']['links']['prev_page_url']  = $payments->previousPageUrl();
            $data['payments']['links']['to']             = $payments->lastItem();
            $data['payments']['links']['total']          = $payments->total();
        } else {
            $data['payments']['list']  = [];
            $data['payments']['links'] = new \stdClass();
        }

        return response()->json([
            'status'   => true,
            'messages' => 'Payments',
            'data'     => $data,
        ]);
    }
}
