
@extends('layouts.app')

@section('content')
    <section class="bg-gray-50">
        <div class="mx-auto">
            <div class="flex justify-between mb-4">
                <h3 class="text-2xl font-medium font-sans">{{ isset($crudEmployee) ? 'Edit Employee' : 'Add Employee' }}</h3>
                <div class="flex items-center gap-2">
                    <a href="{{ route('hrms.employee.index') }}" 
                        class="inline-flex items-center justify-center text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700"
                    >
                        Back
                    </a>
                    @if(isset($crudEmployee))
                        <form id="data_delete" action="{{ route('hrms.employee.delete', ['id' => $crudEmployee->id]) }}" method="POST" class="contents">
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
                        form="employee-form"
                        class="flex items-center justify-center text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800"
                    >
                        Save
                    </button>
                </div>
            </div>

            <div class="relative overflow-hidden bg-white border border-gray-200 p-4 sm:rounded-lg">
                @if ($errors->any())
                    <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
                        <span class="font-medium">Whoops! Something went wrong.</span>
                        <ul class="mt-1.5 ml-4 list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form id="employee-form" action="{{ isset($crudEmployee) ? route('hrms.employee.update', ['id' => $crudEmployee->id]) : route('hrms.employee.store') }}" method="POST" autocomplete="off">
                    @csrf
                    @if(isset($crudEmployee))
                        @method('PUT')
                    @endif

                    <div class="grid gap-4 sm:grid-cols-2 sm:gap-6">
                        <div>
                            <label for="salutation" class="block mb-2 text-sm font-medium text-gray-900">Salutation</label>
                            <select id="salutation" name="salutation" class="select2 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
                                <option value="">Select Salutation</option>
                                @foreach(['Mr', 'Ms', 'Mrs'] as $salutation)
                                    <option value="{{ $salutation }}" {{ old('salutation', $crudEmployee->salutation ?? '') == $salutation ? 'selected' : '' }}>{{ $salutation }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="code" class="block mb-2 text-sm font-medium text-gray-900">Code</label>
                            <input
                                type="text"
                                name="code"
                                id="code"
                                value="{{ old('code', $crudEmployee->code ?? '') }}"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5"
                                placeholder="Employee Code"
                                required
                            >
                        </div>
                        <div>
                            <label for="first_name" class="block mb-2 text-sm font-medium text-gray-900">First Name</label>
                            <input
                                type="text"
                                name="first_name"
                                id="first_name"
                                value="{{ old('first_name', $crudEmployee->first_name ?? '') }}"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5"
                                placeholder="First Name"
                                required
                            >
                        </div>
                        <div>
                            <label for="last_name" class="block mb-2 text-sm font-medium text-gray-900">Last Name</label>
                            <input
                                type="text"
                                name="last_name"
                                id="last_name"
                                value="{{ old('last_name', $crudEmployee->last_name ?? '') }}"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5"
                                placeholder="Last Name"
                                required
                            >
                        </div>
                        <div>
                            <label for="email" class="block mb-2 text-sm font-medium text-gray-900">Email</label>
                            <input
                                type="email"
                                name="email"
                                id="email"
                                value="{{ old('email', $crudEmployee->email ?? '') }}"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5"
                                placeholder="name@company.com"
                                required
                            >
                        </div>

                        
                        @if(!isset($crudEmployee))
                        <div>
                            <label for="password" class="block mb-2 text-sm font-medium text-gray-900">Password</label>
                            <input
                                type="password"
                                name="password"
                                id="password"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5"
                                placeholder="••••••••"
                                required
                                autocomplete="new-password"
                            >
                        </div>
                        @endif

                        
                        <div>
                            <label for="gender" class="block mb-2 text-sm font-medium text-gray-900">Gender</label>
                            <select id="gender" name="gender" class="select2 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
                                <option value="">Select Gender</option>
                                @foreach(['male', 'female', 'other'] as $gender)
                                    <option value="{{ $gender }}" {{ old('gender', $crudEmployee->gender ?? '') == $gender ? 'selected' : '' }}>{{ ucfirst($gender) }}</option>
                                @endforeach
                            </select>
                        </div>

                       
                        <div>
                            <label for="employment_type" class="block mb-2 text-sm font-medium text-gray-900">Employment Type</label>
                            <select id="employment_type" name="employment_type" class="select2 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
                                @foreach(['internship', 'contractual', 'part-time', 'full-time'] as $type)
                                    <option value="{{ $type }}" {{ old('employment_type', $crudEmployee->employment_type ?? 'full-time') == $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                                @endforeach
                            </select>
                        </div>

                        
                        <div>
                            <label for="payment_type" class="block mb-2 text-sm font-medium text-gray-900">Payment Type</label>
                            <select id="payment_type" name="payment_type" class="select2 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
                                @foreach(['salary', 'wage'] as $type)
                                    <option value="{{ $type }}" {{ old('payment_type', $crudEmployee->payment_type ?? 'salary') == $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                                @endforeach
                            </select>
                        </div>

                        
                        <div>
                            <label for="date_of_birth" class="block mb-2 text-sm font-medium text-gray-900">Date of Birth</label>
                            <input
                                type="date"
                                name="date_of_birth"
                                id="date_of_birth"
                                value="{{ old('date_of_birth', $crudEmployee->date_of_birth ?? '') }}"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5"
                            >
                        </div>

                       
                        <div>
                            <label for="date_of_joining" class="block mb-2 text-sm font-medium text-gray-900">Date of Joining</label>
                            <input
                                type="date"
                                name="date_of_joining"
                                id="date_of_joining"
                                value="{{ old('date_of_joining', $crudEmployee->date_of_joining ?? '') }}"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5"
                            >
                        </div>

                        <div>
                            <label for="department_id" class="block mb-2 text-sm font-medium text-gray-900">Department</label>
                            <select id="department_id" name="department_id" class="select2 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
                                <option value="">Select Department</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ old('department_id', $crudEmployee->department_id ?? '') == $department->id ? 'selected' : '' }}>{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>

                      
                        <div>
                            <label for="under_id" class="block mb-2 text-sm font-medium text-gray-900">Reporting To</label>
                            <select id="under_id" name="under_id" class="select2 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
                                <option value="">Select Reporting Manager</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}" {{ old('under_id', $crudEmployee->under_id ?? '') == $emp->id ? 'selected' : '' }}>{{ $emp->first_name }} {{ $emp->last_name }}</option>
                                @endforeach
                            </select>
                        </div>

                       
                            <div>
                            <label for="is_active" class="block mb-2 text-sm font-medium text-gray-900">Status</label>
                            <select id="is_active" name="is_active" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
                                <option value="1" {{ old('is_active', isset($crudEmployee->is_active) ? $crudEmployee->is_active : 1) == 1 ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('is_active', isset($crudEmployee->is_active) ? $crudEmployee->is_active : 1) == 0 ? 'selected' : '' }}>Inactive</option>
                            </select>
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
            <h3 class="mb-5 text-lg font-normal text-gray-700">Are you sure you want to delete this employee?</h3>
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
