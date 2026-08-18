<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransitionWorkOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Ownership sudah dicek di Policy (method 'transition' di WorkOrderPolicy)
        // lewat route model binding + $this->authorize() di controller.
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:in_progress,completed'],
            'note' => ['nullable', 'string'],
            'parts' => ['nullable', 'array'],
            'parts.*.part_name' => ['required_with:parts', 'string', 'max:150'],
            'parts.*.quantity' => ['required_with:parts', 'integer', 'min:1'],
            'parts.*.unit' => ['nullable', 'string', 'max:20'],
        ];
    }
}