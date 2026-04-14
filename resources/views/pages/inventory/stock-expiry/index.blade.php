@extends('layouts.app')

@section('title', 'Stock Expiry Report')

@section('content')
    <section class="bg-gray-50">
        <div class="mx-auto">
            <div class="block md:flex justify-between mb-4 items-center">
                <h3 class="text-2xl font-medium font-sans">Stock Expiry Report</h3>
                
                <div class="block md:flex gap-2 items-center">
                    <form id="search_form" action="#" method="GET" class="">
                        <!-- Hidden search for now, or implement client side filter -->
                    </form>
                    <div id="export_buttons" class="hidden flex gap-2">
                        <div class="pb-0.5">
                             <button type="button" id="btn_export_pdf" class="text-white bg-gray-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center flex justify-center items-center gap-2">
                                <i class="fa-regular fa-file-pdf"></i> Export PDF
                            </button>
                        </div>
                        <div class="pb-0.5">
                             <button type="button" id="btn_export_excel" class="text-white bg-green-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center flex justify-center items-center gap-2">
                                <i class="fa-solid fa-table"></i> Export EXCEL
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm mb-6 p-4">
                <form id="filter_form" class="flex flex-wrap gap-4 items-end">
                    <div class="flex-1 min-w-[200px]">
                        <label for="warehouse_id" class="block mb-2 text-sm font-medium text-gray-900">Warehouse <span class="text-red-500">*</span></label>
                        <select id="warehouse_id" name="warehouse_id" class="select2 w-full" style="width: 100%" data-placeholder="Select Warehouse">
                            <option value="">Select Warehouse</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex-1 min-w-[200px]">
                        <label for="item_id" class="block mb-2 text-sm font-medium text-gray-900">Items (Optional)</label>
                        <select id="item_id" name="item_id[]" multiple="multiple" class="select2-item w-full" style="width: 100%" data-url="{{ route('inventory.item.select2') }}" data-placeholder="Select Items">
                            <!-- Options populated via AJAX -->
                        </select>
                    </div>


                    <div class="w-40">
                        <label for="date_range_type" class="block mb-2 text-sm font-medium text-gray-900">Expiry Range</label>
                        <select id="date_range_type" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5" name="date_range_type">
                            <option value="all" selected>All Dates</option>
                            <option value="this_month">Expiring This Month</option>
                            <option value="next_month">Expiring Next Month</option>
                            <option value="this_year">Expiring This Year</option>
                            <option value="custom">Custom Range</option>
                        </select>
                    </div>


                    <div id="custom_date_container" class="contents hidden">
                        <div class="w-40">
                            <label for="date_from" class="block mb-2 text-sm font-medium text-gray-900">From Date</label>
                            <input type="date" id="date_from" name="date_from" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
                        </div>
                        <div class="w-40">
                            <label for="date_to" class="block mb-2 text-sm font-medium text-gray-900">To Date</label>
                            <input type="date" id="date_to" name="date_to" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
                        </div>
                    </div>

                    <div class="pb-0.5">
                         <button type="submit" class="text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center flex justify-center items-center gap-2">
                            <i class="fa-solid fa-filter"></i> Generate
                        </button>
                    </div>
                </form>
            </div>
            <div id="report_container" class="space-y-6 pb-10">
                <!-- Initial State -->
                 <div class="relative overflow-hidden bg-white border border-gray-200 sm:rounded-lg">
                    <div class="flex items-center justify-center h-[calc(100vh-300px)] text-gray-500">
                        <div class="text-center">
                            <i class="fa-regular fa-clock text-4xl mb-3 opacity-50"></i>
                            <p>Select filters and click Generate to view the Stock Expiry Report.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection

@push('styles')
<style>
    .select2-container .select2-selection--multiple {
        min-height: 42px !important;
        border-color: #D1D5DB !important;
        border-radius: 0.5rem !important;
        padding-top: 4px; 
        padding-bottom: 4px;
        display: flex !important;
        align-items: center !important; 
    }
    .select2-container .select2-search--inline .select2-search__field {
        margin-top: 0 !important; 
        height: 24px !important; 
        line-height: 24px !important;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        margin-top: 0 !important; 
        margin-bottom: 0 !important;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        $('#item_id').select2({
            ajax: {
                url: "{{ route('inventory.item.select2') }}",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term,
                        has_expiry: true,
                        page: params.page
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.results,
                        pagination: {
                            more: data.pagination.more
                        }
                    };
                },
                cache: true
            },
            placeholder: 'Select Items',
            minimumInputLength: 0,
            allowClear: true,
            closeOnSelect: false 
        });

        // Date Logic
        const setDates = (type) => {
            const today = new Date();
            let fromDate = '', toDate = '';
            const formatDate = (date) => {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            };

            if (type === 'this_month') {
                fromDate = formatDate(new Date(today.getFullYear(), today.getMonth(), 1));
                toDate = formatDate(new Date(today.getFullYear(), today.getMonth() + 1, 0));
            } else if (type === 'next_month') {
                fromDate = formatDate(new Date(today.getFullYear(), today.getMonth() + 1, 1));
                toDate = formatDate(new Date(today.getFullYear(), today.getMonth() + 2, 0));
            } else if (type === 'this_year') {
                fromDate = formatDate(new Date(today.getFullYear(), 0, 1));
                toDate = formatDate(new Date(today.getFullYear(), 11, 31));
            }else if (type === 'all') {
                fromDate = '';
                toDate = '';
            }

            if (type !== 'custom') {
                $('#date_from').val(fromDate);
                $('#date_to').val(toDate);
                $('#custom_date_container').addClass('hidden');
            } else {
                $('#custom_date_container').removeClass('hidden');
            }
        };

        $('#date_range_type').change(function() {
            setDates($(this).val());
        });

        // setDates('all'); // Default

        const generateReport = () => {
            const formData = $('#filter_form').serializeArray();

            $('#export_buttons').addClass('hidden');

            const container = $('#report_container');
            container.html(`
                <div class="relative overflow-hidden bg-white border border-gray-200 sm:rounded-lg">
                    <div class="flex items-center justify-center h-[calc(100vh-300px)]">
                        <div class="h-8 w-8 animate-spin rounded-full border-4 border-solid border-primary-500 border-t-transparent"></div>
                    </div>
                </div>
            `);

            $.ajax({
                url: "{{ route('inventory.stock-expiry.index') }}",
                type: "GET",
                data: $.param(formData),
                success: function(response) {
                    if (response.status === 'success') {
                        renderReport(response.data);
                         $('#export_buttons').removeClass('hidden');
                    } else {
                        toastr.error('Failed to generate report');
                        container.html(`<div class="p-10 text-center text-red-500 bg-white border border-gray-200 rounded-lg">Error loading data.</div>`);
                    }
                },
                error: function(xhr) {
                    toastr.error('An error occurred.');
                    console.error(xhr);
                    container.html(`<div class="p-10 text-center text-red-500 bg-white border border-gray-200 rounded-lg">An error occurred. check console.</div>`);
                }
            });
        };

        $('#filter_form').submit(function(e) {
            e.preventDefault();
            generateReport();
        });

        const renderReport = (data) => {
            const container = $('#report_container');
            container.empty();

            if (data.length === 0) {
                 container.html(`
                    <div class="relative overflow-hidden bg-white border border-gray-200 sm:rounded-lg">
                        <div class="flex items-center justify-center h-[calc(100vh-300px)] text-gray-500">
                             <div class="text-center">
                                <p>No stock found with expiry dates for the selected criteria.</p>
                            </div>
                        </div>
                    </div>
                `);
                return;
            }

            let rowsHtml = '';
            
            data.forEach(item => {
                let statusClass = 'bg-gray-100 text-gray-800';
                let statusText = 'Valid';
                
                if (item.status === 'expired') {
                    statusClass = 'bg-red-100 text-red-800 border-red-200';
                    statusText = 'Expired';
                } else if (item.status === 'nearing_expiry') {
                    statusClass = 'bg-yellow-100 text-yellow-800 border-yellow-200';
                    statusText = 'Expiring Soon';
                } else {
                     statusClass = 'bg-green-100 text-green-800 border-green-200';
                }

                rowsHtml += `
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                            ${item.item_name}
                            <div class="text-xs text-gray-500 font-normal">${item.item_code}</div>
                        </td>
                         <td class="px-6 py-4 font-mono text-xs">
                            ${item.batch_no}
                        </td>
                        <td class="px-6 py-4 text-right">
                            ${item.quantity} ${item.uom}
                        </td>
                        <td class="px-6 py-4">
                             ${item.exp_date}
                        </td>
                        <td class="px-6 py-4 text-right">
                             ${item.days_left}
                        </td>
                        <td class="px-6 py-4">
                            <span class="${statusClass} text-xs font-medium px-2.5 py-0.5 rounded border">
                                ${statusText}
                            </span>
                        </td>
                    </tr>
                `;
            });

            const tableHtml = `
                <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-blue-100">
                            <tr>
                                <th scope="col" class="px-6 py-3">Item</th>
                                <th scope="col" class="px-6 py-3">Batch No</th>
                                <th scope="col" class="px-6 py-3 text-right">Quantity</th>
                                <th scope="col" class="px-6 py-3">Expiry Date</th>
                                <th scope="col" class="px-6 py-3 text-right">Days Left</th>
                                <th scope="col" class="px-6 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${rowsHtml}
                        </tbody>
                    </table>
                </div>
            `;
            
            container.append(tableHtml);
        }

        $('#btn_export_pdf').click(function(e){
            e.preventDefault();
            const formData = $('#filter_form').serialize();
            const url = "{{ route('inventory.stock-expiry.export-pdf') }}?" + formData;
            window.open(url, '_blank');
        });

        $('#btn_export_excel').click(function(e){
            e.preventDefault();
            const formData = $('#filter_form').serialize();
            const url = "{{ route('inventory.stock-expiry.export-excel') }}?" + formData;
            window.open(url, '_blank');
        });
    });
</script>
@endpush
