@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="flex h-screen bg-gray-100">
    <!-- Sidebar -->
    <div class="w-64 bg-white border-r border-gray-200 flex flex-col">
        <div class="p-6 border-b border-gray-200">
            <h1 class="text-2xl font-semibold text-purple-600">TripyTrip</h1>
        </div>
        <div class="flex-1 py-4 overflow-y-auto">
            <nav class="space-y-1 px-2">
                <a href="#" class="flex items-center w-full px-4 py-3 text-sm font-medium rounded-r-md text-blue-600 bg-blue-50 border-l-4 border-blue-600">
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    Dashboard
                </a>
                <a href="#" class="flex items-center w-full px-4 py-3 text-sm font-medium rounded-r-md text-gray-600 hover:bg-gray-50">
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    Check In-Out
                </a>
                <a href="#" class="flex items-center w-full px-4 py-3 text-sm font-medium rounded-r-md text-gray-600 hover:bg-gray-50">
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 4h4"></path></svg>
                    Rooms
                </a>
                <a href="#" class="flex items-center w-full px-4 py-3 text-sm font-medium rounded-r-md text-gray-600 hover:bg-gray-50">
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Messages
                </a>
                <a href="#" class="flex items-center w-full px-4 py-3 text-sm font-medium rounded-r-md text-gray-600 hover:bg-gray-50">
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                    Customer Review
                </a>
                <a href="#" class="flex items-center w-full px-4 py-3 text-sm font-medium rounded-r-md text-gray-600 hover:bg-gray-50">
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h10m4 0a1 1 0 11-2 0 1 1 0 012 0z"></path></svg>
                    Billing System
                </a>
                <a href="#" class="flex items-center w-full px-4 py-3 text-sm font-medium rounded-r-md text-gray-600 hover:bg-gray-50">
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    Food Delivery
                </a>
            </nav>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Header -->
        <header class="bg-white border-b border-gray-200 flex items-center justify-between px-6 py-4">
            <h1 class="text-xl font-semibold text-gray-800">Dashboard</h1>
            <div class="flex items-center space-x-4">
                <button class="relative p-2 text-gray-600 hover:text-gray-900">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    <span class="absolute top-0 right-0 h-2 w-2 bg-red-500 rounded-full"></span>
                </button>

                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-md text-sm font-medium">
                        Logout
                    </button>
                </form>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
            <div class="flex justify-end mb-4">
                <p class="text-sm text-gray-600">{{ now()->format('l // F jS, Y') }}</p>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow p-4 flex items-center">
                    <div class="bg-blue-50 p-3 rounded-full mr-4">
                        <svg class="h-6 w-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Arrival <span class="text-xs">(This week)</span></p>
                        <div class="flex items-center">
                            <h3 class="text-2xl font-bold mr-2">73</h3>
                            <span class="text-xs px-1.5 py-0.5 bg-green-100 text-green-600 rounded">+24%</span>
                        </div>
                        <p class="text-xs text-gray-500">Previous week: 35</p>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-4 flex items-center">
                    <div class="bg-amber-50 p-3 rounded-full mr-4">
                        <svg class="h-6 w-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Departure <span class="text-xs">(This week)</span></p>
                        <div class="flex items-center">
                            <h3 class="text-2xl font-bold mr-2">35</h3>
                            <span class="text-xs px-1.5 py-0.5 bg-red-100 text-red-600 rounded">-12%</span>
                        </div>
                        <p class="text-xs text-gray-500">Previous week: 97</p>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-4 flex items-center">
                    <div class="bg-cyan-50 p-3 rounded-full mr-4">
                        <svg class="h-6 w-6 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Booking <span class="text-xs">(This week)</span></p>
                        <div class="flex items-center">
                            <h3 class="text-2xl font-bold mr-2">237</h3>
                            <span class="text-xs px-1.5 py-0.5 bg-green-100 text-green-600 rounded">+31%</span>
                        </div>
                        <p class="text-xs text-gray-500">Previous week: 187</p>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-sm text-gray-500 mb-2">Today Activities</p>
                    <div class="flex justify-between mb-2">
                        <div class="text-center">
                            <div class="bg-blue-500 text-white rounded-full w-10 h-10 flex items-center justify-center mx-auto mb-1">5</div>
                            <p class="text-xs">Room<br>Available</p>
                        </div>
                        <div class="text-center">
                            <div class="bg-blue-500 text-white rounded-full w-10 h-10 flex items-center justify-center mx-auto mb-1">10</div>
                            <p class="text-xs">Room<br>Blocked</p>
                        </div>
                        <div class="text-center">
                            <div class="bg-blue-500 text-white rounded-full w-10 h-10 flex items-center justify-center mx-auto mb-1">15</div>
                            <p class="text-xs">Guest</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <p class="text-xs text-gray-500">Total Revenue</p>
                        <p class="text-lg font-bold">Rs.35k</p>
                    </div>
                </div>
            </div>

            <!-- Booking Table -->
            <div class="bg-white rounded-lg shadow mb-6">
                <div class="p-4 border-b border-gray-200">
                    <h2 class="text-base font-medium">Todays Booking <span class="text-xs font-normal text-gray-500">(8 Guest today)</span></h2>
                </div>
                <div class="p-4">
                    <div class="flex flex-col md:flex-row justify-between mb-4 gap-4">
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            <input type="text" placeholder="Search guest by name or phone number or booking ID" class="pl-10 pr-4 py-2 border border-gray-300 rounded-md w-full md:w-96 text-sm">
                        </div>
                        <button class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium">
                            + Add Booking
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="text-left py-3 px-4 font-medium">NAME</th>
                                    <th class="text-left py-3 px-4 font-medium">BOOKING ID</th>
                                    <th class="text-left py-3 px-4 font-medium">NIGHTS</th>
                                    <th class="text-left py-3 px-4 font-medium">ROOM TYPE</th>
                                    <th class="text-left py-3 px-4 font-medium">GUESTS</th>
                                    <th class="text-left py-3 px-4 font-medium">PAID</th>
                                    <th class="text-left py-3 px-4 font-medium">COST</th>
                                    <th class="text-left py-3 px-4 font-medium">ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bookingData as $booking)
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="py-3 px-4">
                                        <div class="flex items-center">
                                            <div class="h-8 w-8 rounded-full bg-gray-300 mr-3 flex items-center justify-center text-white text-xs font-bold">
                                                {{ substr($booking['name'], 0, 1) }}
                                            </div>
                                            <div>
                                                <p class="font-medium">{{ $booking['name'] }}</p>
                                                <p class="text-xs text-gray-500">{{ $booking['phone'] }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">{{ $booking['bookingId'] }}</td>
                                    <td class="py-3 px-4">{{ $booking['nights'] }}</td>
                                    <td class="py-3 px-4">
                                        @if(is_array($booking['roomType']))
                                            @foreach($booking['roomType'] as $type)
                                                <p>{{ $type }}</p>
                                            @endforeach
                                        @else
                                            {{ $booking['roomType'] }}
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">{{ $booking['guests'] }} Guests</td>
                                    <td class="py-3 px-4">
                                        @if($booking['paid'] === 'paid')
                                            <span class="px-2 py-1 bg-green-100 text-green-600 rounded text-xs">paid</span>
                                        @else
                                            {{ $booking['paid'] }}
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">{{ $booking['cost'] }}</td>
                                    <td class="py-3 px-4">
                                        <div class="flex space-x-2">
                                            <button class="p-1 hover:bg-gray-100 rounded">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            </button>
                                            <button class="p-1 hover:bg-gray-100 rounded">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection
