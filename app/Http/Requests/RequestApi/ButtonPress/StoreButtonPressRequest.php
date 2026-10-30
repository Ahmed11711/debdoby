<?php

namespace App\Http\Requests\RequestApi\ButtonPress;

use App\Http\Requests\BaseRequest\BaseRequest;

class StoreButtonPressRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            // 1 بحبك | 2 وحشتيني | 3 طمني عليك | 4 طوارئ | 5 تصبح على خير
            'button' => ['required', 'integer', 'in:1,2,3,4,5'],
        ];
    }
}
