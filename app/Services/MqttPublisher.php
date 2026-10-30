<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use PhpMqtt\Client\Facades\MQTT;
use Throwable;

class MqttPublisher
{
    public function publish(string $topic, string $message, int $qos = 1, bool $retain = false): bool
    {
        try {
            $mqtt = MQTT::connection();

            $mqtt->publish($topic, $message, $qos, $retain);

            $mqtt->disconnect();

            return true;
        } catch (Throwable $e) {
            Log::error('MQTT publish failed', [
                'topic' => $topic,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
