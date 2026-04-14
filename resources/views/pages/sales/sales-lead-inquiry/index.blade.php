@extends('layouts.app')

@section('title', 'Selling Lead Inquiries')

@section('content')
    <section class="bg-gray-50">
        <div class="mx-auto">
            <div class="block md:flex justify-between mb-4 items-center">
                <h3 class="text-2xl font-medium font-sans">Inquiries</h3>

                {{-- <div class="block md:flex gap-2 items-center"> 
                    <a
                        href="{{ route('sales.inquiry.create') }}"
                        class="flex items-center justify-center px-4 py-2 text-sm font-medium text-white rounded-lg bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 focus:outline-none"
                    >
                        <svg class="h-3.5 w-3.5 mr-2" fill="currentColor" viewBox="0 0 20 20"
                            xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path clip-rule="evenodd" fill-rule="evenodd"
                                d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" />
                        </svg>
                        Add Inquiry
                    </a>
                </div> --}}
            </div>

            <div class="relative overflow-hidden bg-white border border-gray-200 sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table id="data_table" class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-blue-100">
                            <tr>
                                <th scope="col" class="px-4 py-3">Lead</th>
                                <th scope="col" class="px-4 py-3">Product</th>
                                <th scope="col" class="px-4 py-3">Source</th>
                                <th scope="col" class="px-4 py-3">Employee</th>
                                <th scope="col" class="px-4 py-3">Date</th>
                            </tr>
                        </thead>

                        <tbody>
                            
                        </tbody>
                    </table>
                </div>

                <nav class="flex flex-col md:flex-row justify-between items-start md:items-center space-y-3 md:space-y-0 p-4" aria-label="Table navigation">
                    <div class="flex flex-col md:flex-row items-center gap-3">
                        <span class="text-sm font-normal text-gray-500">Showing <span class="font-semibold text-gray-900" id="showing_range"></span> of <span class="font-semibold text-gray-900" id="total_records"></span></span>
                        
                        <div class="flex items-center gap-1">
                            <label for="rows_per_page" class="text-sm font-medium text-gray-900">Show</label>
                            <select id="rows_per_page" class="bg-gray-50 border border-gray-300 text-gray-900 w-16 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block p-1.5 py-1">
                                <option value="25" {{ request('limit') == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ request('limit') == 50 ? 'selected' : '' }}>50</option>
                            </select>
                            <label for="rows_per_page" class="text-sm font-medium text-gray-900">entries</label>
                        </div>
                    </div>
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
                    dataHtml += `
                        <tr
                            class="border-b hover:bg-gray-50 cursor-pointer"
                            data-id="${row.id}"
                            role="link"
                        >
                            <td class="px-4 py-2">${row.lead ? row.lead.name : '-'}</td>
                            <td class="px-4 py-2">${row.product ? row.product.name : '-'}</td>
                            <td class="px-4 py-2">${row.source || '-'}</td>
                            <td class="px-4 py-2">${row.employee ? row.employee.first_name : '-'}</td>
                            <td class="px-4 py-2">${new Date(row.created_at).toLocaleDateString()}</td>
                        </tr>
                    `;
                });
            } else {
                dataHtml = `
                    <tr class="bg-white h-[calc(100vh-300px)]">
                        <td colspan="5">
                            <div class="flex items-center justify-center">
                                <div>No records found</div>
                            </div>
                        </td>
                    </tr>
                `;
            }
            $(`#data_table tbody`).html(dataHtml);
            
            $(`#data_table tbody tr`).each((index, elem) => {
                let showRoute = `{{ route('sales.inquiry.show', ['inquiry' => '@@@']) }}`;
                $(elem).click(() => {
                    if($(elem).data('id')) {
                        window.location.href = showRoute.replace('@@@', $(elem).data('id'));
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
            // Simple pagination (Prev/Next) for brevity
            if (last > 1) {
                 linksHtml += `<li><a href="#" onclick="fetchData(${current - 1}); return false;" class="px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300 rounded-l-lg hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">Prev</a></li>`;
                 linksHtml += `<li><a href="#" onclick="fetchData(${current + 1}); return false;" class="px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300 rounded-r-lg hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">Next</a></li>`;
            }

            $('#pagination_links').html(linksHtml);
        }

        const fetchData = (page = 1) => {
            const limit = $('#rows_per_page').val();
            const url = new URL(window.location.href);
            url.searchParams.set('page', page);
            url.searchParams.set('limit', limit);
            window.history.pushState({}, '', url);

            const loader = () => {
                $(`#data_table tbody`).html(`
                    <tr class="bg-white h-[calc(100vh-300px)]">
                        <td colspan="5">
                            <div class="flex items-center justify-center">
                                <div class="h-8 w-8 animate-spin rounded-full border-4 border-solid border-primary-500 border-t-transparent"></div>
                            </div>
                        </td>
                    </tr>
                `);
            }

             $.ajax({
                url: "{{ route('sales.inquiry.index') }}",
                type: "GET",
                data: { page: page, limit: limit, _ajax: 1 },
                beforeSend: function () {
                    loader();
                },
                success: function(response) {
                    printData(response.data.inquiries.data);
                    renderPagination(response.data.inquiries);
                },
                error: function(xhr) {
                    toastr.error("Something went wrong. Please try again.");
                }
            });
        }
        
        $(`#rows_per_page`).on('change', () => { fetchData(1); });
        fetchData({{ request('page', 1) }});
    </script>
@endpush
