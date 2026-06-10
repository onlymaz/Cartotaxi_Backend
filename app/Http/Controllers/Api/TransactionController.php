<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Gateway;
use App\Models\Payment;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function store(Request $request)
    {
        $validation_fields  =   [
            'order_id'         => 'required',
            'payment_type'         => 'required',
            'transaction_id'         => 'required',
            'amount'         => 'required',
        ];
        $validator     =  $this->getValidationFactory()->make($request->all(),$validation_fields);
        if($validator->fails()) {
            $messages   =   [];
            foreach ($validator->messages()->getMessages() as $key =>   $message){
                $messages[]  =
                    $message[0];
            }

            return response()->json([
                'status'     =>  false,
                'messages'   =>  $messages
            ], 200);
        }

        $payment_type   =   $request->payment_type;     /*[cod,Paypal]*/
        $user           =   $request->user();
        $gateway_id =   1;
        $gateway    =   Gateway::where('name',$payment_type)->first();
        if ($gateway){
            $gateway_id =   $gateway->id;
        }

        if ($payment_type=='cod'){
            $status =   'pending';
        }else{
            $status =   'completed';
        }
        Payment::create([
            'order_id'  =>  $request->order_id,
            'customer_id'  =>  $user->id,
            'gateway_id'  =>  $gateway_id,
            'amount'  =>  $request->amount,
            'status'  =>  $status,
        ]);

        return response()->json([
            'status'    =>  true,
            'messages'  =>  'Transaction Successfully executed'
        ]);
    }
}
