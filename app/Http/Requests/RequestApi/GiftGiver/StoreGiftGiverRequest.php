<?php

namespace App\Http\Requests\RequestApi\GiftGiver;

use App\Http\Requests\BaseRequest\BaseRequest;

class StoreGiftGiverRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}
