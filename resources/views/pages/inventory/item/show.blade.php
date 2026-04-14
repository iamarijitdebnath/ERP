@extends('layouts.app')

@section('content')
    <section class="bg-gray-50">
        <div class="mx-auto">
            <div class="flex justify-between mb-4">
                <h3 class="text-2xl font-medium font-sans">{{ isset($item) ? 'Edit Item' : 'Add Item' }}</h3>
                <div class="flex items-center gap-2">
                    <a href="{{ route('inventory.item.index') }}" 
                        class="inline-flex items-center justify-center text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700"
                    >
                        Back
                    </a>
                    @if(isset($item))
                        <form id="data_delete" action="{{ route('inventory.item.delete', ['item' => $item->id]) }}" method="POST" class="contents">
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
                        form="item-form"
                        class="flex items-center justify-center text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800"
                    >
                        Save
                    </button>
                </div>
            </div>

            <form id="item-form" action="{{ isset($item) ? route('inventory.item.update', ['item' => $item->id]) : route('inventory.item.store') }}" method="POST" autocomplete="off">
                <div class="relative overflow-hidden bg-white border border-gray-200 p-4 sm:rounded-lg mb-4">
                    @csrf
                    @if(isset($item))
                        @method('PUT')
                    @endif

                    @php
                        $isVariant = request('type') === 'variant' || old('master_id') || (isset($item) && $item->master_id);
                    @endphp

                    <div class="grid gap-4 sm:grid-cols-2 sm:gap-6">
                        <div>
                            <label for="name" class="block mb-2 text-sm font-medium text-gray-900">Name</label>
                            <input
                                type="text"
                                name="name"
                                id="name"
                                value="{{ old('name', $item->name ?? '') }}"
                                class="bg-white border text-gray-900 text-sm rounded-lg block w-full p-2.5 {{ $errors->has('name') ? 'border-red-500' : 'border-gray-300' }}"
                                placeholder="Item Name"
                            >
                            @error('name')
                                <span class="text-red-400 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="code" class="block mb-2 text-sm font-medium text-gray-900">Code</label>
                            <input
                                type="text"
                                name="code"
                                id="code"
                                value="{{ old('code', $item->code ?? '') }}"
                                class="bg-white border text-gray-900 text-sm rounded-lg block w-full p-2.5 {{ $errors->has('code') ? 'border-red-500' : 'border-gray-300' }}"
                                placeholder="Item Code"
                            >
                            @error('code')
                                <span class="text-red-400 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        @if($isVariant)
                        <div>
                            <label for="master_id" class="block mb-2 text-sm font-medium text-gray-900">Master Item</label>
                            <select 
                                id="master_id" 
                                name="master_id" 
                                class="select2-item bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5"
                                data-placeholder="Select Master Item"
                                data-url="{{ route('inventory.item.select2') }}"
                            >
                                @if(old('master_id'))
                                    @php $masterItem = \App\Models\Inventory\Item::find(old('master_id')); @endphp
                                    @if($masterItem)
                                        <option value="{{ $masterItem->id }}" selected>{{ $masterItem->name }} ({{ $masterItem->code }})</option>
                                    @endif
                                @elseif(isset($item) && $item->master_id)
                                    <option value="{{ $item->master_id }}" selected>{{ $item->master->name }} ({{ $item->master->code }})</option>
                                @endif
                            </select>
                            @error('master_id')
                                <span class="text-red-400 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                        @else
                            
                        @endif

                        <div>
                            <label for="sku" class="block mb-2 text-sm font-medium text-gray-900">SKU</label>
                            <input
                                type="text"
                                name="sku"
                                id="sku"
                                value="{{ old('sku', $item->sku ?? '') }}"
                                class="bg-white border text-gray-900 text-sm rounded-lg block w-full p-2.5 {{ $errors->has('sku') ? 'border-red-500' : 'border-gray-300' }}"
                                placeholder="SKU"
                            >
                            @error('sku')
                                <span class="text-red-400 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="group_id" class="block mb-2 text-sm font-medium text-gray-900">Item Group</label>
                            <select 
                                id="group_id" 
                                name="group_id" 
                                class="select2-item bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5"
                                data-url="{{ route('inventory.item-group.select2') }}"
                                data-placeholder="Select Item Group"
                            >
                                @if(old('group_id'))
                                    @php $group = \App\Models\Inventory\ItemGroup::find(old('group_id')); @endphp
                                    @if($group)
                                        <option value="{{ $group->id }}" selected>{{ $group->name }}</option>
                                    @endif
                                @elseif(isset($item) && $item->group_id)
                                    <option value="{{ $item->group_id }}" selected>{{ $item->group->name }}</option>
                                @endif
                            </select>
                            @error('group_id')
                                <span class="text-red-400 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="uom_id" class="block mb-2 text-sm font-medium text-gray-900">UOM</label>
                            <select 
                                id="uom_id" 
                                name="uom_id" 
                                class="select2-item bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5"
                                data-url="{{ route('inventory.uom.select2') }}"
                                data-placeholder="Select UOM"
                            >
                                @if(old('uom_id'))
                                    @php $uom = \App\Models\Inventory\Uom::find(old('uom_id')); @endphp
                                    @if($uom)
                                        <option value="{{ $uom->id }}" selected>{{ $uom->name }} ({{ $uom->si_unit }})</option>
                                    @endif
                                @elseif(isset($item) && $item->uom_id)
                                    <option value="{{ $item->uom_id }}" selected>{{ $item->uom->name }} ({{ $item->uom->si_unit }})</option>
                                @endif
                            </select>
                            @error('uom_id')
                                <span class="text-red-400 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="acquire" class="block mb-2 text-sm font-medium text-gray-900">Acquire</label>
                            <select id="acquire" name="acquire" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
                                <option value="purchase" {{ (old('acquire', $item->acquire ?? 'purchase') == 'purchase') ? 'selected' : '' }}>Purchase</option>
                                <option value="manufacture" {{ (old('acquire', $item->acquire ?? 'purchase') == 'manufacture') ? 'selected' : '' }}>Manufacture</option>
                            </select>
                            @error('acquire')
                                <span class="text-red-400 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="tracking" class="block mb-2 text-sm font-medium text-gray-900">Tracking</label>
                            <select id="tracking" name="tracking" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
                                <option value="not-applicable" {{ (old('tracking', $item->tracking ?? 'not-applicable') == 'not-applicable') ? 'selected' : '' }}>Not Applicable</option>
                                <option value="batch" {{ (old('tracking', $item->tracking ?? 'not-applicable') == 'batch') ? 'selected' : '' }}>Batch</option>
                                <option value="batch-lot" {{ (old('tracking', $item->tracking ?? 'not-applicable') == 'batch-lot') ? 'selected' : '' }}>Batch & Lot</option>
                            </select>
                            @error('tracking')
                                <span class="text-red-400 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="has_expiry" class="block mb-2 text-sm font-medium text-gray-900">Has Expiry</label>
                            <select id="has_expiry" name="has_expiry" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
                                <option value="0" {{ (old('has_expiry', $item->has_expiry ?? 0) == 0) ? 'selected' : '' }}>No</option>
                                <option value="1" {{ (old('has_expiry', $item->has_expiry ?? 0) == 1) ? 'selected' : '' }}>Yes</option>
                            </select>
                            @error('has_expiry')
                                <span class="text-red-400 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="is_active" class="block mb-2 text-sm font-medium text-gray-900">Status</label>
                            <select id="is_active" name="is_active" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
                                <option value="1" {{ (old('is_active', $item->is_active ?? 1) == 1) ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ (old('is_active', $item->is_active ?? 1) == 0) ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('is_active')
                                <span class="text-red-400 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="description" class="block mb-2 text-sm font-medium text-gray-900">Description</label>
                            <textarea
                                name="description"
                                id="description"
                                rows="5"
                                class="summernote bg-white border text-gray-900 text-sm rounded-lg block w-full p-2.5 {{ $errors->has('description') ? 'border-red-500' : 'border-gray-300' }}"
                                placeholder="Description"
                            >{{ old('description', $item->description ?? '') }}</textarea>
                            @error('description')
                                <span class="text-red-400 text-xs">{{ $message }}</span>
                            @enderror
                        </div>


                    </div>
                </div>

                @if(!$isVariant)
                <div class="relative overflow-hidden bg-white border border-gray-200 p-4 sm:rounded-lg">
                    <div class="sm:col-span-2">
                        <div class="mb-4">
                            <label class="block mb-2 text-sm font-medium text-gray-900">Variants</label>
                            <div class="relative overflow-x-auto sm:rounded-lg">
                                <table class="w-full text-sm text-left text-gray-500" id="variants-table">
                                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3">Name</th>
                                            <th scope="col" class="px-6 py-3">Code</th>
                                            <th scope="col" class="px-6 py-3">SKU</th>
                                            <th scope="col" class="px-6 py-3">
                                                <button id="row_add_btn" type="button" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-3 py-1.5 focus:outline-none">Add Row</button>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($item->variants ?? [] as $index => $variant)
                                            <tr class="bg-white border-b hover:bg-gray-50 text-sm">
                                                <td>
                                                    <input type="text" name="variants[{{ $index }}][name]" value="{{ $variant->name }}" class="w-full focus:outline-none bg-transparent">
                                                    <input type="hidden" name="variants[{{ $index }}][id]" value="{{ $variant->id }}">
                                                </td>
                                                <td>
                                                    <input type="text" name="variants[{{ $index }}][code]" value="{{ $variant->code }}" class="w-full bg-transparent">
                                                </td>
                                                <td>
                                                    <input type="text" name="variants[{{ $index }}][sku]" value="{{ $variant->sku }}" class="w-full bg-transparent">
                                                </td>
                                                <td>
                                                    <button type="button" class="remove-variant-btn font-medium pl-10 text-red-600 dark:text-red-500 hover:underline">Remove</button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr class="bg-white border-b hover:bg-gray-50 text-sm">
                                                <td>
                                                    <input type="text" name="variants[0][name]" class="w-full focus:outline-none">
                                                </td>
                                                <td>
                                                    <input type="text" name="variants[0][code]" class="w-full">
                                                </td>
                                                <td>
                                                    <input type="text" name="variants[0][sku]" class="w-full">
                                                </td>
                                                <td>
                                                    <button type="button" class="remove-variant-btn font-medium pl-10 text-red-600 dark:text-red-500 hover:underline">Remove</button>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </form>

            {{-- Timeline --}}

            <div class="relative overflow-hidden bg-white border border-gray-200 p-4 sm:rounded-lg mt-5">
                    
                <ol class="relative border-s border-default">                  
                    <li class="mb-10 ms-4">
                        <div class="absolute w-3 h-3 bg-neutral-quaternary rounded-full mt-1.5 -start-1.5 border border-buffer"></div>
                        <time class="text-sm font-normal leading-none text-body">February 2022</time>
                        <h3 class="text-lg font-semibold text-heading my-2">Application UI code in Tailwind CSS</h3>
                        <p class="mb-4 text-base font-normal text-body">Get access to over 20+ pages including a dashboard layout, charts, kanban board, calendar, and pre-order E-commerce & Marketing pages.</p>
                        <a href="#" class="inline-flex items-center text-body bg-neutral-secondary-medium box-border border border-default-medium hover:bg-neutral-tertiary-medium hover:text-heading focus:ring-4 focus:ring-neutral-tertiary shadow-xs font-medium leading-5 rounded-base text-sm px-4 py-2.5 focus:outline-none">
                            Learn more
                            <svg class="w-4 h-4 ms-1.5 -me-0.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m14 0-4 4m4-4-4-4"/></svg>
                        </a>
                    </li>
                    <li class="mb-10 ms-4">
                        <div class="absolute w-3 h-3 bg-neutral-quaternary rounded-full mt-1.5 -start-1.5 border border-buffer"></div>
                        <time class="text-sm font-normal leading-none text-body">March 2022</time>
                        <h3 class="text-lg font-semibold text-heading my-2">Marketing UI design in Figma</h3>
                        <p class="text-base font-normal text-body">All of the pages and components are first designed in Figma and we keep a parity between the two versions even as we update the project.</p>
                    </li>
                    <li class="ms-4">
                        <div class="absolute w-3 h-3 bg-neutral-quaternary rounded-full mt-1.5 -start-1.5 border border-buffer"></div>
                        <time class="mb-1 text-sm font-normal leading-none text-body">April 2022</time>
                        <h3 class="text-lg font-semibold text-heading my-2">E-Commerce UI code in Tailwind CSS</h3>
                        <p class="text-base font-normal text-body">Get started with dozens of web components and interactive elements built on top of Tailwind CSS.</p>
                    </li>
                </ol>


            </div>

            {{-- TimlineEnds --}}

        </div>
    </section>

    @if(isset($item))
    <div id="popup-modal" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-md max-h-full">
            <div class="relative bg-white rounded-lg shadow border border-gray-200 p-6 text-center">
                <h3 class="mb-5 text-lg font-normal text-gray-700">Are you sure you want to delete this item?</h3>
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
@endsection


@push('scripts')
    <script>
        $(document).ready(function() {
            $('#variants-table tbody').on('click', '.remove-variant-btn', function() {
                const row = $(this).closest('tr');
                const tableBody = $('#variants-table tbody');
                if (tableBody.find('tr').length > 1) {
                    row.remove();
                } else {
                    alert("At least one variant row is required.");
                }
            });

            $('#row_add_btn').click(() => {
                const tableBody = $('#variants-table tbody');
                const uniqueIndex = Date.now();
                const newRow = `
                    <tr class="bg-white border-b hover:bg-gray-50 text-sm">
                        <td>
                            <input type="text" name="variants[${uniqueIndex}][name]" class="w-full focus:outline-none">
                        </td>
                        <td>
                            <input type="text" name="variants[${uniqueIndex}][code]" class="w-full">
                        </td>
                        <td>
                            <input type="text" name="variants[${uniqueIndex}][sku]" class="w-full">
                        </td>
                        <td>
                            <button type="button" class="remove-variant-btn font-medium pl-10 text-red-600 dark:text-red-500 hover:underline">Remove</button>
                        </td>
                    </tr>
                `;
                tableBody.append(newRow);
            });
        });
    </script>    
@endpush