<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\Client::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'code_client' => ['required', 'string', 'max:20', 'unique:clients,code_client'],
            'nom' => ['required', 'string', 'max:100'],
            'prenom' => ['required', 'string', 'max:150'],
            'telephone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:191'],
            'adresse' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code_client' => strtoupper(trim((string) $this->input('code_client'))),
            'nom' => mb_strtoupper(trim((string) $this->input('nom'))),
            'prenom' => mb_strtoupper(trim((string) $this->input('prenom'))),
            'telephone' => trim((string) $this->input('telephone')) ?: null,
            'email' => strtolower(trim((string) $this->input('email'))) ?: null,
            'adresse' => trim((string) $this->input('adresse')) ?: null,
        ]);
    }
}
