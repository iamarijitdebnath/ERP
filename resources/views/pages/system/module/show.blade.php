
{{-- {{ dd($module)}} --}}

@extends('layouts.app')

@section('content')
    <section class="bg-gray-50">
        <div class="mx-auto">
            <div class="flex justify-between mb-4">
                <h3 class="text-2xl font-medium font-sans">{{ isset($module) ? 'Edit Module' : 'Add Module' }}</h3>

                <div class="flex gap-2">
                    @if(isset($module))
                        <form action="{{ route('system.module.delete', $module) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this module?');">
                            @csrf
                            @method('DELETE')
                            <button 
                                type="submit"
                                class="flex items-center justify-center px-4 py-2 text-sm font-medium text-white rounded-lg bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 focus:outline-none"
                            >
                                Delete
                            </button>
                        </form>
                    @endif

                    <button 
                        type="submit"
                        form="module-form"
                        class="flex items-center justify-center px-4 py-2 text-sm font-medium text-white rounded-lg bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 focus:outline-none"
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
                <form id="module-form" action="{{ isset($module) ? route('system.module.update', $module) : route('system.module.store') }}" method="POST">
                    @csrf
                    @if(isset($module))
                        @method('PUT')
                    @endif

                    <div class="grid gap-4 sm:grid-cols-2 sm:gap-6">
                        <div>
                            <label for="name" class="block mb-2 text-sm font-medium text-gray-900">Name</label>
                            <input
                                type="text"
                                name="name"
                                id="name"
                                value="{{ old('name', $module->name ?? '') }}"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5"
                                placeholder="Enter module name"
                                required
                            >
                        </div>
                        <div>
                            <label for="slug" class="block mb-2 text-sm font-medium text-gray-900">Slug</label>
                            <input
                                type="text"
                                name="slug"
                                id="slug"
                                value="{{ old('slug', $module->slug ?? '') }}"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5"
                                placeholder="enter-slug"
                                required
                            >
                        </div>
                        <div class="sm:col-span-2">
                            <span class="block mb-2 text-sm font-medium text-gray-900">Status</span>

                            <div class="flex items-center gap-6">
                                <label class="inline-flex items-center">
                                    <input
                                        type="radio"
                                        name="is_active"
                                        value="1"
                                        {{ old('is_active', isset($module->is_active) ? $module->is_active : 1) == 1 ? 'checked' : '' }}
                                        class="w-4 h-4 text-primary-600 bg-gray-100 border-gray-300 focus:ring-primary-500"
                                    >
                                    <span class="ml-2 text-sm text-gray-700">Active</span>
                                </label>

                                <label class="inline-flex items-center">
                                    <input
                                        type="radio"
                                        name="is_active"
                                        value="0"
                                        {{ old('is_active', isset($module->is_active) ? $module->is_active : 1) == 0 ? 'checked' : '' }}
                                        class="w-4 h-4 text-primary-600 bg-gray-100 border-gray-300 focus:ring-primary-500"
                                    >
                                    <span class="ml-2 text-sm text-gray-700">Inactive</span>
                                </label>
                            </div>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
