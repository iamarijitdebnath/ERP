@extends('layouts.app')

@section('content')
    <section class="bg-gray-50">
        <div class="mx-auto">
            <div class="flex justify-between mb-4">
                <h3 class="text-2xl font-medium font-sans">{{ isset($uom) ? 'Edit UOM' : 'Add UOM' }}</h3>
                <div class="flex items-center gap-2">
                    <a href="{{ route('inventory.uom.index') }}" 
                        class="inline-flex items-center justify-center text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700"
                    >
                        Back
                    </a>
                    @if(isset($uom))
                        <form id="data_delete" action="{{ route('inventory.uom.delete', ['uom' => $uom->id]) }}" method="POST" class="contents">
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
                        form="uom-form"
                        class="flex items-center justify-center text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800"
                    >
                        Save
                    </button>
                </div>
            </div>

            <div class="relative overflow-hidden bg-white border border-gray-200 p-4 sm:rounded-lg">
                <form id="uom-form" action="{{ isset($uom) ? route('inventory.uom.update', ['uom' => $uom->id]) : route('inventory.uom.store') }}" method="POST" autocomplete="off">
                    @csrf
                    @if(isset($uom))
                        @method('PUT')
                    @endif

                    <div class="grid gap-4 sm:grid-cols-2 sm:gap-6">
                        
                        <div>
                            <label for="name" class="block mb-2 text-sm font-medium text-gray-900">Name</label>
                            <input
                                type="text"
                                name="name"
                                id="name"
                                value="{{ old('name', $uom->name ?? '') }}"
                                class="bg-white border text-gray-900 text-sm rounded-lg block w-full p-2.5 {{ $errors->has('name') ? 'border-red-500' : 'border-gray-300' }}"
                                placeholder="UOM Name"
                                required
                            >
                            @error('name')
                                <span class="text-red-400 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="si_unit" class="block mb-2 text-sm font-medium text-gray-900">SI Unit</label>
                            <input
                                type="text"
                                name="si_unit"
                                id="si_unit"
                                value="{{ old('si_unit', $uom->si_unit ?? '') }}"
                                class="bg-white border text-gray-900 text-sm rounded-lg block w-full p-2.5 {{ $errors->has('si_unit') ? 'border-red-500' : 'border-gray-300' }}"
                                placeholder="SI Unit"
                                required
                            >
                            @error('si_unit')
                                <span class="text-red-400 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="category" class="block mb-2 text-sm font-medium text-gray-900">Category</label>
                            <select
                                name="category"
                                id="category"
                                class="bg-white border text-gray-900 text-sm rounded-lg block w-full p-2.5 {{ $errors->has('category') ? 'border-red-500' : 'border-gray-300' }}"
                                required
                            >
                                <option value=""></option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat }}" {{ old('category', $uom->category ?? '') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                @endforeach
                            </select>
                            @error('category')
                                <span class="text-red-400 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="is_active" class="block mb-2 text-sm font-medium text-gray-900">Status</label>
                            <select id="is_active" name="is_active" class=" bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
                                <option value="1" {{ (old('is_active', $uom->is_active ?? 1) == 1) ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ (old('is_active', $uom->is_active ?? 1) == 0) ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('is_active')
                                <span class="text-red-400 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection

<div id="popup-modal" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-md max-h-full">
        <div class="relative bg-white rounded-lg shadow border border-gray-200 p-6 text-center">
            <h3 class="mb-5 text-lg font-normal text-gray-700">Are you sure you want to delete this uom?</h3>
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

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#category').select2({
                placeholder: 'Select Category',
                allowClear: true,
                tags: true,
                width: '100%'
            });
        });
    </script>
@endpush
