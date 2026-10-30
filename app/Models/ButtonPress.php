<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ButtonPress extends Model
{
    public const LOVE      = 1; // بحبك      -> قلب أحمر
    public const MISS      = 2; // وحشتيني   -> قلب بلون تاني وخلفية مختلفة
    public const CHECK_ON  = 3; // طمني عليك
    public const EMERGENCY = 4; // حالة طوارئ
    public const GOODNIGHT = 5; // تصبح على خير -> الدبدوب نايم

    public const ALL = [self::LOVE, self::MISS, self::CHECK_ON, self::EMERGENCY, self::GOODNIGHT];

    protected $fillable = ['user_id', 'button', 'delivered', 'delivered_at'];

    protected $casts = [
        'button'       => 'integer',
        'delivered'    => 'boolean',
        'delivered_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
