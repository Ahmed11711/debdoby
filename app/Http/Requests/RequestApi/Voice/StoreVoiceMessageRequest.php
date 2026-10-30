<?php

namespace App\Http\Requests\RequestApi\Voice;

use App\Http\Requests\BaseRequest\BaseRequest;
use Illuminate\Validation\Rule;

class StoreVoiceMessageRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'voice'    => [
                'required',
                'file',
                'mimetypes:audio/mpeg,audio/mp4,audio/x-m4a,audio/aac,audio/wav,audio/x-wav,audio/webm,audio/ogg,video/mp4,video/webm',
                'max:5120', // 5MB
            ],
            // مدة التسجيل بالثواني (30 ثانية بحد أقصى)
            'duration' => ['required', 'integer', 'min:1', 'max:30'],
            // اختياري: اسم صاحب الهدية اللي التسجيل منه (لازم يكون بتاع نفس المستخدم)
            'gift_giver_id' => [
                'nullable',
                'integer',
                Rule::exists('gift_givers', 'id')->where('user_id', auth('api')->id()),
            ],
        ];
    }
}
