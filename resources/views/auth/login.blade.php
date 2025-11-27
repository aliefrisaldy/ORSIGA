@extends('Components.auth-layout')

@section('title', 'Login')
@section('header-title', 'Welcome Back')
@section('header-subtitle', 'Sign in to your account')

@section('content')
<form method="POST" action="{{ route('login') }}" class="space-y-6">
    @csrf
    
    @if ($errors->any())
        <!-- Updated error styling with red accent but gray background -->
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div>
        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
            Email Address
        </label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-envelope text-telkom-gray"></i>
            </div>
            <input id="email" name="email" type="email" required 
                   class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-telkom-red focus:border-telkom-red transition-colors"
                   placeholder="Enter your email" value="{{ old('email') }}">
        </div>
    </div>

    <div>
        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
            Password
        </label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-lock text-telkom-gray"></i>
            </div>
            <input id="password" name="password" type="password" required 
                   class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-telkom-red focus:border-telkom-red transition-colors"
                   placeholder="Enter your password">
        </div>
    </div>

    <div class="flex items-center">
        <input id="remember" name="remember" type="checkbox" 
               class="h-4 w-4 text-telkom-red focus:ring-telkom-red border-gray-300 rounded">
        <label for="remember" class="ml-2 block text-sm text-gray-700">
            Remember me
        </label>
    </div>

    <!-- Updated button with balanced red color and better hover state -->
    <button type="submit" 
            class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-telkom-red hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-telkom-red transition-colors">
        <i class="fas fa-sign-in-alt mr-2"></i>
        Sign In
    </button>
</form>
@endsection
