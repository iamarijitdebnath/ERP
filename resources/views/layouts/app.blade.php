<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ERP') - {{ config('app.name') }}</title>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/afd63ff47d.js" crossorigin="anonymous"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css" integrity="sha512-3pIirOrwegjM6erE5gPSwkUzO+3cTjpnV9lexlNZqvupR64iZBnOOTiiLPb9M36zpMScbmUNIcHUqKD47M719g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js" integrity="sha512-VEd+nq25CkR676O+pLBnDW09R7VQX9Mdiij052gVCp5yVH3jGtH70Ho/UUv4mJDsEdTvqRCFZg0NKGiojGnUCw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').each((index, elem) => {
                $(elem).select2({
                    placeholder: $(elem).data('placeholder'),
                });
            });

            $('.select2-item').select2({
                placeholder: 'Select an item',
                allowClear: true,
                ajax: {
                    url: function() {
                        return $(this).data('url');
                    },
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term,
                            page: params.page
                        };
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;
                        return {
                            results: data.results,
                            pagination: {
                                more: data.pagination.more
                            }
                        };
                    },
                    cache: true
                }
            });

            $('.summernote').summernote({
                height: 250,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                ]
            });
        });
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body>
    <div class="antialiased bg-gray-50">
        <nav class="bg-white border-b border-gray-200 px-4 py-2.5 fixed left-0 right-0 top-0 z-50">
            <div class="flex flex-wrap justify-between items-center">
                <div class="flex-1 flex justify-start items-center">
                    <button data-drawer-target="drawer-navigation" data-drawer-toggle="drawer-navigation" aria-controls="drawer-navigation"
                        class="p-2 mr-2 text-gray-600 rounded-lg cursor-pointer md:hidden hover:text-gray-900 hover:bg-gray-100 focus:bg-gray-100 focus:ring-2 focus:ring-gray-100">
                        <svg aria-hidden="true" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"
                            xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h6a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                                clip-rule="evenodd">
                            </path>
                        </svg>
                        <svg aria-hidden="true" class="hidden w-6 h-6" fill="currentColor" viewBox="0 0 20 20"
                            xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd"></path>
                        </svg>
                        <span class="sr-only">Toggle sidebar</span>
                    </button>
                    <a href="#" class="flex items-center justify-between mr-4">
                        <img src="{{ asset('assets/logo.png') }}" class="mr-3 h-8" alt="Flowbite Logo" />
                        <span class="self-center text-2xl font-semibold whitespace-nowrap"></span>
                    </a>
                </div>
                <div class="flex items-center lg:order-2">
                    <button type="button"
                        class="flex mx-3 text-sm bg-blue-100 rounded-full md:mr-0 focus:ring-4 focus:ring-gray-300"
                        id="user-menu-button" aria-expanded="false" data-dropdown-toggle="dropdown">
                        <span class="sr-only">Open user menu</span>
                       
                        <img class="w-8 h-8 rounded-full"
                            src="{{ asset('assets/images/user.png') }}"
                            alt="user photo" />
                    </button>
                    <!-- Dropdown menu -->
                    <div class="hidden z-50 my-4 w-56 text-base list-none bg-white rounded divide-y divide-gray-100 shadow rounded-xl"
                        id="dropdown">
                        <div class="py-3 px-4">
                            <span class="block text-sm font-semibold text-gray-900">{{ $employee['first_name'] }} {{ $employee['last_name'] }}</span>
                            <span class="block text-sm text-gray-900 truncate">{{ $employee['email'] }}</span>
                        </div>
                        <ul class="py-1 text-gray-700" aria-labelledby="dropdown">
                            <li>
                                <a href="#"
                                    class="block py-2 px-4 text-sm hover:bg-gray-100">My profile</a>
                            </li>
                            <li>
                                <form method="POST" action="{{ route('auth.logout') }}">
                                   @csrf
                                    <button type="submit" class="block w-full py-2 px-4 text-sm text-left hover:bg-gray-100">
                                        <div class="text-sm text-gray-900">
                                            Logout
                                        </div>
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        <aside
            class="fixed top-0 left-0 z-40 w-64 h-screen pt-14 transition-transform -translate-x-full bg-white border-r border-gray-200 md:translate-x-0"
            aria-label="Sidenav" id="drawer-navigation">
            <div class="overflow-y-auto py-5 px-3 h-full bg-white">
                
                <ul class="space-y-2">
                    <li>
                        <a href="{{ route('application.dashboard') }}"
                            class="flex items-center p-2 text-base font-medium text-gray-900 rounded-lg hover:bg-gray-100 group {{ request()->routeIs('application.dashboard') ? 'bg-gray-100' : 'hover:bg-gray-100' }}">
                            <i class="fa-solid fa-grip"></i>
                            <span class="ml-3">Dashboard</span>
                        </a>
                    </li>
                    @foreach($modules as $module)
                        <li>
                            <a href="{{ route('system.module', ['slug' => $module->slug]) }}"
                                class="flex items-center p-2 text-base font-medium text-gray-900 rounded-lg group {{ request()->is('*' . $module->slug . '*') ? 'bg-gray-100' : 'hover:bg-gray-100' }}">
                                <i class="{{ $module->icon }}"></i>
                                <span class="ml-3">{{ $module->name }}</span>
                            </a>
                        </li>
                    @endforeach     
                </ul>
            </div>
        </aside>

        <main class="p-4 md:ml-64 min-h-screen pt-20">
            @yield('content')
        </main>
    </div>
    
    <script>
        
        @if(session()->has('success'))
            toastr.success("{{ session()->get('success') }}");
        @endif

        @if(session()->has('error'))
            toastr.error("{{ session()->get('error') }}");
        @endif
    </script>
    @stack('scripts')
</body>

</html>
