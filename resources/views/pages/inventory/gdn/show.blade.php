@extends('layouts.app')

@section('content')
    <section class="bg-gray-50">
        <div class="mx-auto">
            <div class="flex justify-between mb-4">
                <h3 class="text-2xl font-medium font-sans">
                    {{ isset($transaction) ? 'Edit GDN' : 'Create GDN' }}
                </h3>
                <div class="flex items-center gap-2">
                    <a href="{{ route($routePrefix . '.index') }}" 
                        class="inline-flex items-center justify-center text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700"
                    >
                        Back
                    </a>
                    @if(isset($gdn))
                        <form id="data_delete" action="{{ route($routePrefix . '.delete', ['transaction' => $gdn->id]) }}" method="POST" class="contents">
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

            <form id="transaction-form" action="{{ isset($gdn) ? route($routePrefix . '.update', ['transaction' => $gdn->id]) : route($routePrefix . '.store') }}" method="POST" autocomplete="off">
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
                    @if(isset($gdn))
                        @method('PUT')
                    @endif
                    <input type="hidden" name="type" value="gdn">

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
                            <label for="customer_id" class="block mb-2 text-sm font-medium text-gray-900">Customer</label>
                            <select 
                                id="customer_id" 
                                name="customer_id" 
                                class="select2-basic bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5"
                            >
                                <option value="">Select Customer</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ (old('customer_id', $gdn->customer_id ?? '') == $customer->id) ? 'selected' : '' }}>
                                        {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                           
                        </div>

                        <div>
                            <label for="invoice_id" class="block mb-2 text-sm font-medium text-gray-900">Invoice</label>
                            <select 
                                id="invoice_id" 
                                name="invoice_id" 
                                class="select2-basic bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5"
                            >
                                <option value="">Select Invoice</option>
                                @foreach($invoices as $invoice)
                                    <option value="{{ $invoice->id }}" {{ (old('invoice_id', $gdn->invoice_id ?? '') == $invoice->id) ? 'selected' : '' }}>
                                        {{ $invoice->doc_no }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="from_warehouse_id" class="block mb-2 text-sm font-medium text-gray-900">From Warehouse</label>
                            <select 
                                id="from_warehouse_id" 
                                name="from_warehouse_id" 
                                class="select2-basic bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5"
                            >
                                <option value="">Select Warehouse</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" {{ (old('from_warehouse_id', $transaction->from_warehouse_id ?? '') == $warehouse->id) ? 'selected' : '' }}>
                                        {{ $warehouse->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('from_warehouse_id')
                                <span class="text-red-400 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="issued_by" class="block mb-2 text-sm font-medium text-gray-900">Issued By</label>
                             <select 
                                id="issued_by" 
                                name="issued_by" 
                                class="select2-basic bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5"
                            >
                                <option value="">Select Employee</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" {{ (old('issued_by', $gdn->issued_by ?? '') == $employee->id) ? 'selected' : '' }}>
                                        {{ $employee->first_name }} {{ $employee->last_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label for="way_bill_no" class="block mb-2 text-sm font-medium text-gray-900">Way Bill No</label>
                            <input
                                type="text"
                                name="way_bill_no"
                                id="way_bill_no"
                                value="{{ old('way_bill_no', $gdn->way_bill_no ?? '') }}"
                                class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5"
                            >
                        </div>

                         <div class="sm:col-span-2">
                             <label for="remarks" class="block mb-2 text-sm font-medium text-gray-900">Remarks</label>
                            <textarea
                                id="remarks"
                                name="remarks"
                                rows="2"
                                class="block p-2.5 w-full text-sm text-gray-900 bg-white rounded-lg border border-gray-300 focus:ring-primary-500 focus:border-primary-500"
                            >{{ old('remarks', $gdn->remarks ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden bg-white border border-gray-200 p-4 sm:rounded-lg">
                    <div class="sm:col-span-2">
                        <div class="mb-4">
                            <label class="block mb-2 text-sm font-medium text-gray-900">Items</label>
                            <div class="relative overflow-x-auto sm:rounded-lg">
                                <table class="w-full text-sm text-left text-gray-500" id="items-table">
                                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-2 py-3" style="width: 2%">
                                                <input type="checkbox" id="select-all-rows" class="w-4 h-4 text-blue-600 bg-white border-gray-300 rounded focus:ring-blue-500">
                                            </th>
                                            <th scope="col" class="px-6 py-3" style="width: 30%">Item</th>
                                            <th scope="col" class="px-6 py-3" style="width: 10%">Qty</th>
                                            <th scope="col" class="px-6 py-3" style="width: 15%">UOM</th>
                                            <th scope="col" class="px-6 py-3" style="width: 15%">Batch</th>
                                            <th scope="col" class="px-6 py-3" style="width: 15%">Serial</th>
                                            <th scope="col" class="px-6 py-3" style="width: 15%">Exp Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $items = $transaction->items ?? [];
                                            if(old('items')) {
                                                $items = collect(old('items'))->map(function($item) {
                                                    $itemObj = (object) $item;
                                                    if(isset($itemObj->item_id)) {
                                                        $itemObj->item = \App\Models\Inventory\Item::find($itemObj->item_id);
                                                    }
                                                    if(isset($itemObj->uom_id)) {
                                                        $itemObj->uom = \App\Models\Inventory\Uom::find($itemObj->uom_id);
                                                    }
                                                    return $itemObj;
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
                                                        <option value="{{ $item->item_id ?? '' }}" selected>{{ $item->item->name ?? 'Unknown Item' }} ({{ $item->item->code ?? 'N/A' }})</option>
                                                    </select>
                                                    <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id ?? '' }}">
                                                </td>
                                                <td class="p-2">
                                                    <input 
                                                        type="number" 
                                                        step="0.01" 
                                                        name="items[{{ $index }}][quantity]" 
                                                        value="{{ $errors->has('items.'.$index.'.quantity') ? '' : ($item->quantity ?? '') }}" 
                                                        class="w-full rounded-md focus:ring-blue-500 focus:border-blue-500 {{ $errors->has('items.'.$index.'.quantity') ? 'border-red-500' : 'border-gray-300' }}"
                                                    >
                                                    <!-- @error('items.'.$index.'.quantity')
                                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                                    @enderror -->
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
                                                    <input type="text" name="items[{{ $index }}][batch_no]" value="{{ $item->batch_no ?? '' }}" class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                                </td>
                                                <td class="p-2">
                                                    <input type="text" name="items[{{ $index }}][serial_no]" value="{{ $item->serial_no ?? '' }}" class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                                </td>
                                                <td class="p-2">
                                                    <input type="date"  name="items[{{ $index }}][exp_date]" value="{{ $item->formatted_exp_date ?? $item->exp_date ?? '' }}" class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                                </td>
                                            </tr>
                                        @empty
                                            <!-- Empty state handled by JS on load if needed, or just keep one empty row -->
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
                                                </td>
                                                <td class="p-2">
                                                    <input type="number" step="0.01" name="items[0][quantity]" class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
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
                                                    <input type="text" name="items[0][batch_no]" class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                                </td>
                                                <td class="p-2">
                                                    <input type="text" name="items[0][serial_no]" class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                                </td>
                                                <td class="p-2">
                                                    <input type="date" name="items[0][exp_date]" class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
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

    @if(isset($gdn))
    <div id="popup-modal" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-md max-h-full">
            <div class="relative bg-white rounded-lg shadow border border-gray-200 p-6 text-center">
                <h3 class="mb-5 text-lg font-normal text-gray-700">Are you sure you want to delete this GDN?</h3>
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

             // When item changes, clear UOM
            $('#items-table').on('select2:select', '.item-select', function(e) {
                const row = $(this).closest('tr');
                row.find('.uom-select').val(null).trigger('change');
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
                $('#select-all-rows').prop('checked', false);

                if ($('#items-table tbody tr').length === 0) {
                    $('#row_add_btn').click();
                }
            });

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
                            <input type="number" step="0.01" name="items[${uniqueIndex}][quantity]" class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
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
                            <input type="text" name="items[${uniqueIndex}][batch_no]" class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        </td>
                        <td class="p-2">
                            <input type="text" name="items[${uniqueIndex}][serial_no]" class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        </td>
                        <td class="p-2">
                            <input type="date" name="items[${uniqueIndex}][exp_date]"  class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        </td>
                    </tr>
                `;
                const $newRow = $(newRow);
                tableBody.append($newRow);
                
                $newRow.find('.select2-item').each(function() {
                    initSelect2($(this));
                });
            });

             // Auto focus on first error
             const firstErrorInput = $('.border-red-500, .is-invalid').first();
             if (firstErrorInput.length) {
                 if (firstErrorInput.hasClass('select2-hidden-accessible')) {
                     firstErrorInput.select2('open');
                 } else {
                     firstErrorInput.focus();
                 }
                 
                 $('html, body').animate({
                    scrollTop: firstErrorInput.offset().top - 100
                 }, 500);
             }
        });
    </script>    
@endpush
