@extends('layouts.app')

@section('title', 'Settings - Burnalytics')

@section('content')
<!-- Main Content Area -->
<main class="flex-1 overflow-y-auto p-3">
    @if(session('success'))
    <div class="mb-6 rounded-lg p-4 bg-green-100 border border-green-200">
        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
    </div>
    @endif

    @if(session('error'))
    <div class="mb-6 rounded-lg p-4 bg-red-100 border border-red-200">
        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
    </div>
    @endif

    <!-- User Settings Section -->
    <div class="rounded-xl shadow-sm p-6 bg-white border border-gray-200 mb-6">
        <h3 class="text-lg font-semibold text-neutral-800 mb-4">User Settings</h3>
        
        @if ($errors->any())
            <div class="mb-4 rounded-lg p-3 bg-red-100 border border-red-200">
                @foreach ($errors->all() as $error)
                    <p class="text-xs font-medium text-red-800">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin.update-user') }}" class="space-y-4">
            @csrf
            
            <!-- Email Field -->
            <div>
                <label for="email" class="block text-sm font-medium text-neutral-800 mb-2">Email Address</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="{{ old('email', $user->email ?? '') }}"
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    required
                >
            </div>

            <!-- Current Password Field -->
            <div>
                <label for="current_password" class="block text-sm font-medium text-neutral-800 mb-2">Current Password</label>
                <input 
                    type="password" 
                    id="current_password" 
                    name="current_password" 
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    required
                >
                <p class="text-xs text-gray-500 mt-1">Required to verify your identity before making changes</p>
            </div>

            <!-- New Password Field -->
            <div>
                <label for="new_password" class="block text-sm font-medium text-neutral-800 mb-2">New Password</label>
                <input 
                    type="password" 
                    id="new_password" 
                    name="new_password" 
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    minlength="8"
                >
                <p class="text-xs text-gray-500 mt-1">Leave blank if you don't want to change your password. Minimum 8 characters.</p>
            </div>

            <!-- Confirm New Password Field -->
            <div>
                <label for="new_password_confirmation" class="block text-sm font-medium text-neutral-800 mb-2">Confirm New Password</label>
                <input 
                    type="password" 
                    id="new_password_confirmation" 
                    name="new_password_confirmation" 
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    minlength="8"
                >
            </div>

            <div class="flex justify-end">
                <button 
                    type="submit"
                    class="px-6 py-2 text-sm font-medium rounded-lg transition text-white bg-indigo-500 hover:bg-indigo-600"
                >
                    Update Settings
                </button>
            </div>
        </form>
    </div>
</main>
@endsection
