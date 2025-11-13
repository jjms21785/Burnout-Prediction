@extends('layouts.app')

@section('title', 'Records - Burnalytics')

@section('content')
<!-- Main Content Area -->
<main class="flex-1 overflow-y-auto p-3">
    <!-- Table View Container -->
    <div id="tableView" class="rounded-xl shadow-sm p-6 mb-6 bg-white border border-gray-200">
        <!-- Assessments Data Table -->
                <!-- Controls Row -->
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        <!-- Search -->
                        <input type="text" id="searchInput" placeholder="Search..." class="px-4 py-2 text-sm rounded-lg border border-gray-200 bg-white w-48">
                    </div>
                    
                    <div class="flex items-center space-x-2">
                        <!-- Category Filter -->
                        <div class="relative">
                            <button id="categoryBtn" onclick="toggleCategoryDropdown()" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-neutral-800 hover:bg-gray-50 transition">
                                <span id="categoryBtnText">Category</span>
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div id="categoryDropdown" class="hidden absolute right-0 mt-2 w-56 rounded-lg shadow-lg z-50 bg-white border border-gray-200">
                                <div class="py-2">
                                    <button onclick="filterByCategory('all')" class="w-full px-4 py-2 text-sm text-left hover:bg-gray-50 transition text-neutral-800">
                                        All Categories
                                    </button>
                                    <button onclick="filterByCategory('High Burnout')" class="w-full px-4 py-2 text-sm text-left hover:bg-gray-50 transition text-neutral-800">
                                        High Burnout
                                    </button>
                                    <button onclick="filterByCategory('Exhausted')" class="w-full px-4 py-2 text-sm text-left hover:bg-gray-50 transition text-neutral-800">
                                        Exhausted
                                    </button>
                                    <button onclick="filterByCategory('Disengaged')" class="w-full px-4 py-2 text-sm text-left hover:bg-gray-50 transition text-neutral-800">
                                        Disengaged
                                    </button>
                                    <button onclick="filterByCategory('Low Burnout')" class="w-full px-4 py-2 text-sm text-left hover:bg-gray-50 transition text-neutral-800">
                                        Low Burnout
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Sort By Dropdown -->
                        <div class="relative">
                            <button id="sortByBtn" onclick="toggleSortByDropdown()" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-neutral-800 hover:bg-gray-50 transition">
                                Sort by
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div id="sortByDropdown" class="hidden absolute right-0 mt-2 w-48 rounded-lg shadow-lg z-50 bg-white border border-gray-200">
                                <div class="py-2">
                                    <button onclick="sortBy('id')" class="w-full px-4 py-2 text-sm text-left hover:bg-gray-50 transition text-neutral-800">
                                        ID
                                    </button>
                                    <button onclick="sortBy('firstName')" class="w-full px-4 py-2 text-sm text-left hover:bg-gray-50 transition text-neutral-800">
                                        First Name
                                    </button>
                                    <button onclick="sortBy('lastName')" class="w-full px-4 py-2 text-sm text-left hover:bg-gray-50 transition text-neutral-800">
                                        Last Name
                                    </button>
                                    <button onclick="sortBy('gender')" class="w-full px-4 py-2 text-sm text-left hover:bg-gray-50 transition text-neutral-800">
                                        Gender
                                    </button>
                                    <button onclick="sortBy('age')" class="w-full px-4 py-2 text-sm text-left hover:bg-gray-50 transition text-neutral-800">
                                        Age
                                    </button>
                                    <button onclick="sortBy('program')" class="w-full px-4 py-2 text-sm text-left hover:bg-gray-50 transition text-neutral-800">
                                        Program
                                    </button>
                                    <button onclick="sortBy('yearLevel')" class="w-full px-4 py-2 text-sm text-left hover:bg-gray-50 transition text-neutral-800">
                                        Year Level
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Sort Order Arrows -->
                        <button id="sortAscBtn" onclick="setSortOrder('asc')" class="flex items-center justify-center w-9 h-9 rounded-lg transition border border-gray-200 bg-indigo-500 text-white hover:bg-indigo-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                            </svg>
                        </button>
                        <button id="sortDescBtn" onclick="setSortOrder('desc')" class="flex items-center justify-center w-9 h-9 rounded-lg transition border border-gray-200 bg-white text-neutral-800 hover:bg-gray-50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b-2 border-gray-200">
                                <th class="px-4 py-3 text-left font-semibold text-gray-500">ID</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-500">First Name</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-500">Last Name</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-500">Gender</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-500">Age</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-500">Program</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-500">Year Level</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-500">Category</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-500">Action</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <!-- Data will be populated by JavaScript -->
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="flex items-center justify-end mt-4">
                    <div class="flex items-center space-x-2">
                        <button id="startBtn" onclick="changePage('start')" class="flex items-center justify-center w-9 h-9 rounded-lg transition border border-gray-200 bg-white text-neutral-800 hover:bg-indigo-600 hover:text-white disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                            </svg>
                        </button>
                        <button id="prevBtn" onclick="changePage('prev')" class="flex items-center justify-center w-9 h-9 rounded-lg transition border border-gray-200 bg-white text-neutral-800 hover:bg-indigo-600 hover:text-white disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>
                        <button id="nextBtn" onclick="changePage('next')" class="flex items-center justify-center w-9 h-9 rounded-lg transition border border-gray-200 bg-white text-neutral-800 hover:bg-indigo-600 hover:text-white disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                        <button id="endBtn" onclick="changePage('end')" class="flex items-center justify-center w-9 h-9 rounded-lg transition border border-gray-200 bg-white text-neutral-800 hover:bg-indigo-600 hover:text-white disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </div>
                </div>
    </div>

    <!-- View Container - displays detailed assessment information -->
    <div id="viewContainer" class="hidden rounded-xl shadow-sm p-6 mb-6 bg-white border border-gray-200">
        <!-- View Content will be populated by JavaScript -->
        <div id="viewContent"></div>
    </div>
</main>

<!-- Pass configuration to JavaScript -->
<script>
    window.recordsConfig = {
        recordsRoute: '{{ route("admin.records") }}',
        updateRoute: '{{ route("admin.assessment.update", ":id") }}',
        deleteRoute: '{{ route("admin.assessment.delete", ":id") }}',
        csrfToken: '{{ csrf_token() }}'
    };
    
    // Configuration for view functionality
    window.viewConfig = {
        showRoute: '{{ route("admin.view.show", ":id") }}',
        sendEmailRoute: '{{ route("admin.view.send-email", ":id") }}',
        csrfToken: '{{ csrf_token() }}'
    };
</script>

<!-- Load JavaScript modules -->
@vite(['resources/js/records.js', 'resources/js/view.js'])
@endsection
