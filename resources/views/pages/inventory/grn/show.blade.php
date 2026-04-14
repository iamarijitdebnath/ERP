@extends('layouts.app')

@section('content')
    <section class="bg-gray-50">
        <div class="mx-auto">
            <div class="flex justify-between mb-4">
                <h3 class="text-2xl font-medium font-sans">
                    {{ isset($grn) ? 'Edit GRN' : 'Create GRN' }}
                </h3>
                <div class="flex items-center gap-2">
                    <a href="{{ route($routePrefix . '.index') }}" 
                        class="inline-flex items-center justify-center text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700"
                    >
                        Back
                    </a>
                    @if(isset($grn))
                        <form id="data_delete" action="{{ route($routePrefix . '.delete', ['transaction' => $grn->id]) }}" method="POST" class="contents">
                            @csrf
                            @method('DELETE')
                            <button 
                                type="button"
                                data-modal-target="popup-modal"
                                data-modal-toggle="popup-modal"
                                class="inline-flex items-center justify-center focus:outline-none text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900"
                            >
                                Delete
                            </button>
                        </form>
                    @endif

                    <button 
                        type="submit"
                        form="transaction-form"
                        class="flex items-center justify-center text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800"
                    >
                        Save
                    </button>
                </div>
            </div>

            <form id="transaction-form" action="{{ isset($grn) ? route($routePrefix . '.update', ['transaction' => $grn->id]) : route($routePrefix . '.store') }}" method="POST" autocomplete="off">
                <div class="relative overflow-hidden bg-white border border-gray-200 p-4 sm:rounded-lg mb-4">
                    @if ($errors->any())
                        <div class="bg-red-50 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <ul class="list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @csrf
                    @if(isset($grn))
                        @method('PUT')
                    @endif
                    <input type="hidden" name="type" value="grn">

                    <div class="grid gap-4 sm:grid-cols-2 sm:gap-6">
                        


                        <div>
                            <label for="transaction_date" class="block mb-2 text-sm font-medium text-gray-900">Date</label>
                            <input
                                type="date"
                                name="transaction_date"
                                id="transaction_date"
                                value="{{ old('transaction_date', $default_transaction_date) }}"
                                class="bg-white border text-gray-900 text-sm rounded-lg block w-full p-2.5 {{ $errors->has('transaction_date') ? 'border-red-500' : 'border-gray-300' }}"
                            >
                            @error('transaction_date')
                                <span class="text-red-400 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="supplier_id" class="block mb-2 text-sm font-medium text-gray-900">Supplier</label>
                            <select 
                                id="supplier_id" 
                                name="supplier_id" 
                                class="select2-basic bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5"
                            >
                                <option value="">Select Supplier</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ (old('supplier_id', $grn->supplier_id ?? '') == $supplier->id) ? 'selected' : '' }}>
                                        {{ $supplier->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('supplier_id')
                                <span class="text-red-400 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="purchase_order_id" class="block mb-2 text-sm font-medium text-gray-900">Purchase Order</label>
                            @if(count($purchase_orders) > 0)
                                <select 
                                    id="purchase_order_id" 
                                    name="purchase_order_id" 
                                    class="select2-basic bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5"
                                >
                                    <option value="">Select PO</option>
                                    @foreach($purchase_orders as $po)
                                        <option value="{{ $po->id }}" {{ (old('purchase_order_id', $grn->purchase_order_id ?? '') == $po->id) ? 'selected' : '' }}>
                                            {{ $po->order_no ?? $po->id }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <input 
                                    type="text" 
                                    name="purchase_order_no" 
                                    placeholder="Enter PO No"
                                    value="{{ old('purchase_order_no', $grn->purchase_order_no ?? '') }}"
                                    class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5"
                                >
                            @endif
                            @error('purchase_order_id')
                                <span class="text-red-400 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="received_by" class="block mb-2 text-sm font-medium text-gray-900">Received By</label>
                            <select 
                                id="received_by" 
                                name="received_by" 
                                class="select2-basic bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5"
                            >
                                <option value="">Select Employee</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" {{ (old('received_by', $grn->received_by ?? '') == $employee->id) ? 'selected' : '' }}>
                                        {{ $employee->first_name }} {{ $employee->last_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('received_by')
                                <span class="text-red-400 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="to_warehouse_id" class="block mb-2 text-sm font-medium text-gray-900">To Warehouse</label>
                            <select 
                                id="to_warehouse_id" 
                                name="to_warehouse_id" 
                                class="select2-basic bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5"
                            >
                                <option value="">Select Warehouse</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" {{ (old('to_warehouse_id', $transaction->to_warehouse_id ?? '') == $warehouse->id) ? 'selected' : '' }}>
                                        {{ $warehouse->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('to_warehouse_id')
                                <span class="text-red-400 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                         <div class="sm:col-span-2">
                            <label for="remarks" class="block mb-2 text-sm font-medium text-gray-900">Remarks</label>
                            <textarea 
                                id="remarks" 
                                name="remarks" 
                                rows="2" 
                                class="block p-2.5 w-full text-sm text-gray-900 bg-white rounded-lg border border-gray-300 focus:ring-primary-500 focus:border-primary-500" 
                                placeholder="Add remarks..."
                            >{{ old('remarks', $grn->remarks ?? '') }}</textarea>
                             @error('remarks')
                                <span class="text-red-400 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="amount" class="block mb-2 text-sm font-medium text-gray-900">Total Amount</label>
                            <input
                                type="text"
                                name="amount"
                                id="amount"
                                value="0"
                                readonly
                                class="bg-gray-100 border text-gray-900 text-sm rounded-lg block w-full p-2.5"
                            >
                            @error('amount')
                                <span class="text-red-400 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                        
                    </div>
                </div>

                <div class="relative overflow-hidden bg-white border border-gray-200 p-4 sm:rounded-lg">
                    <div class="sm:col-span-2">
                        <div class="mb-4">
                            <label class="block mb-2 text-sm font-medium text-gray-900">Items</label>
                            <div class="relative overflow-x-auto sm:rounded-lg">
                                <table class="w-full text-sm text-left text-gray-500" id="items-table">
                                    <thead class="text-xs text-gray-700 uppercase bg-blue-200">
                                        <tr>
                                            <th scope="col" class="px-2 py-3" style="width: 2%">
                                                <input type="checkbox" id="select-all-rows" class="w-4 h-4 text-blue-600 bg-white border-gray-300 rounded focus:ring-blue-500">
                                            </th>
                                            <th scope="col" class="px-6 py-3" style="width: 20%">Item</th>
                                            <th scope="col" class="px-6 py-3" style="width: 8%">Qty</th>
                                            <th scope="col" class="px-6 py-3" style="width: 10%">UOM</th>
                                            <th scope="col" class="px-6 py-3" style="width: 10%">Price</th>
                                            <th scope="col" class="px-6 py-3" style="width: 8%">IGST %</th>
                                            <th scope="col" class="px-6 py-3" style="width: 8%">CGST %</th>
                                            <th scope="col" class="px-6 py-3" style="width: 8%">SGST %</th>
                                            <th scope="col" class="px-6 py-3" style="width: 8%">Cess %</th>
                                            <th scope="col" class="px-6 py-3" style="width: 10%">Amount</th>
                                            <th scope="col" class="px-6 py-3" style="width: 10%">Batch</th>
                                            <th scope="col" class="px-6 py-3" style="width: 10%">Lot No</th>
                                            <th scope="col" class="px-6 py-3" style="width: 10%">Serial</th>
                                            <th scope="col" class="px-6 py-3" style="width: 10%">Exp Date</th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $items = $transaction->items ?? [];
                                            if(old('items')) {
                                                $items = collect(old('items'))->map(function($item) {
                                                    return (object) $item;
                                                });
                                            }
                                        @endphp
                                        @forelse($items as $index => $item)
                                            <tr class="bg-white border-b hover:bg-gray-50 text-sm">
                                                <td class="p-2 text-center">
                                                    <input type="checkbox" class="row-checkbox w-4 h-4 text-blue-600 bg-white border-gray-300 rounded focus:ring-blue-500">
                                                </td>
                                                <td class="p-2">
                                                    <select 
                                                        name="items[{{ $index }}][item_id]" 
                                                        class="select2-item item-select w-full"
                                                        data-url="{{ route('inventory.item.select2') }}"
                                                        data-placeholder="Select Item"
                                                    >
                                                        <option value="{{ $item->item_id ?? '' }}" data-tracking="{{ $item->item->tracking ?? '' }}" data-has-expiry="{{ $item->item->has_expiry ?? '' }}" selected>{{ $item->item->name ?? 'Unknown Item' }} ({{ $item->item->code ?? 'N/A' }})</option>
                                                    </select>
                                                    <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id ?? '' }}">
                                                    @error("items.$index.item_id")
                                                        <!-- <span class="text-red-500 text-xs">{{ $message }}</span> -->
                                                    @enderror
                                                </td>
                                                <td class="p-2">
                                                    <input type="number" step="0.01" name="items[{{ $index }}][quantity]" value="{{ $item->quantity ?? '' }}" class="w-full rounded-md focus:ring-blue-500 focus:border-blue-500 qty-input {{ $errors->has('items.'.$index.'.quantity') ? 'border-red-500' : 'border-gray-300' }}">
                                                </td>
                                                <td class="p-2">
                                                    <select 
                                                        name="items[{{ $index }}][uom_id]" 
                                                        class="select2-item uom-select w-full"
                                                        data-url="{{ route('inventory.uom.select2') }}"
                                                        data-placeholder="Select UOM"
                                                    >
                                                        <option value="{{ $item->uom_id ?? '' }}" selected>{{ $item->uom->name ?? 'Unknown' }}</option>
                                                    </select>
                                                </td>
                                                <td class="p-2">
                                                    <input type="number" step="0.01" name="items[{{ $index }}][price]" value="{{ $item->price ?? '' }}" class="w-full rounded-md focus:ring-blue-500 focus:border-blue-500 price-input {{ $errors->has('items.'.$index.'.price') ? 'border-red-500' : 'border-gray-300' }}">
                                                </td>
                                                <td class="p-2">
                                                    <input type="number" step="0.01" name="items[{{ $index }}][igst]" value="{{ $item->igst ?? '' }}" class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 igst-input">
                                                </td>
                                                <td class="p-2">
                                                    <input type="number" step="0.01" name="items[{{ $index }}][cgst]" value="{{ $item->cgst ?? '' }}" class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 cgst-input">
                                                </td>
                                                <td class="p-2">
                                                    <input type="number" step="0.01" name="items[{{ $index }}][sgst]" value="{{ $item->sgst ?? '' }}" class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 sgst-input">
                                                </td>
                                                <td class="p-2">
                                                    <input type="number" step="0.01" name="items[{{ $index }}][cess]" value="{{ $item->cess ?? '' }}" class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 cess-input">
                                                </td>
                                                <td class="p-2">
                                                    <input type="number" step="0.01" value="{{ number_format(($item->quantity ?? 0) * ($item->price ?? 0) * (1 + (($item->igst ?? 0) + ($item->cgst ?? 0) + ($item->sgst ?? 0) + ($item->cess ?? 0)) / 100), 2, '.', '') }}" readonly class="w-full bg-gray-100 border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-right row-amount-input">
                                                </td>
                                                <td class="p-2">
                                                    <input type="text" name="items[{{ $index }}][batch_no]" value="{{ $item->batch_no ?? '' }}" class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 batch-input">
                                                </td>
                                                <td class="p-2">
                                                    <input type="text" name="items[{{ $index }}][lot_no]" value="{{ $item->lot_no ?? '' }}" class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 lot-input">
                                                </td>
                                                <td class="p-2">
                                                    <input type="text" name="items[{{ $index }}][serial_no]" value="{{ $item->serial_no ?? '' }}" class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                                </td>
                                                <td class="p-2">
                                                    <input type="date" name="items[{{ $index }}][exp_date]" value="{{ $item->formatted_exp_date ?? $item->exp_date ?? '' }}" class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 exp-input">
                                                </td>

                                            </tr>
                                        @empty
                                            <tr class="bg-white border-b hover:bg-gray-50 text-sm">
                                                <td class="p-2 text-center">
                                                    <input type="checkbox" class="row-checkbox w-4 h-4 text-blue-600 bg-white border-gray-300 rounded focus:ring-blue-500">
                                                </td>
                                                <td class="p-2">
                                                    <select 
                                                        name="items[0][item_id]" 
                                                        class="select2-item item-select w-full"
                                                        data-url="{{ route('inventory.item.select2') }}"
                                                        data-placeholder="Select Item"
                                                    ></select>
                                                    @error('items.0.item_id')
                                                        <!-- <span class="text-red-500 text-xs">{{ $message }}</span> -->
                                                    @enderror
                                                </td>
                                                <td class="p-2">
                                                    <input type="number" step="0.01" name="items[0][quantity]" class="w-full rounded-md focus:ring-blue-500 focus:border-blue-500 qty-input {{ $errors->has('items.0.quantity') ? 'border-red-500' : 'border-gray-300' }}">
                                                </td>
                                                <td class="p-2">
                                                    <select 
                                                        name="items[0][uom_id]" 
                                                        class="select2-item uom-select w-full"
                                                        data-url="{{ route('inventory.uom.select2') }}"
                                                        data-placeholder="Select UOM"
                                                    ></select>
                                                </td>
                                                <td class="p-2">
                                                    <input type="number" step="0.01" name="items[0][price]" class="w-full rounded-md focus:ring-blue-500 focus:border-blue-500 price-input {{ $errors->has('items.0.price') ? 'border-red-500' : 'border-gray-300' }}">
                                                </td>
                                                <td class="p-2">
                                                    <input type="number" step="0.01" name="items[0][igst]" class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 igst-input">
                                                </td>
                                                <td class="p-2">
                                                    <input type="number" step="0.01" name="items[0][cgst]" class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 cgst-input">
                                                </td>
                                                <td class="p-2">
                                                    <input type="number" step="0.01" name="items[0][sgst]" class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 sgst-input">
                                                </td>
                                                <td class="p-2">
                                                    <input type="number" step="0.01" name="items[0][cess]" class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 cess-input">
                                                </td>
                                                <td class="p-2">
                                                    <input type="number" step="0.01" readonly class="w-full bg-gray-100 border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-right row-amount-input">
                                                </td>
                                                <td class="p-2">
                                                    <input type="text" name="items[0][batch_no]" class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 batch-input">
                                                </td>
                                                <td class="p-2">
                                                    <input type="text" name="items[0][lot_no]" class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 lot-input">
                                                </td>
                                                <td class="p-2">
                                                    <input type="text" name="items[0][serial_no]" class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                                </td>
                                                <td class="p-2">
                                                    <input type="date" name="items[0][exp_date]" class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 exp-input">
                                                </td>

                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="flex gap-2 mt-4 justify-end">
                                <button id="row_add_btn" type="button" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 focus:outline-none">Add Item</button>
                                <button id="delete_selected_btn" type="button" class="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 focus:outline-none">Delete Selected</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>

    @if(isset($grn))
    <div id="popup-modal" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-md max-h-full">
            <div class="relative bg-white rounded-lg shadow border border-gray-200 p-6 text-center">
                <h3 class="mb-5 text-lg font-normal text-gray-700">Are you sure you want to delete this GRN?</h3>
                <div class="flex justify-center gap-4">
                    <button data-modal-hide="popup-modal" type="button" onclick="document.getElementById('data_delete').submit();" class="text-white bg-red-600 hover:bg-red-700 font-medium rounded-lg text-sm px-5 py-2.5 focus:outline-none">
                        Yes, Delete
                    </button>
                    <button data-modal-hide="popup-modal" type="button" class="text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 font-medium rounded-lg text-sm px-5 py-2.5">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div id="delete-rows-modal" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-md max-h-full">
            <div class="relative bg-white rounded-lg shadow border border-gray-200 p-6 text-center">
                <h3 class="mb-5 text-lg font-normal text-gray-700">Are you sure you want to delete the selected items?</h3>
                <div class="flex justify-center gap-4">
                    <button id="confirm-delete-rows" data-modal-hide="delete-rows-modal" type="button" class="text-white bg-red-600 hover:bg-red-700 font-medium rounded-lg text-sm px-5 py-2.5 focus:outline-none">
                        Yes, Delete
                    </button>
                    <button data-modal-hide="delete-rows-modal" type="button" class="text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 font-medium rounded-lg text-sm px-5 py-2.5">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Hidden button to toggle modal -->
    <button id="trigger-delete-modal" data-modal-target="delete-rows-modal" data-modal-toggle="delete-rows-modal" type="button" class="hidden"></button>
@endsection


@push('scripts')
    <script>
        $(document).ready(function() {
            
            // Perform basic select2 init
            $('.select2-basic').select2({
                width: '100%',
                placeholder: "Select an option",
                allowClear: true
            });

            // Tax Logic
            function toggleTaxInputs(row) {
                const igstInput = row.find('.igst-input');
                const cgstInput = row.find('.cgst-input');
                const sgstInput = row.find('.sgst-input');

                const igstVal = parseFloat(igstInput.val()) || 0;
                const cgstVal = parseFloat(cgstInput.val()) || 0;
                const sgstVal = parseFloat(sgstInput.val()) || 0;

                if (igstVal > 0) {
                    cgstInput.val('').prop('disabled', true).addClass('bg-gray-100');
                    sgstInput.val('').prop('disabled', true).addClass('bg-gray-100');
                } else {
                    cgstInput.prop('disabled', false).removeClass('bg-gray-100');
                    sgstInput.prop('disabled', false).removeClass('bg-gray-100');
                }

                if (cgstVal > 0 || sgstVal > 0) {
                    igstInput.val('').prop('disabled', true).addClass('bg-gray-100');
                } else {
                    igstInput.prop('disabled', false).removeClass('bg-gray-100');
                }
            }

            // Tracking Logic
            function updateTrackingInputs(row, trackingType) {
                const batchInput = row.find('.batch-input');
                const lotInput = row.find('.lot-input');

                // Reset
                batchInput.prop('disabled', true).addClass('bg-gray-100');
                lotInput.prop('disabled', true).addClass('bg-gray-100');

                if (trackingType === 'batch') {
                    batchInput.prop('disabled', false).removeClass('bg-gray-100');
                } else if (trackingType === 'batch-lot') {
                     // "if lot both batch and lot is editable"
                    batchInput.prop('disabled', false).removeClass('bg-gray-100');
                    lotInput.prop('disabled', false).removeClass('bg-gray-100');
                }
                // 'not-applicable' or others -> both disabled
            }

            // Expiry Logic
            function updateExpiryInput(row, hasExpiry) {
                 const expInput = row.find('.exp-input');
                 if (hasExpiry == 1 || hasExpiry == 'true' || hasExpiry === true) {
                     expInput.prop('disabled', false).removeClass('bg-gray-100');
                 } else {
                     expInput.val('').prop('disabled', true).addClass('bg-gray-100');
                 }
            }

            function clearRowFields(row) {
                row.find('input').not('[type="hidden"]').val('');
                row.find('select').not('.item-select').val(null).trigger('change');
                // Re-init tax logic to clear/reset disabled states based on empty values
                toggleTaxInputs(row);
                calculateTotalAmount();
            }

            function calculateTotalAmount() {
                let grandTotal = 0;
                
                $('#items-table tbody tr').each(function() {
                    const row = $(this);
                    const qty = parseFloat(row.find('.qty-input').val()) || 0;
                    const price = parseFloat(row.find('.price-input').val()) || 0;
                    
                    if (qty > 0 && price > 0) {
                        const baseAmount = qty * price;
                        
                        const igst = parseFloat(row.find('.igst-input').val()) || 0;
                        const cgst = parseFloat(row.find('.cgst-input').val()) || 0;
                        const sgst = parseFloat(row.find('.sgst-input').val()) || 0;
                        const cess = parseFloat(row.find('.cess-input').val()) || 0;
                        
                        const totalTaxPercent = igst + cgst + sgst + cess;
                        const taxAmount = baseAmount * (totalTaxPercent / 100);
                        const rowTotal = baseAmount + taxAmount;
                        
                        row.find('.row-amount-input').val(rowTotal.toFixed(2));
                        
                        grandTotal += rowTotal;
                    } else {
                        row.find('.row-amount-input').val('');
                    }
                });
                
                $('#amount').val(grandTotal.toFixed(2));
            }

            $('#items-table').on('input', '.qty-input, .price-input, .igst-input, .cgst-input, .sgst-input, .cess-input', function() {
                // If this is a tax input, also toggle exclusivity
                if ($(this).is('.igst-input, .cgst-input, .sgst-input')) {
                     toggleTaxInputs($(this).closest('tr'));
                }
                calculateTotalAmount();
            });

            // Tracking & Expiry Event Listener
            // Use delegation for select2:select event on the table
            $('#items-table').on('select2:select', '.item-select', function(e) {
                const row = $(this).closest('tr');
                const data = e.params.data;
                const trackingType = data.tracking; // From ItemController
                const hasExpiry = data.has_expiry; // From ItemController
                
                clearRowFields(row); // Clear all fields when item changes
                
                updateTrackingInputs(row, trackingType);
                updateExpiryInput(row, hasExpiry);
            });
            
             $('#items-table').on('select2:unselect', '.item-select', function(e) {
                const row = $(this).closest('tr');
                clearRowFields(row); // Clear all fields when item is removed
                
                updateTrackingInputs(row, 'not-applicable');
                updateExpiryInput(row, false);
            });

            $('#items-table tbody tr').each(function() {
                const row = $(this);
                // Tax Init
                toggleTaxInputs(row);

                // Tracking & Expiry Init
                // Check if item is selected and has tracking data attribute (server rendered)
                const selectedOption = row.find('.item-select option:selected');
                if (selectedOption.length > 0) {
                    const trackingType = selectedOption.data('tracking');
                    const hasExpiry = selectedOption.data('has-expiry');
                    
                    if (trackingType) {
                        updateTrackingInputs(row, trackingType);
                    } else {
                         updateTrackingInputs(row, 'not-applicable');
                    }

                    if (hasExpiry !== undefined) {
                        updateExpiryInput(row, hasExpiry);
                    } else {
                        updateExpiryInput(row, false);
                    }

                } else {
                     updateTrackingInputs(row, 'not-applicable');
                     updateExpiryInput(row, false);
                }
            });

            function initSelect2(element) {
                element.select2({
                    ajax: {
                        url: element.data('url'),
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            let itemId = null;
                            if (element.hasClass('uom-select')) {
                                const row = element.closest('tr');
                                itemId = row.find('.item-select').val();
                            }
                            return {
                                q: params.term,
                                page: params.page,
                                item_id: itemId
                            };
                        },
                        processResults: function(data, params) {
                            params.page = params.page || 1;
                            return {
                                results: data.results,
                                pagination: data.pagination
                            };
                        },
                        cache: true
                    },
                    placeholder: element.data('placeholder'),
                    minimumInputLength: 0,
                    allowClear: true,
                    width: '100%' 
                });
            }

            $('.select2-item').each(function() {
                initSelect2($(this));
            });



            // Select All logic
            $('#select-all-rows').change(function() {
                const isChecked = $(this).is(':checked');
                $('.row-checkbox').prop('checked', isChecked);
            });

            // Individual checkbox logic to update select all
            $('#items-table').on('change', '.row-checkbox', function() {
                const allChecked = $('.row-checkbox').length === $('.row-checkbox:checked').length;
                $('#select-all-rows').prop('checked', allChecked);
            });

            // Delete selected logic
            $('#delete_selected_btn').click(function() {
                const selectedRows = $('.row-checkbox:checked').closest('tr');
                
                if (selectedRows.length === 0) return;

                // Trigger the modal
                $('#trigger-delete-modal').click();
            });

            // Confirm delete rows
            $('#confirm-delete-rows').click(function() {
                const selectedRows = $('.row-checkbox:checked').closest('tr');
                selectedRows.remove();
                calculateTotalAmount();
                $('#select-all-rows').prop('checked', false);

                if ($('#items-table tbody tr').length === 0) {
                    $('#row_add_btn').click();
                }
            });

            // function checkDeleteButtonVisibility() {
            //     const checkedCount = $('.row-checkbox:checked').length;
            //     if (checkedCount > 0) {
            //         $('#delete_selected_btn').removeClass('hidden');
            //     } else {
            //         $('#delete_selected_btn').addClass('hidden');
            //     }
            // }

            $('#row_add_btn').click(() => {
                const tableBody = $('#items-table tbody');
                const uniqueIndex = Date.now();
                const newRow = `
                    <tr class="bg-white border-b hover:bg-gray-50 text-sm">
                        <td class="p-2 text-center">
                            <input type="checkbox" class="row-checkbox w-4 h-4 text-blue-600 bg-white border-gray-300 rounded focus:ring-blue-500">
                        </td>
                        <td class="p-2">
                             <select 
                                name="items[${uniqueIndex}][item_id]" 
                                class="select2-item item-select w-full"
                                data-url="{{ route('inventory.item.select2') }}"
                                data-placeholder="Select Item"
                            ></select>
                        </td>
                        <td class="p-2">
                            <input type="number" step="0.01" name="items[${uniqueIndex}][quantity]" class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 qty-input">
                        </td>
                        <td class="p-2">
                            <select 
                                name="items[${uniqueIndex}][uom_id]" 
                                class="select2-item uom-select w-full"
                                data-url="{{ route('inventory.uom.select2') }}"
                                data-placeholder="Select UOM"
                            ></select>
                        </td>
                        <td class="p-2">
                            <input type="number" step="0.01" name="items[${uniqueIndex}][price]" class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 price-input">
                        </td>
                        <td class="p-2">
                            <input type="number" step="0.01" name="items[${uniqueIndex}][igst]" class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 igst-input">
                        </td>
                        <td class="p-2">
                            <input type="number" step="0.01" name="items[${uniqueIndex}][cgst]" class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 cgst-input">
                        </td>
                        <td class="p-2">
                            <input type="number" step="0.01" name="items[${uniqueIndex}][sgst]" class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 sgst-input">
                        </td>
                        <td class="p-2">
                            <input type="number" step="0.01" name="items[${uniqueIndex}][cess]" class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 cess-input">
                        </td>
                        <td class="p-2">
                            <input type="number" step="0.01" readonly class="w-full bg-gray-100 border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-right row-amount-input">
                        </td>
                        <td class="p-2">
                            <input type="text" name="items[${uniqueIndex}][batch_no]" class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 batch-input">
                        </td>
                        <td class="p-2">
                            <input type="text" name="items[${uniqueIndex}][lot_no]" class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 lot-input">
                        </td>
                        <td class="p-2">
                            <input type="text" name="items[${uniqueIndex}][serial_no]" class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        </td>
                        <td class="p-2">
                            <input type="date" name="items[${uniqueIndex}][exp_date]" class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 exp-input">
                        </td>

                    </tr>
                `;
                const $newRow = $(newRow);
                tableBody.append($newRow);
                
                $newRow.find('.select2-item').each(function() {
                    initSelect2($(this));
                });
            });

            // Calculate total on load
            calculateTotalAmount();

             // Auto focus on first error
             const firstErrorInput = $('.border-red-500, .is-invalid').first();
             if (firstErrorInput.length) {
                 // Check if it's a select2
                 if (firstErrorInput.hasClass('select2-hidden-accessible')) {
                     firstErrorInput.select2('open');
                 } else {
                     firstErrorInput.focus();
                 }
                 
                 // Scroll to it
                 $('html, body').animate({
                    scrollTop: firstErrorInput.offset().top - 100
                 }, 500);
             }

        });
    </script>    
@endpush
