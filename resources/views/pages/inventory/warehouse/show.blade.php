@extends('layouts.app')

@section('content')
    <section class="bg-gray-50">
        <div class="mx-auto">
            <div class="flex justify-between mb-4">
                <h3 class="text-2xl font-medium font-sans">{{ isset($warehouse) ? 'Edit Warehouse' : 'Add Warehouse' }}</h3>
                <div class="flex items-center gap-2">
                    <a href="{{ route('inventory.warehouse.index') }}" 
                        class="inline-flex items-center justify-center text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700"
                    >
                        Back
                    </a>
                    @if(isset($warehouse))
                        <form id="data_delete" action="{{ route('inventory.warehouse.delete', ['warehouse' => $warehouse->id]) }}" method="POST" class="contents">
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
                        form="warehouse-form"
                        class="flex items-center justify-center text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800"
                    >
                        Save
                    </button>
                </div>
            </div>

            <div class="relative overflow-hidden bg-white border border-gray-200 p-4 sm:rounded-lg">
                <form id="warehouse-form" action="{{ isset($warehouse) ? route('inventory.warehouse.update', ['warehouse' => $warehouse->id]) : route('inventory.warehouse.store') }}" method="POST" autocomplete="off">
                    @csrf
                    @if(isset($warehouse))
                        @method('PUT')
                    @endif

                    <div class="grid gap-4 sm:grid-cols-2 sm:gap-6">
                        
                        <div>
                            <label for="name" class="block mb-2 text-sm font-medium text-gray-900">Name</label>
                            <input
                                type="text"
                                name="name"
                                id="name"
                                value="{{ old('name', $warehouse->name ?? '') }}"
                                class="bg-white border text-gray-900 text-sm rounded-lg block w-full p-2.5 {{ $errors->has('name') ? 'border-red-500' : 'border-gray-300' }}"
                                placeholder="Warehouse Name"
                                required
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
                                value="{{ old('code', $warehouse->code ?? '') }}"
                                class="bg-white border text-gray-900 text-sm rounded-lg block w-full p-2.5 {{ $errors->has('code') ? 'border-red-500' : 'border-gray-300' }}"
                                placeholder="Warehouse Code"
                                required
                            >
                            @error('code')
                                <span class="text-red-400 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                             <label for="address1" class="block mb-2 text-sm font-medium text-gray-900">Address Line 1</label>
                            <input
                                type="text"
                                name="address1"
                                id="address1"
                                value="{{ old('address1', $warehouse->address1 ?? '') }}"
                                class="bg-white border text-gray-900 text-sm rounded-lg block w-full p-2.5 {{ $errors->has('address1') ? 'border-red-500' : 'border-gray-300' }}"
                                placeholder="Address Line 1"
                                required
                            >
                            @error('address1')
                                <span class="text-red-400 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                             <label for="address2" class="block mb-2 text-sm font-medium text-gray-900">Address Line 2</label>
                            <input
                                type="text"
                                name="address2"
                                id="address2"
                                value="{{ old('address2', $warehouse->address2 ?? '') }}"
                                class="bg-white border text-gray-900 text-sm rounded-lg block w-full p-2.5 {{ $errors->has('address2') ? 'border-red-500' : 'border-gray-300' }}"
                                placeholder="Address Line 2"
                            >
                            @error('address2')
                                <span class="text-red-400 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="city" class="block mb-2 text-sm font-medium text-gray-900">City</label>
                            <input
                                type="text"
                                name="city"
                                id="city"
                                value="{{ old('city', $warehouse->city ?? '') }}"
                                class="bg-white border text-gray-900 text-sm rounded-lg block w-full p-2.5 {{ $errors->has('city') ? 'border-red-500' : 'border-gray-300' }}"
                                placeholder="City"
                                required
                            >
                            @error('city')
                                <span class="text-red-400 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="state" class="block mb-2 text-sm font-medium text-gray-900">State</label>
                            <input
                                type="text"
                                name="state"
                                id="state"
                                value="{{ old('state', $warehouse->state ?? '') }}"
                                class="bg-white border text-gray-900 text-sm rounded-lg block w-full p-2.5 {{ $errors->has('state') ? 'border-red-500' : 'border-gray-300' }}"
                                placeholder="State"
                                required
                            >
                            @error('state')
                                <span class="text-red-400 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="contact_person" class="block mb-2 text-sm font-medium text-gray-900">Contact Person</label>
                            <input
                                type="text"
                                name="contact_person"
                                id="contact_person"
                                value="{{ old('contact_person', $warehouse->contact_person ?? '') }}"
                                class="bg-white border text-gray-900 text-sm rounded-lg block w-full p-2.5 {{ $errors->has('contact_person') ? 'border-red-500' : 'border-gray-300' }}"
                                placeholder="Contact Person"
                            >
                            @error('contact_person')
                                <span class="text-red-400 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="mobile_no" class="block mb-2 text-sm font-medium text-gray-900">Mobile No</label>
                            <input
                                type="text"
                                name="mobile_no"
                                id="mobile_no"
                                value="{{ old('mobile_no', $warehouse->mobile_no ?? '') }}"
                                class="bg-white border text-gray-900 text-sm rounded-lg block w-full p-2.5 {{ $errors->has('mobile_no') ? 'border-red-500' : 'border-gray-300' }}"
                                placeholder="Mobile No"
                            >
                            @error('mobile_no')
                                <span class="text-red-400 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                             <label for="email" class="block mb-2 text-sm font-medium text-gray-900">Email</label>
                            <input
                                type="email"
                                name="email"
                                id="email"
                                value="{{ old('email', $warehouse->email ?? '') }}"
                                class="bg-white border text-gray-900 text-sm rounded-lg block w-full p-2.5 {{ $errors->has('email') ? 'border-red-500' : 'border-gray-300' }}"
                                placeholder="Email"
                            >
                            @error('email')
                                <span class="text-red-400 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="is_active" class="block mb-2 text-sm font-medium text-gray-900">Status</label>
                            <select id="is_active" name="is_active" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
                                <option value="1" {{ (old('is_active', $warehouse->is_active ?? 1) == 1) ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ (old('is_active', $warehouse->is_active ?? 1) == 0) ? 'selected' : '' }}>Inactive</option>
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
            <h3 class="mb-5 text-lg font-normal text-gray-700">Are you sure you want to delete this warehouse?</h3>
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