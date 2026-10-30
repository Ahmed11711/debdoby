<?php

namespace App\Http\Controllers\Api\GiftGive;

use App\Http\Controllers\Controller;
use App\Http\Requests\RequestApi\ButtonPress\StoreButtonPressRequest;
use App\Services\DabdobyMoodService;
use App\Services\MqttPublisher;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use PhpMqtt\Client\Facades\MQTT;

class ButtonPressController extends Controller
{
    /**
     * تسجيل ضغطة فقط. الحالة الحالية للدبدوب بتيجي من الـ me.
     * (بنرجّع الـ mood برضه للراحة، بس الفرونت يعتمد على الـ me.)
     */
    // public function store(StoreButtonPressRequest $request, DabdobyMoodService $mood)
    // {
    //     $user = auth('api')->user();

    //     $press = $user->buttonPresses()->create([
    //         'button' => $request->button,
    //     ]);

    //     return response()->json([
    //         'status'  => true,
    //         'message' => 'Press saved',
    //         'data'    => [
    //             'press' => $press->only(['id', 'button', 'delivered', 'created_at']),
    //             'mood'  => $mood->for($user),
    //         ],
    //     ], 201);
    // }
    // public function store(
    //     StoreButtonPressRequest $request,
    //     DabdobyMoodService $mood,
    //     MqttPublisher $mqtt
    // ) {
    //     $user = auth('api')->user();

    //     $device = $user->device;

    //     if (! $device) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => 'No device linked to this user',
    //         ], 422);
    //     }

    //     $press = $user->buttonPresses()->create([
    //         'button' => $request->button,
    //     ]);

    //     // نبعت الرقم بس: "1" أو "2" ... "5"
    //     $delivered = $mqtt->publish($device->device_id, (string) $request->button);

    //     if ($delivered) {
    //         $press->forceFill(['delivered' => true])->save();
    //     }

    //     return response()->json([
    //         'status'  => true,
    //         'message' => 'Press saved',
    //         'data'    => [
    //             'press' => $press->only(['id', 'button', 'delivered', 'created_at']),
    //             'mood'  => $mood->for($user),
    //         ],
    //     ], 201);
    // }


    public function store(StoreButtonPressRequest $request, DabdobyMoodService $mood)
    {
        $user = auth('api')->user();

        $press = $user->buttonPresses()->create([
            'button' => $request->button,
        ]);

        $delivered = false;
        $device = $user->device;

        Log::info('press: device lookup', ['user' => $user->id, 'device' => $device?->device_id]);

        if ($device) {
            try {
                MQTT::publish(
                    "dabbub/devices/{$device->device_id}/commands",
                    (string) $press->button,   // الرقم لوحده: "1"
                    1
                );
                MQTT::loop(true, true);
                $delivered = true;
                Log::info('press: published OK');
            } catch (\Throwable $e) {
                Log::error('press: mqtt failed: ' . $e->getMessage());
            } finally {
                MQTT::disconnect();
            }

            if ($delivered) {
                $press->forceFill(['delivered' => true])->save(); // يتخطى الـ fillable
            }
        }

        return response()->json([
            'status'  => true,
            'message' => 'Press saved',
            'data'    => [
                'press' => $press->only(['id', 'button', 'delivered', 'created_at']),
                'mood'  => $mood->for($user),
            ],
        ], 201);
    }
}
