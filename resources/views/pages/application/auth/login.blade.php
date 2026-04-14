@extends('layouts.auth')

@section('content')
    <h1 class="text-xl text-center font-bold leading-tight tracking-tight text-gray-900 md:text-2xl">
        Sign in to your account
    </h1>
    <form class="space-y-4 md:space-y-6" action="{{ route('application.auth.login') }}" method="POST">
        @csrf
        <div>
            <label for="email" class="block mb-2 text-sm font-medium text-gray-900">Your email</label>
            <input 
                type="email" 
                name="email" 
                class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5"
                placeholder="name@company.com" 
                required
            />
        </div>
        <div>
            <label for="password" class="block mb-2 text-sm font-medium text-gray-900">Password</label>
            <input 
                type="password" 
                name="password" 
                id="password" 
                placeholder="••••••••"
                class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 mb-2"
                required
            />
            <div class="flex items-center justify-end">
                <a href="#" class="text-sm font-medium text-primary-600 hover:underline">Forgot password?</a>
            </div>
        </div>

        <div>
            <label for="company" class="block mb-2 text-sm font-medium text-gray-900">Company</label>
            <select 
                name="company_id" 
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5"
            >
                @foreach($companies as $company)
                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                @endforeach
            </select>
        </div>
        
        <button 
            type="submit"
            class="w-full text-white bg-primary-600 hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center"
        >Sign in</button>
    </form>
@endsection