<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Burnalytics - Burnout Assessment System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen overflow-hidden bg-gradient-to-br from-indigo-50 via-white to-indigo-100">
    <div class="h-screen flex items-center justify-center p-8">
        <div class="max-w-7xl w-full grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <!-- LEFT COLUMN: App Name and Buttons -->
            <div class="flex flex-col items-center lg:items-start text-center lg:text-left">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-6xl font-bold mb-4">
                        <span class="bg-gradient-to-r from-indigo-500 to-indigo-600 bg-clip-text text-transparent">Burnalytics</span>
                    </h1>
                    <p class="text-xl text-gray-600 leading-relaxed">
                        Determine Academic Burnout using Machine Learning and visualize the data.
                    </p>
                </div>

                <!-- Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 items-center lg:items-start">
                    <a href="{{ route('assessment.index') }}" class="group">
                        <div class="flex items-center justify-center bg-indigo-500 px-6 py-3 rounded-xl shadow-xl border border-gray-200 hover:scale-105 transition duration-200">
                            <span class="text-base font-semibold text-white">Take Assessment</span>
                        </div>
                    </a>

                    <a href="{{ route('admin.dashboard') }}" class="group">
                        <div class="flex items-center justify-center bg-white px-6 py-3 rounded-xl shadow-xl border border-indigo-500 hover:scale-105 transition duration-200">
                            <span class="text-indigo-500 font-semibold">Dashboard</span>
                        </div>
                    </a>
                </div>
            </div>

            <!-- RIGHT COLUMN: About Section -->
            <div class="flex flex-col justify-center">
                <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-200">
                    <h2 class="text-3xl font-bold text-gray-900 mb-6">About</h2>
                    <div class="space-y-4 text-gray-700">
                        <p class="leading-relaxed">
                            Burnalytics is an academic burnout assessment system that uses machine learning technology to help identify and analyze burnout among students.<br>
                            The OLBI-S (Oldenburg Burnout Inventory for Students) assessment combined with intepretations and recommendations to provide insights into burnout categories.<br>
                            Through comprehensive data visualization, administrators can track trends, identify at-risk populations, and make decisions base on the results and data to anayalze burnout among students.<br>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>