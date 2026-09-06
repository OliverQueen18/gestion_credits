<?php

namespace App\Http\Requests;

use App\Enums\OperationSens;
use App\Models\TypeOperation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTypeOperationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', TypeOperation::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:20', 'unique:type_operations,code'],
            'libelle' => ['required', 'string', 'max:100'],
            'sens' => ['required', Rule::enum(OperationSens::class)],
            'actif' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => strtoupper(trim((string) $this->input('code'))),
            'actif' => $this->boolean('actif'),
        ]);
    }
}
