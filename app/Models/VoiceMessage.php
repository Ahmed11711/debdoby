<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class VoiceMessage extends Model
{
    protected $fillable = ['user_id', 'gift_giver_id', 'path', 'duration', 'delivered', 'delivered_at'];

    protected $casts = [
        'duration'     => 'integer',
        'delivered'    => 'boolean',
        'delivered_at' => 'datetime',
    ];

    protected $appends = ['url'];

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function giftGiver(): BelongsTo
    {
        return $this->belongsTo(GiftGiver::class);
    }
}
