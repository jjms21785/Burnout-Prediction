@extends('layouts.app')

@section('title', 'Admin Dashboard - Burnalytix')
@section('subtitle', 'Admin Dashboard')

@section('content')
<template id="dashboardSectionTemplate">
<!-- Dashboard Content -->
<div class="flex flex-col h-full min-h-screen p-6 md:p-10 lg:p-12 xl:p-16" style="max-width: 1600px; margin: 0 auto;">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8 w-full">
        <!-- Metrics 2x2 on the left -->
        <div class="grid grid-cols-2 gap-6 h-full">
            <div class="bg-white border border-green-200 rounded-lg p-6 flex flex-col items-center justify-center">
                <p class="text-sm font-medium text-green-600">Total Assessments</p>
                <p class="text-2xl font-bold text-green-800">{{ $totalAssessments }}</p>
                <p class="text-xs text-green-600">All time</p>
            </div>
            <div class="bg-red-50 border border-red-200 rounded-lg p-6 flex flex-col items-center justify-center">
                <p class="text-sm font-medium text-red-600">High Risk</p>
                <p class="text-2xl font-bold text-red-800">{{ $highRisk }}</p>
                <p class="text-xs text-red-600">Requires immediate attention</p>
            </div>
            <div class="bg-orange-50 border border-orange-200 rounded-lg p-6 flex flex-col items-center justify-center">
                <p class="text-sm font-medium text-orange-600">Moderate Risk</p>
                <p class="text-2xl font-bold text-orange-800">{{ $moderateRisk }}</p>
                <p class="text-xs text-orange-600">Monitor closely</p>
            </div>
            <div class="bg-green-50 border border-green-200 rounded-lg p-6 flex flex-col items-center justify-center">
                <p class="text-sm font-medium text-green-600">Low Risk</p>
                <p class="text-2xl font-bold text-green-800">{{ $lowRisk }}</p>
                <p class="text-xs text-green-600">Healthy status</p>
            </div>
        </div>
        <!-- High Risk Students on the right -->
        <div class="bg-white border border-red-200 rounded-lg p-6 w-full h-full flex flex-col" style="max-height: 480px;">
            <h3 class="text-xl font-bold text-red-800 mb-2 text-center">High Risk Students</h3>
            <div class="overflow-x-auto flex-1">
                <table class="min-w-full divide-y divide-gray-200 text-sm text-center" id="highRiskTable">
                    <thead>
                        <tr class="bg-red-50">
                            <th class="px-4 py-2">Student ID</th>
                            <th class="px-4 py-2">Name</th>
                            <th class="px-4 py-2">Program</th>
                            <th class="px-4 py-2">Burnout Risk</th>
                            <th class="px-4 py-2">OLBI Score</th>
                        </tr>
                    </thead>
                    <tbody id="highRiskTableBody">
                        <tr><td colspan="5" class="text-center text-gray-400 py-8">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script>
    function loadHighRiskStudents() {
        fetch('/admin/high-risk-students')
            .then(res => res.json())
            .then(data => {
                const tbody = document.getElementById('highRiskTableBody');
                if (!data || !Array.isArray(data) || data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center text-gray-400 py-8">No high risk students found.</td></tr>';
                    return;
                }
                tbody.innerHTML = data.map(row => `
                    <tr>
                        <td class="px-4 py-2 text-center">${row.student_id || 'N/A'}</td>
                        <td class="px-4 py-2 text-center">${row.name}</td>
                        <td class="px-4 py-2 text-center">${row.program}</td>
                        <td class="px-4 py-2 text-center">${row.risk}</td>
                        <td class="px-4 py-2 text-center">${row.score}</td>
                    </tr>
                `).join('');
            })
            .catch(() => {
                document.getElementById('highRiskTableBody').innerHTML = '<tr><td colspan="5" class="text-center text-red-400 py-8">Failed to load data.</td></tr>';
            });
    }
    document.addEventListener('DOMContentLoaded', function() {
        loadHighRiskStudents();
    });
    </script>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8 w-full flex-1">
        <!-- Risk Distribution Pie Chart -->
        <div class="bg-white border border-gray-200 rounded-lg p-6 h-full flex flex-col" style="max-height: 480px;">
            <h3 class="text-xl font-bold text-green-800 mb-2">Burnout Risk Distribution</h3>
            <p class="text-gray-600 mb-6">Current assessment results breakdown</p>
            @if($totalAssessments > 0)
                <div class="relative">
                    <canvas id="riskDistributionChart" width="400" height="400"></canvas>
                </div>
                <div class="flex justify-center space-x-6 mt-4">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                        <span class="text-sm">
                            Low Risk {{ number_format(($lowRisk / $totalAssessments) * 100, 1) }}%
                        </span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-orange-500 rounded-full mr-2"></div>
                        <span class="text-sm">
                            Moderate Risk {{ number_format(($moderateRisk / $totalAssessments) * 100, 1) }}%
                        </span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-red-500 rounded-full mr-2"></div>
                        <span class="text-sm">
                            High Risk {{ number_format(($highRisk / $totalAssessments) * 100, 1) }}%
                        </span>
                    </div>
                </div>
            @else
                <div class="flex items-center justify-center h-40 text-gray-400">No data available for this chart.</div>
            @endif
        </div>

        <!-- Recent Assessments -->
        <div class="bg-white border border-gray-200 rounded-lg p-6 h-full flex flex-col" style="max-height: 480px;">
            <h3 class="text-xl font-bold text-green-800 mb-2">Recent Assessments</h3>
            <p class="text-gray-600 mb-6">Latest burnout assessments submitted</p>
            @if(isset($recentAssessments) && $recentAssessments->count() > 0)
                <div class="space-y-4">
                    @foreach($recentAssessments->take(5) as $assessment)
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
            @else
                <div class="flex items-center justify-center h-24 text-gray-400">No recent assessments available.</div>
            @endif
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8 w-full flex-1">
        <!-- Department Breakdown -->
        <div class="bg-white border border-gray-200 rounded-lg p-6 h-full flex flex-col" style="max-height: 480px;">
            <h3 class="text-xl font-bold text-green-800 mb-2">Burnout Trends by Department</h3>
            <p class="text-gray-600 mb-6">Assessment distribution by academic department</p>
            @if(isset($departmentData) && count($departmentData['labels']) > 0)
                <canvas id="departmentChart" width="400" height="300"></canvas>
            @else
                <div class="flex items-center justify-center h-full text-gray-400">No data available for this chart.</div>
            @endif
        </div>

        <!-- Trend Over Time -->
        <div class="bg-white border border-gray-200 rounded-lg p-6 h-full flex flex-col" style="max-height: 480px;">
            <h3 class="text-xl font-bold text-green-800 mb-2">Burnout Trends Over Time</h3>
            <p class="text-gray-600 mb-6">Monthly assessment trends by risk level</p>
            @if(isset($trendData) && count($trendData['labels']) > 0)
                <canvas id="trendChart" width="400" height="300"></canvas>
            @else
                <div class="flex items-center justify-center h-full text-gray-400">No data available for this chart.</div>
            @endif
        </div>
    </div>

    <!-- Action Items -->
    <div class="bg-white border border-gray-200 rounded-lg p-6 w-full flex flex-col" style="max-height: 400px;">
        <h3 class="text-xl font-bold text-green-800 mb-2">Action Items</h3>
        <p class="text-gray-600 mb-6">Recommended actions based on current data</p>
        @if($totalAssessments > 0)
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
        @else
        <div class="flex items-center justify-center h-24 text-gray-400">No action items available.</div>
        @endif
    </div>
</div>
</template>
<template id="dataMonitoringSectionTemplate">
<div class="flex flex-col h-full min-h-screen p-6 md:p-10 lg:p-12 xl:p-16" style="max-width: 1600px; margin: 0 auto;">
    <div class="bg-white border border-gray-200 rounded-lg p-6 mb-8">
        <div class="flex flex-col md:flex-row md:items-end md:space-x-6 space-y-4 md:space-y-0">
            <div class="flex-1">
                <input id="dmSearchInput" type="text" placeholder="Search keywords" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-200" />
            </div>
            <div class="flex flex-wrap gap-4 mt-4 md:mt-0">
                <select id="dmGradeFilter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">Grade Level</option>
                    <option value="1st Year">1st Year</option>
                    <option value="2nd Year">2nd Year</option>
                    <option value="3rd Year">3rd Year</option>
                    <option value="4th Year">4th Year</option>
                </select>
                <input id="dmAgeFilter" type="number" min="0" placeholder="Age" class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-24" />
                <select id="dmGenderFilter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
                <select id="dmDeptFilter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">Department / Program</option>
                </select>
                <select id="dmRiskFilter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">Risk Level</option>
                    <option value="Low">Low</option>
                    <option value="Moderate">Moderate</option>
                    <option value="High">High</option>
                </select>
                <select id="dmTimeFilter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">Time Period</option>
                    <option value="7days">Last 7 days</option>
                    <option value="month">This month</option>
                    <option value="custom">Last 6 months</option>
                    <option value="custom">1</option>
                </select>
                <select id="dmOlbiSort" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">OLBI Score</option>
                    <option value="desc">Highest - Lowest</option>
                    <option value="asc">Lowest - Highest</option>
                </select>
                <select id="dmConfSort" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">Confidence (%)</option>
                    <option value="desc">Highest - Lowest</option>
                    <option value="asc">Lowest - Highest</option>
                </select>
            </div>
        </div>
    </div>
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm" id="dmTable">
                <thead>
                    <tr class="bg-green-50">
                        <th class="px-4 py-2 text-left">Student ID</th>
                        <th class="px-4 py-2 text-left">Name</th>
                        <th class="px-4 py-2 text-left">Gender</th>
                        <th class="px-4 py-2 text-left">Age</th>
                        <th class="px-4 py-2 text-left">Academic Program</th>
                        <th class="px-4 py-2 text-left">Grade/Year</th>
                        <th class="px-4 py-2 text-left">Burnout Risk</th>
                        <th class="px-4 py-2 text-left">OLBI Score</th>
                        <th class="px-4 py-2 text-left">Confidence (%)</th>
                        <th class="px-4 py-2 text-left">Last Update</th>
                        <th class="px-4 py-2 text-left">View Profile</th>
                    </tr>
                </thead>
                <tbody id="dmTableBody">
                    <tr><td colspan="11" class="text-center text-gray-400 py-8">Loading data...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
</template>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Section switching logic
    const sectionContainer = document.getElementById('adminSectionContainer');
    const dashboardBtn = document.getElementById('sidebarDashboardBtn');
    const dataMonitoringBtn = document.getElementById('sidebarDataMonitoringBtn');
    const dashboardTemplate = document.getElementById('dashboardSectionTemplate');
    const dataMonitoringTemplate = document.getElementById('dataMonitoringSectionTemplate');
    function showSection(section) {
        if(section === 'dashboard') {
            sectionContainer.innerHTML = dashboardTemplate.innerHTML;
        } else if(section === 'data-monitoring') {
            sectionContainer.innerHTML = dataMonitoringTemplate.innerHTML;
            loadDataMonitoring();
        }
    }
    if(dashboardBtn && dataMonitoringBtn) {
        dashboardBtn.addEventListener('click', function(e) {
            e.preventDefault();
            showSection('dashboard');
        });
        dataMonitoringBtn.addEventListener('click', function(e) {
            e.preventDefault();
            showSection('data-monitoring');
        });
    }
    // Show dashboard by default
    showSection('dashboard');
    // Data Monitoring logic
    window.loadDataMonitoring = function() {
        fetch('/admin/data-monitoring')
            .then(res => res.json())
            .then(data => renderDMTable(data))
            .catch(() => {
                document.getElementById('dmTableBody').innerHTML = '<tr><td colspan="11" class="text-center text-red-400 py-8">Failed to load data.</td></tr>';
            });
        // TODO: Add filter/search event listeners and AJAX calls
    };
    function renderDMTable(data) {
        const tbody = document.getElementById('dmTableBody');
        if(!data || !Array.isArray(data) || data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="11" class="text-center text-gray-400 py-8">No data available.</td></tr>';
            return;
        }
        tbody.innerHTML = data.map(row => `
            <tr>
                <td class="px-4 py-2">${row.student_id || '-'}</td>
                <td class="px-4 py-2">${row.name || '-'}</td>
                <td class="px-4 py-2">${row.gender || '-'}</td>
                <td class="px-4 py-2">${row.age || '-'}</td>
                <td class="px-4 py-2">${row.program || '-'}</td>
                <td class="px-4 py-2">${row.grade || '-'}</td>
                <td class="px-4 py-2">${riskBadge(row.risk)}</td>
                <td class="px-4 py-2">${row.olbi_score ?? '-'}</td>
                <td class="px-4 py-2">${row.confidence ?? '-'}</td>
                <td class="px-4 py-2">${row.last_update || '-'}</td>
                <td class="px-4 py-2"><button class="text-blue-600 hover:underline">🔍 View</button></td>
            </tr>
        `).join('');
    }
    function riskBadge(risk) {
        if(risk === 'High') return '<span class="text-red-700 font-bold">🔴 High</span>';
        if(risk === 'Moderate') return '<span class="text-yellow-700 font-bold">🟡 Moderate</span>';
        if(risk === 'Low') return '<span class="text-green-700 font-bold">🟢 Low</span>';
        return '-';
    }

    function loadHighRiskStudents() {
        fetch('/admin/high-risk-students')
            .then(res => res.json())
            .then(data => {
                const tbody = document.getElementById('highRiskTableBody');
                if (!data || !Array.isArray(data) || data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center text-gray-400 py-8">No high risk students found.</td></tr>';
                    return;
                }
                tbody.innerHTML = data.map(row => `
                    <tr>
                        <td class="px-4 py-2 text-center">${row.student_id || 'N/A'}</td>
                        <td class="px-4 py-2 text-center">${row.name}</td>
                        <td class="px-4 py-2 text-center">${row.program}</td>
                        <td class="px-4 py-2 text-center">${row.risk}</td>
                        <td class="px-4 py-2 text-center">${row.score}</td>
                    </tr>
                `).join('');
            })
            .catch(() => {
                document.getElementById('highRiskTableBody').innerHTML = '<tr><td colspan="5" class="text-center text-red-400 py-8">Failed to load data.</td></tr>';
            });
    }
    document.addEventListener('DOMContentLoaded', function() {
        loadHighRiskStudents();
    });
});
</script>
@endsection