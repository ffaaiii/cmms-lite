<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInspectionChecklistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('technician');
    }

    public function rules(): array
    {
        return [
            'asset_id' => ['required', 'exists:assets,id'],
            'condition_found' => ['required', 'in:good,needs_attention,damaged'],
            // wajib diisi kalau kondisi bukan 'good', sesuai 11-ui-ux.md
            'notes' => ['required_unless:condition_found,good', 'nullable', 'string'],
        ];
    }
}
