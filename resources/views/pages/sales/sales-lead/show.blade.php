@extends('layouts.app')

@section('content')
    <section class="bg-gray-50">
        <div class="mx-auto">
            <div class="flex justify-between mb-4">
                <h3 class="text-2xl font-medium font-sans">{{ $lead->exists ? 'Edit Lead' : 'Add Lead' }}</h3>
                <div class="flex items-center gap-2">
                    <a href="{{ route('sales.sales-lead.index') }}"
                        class="inline-flex items-center justify-center text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700"
                    >
                        Back
                    </a>
                    
                    @if($lead->exists)
                    <form id="lead_delete_form" action="{{ route('sales.sales-lead.delete', ['lead' => $lead->id]) }}" method="POST" class="contents">
                        @csrf
                        @method('DELETE')
                        <button 
                            type="button"
                            onclick="if(confirm('Are you sure?')) document.getElementById('lead_delete_form').submit();"
                            class="inline-flex items-center justify-center focus:outline-none text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5"
                        >
                            Delete
                        </button>
                    </form>
                    @endif

                    <button 
                        type="submit"
                        form="lead-form"
                        class="flex items-center justify-center text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5"
                    >
                        Save
                    </button>
                </div>
            </div>

            <!-- Lead Details Form -->
            <form id="lead-form" action="{{ $lead->exists ? route('sales.sales-lead.update', ['lead' => $lead->id]) : route('sales.sales-lead.store') }}" method="POST" class="mb-6">
                @csrf
                @if($lead->exists)
                    @method('PUT')
                @endif
                <div class="relative overflow-hidden bg-white border border-gray-200 p-4 sm:rounded-lg">
                    <div class="grid gap-4 sm:grid-cols-3 sm:gap-6">
                        <div>
                            <label for="name" class="block mb-2 text-sm font-medium text-gray-900">Name</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $lead->name ?? '') }}" class="bg-white border text-gray-900 text-sm rounded-lg block w-full p-2.5 border-gray-300" required placeholder="Client Name">
                            @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="email" class="block mb-2 text-sm font-medium text-gray-900">Email</label>
                            <input type="email" name="email" id="email" value="{{ old('email', $lead->email ?? '') }}" class="bg-white border text-gray-900 text-sm rounded-lg block w-full p-2.5 border-gray-300" placeholder="Email Address">
                            @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="mobile" class="block mb-2 text-sm font-medium text-gray-900">Mobile</label>
                            <input type="text" name="mobile" id="mobile" value="{{ old('mobile', $lead->mobile ?? '') }}" class="bg-white border text-gray-900 text-sm rounded-lg block w-full p-2.5 border-gray-300" placeholder="Mobile Number">
                            @error('mobile') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </form>

            @if($lead->exists)
            <!-- Inquiries Section (Table Style like Variants) -->
            <div class="relative overflow-hidden bg-white border border-gray-200 p-4 sm:rounded-lg mt-5">
                <div class="sm:col-span-2">
                    <div class="mb-4 flex justify-between items-center">
                        <label class="block mb-2 text-sm font-medium text-gray-900">Inquiries</label>
                        <a href="{{ route('sales.inquiry.create', ['lead_id' => $lead->id]) }}" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-3 py-1.5 focus:outline-none">
                            Add Inquiry
                        </a>
                    </div>
                    <div class="relative overflow-x-auto sm:rounded-lg">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Product</th>
                                    <th scope="col" class="px-6 py-3">Source</th>
                                    <th scope="col" class="px-6 py-3">Employee</th>
                                    <th scope="col" class="px-6 py-3">Remarks</th>
                                    <th scope="col" class="px-6 py-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lead->inquiries as $inquiry)
                                    <tr class="bg-white border-b hover:bg-gray-50">
                                        <td class="px-6 py-4">{{ $inquiry->product->name ?? '-' }}</td>
                                        <td class="px-6 py-4">{{ $inquiry->source }}</td>
                                        <td class="px-6 py-4">{{ $inquiry->employee->first_name ?? '' }} {{ $inquiry->employee->last_name ?? '' }}</td>
                                        <td class="px-6 py-4 truncate max-w-xs">{{ $inquiry->remarks }}</td>
                                        <td class="px-6 py-4 flex gap-2">
                                            <a href="{{ route('sales.inquiry.show', $inquiry->id) }}" class="font-medium text-blue-600 hover:underline">Edit</a>
                                            <!-- Optionally Delete here or inside Edit -->
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center">No Inquiries Found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </section>
@endsection
