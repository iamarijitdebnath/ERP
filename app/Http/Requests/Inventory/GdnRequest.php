<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GdnRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['gdn'])],
            'transaction_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.uom_id' => 'required|exists:inventory_uoms,id',
            'from_warehouse_id' => 'required|exists:inventory_warehouses,id',
            'to_warehouse_id' => 'nullable|exists:inventory_warehouses,id',
            'customer_id' => 'nullable|exists:sales_customers,id',
            'invoice_id' => 'nullable|exists:sales_invoices,id',
            'issued_by' => 'nullable|exists:hrms_employees,id',
            'way_bill_no' => 'nullable|string|max:30',
            'remarks' => 'nullable|string|max:400',
        ];
    }
}
