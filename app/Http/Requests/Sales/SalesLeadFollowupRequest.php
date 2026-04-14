<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;

class SalesLeadFollowupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'date' => ['required', 'date'],
            'follow_up_date' => ['nullable', 'date', 'after_or_equal:date'],
            'remarks' => ['nullable', 'string'],
            'is_complete' => ['nullable', 'boolean'],
        ];

        if ($this->isMethod('post')) {
            $rules['inquiry_id'] = ['required', 'exists:sales_leads_inquiries,id'];
        }

        return $rules;
    }
}
