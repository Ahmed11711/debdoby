<?php

namespace App\Http\Controllers\Api\Device;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\User;
use App\Models\VoiceMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class DeviceVoiceController extends Controller
{
    /** الـ disk اللي بتتخزن عليه الملفات (لازم يطابق اللي بيرفع الرسالة) */
    private const DISK = 'public';

    /**
     * الدبدوب بينادي هنا بعد ما يجيله الرقم 6.
     *
     * GET /api/device/voice/latest?device_id=TEDDY-XXXXXXXXXXXX
     *
     * 200 → ملف الصوت (فيه Content-Length عشان الدبدوب يتأكد إن التحميل كمل)
     * 204 → مفيش رسايل لسه
     * 404 → الجهاز مش معروف أو مش مربوط بحساب، أو الملف ناقص من الـ disk
     * 422 → device_id ناقص
     */
    public function latest(Request $request): Response
    {
        $code = trim((string) $request->input('device_id', ''));

        // من غير FormRequest: الـ ESP32 مبيبعتش Accept: application/json،
        // وفي الحالة دي الـ validation بيرجّع redirect مش JSON.
        if ($code === '' || strlen($code) > 64) {
            return $this->fail('device_id is required', 422);
        }

        $device = Device::where('device_id', $code)->first();
        if (! $device) {
            return $this->fail('Device not found', 404);
        }

        $user = User::where('device_id', $device->id)->first();
        if (! $user) {
            return $this->fail('Device is not paired', 404);
        }

        $message = VoiceMessage::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();

        if (! $message) {
            return response()->noContent(); // 204
        }

        $disk = Storage::disk(self::DISK);

        if (! $disk->exists($message->path)) {
            Log::error('Voice file missing on disk', [
                'voice_message_id' => $message->id,
                'path'             => $message->path,
            ]);

            return $this->fail('Voice file is missing', 404);
        }

        // أول مرة الدبدوب يجيبها نعلّمها اتسلّمت
        // (بتتعلّم وقت الطلب، مش وقت ما التحميل يخلص)
        if (! $message->delivered) {
            $message->forceFill([
                'delivered'    => true,
                'delivered_at' => now(),
            ])->save();
        }

        return $disk->response($message->path, null, [
            'Content-Type' => 'audio/mpeg',
            'Content-Length'     => (string) $disk->size($message->path),
            'Cache-Control'      => 'no-store',
            'X-Message-Id'       => (string) $message->id,
            'X-Message-Duration' => (string) $message->duration,
        ]);
    }

    private function fail(string $message, int $status): JsonResponse
    {
        return response()->json(['status' => false, 'message' => $message], $status);
    }
}
