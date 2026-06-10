<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use App\Models\Order;

class UpdateOrderStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $order = Order::find($this->route('id'));
        $user = $this->user();

        if (!$order || !$user) {
            return false;
        }

        if ($user->hasRole('admin') || (int) $order->rider_id === (int) $user->id) {
            return true;
        }

        if ((int) $order->customer_id !== (int) $user->id) {
            return false;
        }

        return $this->input('order_status') === Order::STATUS_CANCEL
            || $this->isLocalSimulatorDeliveryCompletion($order);
    }

    private function isLocalSimulatorDeliveryCompletion(Order $order): bool
    {
        return app()->environment(['local', 'testing'])
            && $this->boolean('simulated_tracking')
            && $this->input('order_status') === Order::STATUS_DELIVERED
            && (int) $order->rider_id > 0
            && (int) $order->is_assign === 1;
    }

    protected function failedAuthorization()
    {
        throw new HttpResponseException(response()->json([
            'status'   => false,
            'messages' => 'You are not authorised to update this order.',
        ], 403));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'order_status' => [
                'required',
                Rule::in([
                    Order::STATUS_PROCESSING,
                    Order::STATUS_PICKING,
                    Order::STATUS_DELIVERED,
                    Order::STATUS_REFUSED,
                    Order::STATUS_NOT_RECEIVED,
                    Order::STATUS_ACCIDENT,
                    Order::STATUS_CANCEL,
                    'on_way',
                    'picked_up'
                ]),
            ],
        ];
    }
}
