<?php

namespace App\Services;

use App\Models\ButtonPress;
use App\Models\User;

/**
 * يحدد شكل الدبدوب بناءً على آخر ضغطة للمستخدم.
 *
 *  - آخر ضغطة من أقل من ساعة  -> الحالة حسب الزر (يفضل ساعة)
 *  - مرّ أكثر من 24 ساعة بدون أي ضغطة -> sad (زعلان إنه نسيه)
 *  - غير كده -> idle
 */
class DabdobyMoodService
{
    public const ACTIVE_MINUTES = 60;
    public const FORGOT_HOURS   = 24;

    private const STATE_BY_BUTTON = [
        ButtonPress::LOVE      => 'love',       // قلب أحمر والخلفية حمراء
        ButtonPress::MISS      => 'miss',       // لون تاني للقلب والخلفية
        ButtonPress::CHECK_ON  => 'check_on',
        ButtonPress::EMERGENCY => 'emergency',
        ButtonPress::GOODNIGHT => 'sleeping',   // الصورة الأخيرة (نايم)
    ];

    public function for(User $user): array
    {
        $last = $user->buttonPresses()->latest('created_at')->latest('id')->first();

        if (! $last) {
            return $this->payload('idle', null, null, null);
        }

        // "تصبح على خير": الدبدوب يفضل نايم لحد ما تيجي رسالة جديدة (أو يعدّي 24 ساعة فيزعل)
        $minutes = $last->button === ButtonPress::GOODNIGHT
            ? self::FORGOT_HOURS * 60
            : self::ACTIVE_MINUTES;

        $expiresAt = $last->created_at->copy()->addMinutes($minutes);

        if (now()->lt($expiresAt)) {
            return $this->payload(self::STATE_BY_BUTTON[$last->button] ?? 'idle', $last, $expiresAt, $last->created_at);
        }

        if ($last->created_at->lte(now()->subHours(self::FORGOT_HOURS))) {
            return $this->payload('sad', $last, null, $last->created_at);
        }

        return $this->payload('idle', $last, null, $last->created_at);
    }

    private function payload(string $state, ?ButtonPress $last, $expiresAt, $lastAt): array
    {
        return [
            'state'         => $state,
            'button'        => $last?->button,
            'delivered'     => $last?->delivered,
            'last_press_at' => $lastAt?->toIso8601String(),
            'expires_at'    => $expiresAt?->toIso8601String(),
        ];
    }
}
