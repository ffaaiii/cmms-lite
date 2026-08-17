<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('asset'));
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'category' => ['required', 'in:turbine,well,pipe,cooling_tower,other'],
            'location' => ['nullable', 'string', 'max:150'],
            'installed_at' => ['nullable', 'date'],
            'condition' => ['required', 'in:good,needs_attention,damaged'],
            'pm_interval_days' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
