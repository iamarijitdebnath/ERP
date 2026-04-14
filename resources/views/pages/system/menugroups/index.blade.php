@extends('layouts.app')

@section('content')
<section class="bg-gray-50">
    <div class="mx-auto">
        <div class="block md:flex justify-between mb-4 items-center">
            <h3 class="text-2xl font-medium font-sans">Menu Groups</h3>

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
                            type="text"
                            name="q"
                            value="{{ request('q') }}"
                            class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 p-2.5"
                            placeholder="Search"
                        />
                    </div>
                </form>

                <a
                    href="{{ route('system.menugroups.create') }}"
                    class="flex items-center justify-center px-4 py-2 text-sm font-medium text-white rounded-lg bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 focus:outline-none"
                >
                    <svg class="h-3.5 w-3.5 mr-2" fill="currentColor" viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path clip-rule="evenodd" fill-rule="evenodd"
                            d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" />
                    </svg>
                    Add Group
                </a>
            </div>
        </div>

        <div class="relative overflow-hidden bg-white border border-gray-200 sm:rounded-lg">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-blue-100">
                        <tr>
                            <th scope="col" class="px-4 py-3">Name</th>
                            <th scope="col" class="px-4 py-3">Color</th>
                            <th scope="col" class="px-4 py-3">Module</th>
                            <th scope="col" class="px-4 py-3">Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($menuGroups as $group)
                            <tr 
                                class="border-b hover:bg-gray-50 cursor-pointer"
                                data-href="{{ route('system.menugroups.edit', $group) }}"
                                role="link"
                                tabindex="0"
                            >
                                <td class="px-4 py-2">
                                    <div class="truncate">{{ $group->name }}</div>
                                </td>
                                <td class="px-4 py-2">
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ $group->color }}
                                    </span>
                                </td>
                                <td class="px-4 py-2">
                                    <div class="truncate">{{ $group->module->name ?? 'N/A' }}</div>
                                </td>
                                <td class="px-4 py-2">
                                    <div class="truncate inline-flex items-center text-white text-sm font-medium px-3 py-1 rounded-full {{ $group->is_active ? 'bg-blue-500' : 'bg-red-500' }}">{{ $group->is_active ? "Active" : "Inactive" }}</div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <nav class="flex flex-col md:flex-row justify-between items-start md:items-center space-y-3 md:space-y-0 p-4" aria-label="Table navigation">
                <span class="text-sm font-normal text-gray-500 ">
                    Showing {{ $menuGroups->count() }} records
                </span>
            </nav>
        </div>
    </div>
</section>
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const rows = document.querySelectorAll('tr[data-href]');
                rows.forEach(row => {
                    row.addEventListener('click', function() {
                        window.location.href = this.dataset.href;
                    });
                });
            });
        </script>
        @endpush

@endsection
