<?php

namespace App\Http\Requests;

use App\Models\Operation;
use Illuminate\Foundation\Http\FormRequest;

class StoreCreditRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Operation::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['required', 'exists:clients,id'],
            'montant' => ['required', 'numeric', 'gt:0'],
            'date_operation' => ['required', 'date'],
            'observation' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
