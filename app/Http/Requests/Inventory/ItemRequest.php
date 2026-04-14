<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ItemRequest extends FormRequest
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
        $rules = [
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('inventory_items')->where(function ($query) {
                    return $query->where('company_id', auth()->user()->company_id);
                })->ignore($this->item),
            ],
            'sku' => 'nullable|string|max:100',
            'group_id' => 'required|exists:inventory_item_groups,id',
            'uom_id' => 'required|exists:inventory_uoms,id',
            'master_id' => 'nullable|exists:inventory_items,id',
            'is_active' => 'boolean|nullable',
            'acquire' => 'nullable|string|in:purchase,manufacture',
            'tracking' => 'nullable|string|in:not-applicable,batch,batch-lot',
            'has_expiry' => 'boolean|nullable',

            'variants' => [
                'nullable',
                'array',
                function ($attribute, $value, $fail) {
                    if (!is_array($value)) return;
                    
                    $codes = array_filter(array_column($value, 'code'));
                    if (count($codes) !== count(array_unique($codes))) {
                        $fail('Variant codes must be unique.');
                    }
                    
                    if (in_array($this->input('code'), $codes)) {
                        $fail('Variant code cannot be the same as the master item code.');
                    }
                },
            ],
            'variants.*.id' => 'nullable|string',
        ];

        if ($this->has('variants')) {
            foreach ($this->input('variants') as $key => $variant) {
                $rules["variants.{$key}.name"] = 'required|string|max:255';
                $codeRule = Rule::unique('inventory_items', 'code')->where(function ($query) {
                    return $query->where('company_id', auth()->user()->company_id);
                });

                if (!empty($variant['id'])) {
                    $codeRule->ignore($variant['id']);
                }

                $rules["variants.{$key}.code"] = [
                    'required',
                    'distinct',
                    'string',
                    'max:100',
                    $codeRule,
                ];

                 $skuRule = Rule::unique('inventory_items', 'sku')->where(function ($query) {
                    return $query->where('company_id', auth()->user()->company_id);
                });

                if (!empty($variant['id'])) {
                    $skuRule->ignore($variant['id']);
                }

                $rules["variants.{$key}.sku"] = [
                    'nullable',
                    'distinct',
                    'string', 
                    'max:100', 
                    $skuRule,
                ];
            }
        }

        return $rules;
    }

    protected function prepareForValidation()
    {
        if ($this->has('variants')) {
            $variants = $this->input('variants');
            $filteredVariants = array_filter($variants, function ($variant) {
                return !empty($variant['name']);
            });
            
            $this->merge([
                'variants' => array_values($filteredVariants),
            ]);
        }
    }
}
