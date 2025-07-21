<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Burnalytix - Academic Burnout Predictor')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="min-h-screen bg-gradient-to-b from-green-50 to-white">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 bg-green-600 rounded-lg flex items-center justify-center">
                        <div class="w-8 h-8 bg-white rounded-full flex items-center justify-center">
                            <div class="w-4 h-4 bg-green-600 rounded-full"></div>
                        </div>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">Burnalytix</h1>
                        <p class="text-sm text-green-600">@yield('subtitle', 'Academic Burnout Predictor')</p>
                    </div>
                </div>
                <nav class="flex items-center space-x-8">
                    <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'text-green-600 font-medium' : 'text-gray-600 hover:text-green-600' }}">
                        Home
                    </a>
                    <a href="{{ route('assessment.index') }}" class="{{ request()->routeIs('assessment.*') ? 'text-green-600 font-medium' : 'text-gray-600 hover:text-green-600' }}">
                        Assessment
                    </a>
                    <a href="#" class="text-gray-600 hover:text-green-600">About</a>
                    <a href="#" class="text-gray-600 hover:text-green-600">Profile</a>
                    @if(request()->routeIs('admin.*'))
                        <a href="{{ route('admin.dashboard') }}" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                            Sign in
                        </a>
                    @else
                        <a href="{{ route('admin.dashboard') }}" class="border border-green-600 text-green-600 px-4 py-2 rounded-md hover:bg-green-50">
                            Sign in
                        </a>
                    @endif
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-green-800 text-white py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col items-center">
                <div class="flex items-center space-x-3 mb-4">
                    <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center">
                        <div class="w-6 h-6 bg-green-800 rounded-full"></div>
                    </div>
                    <span class="text-xl font-bold">Burnalytix</span>
                </div>
                <p class="text-green-200 text-center mb-6">
                    Empowering students and educators with predictive burnout prevention tool
                </p>
                <div class="flex space-x-8">
                    <a href="#" class="text-green-200 hover:text-white">About</a>
                    <a href="#" class="text-green-200 hover:text-white">Privacy Policy</a>
                    <a href="#" class="text-green-200 hover:text-white">Contact</a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>