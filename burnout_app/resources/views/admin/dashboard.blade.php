@extends('layouts.app')

@section('title', 'Admin Dashboard - Burnalytix')
@section('subtitle', 'Admin Dashboard')

@section('content')
<!-- Dashboard Content -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Key Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white border border-green-200 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-green-600">Total Assessments</p>
                    <p class="text-2xl font-bold text-green-800">{{ $totalAssessments }}</p>
                    <p class="text-xs text-green-600">All time</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-red-50 border border-red-200 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-red-600">High Risk</p>
                    <p class="text-2xl font-bold text-red-800">{{ $highRisk }}</p>
                    <p class="text-xs text-red-600">Requires immediate attention</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-orange-50 border border-orange-200 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-orange-600">Moderate Risk</p>
                    <p class="text-2xl font-bold text-orange-800">{{ $moderateRisk }}</p>
                    <p class="text-xs text-orange-600">Monitor closely</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-green-50 border border-green-200 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-green-600">Low Risk</p>
                    <p class="text-2xl font-bold text-green-800">{{ $lowRisk }}</p>
                    <p class="text-xs text-green-600">Healthy status</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-8 mb-8">
        <!-- Risk Distribution Pie Chart -->
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-xl font-bold text-green-800 mb-2">Burnout Risk Distribution</h3>
            <p class="text-gray-600 mb-6">Current assessment results breakdown</p>
            <div class="relative">
                <canvas id="riskDistributionChart" width="400" height="400"></canvas>
            </div>
            <div class="flex justify-center space-x-6 mt-4">
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                    <span class="text-sm">Low Risk {{ number_format(($lowRisk / $totalAssessments) * 100, 1) }}%</span>
                </div>
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-orange-500 rounded-full mr-2"></div>
                    <span class="text-sm">Moderate Risk {{ number_format(($moderateRisk / $totalAssessments) * 100, 1) }}%</span>
                </div>
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-red-500 rounded-full mr-2"></div>
                    <span class="text-sm">High Risk {{ number_format(($highRisk / $totalAssessments) * 100, 1) }}%</span>
                </div>
            </div>
        </div>

        <!-- Recent Assessments -->
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-xl font-bold text-green-800 mb-2">Recent Assessments</h3>
            <p class="text-gray-600 mb-6">Latest burnout assessments submitted</p>
            <div class="space-y-4">
                @foreach($recentAssessments as $assessment)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <p class="font-medium text-gray-900">Assessment #{{ $assessment->id }}</p>
                        <p class="text-sm text-gray-500">{{ $assessment->created_at->format('Y-m-d H:i') }}</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-medium {{ $assessment->risk_badge_color }}">
                        {{ $assessment->formatted_risk }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid lg:grid-cols-2 gap-8 mb-8">
        <!-- Department Breakdown -->
        <div class="bg-white border border-gray-200 rounded-lg p-6 h-[28rem] flex flex-col justify-between overflow-hidden pb-25">
            <h3 class="text-xl font-bold text-green-800 mb-2">Burnout Trends by Department</h3>
            <p class="text-gray-600 mb-6">Assessment distribution by academic department</p>
            <canvas id="departmentChart" width="400" height="300"></canvas>
        </div>

        <!-- Trend Over Time -->
        <div class="bg-white border border-gray-200 rounded-lg p-6 h-[28rem] flex flex-col justify-between overflow-hidden pb-25">
            <h3 class="text-xl font-bold text-green-800 mb-2">Burnout Trends Over Time</h3>
            <p class="text-gray-600 mb-6">Monthly assessment trends by risk level</p>
            <canvas id="trendChart" width="400" height="300"></canvas>
        </div>
    </div>

    <!-- Action Items -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h3 class="text-xl font-bold text-green-800 mb-2">Action Items</h3>
        <p class="text-gray-600 mb-6">Recommended actions based on current data</p>
        <div class="space-y-4">
            @if($highRisk > 0)
            <div class="flex items-start space-x-3 p-4 bg-red-50 rounded-lg border-l-4 border-red-400">
                <svg class="w-5 h-5 text-red-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <div>
                    <h4 class="font-semibold text-red-800">High Priority: {{ $highRisk }} students at high burnout risk</h4>
                    <p class="text-sm text-red-600">Immediate counseling intervention recommended</p>
                </div>
            </div>
            @endif

            @if($moderateRisk > 0)
            <div class="flex items-start space-x-3 p-4 bg-orange-50 rounded-lg border-l-4 border-orange-400">
                <svg class="w-5 h-5 text-orange-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
                <div>
                    <h4 class="font-semibold text-orange-800">Medium Priority: {{ $moderateRisk }} students at moderate risk</h4>
                    <p class="text-sm text-orange-600">Preventive workshops and stress management programs suggested</p>
                </div>
            </div>
            @endif

            <div class="flex items-start space-x-3 p-4 bg-blue-50 rounded-lg border-l-4 border-blue-400">
                <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <div>
                    <h4 class="font-semibold text-blue-800">Trend Alert: Monitor department-specific patterns</h4>
                    <p class="text-sm text-blue-600">Consider department-specific interventions and workload assessment</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Risk Distribution Pie Chart
const riskCtx = document.getElementById('riskDistributionChart').getContext('2d');
new Chart(riskCtx, {
    type: 'doughnut',
    data: {
        labels: ['Low Risk', 'Moderate Risk', 'High Risk'],
        datasets: [{
            data: [{{ $lowRisk }}, {{ $moderateRisk }}, {{ $highRisk }}],
            backgroundColor: ['#22c55e', '#f97316', '#ef4444'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        }
    }
});

// Department Chart
const deptCtx = document.getElementById('departmentChart').getContext('2d');
new Chart(deptCtx, {
    type: 'bar',
    data: {
        labels: ['Computer Science', 'Engineering', 'Business', 'Nursing', 'Psychology'],
        datasets: [
            {
                label: 'High Risk',
                data: [8, 5, 4, 3, 3],
                backgroundColor: '#ef4444'
            },
            {
                label: 'Moderate Risk',
                data: [15, 12, 18, 8, 6],
                backgroundColor: '#f97316'
            },
            {
                label: 'Low Risk',
                data: [25, 20, 22, 15, 12],
                backgroundColor: '#22c55e'
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            x: {
                stacked: true
            },
            y: {
                stacked: true
            }
        }
    }
});

// Trend Chart
const trendCtx = document.getElementById('trendChart').getContext('2d');
new Chart(trendCtx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [
            {
                label: 'High Risk',
                data: [12, 15, 18, 23, 20, 25],
                borderColor: '#ef4444',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                fill: true
            },
            {
                label: 'Moderate Risk',
                data: [45, 52, 48, 55, 60, 58],
                borderColor: '#f97316',
                backgroundColor: 'rgba(249, 115, 22, 0.1)',
                fill: true
            },
            {
                label: 'Low Risk',
                data: [78, 85, 92, 98, 95, 102],
                borderColor: '#22c55e',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                fill: true
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            intersect: false,
            mode: 'index'
        }
    }
});
</script>
@endsection