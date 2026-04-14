@extends('layouts.app')

@section('content')
    <section class="bg-gray-50">
        <div class="mx-auto">
            <div class="flex justify-between mb-4">
                <h3 class="text-2xl font-medium font-sans">{{ isset($inquiry) ? 'Edit Inquiry' : 'Add Inquiry' }}</h3>
                <div class="flex items-center gap-2">
                    <a href="{{ route('sales.inquiry.index') }}"  class="inline-flex items-center justify-center text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 font-medium rounded-lg text-sm px-5 py-2.5">
                        Back
                    </a>
                    
                    @if(isset($inquiry))
                    <form id="delete_form" action="{{ route('sales.inquiry.delete', ['inquiry' => $inquiry->id]) }}" method="POST" class="contents">
                        @csrf
                        @method('DELETE')
                        <button type="button" onclick="if(confirm('Are you sure?')) document.getElementById('delete_form').submit();" class="text-white bg-red-700 hover:bg-red-800 font-medium rounded-lg text-sm px-5 py-2.5">Delete</button>
                    </form>
                    @endif

                    <button type="submit" form="inquiry-form" class="text-white bg-blue-700 hover:bg-blue-800 font-medium rounded-lg text-sm px-5 py-2.5">Save</button>
                </div>
            </div>

            <form id="inquiry-form" action="{{ isset($inquiry) ? route('sales.inquiry.update', $inquiry->id) : route('sales.inquiry.store') }}" method="POST">
                @csrf
                @if(isset($inquiry)) @method('PUT') @endif
                
                <div class="relative overflow-hidden bg-white border border-gray-200 p-4 sm:rounded-lg mb-4">
                    <div class="grid gap-4 sm:grid-cols-2 sm:gap-6">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900">Lead</label>
                            <select name="lead_id" class="select2-basic bg-white border text-sm rounded-lg block w-full p-2.5">
                                <option value="">Select Lead</option>
                                @foreach($leads as $l)
                                    <option value="{{ $l->id }}" {{ (old('lead_id', $inquiry->lead_id ?? '') == $l->id || (request('lead_id') == $l->id)) ? 'selected' : '' }}>{{ $l->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900">Product</label>
                            <select name="product_id" class="select2-basic bg-white border text-sm rounded-lg block w-full p-2.5">
                                <option value="">Select Product</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}" {{ (old('product_id', $inquiry->product_id ?? '') == $p->id) ? 'selected' : '' }}>{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900">Employee</label>
                            <select name="employee_id" class="select2-basic bg-white border text-sm rounded-lg block w-full p-2.5">
                                <option value="">Select Employee</option>
                                @foreach($employees as $e)
                                    <option value="{{ $e->id }}" {{ (old('employee_id', $inquiry->employee_id ?? '') == $e->id) ? 'selected' : '' }}>{{ $e->first_name }}</option>
                                @endforeach
                            </select>
                        </div>
                         <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900">Source</label>
                            <input type="text" name="source" value="{{ old('source', $inquiry->source ?? '') }}" class="bg-white border-gray-300 text-sm rounded-lg block w-full p-2.5">
                        </div>
                        <div class="sm:col-span-2">
                             <label class="block mb-2 text-sm font-medium text-gray-900">Remarks</label>
                             <textarea name="remarks" rows="2" class="bg-white border-gray-300 text-sm rounded-lg block w-full p-2.5">{{ old('remarks', $inquiry->remarks ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
            </form>

            @if(isset($inquiry))
            <!-- Followups Section -->
            <div class="relative overflow-hidden bg-white border border-gray-200 p-4 sm:rounded-lg mt-5">
                <div class="sm:col-span-2">
                    <div class="mb-4 flex justify-between items-center">
                        <label class="block mb-2 text-sm font-medium text-gray-900">Follow-ups</label>
                        <a href="{{ route('sales.followup.create', ['inquiry_id' => $inquiry->id]) }}" class="text-white bg-blue-700 hover:bg-blue-800 font-medium rounded-lg text-sm px-3 py-1.5">
                            Add Follow-up
                        </a>
                    </div>
                    <div class="relative overflow-x-auto sm:rounded-lg">
                        <table class="w-full text-sm text-left text-gray-500">
                             <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3">Date</th>
                                    <th class="px-6 py-3">Next Date</th>
                                    <th class="px-6 py-3">Remarks</th>
                                    <th class="px-6 py-3">Complete?</th>
                                    <th class="px-6 py-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($inquiry->followups as $f)
                                    <tr class="bg-white border-b hover:bg-gray-50">
                                        <td class="px-6 py-4">{{ $f->date->format('Y-m-d') }}</td>
                                        <td class="px-6 py-4">{{ $f->follow_up_date ? $f->follow_up_date->format('Y-m-d') : '-' }}</td>
                                        <td class="px-6 py-4">{{ $f->remarks }}</td>
                                        <td class="px-6 py-4">{{ $f->is_complete ? 'Yes' : 'No' }}</td>
                                        <td class="px-6 py-4">
                                            <a href="{{ route('sales.followup.show', $f->id) }}" class="text-blue-600 hover:underline">Edit</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="px-6 py-4 text-center">No Follow-ups</td></tr>
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

@push('scripts')
<script>
    $(document).ready(function() {
        $('.select2-basic').select2({
            width: '100%',
            placeholder: "Select an option",
            allowClear: true
        });
    });
</script>
@endpush
