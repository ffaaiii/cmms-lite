<?php

namespace App\Http\Requests;

use App\Models\Asset;
use Illuminate\Foundation\Http\FormRequest;

class StoreAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Asset::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'category' => ['required', 'in:turbine,well,pipe,cooling_tower,other'],
            'location' => ['nullable', 'string', 'max:150'],
            'installed_at' => ['nullable', 'date'],
            'condition' => ['nullable', 'in:good,needs_attention,damaged'],
            'pm_interval_days' => ['required', 'integer', 'min:1'],
        ];
    }
}
