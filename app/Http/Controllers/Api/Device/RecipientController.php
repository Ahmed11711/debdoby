<?php

namespace App\Http\Controllers\Api\Device;

use App\Http\Controllers\Controller;
use App\Http\Requests\RequestApi\Device\RecipientDeviceRequest;
use Illuminate\Http\JsonResponse;

class RecipientController extends Controller
{
    public function store(RecipientDeviceRequest $request): JsonResponse
    {
        $user = auth('api')->user();

        $data = $request->validated();

        $user->update([
            'gift_recipient_name' => $data['name'],
        ]);

        return response()->json([
            'message' => 'Name updated successfully',
            'data' => [
                'name' => $user->gift_recipient_name,
            ],
        ]);
    }
}
