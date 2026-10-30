<?php

namespace App\Http\Controllers\Api\GiftGive;

use App\Http\Controllers\Controller;
use App\Http\Requests\RequestApi\GiftGiver\StoreGiftGiverRequest;
use Illuminate\Http\JsonResponse;

class GiftGiverController extends Controller
{
    /**
     * كل أسماء أصحاب الهدية للمستخدم (الأحدث أولاً).
     */
    public function index(): JsonResponse
    {
        $givers = auth('api')->user()
            ->giftGivers()
            ->latest('id')
            ->get(['id', 'name', 'created_at']);

        return response()->json([
            'status' => true,
            'data'   => $givers,
        ]);
    }

    /**
     * تسجيل اسم صاحب هدية جديد (الأخير هو المعتمد في الـ me).
     */
    public function store(StoreGiftGiverRequest $request): JsonResponse
    {
        $giver = auth('api')->user()->giftGivers()->create([
            'name' => $request->name,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Gift giver saved',
            'data'    => $giver->only(['id', 'name', 'created_at']),
        ], 201);
    }
}
