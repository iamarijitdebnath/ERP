<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    </head>

    <body>

        <section class="bg-gray-50">
            <div class="flex flex-col items-center justify-center px-6 py-8 mx-auto md:h-screen lg:py-0">
                <div class="w-full bg-white rounded-lg shadow md:mt-0 sm:max-w-md xl:p-0">
                    <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
                        <a href="#" class="flex justify-center items-center mb-6 text-2xl font-semibold text-gray-900">
                            <img 
                                class="w-8 h-8 mr-2" 
                                src="{{ asset('assets/logo.png') }}"
                                alt="logo"
                            >
                        </a>
                        
                        @yield('content')
                    </div>
                </div>
            </div>
        </section>

        @stack('scripts')
        </body>

        </html>
