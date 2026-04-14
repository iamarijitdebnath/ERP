@extends('layouts.app')

@section('title', 'Follow-ups')

@section('content')
    <section class="bg-gray-50">
        <div class="mx-auto">
            <div class="block md:flex justify-between mb-4 items-center">
                <h3 class="text-2xl font-medium font-sans">Follow-ups</h3>
                {{-- <div class="block md:flex gap-2 items-center"> 
                    <a
                        href="{{ route('sales.followup.create') }}"
                        class="flex items-center justify-center px-4 py-2 text-sm font-medium text-white rounded-lg bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 focus:outline-none"
                    >
                        <svg class="h-3.5 w-3.5 mr-2" fill="currentColor" viewBox="0 0 20 20"
                            xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path clip-rule="evenodd" fill-rule="evenodd"
                                d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" />
                        </svg>
                        Add Follow-up
                    </a>
                </div> --}}
            </div>

            <div class="relative overflow-hidden bg-white border border-gray-200 sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table id="data_table" class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-blue-100">
                            <tr>
                                <th class="px-4 py-3">Lead</th>
                                <th class="px-4 py-3">Product</th>
                                <th class="px-4 py-3">Date</th>
                                <th class="px-4 py-3">Remarks</th>
                                <th class="px-4 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
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
                                <option value="75" {{ request('limit') == 75 ? 'selected' : '' }}>75</option>
                                <option value="100" {{ request('limit') == 100 ? 'selected' : '' }}>100</option>
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
        let html = '';
        if(data.length) {
            data.forEach(row => {
               const leadName = row.inquiry?.lead?.name || '-';
               const productName = row.inquiry?.product?.name || '-';
               const date = new Date(row.date).toLocaleDateString();
               const remarks = row.remarks || '';
               const status = row.is_complete ? '<span class="text-green-600 font-medium">Complete</span>' : '<span class="text-yellow-600 font-medium">Pending</span>';

               html += `
                <tr class="border-b hover:bg-gray-50 cursor-pointer" data-id="${row.id}">
                    <td class="px-4 py-2">${leadName}</td>
                    <td class="px-4 py-2">${productName}</td>
                    <td class="px-4 py-2">${date}</td>
                    <td class="px-4 py-2">${remarks}</td>
                    <td class="px-4 py-2">${status}</td>
                </tr>
               `; 
            });
        } else {
             html = `
                <tr class="bg-white h-[calc(100vh-300px)]">
                    <td colspan="5">
                        <div class="flex items-center justify-center">
                            <div>No records found</div>
                        </div>
                    </td>
                </tr>
            `;
        }
        $('#data_table tbody').html(html);

        $('#data_table tbody tr').each((index, elem) => {
             let showRoute = `{{ route('sales.followup.show', ['followup' => '@@@']) }}`;
             $(elem).click(() => {
                 if($(elem).data('id')) {
                     window.location.href = showRoute.replace('@@@', $(elem).data('id'));
                 }
             });
         });
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
             linksHtml += `<li><a href="#" onclick="fetchData(${current - 1}); return false;" class="px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300 rounded-l-lg hover:bg-gray-100 disabled:opacity-50 ${current === 1 ? 'pointer-events-none opacity-50' : ''}">Prev</a></li>`;
             linksHtml += `<li><a href="#" onclick="fetchData(${current + 1}); return false;" class="px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300 rounded-r-lg hover:bg-gray-100 ${current === last ? 'pointer-events-none opacity-50' : ''}">Next</a></li>`;
        }

        $('#pagination_links').html(linksHtml);
    }

    const fetchData = (page=1) => {
         const limit = $('#rows_per_page').val();
         const url = new URL(window.location.href);
         url.searchParams.set('page', page);
         url.searchParams.set('limit', limit);
         window.history.pushState({}, '', url);

         const loader = () => {
             $('#data_table tbody').html(`
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
            url: "{{ route('sales.followup.index') }}",
            data: { page: page, limit: limit, _ajax: 1 },
            beforeSend: function() {
                loader();
            },
            success: function(res) { 
                printData(res.data.followups.data);
                renderPagination(res.data.followups);
            },
            error: function(xhr) {
                toastr.error("Something went wrong. Please try again.");
            }
        });
    }
    
    $('#rows_per_page').on('change', () => { fetchData(1); });
    fetchData({{ request('page', 1) }});
</script>
@endpush
