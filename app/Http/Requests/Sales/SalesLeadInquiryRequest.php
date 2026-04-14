<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;

class SalesLeadInquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'product_id' => ['nullable', 'exists:inventory_items,id'],
            'employee_id' => ['nullable', 'exists:hrms_employees,id'],
            'source' => ['nullable', 'string', 'max:50'],
            'remarks' => ['nullable', 'string'],
        ];

        if ($this->isMethod('post')) {
            $rules['lead_id'] = ['required', 'exists:sales_leads,id'];
        }

        return $rules;
    }
}
