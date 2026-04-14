@extends('layouts.app')

@section('title', 'Stock Valuation Report')

@section('content')
<section class="bg-gray-50">
    <div class="mx-auto">
        <div class="block md:flex justify-between mb-4 items-center">
            <h3 class="text-2xl font-medium font-sans">Stock Valuation Report</h3>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg shadow-sm mb-6 p-4">
            {{-- Filters --}}
            <form id="valuation-filter-form" class="flex flex-wrap gap-4 items-end">
                
                <div class="flex-1 min-w-[200px]">
                    <label class="block mb-2 text-sm font-medium text-gray-900">Valuation Method <span class="text-red-500">*</span></label>
                    <select class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5" name="method" id="method" required>
                        <option value="FIFO">FIFO (First In First Out)</option>

                        <option value="WEIGHTED_AVERAGE">Weighted Average</option>
                    </select>
                </div>

                <div class="flex-1 min-w-[200px]">
                    <label class="block mb-2 text-sm font-medium text-gray-900">Date (upto) <span class="text-red-500">*</span></label>
                    <input type="date" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5" name="date" id="date" value="{{ date('Y-m-d') }}" required>
                </div>

                <div class="flex-1 min-w-[200px]">
                    <label class="block mb-2 text-sm font-medium text-gray-900">Warehouse <span class="text-red-500">*</span></label>
                    <select class="select2 w-full" name="warehouse_ids[]" id="warehouse_ids" multiple data-placeholder="All Warehouses">
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex-1 min-w-[200px]">
                    <label class="block mb-2 text-sm font-medium text-gray-900">Item (Optional)</label>
                    <select class="select2 w-full" name="item_ids[]" id="item_ids" multiple data-placeholder="All Items">
                        @foreach($items as $item)
                            <option value="{{ $item->id }}">{{ $item->code }} - {{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="pb-0.5 flex gap-2">
                    <button type="button" id="btn-generate" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 flex items-center gap-2">
                        <i class="fa-solid fa-search"></i> Generate
                    </button>
                    
                     <button type="button" id="btn-export-pdf" class="hidden text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 flex items-center gap-2">
                        <i class="fa-regular fa-file-pdf"></i> PDF
                    </button>

                     <button type="button" id="btn-export-excel" class="hidden text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 flex items-center gap-2">
                        <i class="fa-solid fa-table"></i> Excel
                    </button>
                </div>
            </form>
        </div>

        {{-- Results Container --}}
        <div id="report_container" class="space-y-6 pb-10">
            <!-- Initial State -->
                <div class="relative overflow-hidden bg-white border border-gray-200 sm:rounded-lg">
                <div class="flex items-center justify-center h-[calc(100vh-300px)] text-gray-500">
                    <div class="text-center">
                        {{-- <i class="fa-regular fa-clock text-4xl mb-3 opacity-50"></i> --}}
                        <p>Select filters and click Generate to view the Stock Valuation Report.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Redundant Select2 init if layout handles it, but specific ID targeting is safer
        $('#warehouse_ids').select2({ placeholder: 'All Warehouses', width: '100%' });
        $('#item_ids').select2({ placeholder: 'All Items', width: '100%' });

        $('#btn-generate').on('click', function() {
            let formData = {
                method: $('#method').val(),
                date: $('#date').val(),
                warehouse_ids: $('#warehouse_ids').val(),
                item_ids: $('#item_ids').val()
            };

            // Basic Validation
            if (!formData.date) {
                toastr.error('Please select a date.');
                return;
            }

            if (!formData.warehouse_ids || formData.warehouse_ids.length === 0) {
                toastr.error('Please select at least one warehouse.');
                return;
            }

            // Hide export buttons
            $('#btn-export-pdf, #btn-export-excel').addClass('hidden');

            const container = $('#report_container');
            container.html(`
                <div class="relative overflow-hidden bg-white border border-gray-200 sm:rounded-lg">
                    <div class="flex items-center justify-center h-[calc(100vh-300px)]">
                        <div class="h-8 w-8 animate-spin rounded-full border-4 border-solid border-primary-500 border-t-transparent"></div>
                    </div>
                </div>
            `);

            $.ajax({
                url: "{{ route('inventory.stock-valuation.generate') }}",
                type: "GET",
                data: formData,
                success: function(response) {
                    if (response.data.length === 0) {
                         container.html(`
                            <div class="relative overflow-hidden bg-white border border-gray-200 sm:rounded-lg">
                                <div class="flex items-center justify-center h-[calc(100vh-400px)] text-gray-500">
                                    <div class="text-center">
                                        <p>No stock found for the selected criteria.</p>
                                    </div>
                                </div>
                            </div>
                        `);
                    } else {
                        // Show export buttons
                        $('#btn-export-pdf, #btn-export-excel').removeClass('hidden');

                        let rows = '';
                        let grandTotal = 0;

                        response.data.forEach(item => {
                            let qty = parseFloat(item.quantity).toFixed(2);
                            let rate = parseFloat(item.rate).toFixed(2);
                            let val = parseFloat(item.value).toFixed(2);
                            grandTotal += parseFloat(item.value);

                            rows += `
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                        ${item.item_code}
                                    </td>
                                    <td class="px-6 py-4">
                                        ${item.item_name}
                                    </td>
                                    <td class="px-6 py-4">
                                        ${item.warehouse_name}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        ${item.uom}
                                    </td>
                                    <td class="px-6 py-4 text-right font-mono">
                                        ${qty}
                                    </td>
                                    <td class="px-6 py-4 text-right font-mono">
                                        ${rate}
                                    </td>
                                    <td class="px-6 py-4 text-right font-bold font-mono">
                                        ${val}
                                    </td>
                                </tr>
                            `;
                        });

                        let tableHtml = `
                            <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                                <table class="w-full text-sm text-left text-gray-500">
                                    <thead class="text-xs text-gray-700 uppercase bg-blue-100">
                                        <tr>
                                            <th scope="col" class="px-6 py-3">Item Code</th>
                                            <th scope="col" class="px-6 py-3">Item Name</th>
                                            <th scope="col" class="px-6 py-3">Warehouse</th>
                                            <th scope="col" class="px-6 py-3 text-center">UOM</th>
                                            <th scope="col" class="px-6 py-3 text-right">Quantity</th>
                                            <th scope="col" class="px-6 py-3 text-right">Rate</th>
                                            <th scope="col" class="px-6 py-3 text-right">Total Value</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${rows}
                                    </tbody>
                                    <tfoot>
                                        <tr class="font-semibold text-gray-900 bg-gray-50">
                                            <th scope="row" colspan="6" class="px-6 py-3 text-right text-base">Grand Total:</th>
                                            <td class="px-6 py-3 text-right text-base font-bold font-mono">${grandTotal.toFixed(2)}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        `;
                        container.html(tableHtml);
                    }
                },
                error: function(xhr) {
                    console.error(xhr);
                    toastr.error('An error occurred while generating the report.');
                    container.html(`<div class="p-10 text-center text-red-500 bg-white border border-gray-200 rounded-lg">Error loading data.</div>`);
                }
            });
        });

        $('#btn-export-pdf').on('click', function() {
            let formData = $('#valuation-filter-form').serialize();
            let url = "{{ route('inventory.stock-valuation.export-pdf') }}?" + formData;
            window.open(url, '_blank');
        });

        $('#btn-export-excel').on('click', function() {
            let formData = $('#valuation-filter-form').serialize();
            let url = "{{ route('inventory.stock-valuation.export-excel') }}?" + formData;
            window.open(url, '_blank');
        });

    });
</script>
@endpush

@push('styles')
<style>
    .select2-container .select2-selection--multiple {
        min-height: 42px !important;
        border-color: #D1D5DB !important; 
        border-radius: 0.5rem !important;
        padding: 4px;
    }
</style>
@endpush
