<?php

namespace App\Http\Requests\RequestApi\Device;

use App\Http\Requests\BaseRequest\BaseRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RecipientDeviceRequest extends BaseRequest
{

    public function rules(): array
    {
        return [
            'name' => 'required|string'
        ];
    }
}
