<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Burnalytics')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Ensure links are always clickable */
        nav a, nav button {
            pointer-events: auto !important;
            cursor: pointer !important;
            position: relative;
            z-index: 10;
        }
        /* Prevent any overlay issues */
        .sidebar nav, .sidebar .px-2 {
            position: relative;
            z-index: 10;
        }
        /* Ensure smooth transitions don't block clicks */
        * {
            -webkit-tap-highlight-color: transparent;
        }
        
        /* Replaced custom admin container with Tailwind utilities */
    </style>
    <script>
        // Ensure navigation links work immediately
        document.addEventListener('DOMContentLoaded', function() {
            // Remove any potential event blockers on navigation links
            document.querySelectorAll('nav a').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    // Ensure the default link behavior is not prevented
                    if (this.href && !e.defaultPrevented) {
                        return true;
                    }
                }, true); // Use capture phase to run before other handlers
            });
            
            // Handle logout confirmation
            const logoutForm = document.getElementById('logout-form');
            if (logoutForm) {
                logoutForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    if (confirm('Are you sure you want to logout?')) {
                        this.submit();
                    }
                });
            }
            
            // Verify authentication status is preserved via session cookie
            // This ensures session cookie is working across tabs
            if (window.location.pathname.startsWith('/dashboard') || 
                window.location.pathname.startsWith('/records') ||
                window.location.pathname.startsWith('/questions') ||
                window.location.pathname.startsWith('/files') ||
                window.location.pathname.startsWith('/settings')) {
                // On admin pages, verify session is still valid
                fetch('/auth/check', {
                    method: 'GET',
                    credentials: 'same-origin', // Important: include cookies
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.authenticated) {
                        // Session expired or invalid - redirect to login
                        window.location.href = '/login';
                    }
                })
                .catch(() => {
                    // If check fails, rely on server-side middleware
                    console.log('Auth check failed, relying on server-side validation');
                });
            }
        });
    </script>
</head>
<body class="@if(request()->routeIs('admin.*')) bg-gray-50 @else min-h-screen bg-gradient-to-b from-indigo-50 to-white @endif">
    
    @if(request()->routeIs('admin.*'))
        <!-- Admin Layout with Sidebar -->
        <div class="flex h-screen bg-gray-50">
            <!-- Sidebar -->
            <div class="sidebar w-48 flex flex-col bg-gray-50 border-r border-gray-200">
                <div class="p-4">
                    <h1 class="text-2xl items-center py-5 text-center border-b border-gray-200 font-bold bg-gradient-to-r from-indigo-500 to-indigo-600 bg-clip-text text-transparent">Burnalytics</h1>
            </div>
                <nav class="flex-1 px-2 py-2">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center px-3 py-4 text-xs font-medium transition border-b border-gray-200 rounded-lg @if(request()->routeIs('admin.dashboard')) text-white bg-indigo-500 @else text-neutral-800 hover:bg-indigo-100 @endif">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                    Dashboard
                </a>
                    <a href="{{ route('admin.records') }}" class="flex items-center px-3 py-4 text-xs font-medium transition border-b border-gray-200 rounded-lg @if(request()->routeIs('admin.records')) text-white bg-indigo-500 @else text-neutral-800 hover:bg-indigo-100 @endif">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12h18M3 6h18M3 18h18"></path>
                        </svg>
                        Records
                    </a>
                    <a href="{{ route('admin.questions') }}" class="flex items-center px-3 py-4 text-xs font-medium transition border-b border-gray-200 rounded-lg @if(request()->routeIs('admin.questions')) text-white bg-indigo-500 @else text-neutral-800 hover:bg-indigo-100 @endif">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Questions
                    </a>
                    <a href="{{ route('admin.files') }}" class="flex items-center px-3 py-4 text-xs font-medium transition border-b border-gray-200 rounded-lg @if(request()->routeIs('admin.files')) text-white bg-indigo-500 @else text-neutral-800 hover:bg-indigo-100 @endif">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                        </svg>
                        Files
                    </a>
                </nav>
                
                <!-- Bottom Section: Settings and Logout -->
                <div class="px-2 border-t border-gray-200">
                    <a href="{{ route('admin.settings') }}" class="flex items-center px-3 py-4 text-xs font-medium transition border-b border-gray-200 rounded-lg @if(request()->routeIs('admin.settings')) text-white bg-indigo-500 @else text-neutral-800 hover:bg-indigo-100 @endif">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    Settings
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full flex items-center px-3 py-4 text-xs font-medium transition text-neutral-800 hover:bg-red-50 hover:text-red-600">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            Logout
                        </button>
                    </form>
                </div>
                </div>

            <!-- Main Content for Admin Pages -->
            <div class="flex-1 flex flex-col overflow-hidden w-full max-w-screen-2xl 2xl:max-w-[1920px] mx-auto">
                <!-- Unified Header -->
                <header class="bg-white border-b border-gray-200">
                    <div class="flex items-center justify-between px-8 py-4">
                        <h2 class="text-xl font-semibold text-neutral-800">
                            @if(request()->routeIs('admin.dashboard'))
                                Dashboard
                            @elseif(request()->routeIs('admin.records'))
                                Records
                            @elseif(request()->routeIs('admin.questions'))
                                Questions
                            @elseif(request()->routeIs('admin.files'))
                                Files
                            @elseif(request()->routeIs('admin.settings'))
                                Settings
                            @endif
                        </h2>
                        @yield('header-actions')
                    </div>
                </header>
                
                @yield('content')
            </div>
        </div>
    </div>
    @else
    
    <header class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-3">
                    <div>
                        <h1 class="text-2xl font-bold bg-gradient-to-r from-indigo-500 to-indigo-600 bg-clip-text text-transparent">Burnalytics</h1>
                    </div>
                </div>
                <nav class="flex items-center space-x-8">
                        <a href="{{ route('home') }}" class="text-gray-600 hover:text-indigo-500 transition-all duration-200 transform hover:scale-105">Home</a>
                    <a href="#" class="text-gray-600 hover:text-indigo-500 transition-all duration-200 transform hover:scale-105">About</a>
                </nav>
            </div>
        </div>
    </header>
    <main>
        @yield('content')
    </main>
    @endif
</body>
</html>