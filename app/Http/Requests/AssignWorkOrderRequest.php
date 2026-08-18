<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignWorkOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('supervisor');
    }

    public function rules(): array
    {
        return [
            'assigned_to' => [
                'required',
                'exists:users,id',
                // divalidasi lebih lanjut di Action: user itu harus role technician
            ],
        ];
    }
}