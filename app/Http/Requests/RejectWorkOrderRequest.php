<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RejectWorkOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('supervisor');
    }

    public function rules(): array
    {
        return [
            'rejection_note' => ['required', 'string'],
        ];
    }
}