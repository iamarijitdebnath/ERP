@extends('layouts.app')

@section('title', 'Stock Register')

@section('content')
    <section class="bg-gray-50">
        <div class="mx-auto">
            <div class="block md:flex justify-between mb-4 items-center">
                <h3 class="text-2xl font-medium font-sans">Stock Register</h3>
                
                <div class="block md:flex gap-2 items-center">
                    <form id="search_form" action="#" method="GET" class="">
                        <div class="relative md:w-64 w-full">
                            <div class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-500" fill="currentColor"
                                    viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z">
                                    </path>
                                </svg>
                            </div>
                            <input
                                id="data_search"
                                type="text"
                                name="q"
                                value="{{ request('q') }}"
                                class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg outline-none block w-full pl-10 p-2.5"
                                placeholder="Search Items..."
                            />
                        </div>
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
                        <label for="warehouse_id" class="block mb-2 text-sm font-medium text-gray-900">Warehouse</label>
                        <select id="warehouse_id" name="warehouse_id" class="select2 w-full" style="width: 100%" data-placeholder="Select Warehouse">
                            <option value="">All Warehouses</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1 min-w-[200px]">
                        <label for="item_id" class="block mb-2 text-sm font-medium text-gray-900">Item</label>
                        <select id="item_id" name="item_id" class="select2-item w-full" style="width: 100%" data-url="{{ route('inventory.item.select2') }}" data-placeholder="Select Item">
                            <!-- Options populated via AJAX -->
                        </select>
                    </div>


                    <div class="w-40">
                        <label for="date_range_type" class="block mb-2 text-sm font-medium text-gray-900">Period</label>
                        <select id="date_range_type" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
                            <option value="this_week">This Week</option>
                            <option value="this_month" selected>This Month</option>
                            <option value="this_year">This Year</option>
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
                {{-- <div class="relative overflow-hidden bg-white border border-gray-200 sm:rounded-lg">
                    <div class="flex items-center justify-center h-[calc(100vh-300px)] text-gray-500">
                        <div class="text-center">
                            <i class="fa-regular fa-file-lines text-4xl mb-3 opacity-50"></i>
                            <p>Select filters and click Generate to view the Stock Register.</p>
                        </div>
                    </div>
                </div> --}}
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
        const setDates = (type) => {
            const today = new Date();
            let fromDate, toDate;
            const formatDate = (date) => date.toISOString().split('T')[0];

            if (type === 'this_week') {
                const first = today.getDate() - today.getDay(); 
                const last = first + 6; 
                let curr = new Date(); 
                let firstD = curr.getDate() - curr.getDay(); 
                let firstDayDate = new Date(curr.setDate(firstD));
                let lastDayDate = new Date(curr.setDate(firstD+6));
                fromDate = formatDate(firstDayDate);
                toDate = formatDate(lastDayDate);
            } else if (type === 'this_month') {
                fromDate = formatDate(new Date(today.getFullYear(), today.getMonth(), 1));
                toDate = formatDate(new Date(today.getFullYear(), today.getMonth() + 1, 0));
            } else if (type === 'this_year') {
                fromDate = formatDate(new Date(today.getFullYear(), 0, 1));
                toDate = formatDate(new Date(today.getFullYear(), 11, 31));
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

        setDates('this_month');

        const generateReport = () => {
            const formData = $('#filter_form').serializeArray();
            formData.push({name: 'q', value: $('#data_search').val()});

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
                url: "{{ route('inventory.stock-register.index') }}",
                type: "GET",
                data: $.param(formData),
                success: function(response) {
                    if (response.status === 'success') {
                        renderReport(response.data);
                        $('#export_buttons').removeClass('hidden');
                    } else if (response.status === 'error') {
                        toastr.error(response.message);
                        container.html(`<div class="p-10 text-center text-red-500 bg-white border border-gray-200 rounded-lg">${response.message}</div>`);
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

        let debounceTimer;
        $('#data_search').on('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                generateReport();
            }, 500);
        });

        const renderReport = (data) => {
            const container = $('#report_container');
            container.empty();

            if (data.length === 0) {
                 container.html(`
                    <div class="relative overflow-hidden bg-white border border-gray-200 sm:rounded-lg">
                        <div class="flex items-center justify-center h-[calc(100vh-300px)] text-gray-500">
                             <div class="text-center">
                                <p>No records found for the selected criteria.</p>
                            </div>
                        </div>
                    </div>
                `);
                return;
            }

            data.forEach(itemGroup => {
                const item = itemGroup.item;
                const txs = itemGroup.transactions;
                const opBalance = itemGroup.opening_balance;

                let rowsHtml = '';
                rowsHtml += `
                    <tr class="bg-gray-50 font-medium text-gray-700">
                        <td class="px-4 py-3" colspan="5">Opening Balance</td>
                        <td class="px-4 py-3 text-right">-</td>
                        <td class="px-4 py-3 text-right">-</td>
                        <td class="px-4 py-3 text-right">${opBalance}</td>
                    </tr>
                `;

                txs.forEach(tx => {
                    rowsHtml += `
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap">${tx.date}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">${tx.doc_no || '-'}</td>
                            <td class="px-4 py-3">
                                <span class="bg-blue-100 text-blue-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded border border-blue-400">
                                    ${tx.type}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">${tx.from || '-'}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">${tx.to || '-'}</td>

                            <td class="px-4 py-3 text-right font-medium text-green-600">${tx.in !== '-' ? tx.in : '-'}</td>
                            <td class="px-4 py-3 text-right font-medium text-red-600">${tx.out !== '-' ? tx.out : '-'}</td>
                            <td class="px-4 py-3 text-right font-bold text-gray-900">${tx.balance}</td>
                        </tr>
                    `;
                });

                const sectionHtml = `
                    <div class="relative overflow-hidden bg-white border border-gray-200 sm:rounded-lg mb-6">
                        <div class="bg-gray-100 px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                            <div>
                                <h4 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                    ${item.name}
                                    <span class="text-xs font-normal text-gray-500 bg-white border border-gray-300 rounded px-2 py-0.5 shadow-sm">${item.uom ? item.uom.name : ''}</span>
                                </h4>
                                <p class="text-xs text-gray-500 font-mono mt-0.5">Code: ${item.code || 'N/A'}</p>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-blue-100">
                                    <tr>
                                        <th scope="col" class="px-4 py-3 w-32">Date</th>
                                        <th scope="col" class="px-4 py-3">Doc No</th>
                                        <th scope="col" class="px-4 py-3">Type</th>
                                        <th scope="col" class="px-4 py-3">From</th>
                                        <th scope="col" class="px-4 py-3">To</th>

                                        <th scope="col" class="px-4 py-3 text-right">In</th>
                                        <th scope="col" class="px-4 py-3 text-right">Out</th>
                                        <th scope="col" class="px-4 py-3 text-right w-32">Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${rowsHtml}
                                </tbody>
                            </table>
                        </div>
                    </div>
                `;
                container.append(sectionHtml);
            });
        }

        $('#btn_export_pdf').click(function(e){
            e.preventDefault();
            const formData = $('#filter_form').serialize();
            const q = $('#data_search').val();
            const url = "{{ route('inventory.stock-register.export-pdf') }}?" + formData + "&q=" + q;
            window.open(url, '_blank');
        });

        $('#btn_export_excel').click(function(e){
            e.preventDefault();
            const formData = $('#filter_form').serialize();
            const q = $('#data_search').val();
            const url = "{{ route('inventory.stock-register.export-excel') }}?" + formData + "&q=" + q;
            window.open(url, '_blank');
        });
    });
</script>
@endpush
