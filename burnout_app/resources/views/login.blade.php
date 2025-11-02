@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100 py-8">
    <div class="bg-white rounded-lg shadow-md p-5 w-full max-w-sm">
        <h1 class="text-2xl font-bold text-indigo-500 mb-3 text-center">Burnalytics</h1>
        
        <h2 class="text-xl font-semibold text-gray-800 mb-3">Login</h2>

        @if ($errors->any())
            <div class="mb-2 p-2 bg-red-100 border border-red-400 text-red-700 rounded text-sm">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-2.5">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="{{ old('email') }}"
                    class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-200"
                    required
                >
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password"
                    class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-200"
                    required
                >
            </div>re

            <button 
                type="submit"
                class="w-full bg-indigo-500 hover:bg-indigo-600 text-white font-medium py-1.5 text-sm rounded-md transition mt-3"
            >
                Login
            </button>
        </form>
    </div>
</div>
@endsection
