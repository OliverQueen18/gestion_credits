<?php

namespace App\Http\Requests;

use App\Models\Operation;
use Illuminate\Foundation\Http\FormRequest;

class CorrectOperationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $operation = $this->route('operation');

        return $operation instanceof Operation
            && ($this->user()?->can('correct', $operation) ?? false);
    }

    public function rules(): array
    {
        return [
            'motif' => ['required', 'string', 'min:5', 'max:2000'],
        ];
    }
}
