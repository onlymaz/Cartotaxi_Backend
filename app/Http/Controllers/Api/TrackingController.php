<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Utilities\FireBaseRealTimeDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TrackingController extends Controller
{
    public function UpdateCoordinates(Request $request)
    {
        $validation_fields = [
            'lat'  => 'required|numeric|between:-90,90',
            'long' => 'required|numeric|between:-180,180',
        ];

        $validator = $this->getValidationFactory()->make($request->all(), $validation_fields);
        if ($validator->fails()) {
            return response()->json([
                'status'   => false,
                'messages' => implode(' ', array_column($validator->messages()->getMessages(), 0))
            ], 422);
        }

        $user = $request->user();
        if (!empty($user)) {
            $data = [
                'lat'  => (float) $request->lat,
                'long' => (float) $request->long,
            ];
            $reference = 'users/' . $user->id;
            FireBaseRealTimeDatabase::StoreData($reference, $data);
        }

        return response()->json([
            'status'   => true,
            'messages' => 'Location updated successfully'
        ]);
    }
}
