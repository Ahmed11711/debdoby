<?php

namespace App\Http\Controllers\Api\GiftGive;

use App\Http\Controllers\Controller;
use App\Http\Requests\RequestApi\Voice\StoreVoiceMessageRequest;
use Illuminate\Http\JsonResponse;

class VoiceMessageController extends Controller
{

    public function index(): JsonResponse
    {
        $voices = auth('api')->user()
            ->voiceMessages()
            ->with('giftGiver:id,name')
            ->latest('id')
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $voices,
        ]);
    }


    public function store(StoreVoiceMessageRequest $request): JsonResponse
    {
        $path = $request->file('voice')->store('voices', 'public');

        $voice = auth('api')->user()->voiceMessages()->create([
            'path'          => $path,
            'duration'      => $request->duration,
            'gift_giver_id' => $request->gift_giver_id,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Voice uploaded',
            'data'    => $voice,
        ], 201);
    }
}
