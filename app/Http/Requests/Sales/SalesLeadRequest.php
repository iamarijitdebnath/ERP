<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;

class SalesLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:100'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'customer_id' => ['nullable', 'string', 'max:36'],
        ];
    }
}
