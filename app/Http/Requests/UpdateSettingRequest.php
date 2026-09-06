<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-administration') ?? false;
    }

    public function rules(): array
    {
        return [
            'organization_name' => ['required', 'string', 'max:150'],
            'allow_negative_balance' => ['sometimes', 'boolean'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'supprimer_logo' => ['sometimes', 'boolean'],
        ];
    }
}
