<?php

namespace App\Http\Requests\RequestApi\Device;

use App\Http\Requests\BaseRequest\BaseRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PairDeviceRequest extends BaseRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => strtoupper(trim((string) $this->input('code'))),
        ]);
    }

    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:50',
                'regex:/^TEDDY-[A-Z0-9][A-Z0-9-]*$/',
                'exists:devices,device_id',
            ],
        ];
    }
}
