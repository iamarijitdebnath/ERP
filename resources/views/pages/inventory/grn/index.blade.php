@extends('layouts.app')

@section('title', 'Goods Receipt Note (GRN)')

@section('content')
    <section class="bg-gray-50">
        <div class="mx-auto ">
            <div class="block md:flex justify-between mb-4 items-center">
                <h3 class="text-2xl font-medium font-sans">Goods Receipt Note (GRN)</h3>

                <div class="block md:flex gap-2 items-center">
                    <form action="#" method="GET" class="">
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
                                placeholder="Search"
                            />
                        </div>
                    </form>

                    <div class="flex gap-2">
                        <a href="{{ route($routePrefix . '.create') }}" class="flex items-center justify-center px-4 py-2 text-sm font-medium text-white rounded-lg bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 focus:outline-none">
                            +  New Grn
                        </a>
                    </div>
                </div>
            </div>

            <div class="relative overflow-hidden bg-white border border-gray-200 sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table id="data_table" class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-blue-100">
                            <tr>
                                <th scope="col" class="px-4 py-3">Date</th>
                                <th scope="col" class="px-4 py-3">Doc No</th>
                                <th scope="col" class="px-4 py-3">Supplier</th>
                                <th scope="col" class="px-4 py-3">PO No.</th>
                                <th scope="col" class="px-4 py-3">To Warehouse</th>
                                <th scope="col" class="px-4 py-3">Received By</th>
                                <th scope="col" class="px-4 py-3">Items</th>
                                <th scope="col" class="px-4 py-3">Remarks</th>
                            </tr>
                        </thead>

                        <tbody>
                            
                        </tbody>
                    </table>
                </div>

                <nav class="flex flex-col md:flex-row justify-between items-start md:items-center space-y-3 md:space-y-0 p-4" aria-label="Table navigation">
                    <span class="text-sm font-normal text-gray-500">Showing <span class="font-semibold text-gray-900" id="showing_range"></span> of <span class="font-semibold text-gray-900" id="total_records"></span></span>
                    <ul class="inline-flex items-stretch -space-x-px" id="pagination_links">
                        
                    </ul>
                </nav>
            </div>
        </div>
    </section>
@endsection


@push('scripts')
    <script>

        const printData = (data) => {

            let dataHtml = ``;

            if(data.length > 0) {
                data.map((row, index) => {
                    let date = new Date(row.date || row.created_at).toLocaleDateString();
                    let transaction = row.transaction || {}; 
                    let toWarehouseName = transaction.to_warehouse ? transaction.to_warehouse.name : '-';
                    let itemsCount = transaction.items ? transaction.items.length : 0;
                    let receivedByName = row.received_by ? (row.received_by.first_name + ' ' + row.received_by.last_name) : '-';
                    if (!row.received_by && row.receivedBy) {
                         receivedByName = row.receivedBy.first_name + ' ' + row.receivedBy.last_name;
                    }
                    
                    dataHtml += `
                        <tr
                            class="border-b hover:bg-gray-50 cursor-pointer"
                            data-id="${row.id}"
                            role="link"
                        >
                            <td class="px-4 py-2">${date}</td>
                            <td class="px-4 py-2 font-medium text-gray-900">${row.doc_no || '-'}</td>
                            <td class="px-4 py-2">${row.supplier_name || '-'}</td>
                            <td class="px-4 py-2">${row.purchase_order_number || '-'}</td>
                            <td class="px-4 py-2">${toWarehouseName}</td>
                            <td class="px-4 py-2">${receivedByName}</td>
                            <td class="px-4 py-2">${itemsCount}</td>
                            <td class="px-4 py-2 truncate max-w-xs" title="${row.remarks || ''}">${row.remarks || '-'}</td>
                        </tr>
                    `;
                });
            } else {

                dataHtml = `
                    <tr class="bg-white h-[calc(100vh-300px)]">
                        <td colspan="7">
                            <div class="flex items-center justify-center">
                                <div>No records found</div>
                            </div>
                        </td>
                    </tr>
                `;
            }
            
            $(`#data_table tbody`).html(dataHtml);

            
            $(`#data_table tbody tr`).each((index, elem) => {
                let editRoute = `{{ route($routePrefix . '.edit', ['transaction' => '@@@']) }}`;
                $(elem).click(() => {
                    if($(elem).data('id')) {
                        // The controller edit method expects an ID
                        // We need to make sure the route param name is generic enough or matches what we set in web.php
                        // Usually it's resource route so .edit/{grn}
                        // The previous code had 'transaction' => '@@@' 
                        // If the resource param name is implicitly 'grn' or 'transaction', Laravel handles it if we pass ID.
                        // I will assume ID is enough.
                        window.location.href = editRoute.replace('@@@', $(elem).data('id'));
                    }
                });
            })
        }

        const renderPagination = (meta) => {
            let linksHtml = ``;
            const current = meta.current_page;
            const last = meta.last_page;

            if (meta.total > 0) {
                $('#showing_range').parent().show();
                $('#showing_range').text(`${meta.from}-${meta.to}`);
                $('#total_records').text(meta.total);
            } else {
                 $('#showing_range').parent().hide();
            }

            if (last > 1) {
            linksHtml += `
                <li>
                    <a href="#" onclick="fetchData(${current - 1}, '${$('#data_search').val()}'); return false;" class="flex items-center justify-center h-full py-1.5 px-3 ml-0 text-gray-500 bg-white rounded-l-lg border border-gray-300 hover:bg-gray-100 hover:text-gray-700 ${current === 1 ? 'pointer-events-none opacity-50' : ''}">
                        <span class="sr-only">Previous</span>
                        <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </a>
                </li>
            `;
            
            for (let i = 1; i <= last; i++) {
                if (i === 1 || i === last || (i >= current - 2 && i <= current + 2)) {
                     let activeClass = i === current ? 'text-primary-600 bg-primary-50 border-primary-300 hover:bg-primary-100 hover:text-primary-700 z-10' : 'text-gray-500 bg-white border-gray-300 hover:bg-gray-100 hover:text-gray-700';
                     linksHtml += `
                        <li>
                            <a href="#" onclick="fetchData(${i}, '${$('#data_search').val()}'); return false;" class="flex items-center justify-center text-sm py-2 px-3 leading-tight border ${activeClass}">${i}</a>
                        </li>
                     `;
                } else if (i === current - 3 || i === current + 3) {
                    linksHtml += `
                        <li>
                             <span class="flex items-center justify-center text-sm py-2 px-3 leading-tight text-gray-500 bg-white border border-gray-300">...</span>
                        </li>
                    `;
                }
            }

            
             linksHtml += `
                <li>
                    <a href="#" onclick="fetchData(${current + 1}, '${$('#data_search').val()}'); return false;" class="flex items-center justify-center h-full py-1.5 px-3 leading-tight text-gray-500 bg-white rounded-r-lg border border-gray-300 hover:bg-gray-100 hover:text-gray-700 ${current === last ? 'pointer-events-none opacity-50' : ''}">
                        <span class="sr-only">Next</span>
                        <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 01-1.414 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </a>
                </li>
            `;
            }

            $('#pagination_links').html(linksHtml);
        }

        const fetchData = (page = 1, q = null) => {
            if(!window.currentPage) {
                window.currentPage = page;
            }
            
            const loader = () => {
                $(`#data_table tbody`).html(`
                    <tr class="bg-white h-[calc(100vh-300px)]">
                        <td colspan="7">
                            <div class="flex items-center justify-center">
                                <div class="h-8 w-8 animate-spin rounded-full border-4 border-solid border-primary-500 border-t-transparent"></div>
                            </div>
                        </td>
                    </tr>
                `);
            }

            $.ajax({
                url: "{{ route($routePrefix . '.index') }}",
                type: "GET",
                data: {
                    page: page,
                    q: q
                },
                beforeSend: function () {
                    loader();
                },
                headers: {
                    'Accept': 'application/json'
                },
                success: function(response) {
                    printData(response.data.transactions.data);
                    renderPagination(response.data.transactions);
                },
                error: function(xhr) {
                    console.log("Error:", xhr.responseText);
                }
            });
        }

        $(`#data_search`).on('input', () => {
            let q = $(`#data_search`).val();
            if (q.length === 0 || q.length >= 3) {
                fetchData(1, q);
            }
        });

        fetchData();
    </script>
@endpush
