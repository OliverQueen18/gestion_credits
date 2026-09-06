<?php

namespace App\Http\Requests;

use App\Enums\OperationSens;
use App\Models\TypeOperation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTypeOperationRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var TypeOperation $type */
        $type = $this->route('type_operation');

        return $this->user()?->can('update', $type) ?? false;
    }

    public function rules(): array
    {
        /** @var TypeOperation $type */
        $type = $this->route('type_operation');

        return [
            'code' => ['required', 'string', 'max:20', Rule::unique('type_operations', 'code')->ignore($type)],
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
