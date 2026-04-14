@extends('layouts.app')

@section('content')
    <section class="bg-gray-50">
        <div class="mx-auto">
            <div class="flex justify-between mb-4">
                <h3 class="text-2xl font-medium font-sans">{{ isset($followup) ? 'Edit Follow-up' : 'Add Follow-up' }}</h3>
                <div class="flex items-center gap-2">
                    <a href="{{ route('sales.followup.index') }}" class="inline-flex items-center justify-center text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 font-medium rounded-lg text-sm px-5 py-2.5">
                        Back
                    </a>
                    <button type="submit" form="followup-form" class="text-white bg-blue-700 hover:bg-blue-800 font-medium rounded-lg text-sm px-5 py-2.5">Save</button>
                </div>
            </div>

            <form id="followup-form" action="{{ isset($followup) ? route('sales.followup.update', $followup->id) : route('sales.followup.store') }}" method="POST">
                @csrf
                @if(isset($followup)) @method('PUT') @endif
                
                <div class="relative overflow-hidden bg-white border border-gray-200 p-4 sm:rounded-lg">
                    <div class="grid gap-4 sm:grid-cols-2 sm:gap-6">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900">Inquiry (Lead Details)</label>
                            <select name="inquiry_id" class="select2-basic bg-white border text-sm rounded-lg block w-full p-2.5">
                                <option value="">Select Inquiry</option>
                                @foreach($inquiries as $i)
                                    <option value="{{ $i->id }}" {{ (old('inquiry_id', $followup->inquiry_id ?? '') == $i->id || request('inquiry_id') == $i->id) ? 'selected' : '' }}>
                                        {{ $i->lead->name }} - {{ $i->product->name ?? 'No Product' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                             <label class="block mb-2 text-sm font-medium text-gray-900">Date</label>
                             <input type="date" name="date" value="{{ old('date', isset($followup) ? $followup->date->format('Y-m-d') : date('Y-m-d')) }}" class="bg-white border-gray-300 text-sm rounded-lg block w-full p-2.5">
                        </div>
                        <div>
                             <label class="block mb-2 text-sm font-medium text-gray-900">Next Follow-up Date</label>
                             <input type="date" name="follow_up_date" value="{{ old('follow_up_date', isset($followup) && $followup->follow_up_date ? $followup->follow_up_date->format('Y-m-d') : '') }}" class="bg-white border-gray-300 text-sm rounded-lg block w-full p-2.5">
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900">Status</label>
                            <select name="is_complete" class="bg-white border-gray-300 text-sm rounded-lg block w-full p-2.5">
                                <option value="0" {{ (old('is_complete', $followup->is_complete ?? 0) == 0) ? 'selected' : '' }}>Pending</option>
                                <option value="1" {{ (old('is_complete', $followup->is_complete ?? 0) == 1) ? 'selected' : '' }}>Complete</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                             <label class="block mb-2 text-sm font-medium text-gray-900">Remarks</label>
                             <textarea name="remarks" rows="2" class="bg-white border-gray-300 text-sm rounded-lg block w-full p-2.5">{{ old('remarks', $followup->remarks ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
            </form>
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
