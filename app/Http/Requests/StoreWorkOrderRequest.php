<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('supervisor');
    }

    public function rules(): array
    {
        return [
            'asset_id' => ['required', 'exists:assets,id'],
            'type' => ['required', 'in:preventive,corrective'],
            'priority' => ['required', 'in:normal,urgent'],
            'description' => ['nullable', 'string'],
            'scheduled_at' => ['nullable', 'date'],
        ];
    }
}