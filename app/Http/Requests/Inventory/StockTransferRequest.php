<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockTransferRequest extends FormRequest
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
            'type' => ['required', Rule::in(['transfer'])],
            'transaction_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.uom_id' => 'required|exists:inventory_uoms,id',
            'from_warehouse_id' => 'required|exists:inventory_warehouses,id',
            'to_warehouse_id' => 'required|exists:inventory_warehouses,id|different:from_warehouse_id',
        ];
    }
}
