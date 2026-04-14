@extends('layouts.app')

@section('content')
<div>
    <h1 class="text-2xl font-medium mb-4">
        {{ $module->name }}
    </h1>

    <div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-4">
            @foreach($module->menuGroups as $group)
                {{-- <div> --}}
                    <div 
                        class="block border border-gray-200 {{ $group->color ? "bg-[#{$group->color}]" : 'bg-white' }} rounded-md overflow-hidden p-4"
                        aria-label="{{ $group->name }}"
                    >
                        <h3 class="font-medium text-black text-md mb-4">
                            {{ $group->name }}
                        </h3>
                        
                        <div class="flex flex-col gap-2">
                            @foreach($group->menus as $menu)
                                <a 
                                    class="text-sm text-gray-500 hover:text-gray-900"
                                    href="{{ route($menu->route) }}"
                                >
                                    {{ $menu->name }}
                                </a>
                            @endforeach
                        </div>
                        
                    </div>
                {{-- </div> --}}
            @endforeach
        </div>
    </div>
</div>
@endsection
