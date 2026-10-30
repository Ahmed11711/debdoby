<?php

namespace App\Http\Controllers\Api\Device;

use App\Http\Controllers\Controller;
use App\Http\Requests\RequestApi\Device\PairDeviceRequest;
use App\Models\Device;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DeviceController extends Controller
{
    public function store(PairDeviceRequest $request): JsonResponse
    {
        $userId = auth('api')->id();
        $code   = $request->validated()['code'];

        return DB::transaction(function () use ($userId, $code) {
            // نقفل الدبدوب واليوزر الاتنين عشان مفيش طلبين يتعارضوا
            $device = Device::where('device_id', $code)->lockForUpdate()->firstOrFail();
            $user   = User::whereKey($userId)->lockForUpdate()->firstOrFail();

            // نفس الحساب مربوط بنفس الدبدوب: نجاح من غير تغيير
            if ($user->device_id === $device->id) {
                return $this->ok($device, 'الدبدوب مربوط بالفعل');
            }

            // الحساب ده عنده دبدوب تاني
            if ($user->device_id !== null) {
                return $this->conflict();
            }

            // الدبدوب مربوط بحساب تاني
            if (User::where('device_id', $device->id)->exists()) {
                return $this->conflict();
            }

            $user->update(['device_id' => $device->id]);

            return $this->ok($device, 'اتربط الدبدوب بنجاح', 201);
        });
    }

    /** DELETE /api/device-name  (إعادة الربط من الإعدادات) */
    public function destroy(): JsonResponse
    {
        Device::where('user_id', auth('api')->id())
            ->update(['user_id' => null, 'paired_at' => null]);

        return response()->json([
            'success' => true,
            'status'  => 200,
            'message' => 'تم فك الربط',
            'data'    => null,
        ]);
    }

    private function ok(Device $device, string $message, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'status'  => $status,
            'message' => $message,
            'data'    => [
                'message' => $message,
                'topic'   => $device->topic,
            ],
        ], $status);
    }

    private function conflict(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'status'  => 409,
            'message' => 'الدبدوب مربوط بحساب تاني، أو حسابك مربوط بدبدوب بالفعل',
            'data'    => null,
        ], 409);
    }
}
