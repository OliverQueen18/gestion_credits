<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'min:3',
                'max:50',
                'regex:/^[a-z0-9._-]+$/',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'telephone' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'username' => User::normalizeUsername($this->input('username')),
            'telephone' => User::normalizeTelephone($this->input('telephone')),
        ]);
    }
}
