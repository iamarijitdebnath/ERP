@extends('layouts.app')

@section('title', 'Employees')

@section('content')
<section class="bg-gray-50">
    <div class="mx-auto ">
        <div class="block md:flex justify-between mb-4 items-center">
            <h3 class="text-2xl font-medium font-sans">Employees</h3>

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
                            class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 p-2.5"
                            placeholder="Search"
                        />
                    </div>
                </form>

                <a
                    href="{{ route('hrms.employee.create') }}"
                    class="flex items-center justify-center px-4 py-2 text-sm font-medium text-white rounded-lg bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 focus:outline-none"
                >
                    <svg class="h-3.5 w-3.5 mr-2" fill="currentColor" viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path clip-rule="evenodd" fill-rule="evenodd"
                            d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" />
                    </svg>
                    Add Employee
                </a>
            </div>
        </div>

        <div class="relative overflow-hidden bg-white border border-gray-200 sm:rounded-lg">
            <div class="overflow-x-auto">
                <table id="data_table" class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-blue-100">
                        <tr>
                            <th scope="col" class="px-4 py-3">Code</th>
                            <th scope="col" class="px-4 py-3">Name</th>
                            <th scope="col" class="px-4 py-3">Email</th>
                            <th scope="col" class="px-4 py-3">Department</th>
                            <th scope="col" class="px-4 py-3">Reporting To</th>
                            <th scope="col" class="px-4 py-3">Status</th>
                            <th scope="col" class="px-4 py-3">Permission</th>
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
    @push('scripts')
    <script>
        const printData = (data) => {
            let dataHtml = ``;

            if(data.length > 0) {
                data.map((row, index) => {
                    let editRoute = `{{ route('hrms.employee.edit', ['id' => '@@@']) }}`;
                    editRoute = editRoute.replace('@@@', row.id);

                    let manageRoute = `{{ route('hrms.employee.permission', ['id' => '@@@']) }}`;
                    manageRoute = manageRoute.replace('@@@', row.id);

                    let statusBadge = row.is_active 
                        ? `<span class="bg-green-100 text-green-800 text-xs font-medium px-2 py-0.5 rounded">Active</span>`
                        : `<span class="bg-red-100 text-red-800 text-xs font-medium px-2 py-0.5 rounded">Inactive</span>`;

                    let deptName = row.department ? row.department.name : '-';
                    let reportingName = row.reporting_to ? `${row.reporting_to.first_name} ${row.reporting_to.last_name}` : '-';
                    if (!row.reporting_to && row.reportingTo) {
                         reportingName = `${row.reportingTo.first_name} ${row.reportingTo.last_name}`;
                    }
                    if(!row.reporting_to && !row.reportingTo) reportingName = '-';


                    dataHtml += `
                        <tr
                            class="border-b hover:bg-gray-50 cursor-pointer"
                            onclick="window.location.href='${editRoute}'"
                            role="link"
                            tabindex="0"
                        >
                            <td class="px-4 py-2">${row.code}</td>
                            <td class="px-4 py-2">
                                <div class="font-medium text-gray-900">${row.salutation} ${row.first_name} ${row.last_name}</div>
                            </td>
                            <td class="px-4 py-2">${row.email}</td>
                            <td class="px-4 py-2">${deptName}</td>
                            <td class="px-4 py-2">${reportingName}</td>
                            <td class="px-4 py-3">${statusBadge}</td>
                            <td class="px-4 py-3">
                                <a href="${manageRoute}" class="text-blue-600 hover:underline" onclick="event.stopPropagation()">Manage</a>
                            </td>
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

            if(last > 1) {
            // Previous
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

            // Next
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

        const fetchData = (page = 1, q = '') => {
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
                url: "{{ route('hrms.employee.index') }}",
                type: "GET",
                data: {
                    page: page,
                    q: q
                },
                beforeSend: function () {
                    loader();
                },
                success: function(response) {
                    printData(response.data.employees.data);
                    renderPagination(response.data.employees);
                },
                error: function(xhr) {
                    console.log("Error:", xhr.responseText);
                }
            });
        }

        function debounce(func, wait) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        }

        $(document).ready(() => {
            fetchData();

            $('#data_search').on('input', debounce(function() {
                let q = $(this).val();
                if (q.length === 0 || q.length >= 3) {
                    fetchData(1, q);
                }
            }, 300));
            
            $('form').on('submit', function(e) {
                e.preventDefault();
                let q = $('#data_search').val();
                fetchData(1, q);
            });
        });
    </script>
    @endpush
@endsection
