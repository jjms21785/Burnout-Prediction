<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Burnalytics - Burnout Assessment System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen overflow-hidden bg-gradient-to-br from-indigo-50 via-white to-indigo-100">
    <div class="h-screen flex flex-col items-center justify-center p-8">
        <!-- Header -->
        <div class="mb-12 text-center">
            <h1 class="text-6xl font-bold mb-4">
                <span class="bg-gradient-to-r from-indigo-500 to-indigo-600 bg-clip-text text-transparent">Burnalytics</span>
            </h1>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto leading-relaxed">
                "Understanding burnout today, <br class="hidden sm:block">preventing exhaustion tomorrow"
            </p>
        </div>

        <!-- Buttons -->
        <div class="flex flex-col sm:flex-row gap-6 items-center">
            <a href="{{ route('assessment.index') }}" class="group relative">
                <div class="absolute -inset-1 bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-2xl opacity-75 group-hover:opacity-100 blur transition duration-200"></div>
                <div class="relative flex items-center space-x-3 bg-white px-12 py-6 rounded-2xl hover:scale-105 transition duration-200">
                    <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <span class="text-xl font-semibold text-gray-800">Student Portal</span>
                </div>
            </a>

            <a href="{{ route('admin.dashboard') }}" class="group relative">
                <div class="absolute -inset-1 bg-gradient-to-r from-indigo-600 to-indigo-700 rounded-2xl opacity-75 group-hover:opacity-100 blur transition duration-200"></div>
                <div class="relative flex items-center space-x-3 bg-white px-12 py-6 rounded-2xl hover:scale-105 transition duration-200">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                    <span class="text-xl font-semibold text-gray-800">Admin Portal</span>
                </div>
            </a>
        </div>

        <!-- Subtle footer -->
        <div class="mt-16 text-sm text-gray-400">
            Early Detection • Timely Intervention • Better Wellbeing
        </div>
    </div>
</body>
</html>