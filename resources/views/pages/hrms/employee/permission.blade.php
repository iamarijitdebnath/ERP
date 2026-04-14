@extends('layouts.app')

@section('content')
    <section class="bg-gray-50">
        <div class="mx-auto">
            <div class="flex justify-between mb-4">
                <h3 class="text-2xl font-medium font-sans">Permissions for {{ $employee->first_name }} {{ $employee->last_name }}</h3>
                <div class="flex gap-2">
                    <a href="{{ route('hrms.employee.index') }}" class="flex items-center justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 focus:ring-4 focus:ring-primary-300 focus:outline-none">
                        Back
                    </a>
                    <button 
                        type="submit"
                        form="permission-form"
                        class="flex items-center justify-center px-4 py-2 text-sm font-medium text-white rounded-lg bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 focus:outline-none"
                    >
                        Save Permissions
                    </button>
                </div>
            </div>

            <div class="relative overflow-hidden bg-white border border-gray-200 p-4 sm:rounded-lg">
                <form id="permission-form" action="{{ route('hrms.employee.permission.save', $employee->id) }}" method="POST">
                    @csrf
                    <div id="accordion-modules" data-accordion="collapse" class="space-y-4">
                        @foreach($modules as $moduleIndex => $module)
                            <div class="rounded-base border border-default overflow-hidden shadow-xs">
                                <h2 id="accordion-module-heading-{{ $moduleIndex }}">
                                    <button type="button" class="flex items-center justify-between w-full p-5 font-medium rtl:text-right text-gray-900 rounded-t-base border border-t-0 border-x-0 border-b-default hover:bg-gray-50 gap-3" data-accordion-target="#accordion-module-body-{{ $moduleIndex }}" aria-expanded="{{ $moduleIndex === 0 ? 'true' : 'false' }}" aria-controls="accordion-module-body-{{ $moduleIndex }}">
                                        <span class="text-xl font-semibold">{{ $module->name }}</span>
                                        <svg data-accordion-icon class="w-5 h-5 {{ $moduleIndex === 0 ? 'rotate-180' : '' }} shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 15 7-7 7 7"/>
                                        </svg>
                                    </button>
                                </h2>
                                <div id="accordion-module-body-{{ $moduleIndex }}" class="{{ $moduleIndex === 0 ? '' : 'hidden' }} border border-s-0 border-e-0 border-t-0 border-b-default" aria-labelledby="accordion-module-heading-{{ $moduleIndex }}">
                                    <div class="p-4">
                                        <div id="accordion-groups-{{ $moduleIndex }}" data-accordion="collapse" class="rounded-base border border-default overflow-hidden shadow-xs">
                                            @foreach($module->menuGroups as $groupIndex => $group)
                                                <h2 id="accordion-group-heading-{{ $moduleIndex }}-{{ $groupIndex }}">
                                                    <button type="button" class="flex items-center justify-between w-full p-5 font-medium rtl:text-right text-gray-800 {{ $groupIndex === 0 ? 'rounded-t-base' : '' }} border border-t-0 border-x-0 {{ $groupIndex === count($module->menuGroups) - 1 ? 'border-b-0' : 'border-b-default' }} hover:bg-gray-50 gap-3" data-accordion-target="#accordion-group-body-{{ $moduleIndex }}-{{ $groupIndex }}" aria-expanded="{{ $groupIndex === 0 ? 'true' : 'false' }}" aria-controls="accordion-group-body-{{ $moduleIndex }}-{{ $groupIndex }}">
                                                        <span class="text-lg">{{ $group->name }}</span>
                                                        <svg data-accordion-icon class="w-5 h-5 {{ $groupIndex === 0 ? 'rotate-180' : '' }} shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 15 7-7 7 7"/>
                                                        </svg>
                                                    </button>
                                                </h2>
                                                <div id="accordion-group-body-{{ $moduleIndex }}-{{ $groupIndex }}" class="{{ $groupIndex === 0 ? '' : 'hidden' }} {{ $groupIndex === count($module->menuGroups) - 1 ? '' : 'border border-s-0 border-e-0 border-t-0 border-b-default' }}" aria-labelledby="accordion-group-heading-{{ $moduleIndex }}-{{ $groupIndex }}">
                                                    <div class="p-4 overflow-x-auto">
                                                        <table class="w-full text-sm text-left text-gray-500">
                                                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                                                <tr>
                                                                    <th scope="col" class="px-4 py-3 w-1/3">Menu</th>
                                                                    <th scope="col" class="px-4 py-3 text-center">Read</th>
                                                                    <th scope="col" class="px-4 py-3 text-center">Create</th>
                                                                    <th scope="col" class="px-4 py-3 text-center">Update</th>
                                                                    <th scope="col" class="px-4 py-3 text-center">Delete</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($group->menus as $menu)
                                                                    @php
                                                                        $permission = $menu->permissions->first();
                                                                    @endphp
                                                                    <tr class="border-b hover:bg-gray-50">
                                                                        <td class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap">
                                                                            {{ $menu->name }}
                                                                        </td>
                                                                        <td class="px-4 py-3 text-center">
                                                                            <input type="checkbox" name="permissions[{{ $menu->id }}][can_read]" value="1" {{ $permission && $permission->can_read ? 'checked' : '' }} class="w-4 h-4 text-primary-600 bg-gray-100 border-gray-300 rounded focus:ring-primary-500">
                                                                        </td>
                                                                        <td class="px-4 py-3 text-center">
                                                                            <input type="checkbox" name="permissions[{{ $menu->id }}][can_create]" value="1" {{ $permission && $permission->can_create ? 'checked' : '' }} class="w-4 h-4 text-primary-600 bg-gray-100 border-gray-300 rounded focus:ring-primary-500">
                                                                        </td>
                                                                        <td class="px-4 py-3 text-center">
                                                                            <input type="checkbox" name="permissions[{{ $menu->id }}][can_update]" value="1" {{ $permission && $permission->can_update ? 'checked' : '' }} class="w-4 h-4 text-primary-600 bg-gray-100 border-gray-300 rounded focus:ring-primary-500">
                                                                        </td>
                                                                        <td class="px-4 py-3 text-center">
                                                                            <input type="checkbox" name="permissions[{{ $menu->id }}][can_delete]" value="1" {{ $permission && $permission->can_delete ? 'checked' : '' }} class="w-4 h-4 text-primary-600 bg-gray-100 border-gray-300 rounded focus:ring-primary-500">
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection