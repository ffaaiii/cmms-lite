<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAssetConditionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('updateCondition', $this->route('asset'));
    }

    public function rules(): array
    {
        return [
            'condition' => ['required', 'in:good,needs_attention,damaged'],
            'location' => ['nullable', 'string', 'max:150'],
        ];
    }
}
