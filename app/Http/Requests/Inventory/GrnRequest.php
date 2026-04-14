<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GrnRequest extends FormRequest
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
            'type' => ['required', Rule::in(['grn'])],
            'transaction_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.uom_id' => 'required|exists:inventory_uoms,id',
            'items.*.price' => 'nullable|numeric|min:0',
            'items.*.igst' => 'nullable|numeric|min:0|max:100',
            'items.*.cgst' => 'nullable|numeric|min:0|max:100',
            'items.*.sgst' => 'nullable|numeric|min:0|max:100',
            'items.*.cess' => 'nullable|numeric|min:0|max:100',
            'items.*.lot_no' => 'nullable|string|max:50',
            'to_warehouse_id' => 'required|exists:inventory_warehouses,id',
            'from_warehouse_id' => 'nullable|exists:inventory_warehouses,id',
        ];
    }
}
