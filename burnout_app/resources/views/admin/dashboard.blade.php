@extends('layouts.app')

@section('title', 'Dashboard - Burnalytics')

@section('header-actions')
<div class="flex items-center space-x-2">
    <button onclick="exportToExcel()" class="flex items-center px-3 py-1.5 text-xs font-medium text-gray-600 bg-gray-100 border border-gray-200 rounded-lg hover:bg-gray-200 transition">
        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        Excel
    </button>
    <button onclick="exportToCSV()" class="flex items-center px-3 py-1.5 text-xs font-medium text-gray-600 bg-gray-100 border border-gray-200 rounded-lg hover:bg-gray-200 transition">
        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        CSV
    </button>
    <button onclick="exportToPDF()" class="flex items-center px-3 py-1.5 text-xs font-medium text-gray-600 bg-gray-100 border border-gray-200 rounded-lg hover:bg-gray-200 transition">
        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
        </svg>
        PDF
    </button>
</div>
@endsection

@section('content')
        <!-- Main Content Area - Two Column Layout -->
        <main class="flex-1 overflow-y-auto p-3">
            <div class="grid grid-cols-12 gap-6">
                <!-- LEFT COLUMN (5 cols) -->
                <div class="col-span-5 space-y-4">
                    <!-- Total Assessments -->
                    <div class="rounded-xl shadow-sm p-6 bg-white border border-gray-200">
                        <div class="flex items-center gap-6">
                            <!-- Left: Total Assessments -->
                            <div class="flex-1 flex flex-col items-center justify-center border-r border-gray-300">
                                <div class="w-16 h-16 rounded-full flex items-center justify-center mb-3 bg-indigo-100">
                                    <svg class="w-8 h-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-sm mb-1 text-gray-500">Total Assessments</h3>
                            </div>
                            
                            <!-- Right: Burnout Categories Breakdown -->
                            <div class="flex-1 flex flex-col justify-center">
                                <h3 class="text-sm font-semibold mb-3 text-neutral-800">Total Each Categories</h3>
                                <div class="space-y-2">
                                    <!-- High Burnout -->
                                    <div class="flex items-center justify-between py-1">
                                        <div class="flex items-center">
                                            <div class="w-3 h-3 rounded-full mr-2 bg-red-500"></div>
                                            <span class="text-xs text-neutral-800">High Burnout</span>
                                        </div>
                                    </div>
                                    <!-- Exhaustion -->
                                    <div class="flex items-center justify-between py-1">
                                        <div class="flex items-center">
                                            <div class="w-3 h-3 rounded-full mr-2 bg-orange-500"></div>
                                            <span class="text-xs text-neutral-800">Exhaustion</span>
                                        </div>
                                    </div>
                                    <!-- Disengagement -->
                                    <div class="flex items-center justify-between py-1">
                                        <div class="flex items-center">
                                            <div class="w-3 h-3 rounded-full mr-2 bg-yellow-500"></div>
                                            <span class="text-xs text-neutral-800">Disengagement</span>
                                        </div>
                                    </div>
                                    <!-- Low Burnout -->
                                    <div class="flex items-center justify-between py-1">
                                        <div class="flex items-center">
                                            <div class="w-3 h-3 rounded-full mr-2 bg-green-500"></div>
                                            <span class="text-xs text-neutral-800">Low Burnout</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pie Charts Grid (2x3) -->
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Burnout Categories Results -->
                        <div class="rounded-xl shadow-sm p-6 bg-white border border-gray-200">
                            <h4 class="text-sm font-semibold mb-4 text-center text-neutral-800">Burnout Categories</h4>
                            <canvas id="burnoutChart" class="max-h-45"></canvas>
                    </div>

                        <!-- Age Distribution -->
                        <div class="rounded-xl shadow-sm p-6 bg-white border border-gray-200">
                            <h4 class="text-sm font-semibold mb-4 text-center text-neutral-800">Age</h4>
                            <canvas id="ageChart" class="max-h-45"></canvas>
                    </div>

                        <!-- Gender Distribution -->
                        <div class="rounded-xl shadow-sm p-6 bg-white border border-gray-200">
                            <h4 class="text-sm font-semibold mb-4 text-center text-neutral-800">Gender</h4>
                            <canvas id="genderChart" class="max-h-45"></canvas>
                </div>

                        <!-- Year Level Distribution -->
                        <div class="rounded-xl shadow-sm p-6 bg-white border border-gray-200">
                            <h4 class="text-sm font-semibold mb-4 text-center text-neutral-800">Year Level</h4>
                            <canvas id="yearChart" class="max-h-45"></canvas>
        </div>

                        <!-- Program Distribution -->
                        <div class="col-span-2 rounded-xl shadow-sm p-6 bg-white border border-gray-200">
                            <h4 class="text-sm font-semibold mb-4 text-center text-neutral-800">Program</h4>
                            <canvas id="programChart" class="max-h-45"></canvas>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN (7 cols) -->
                <div class="col-span-7 space-y-6">
                    <!-- Latest Submissions -->
                    <div class="rounded-xl shadow-sm p-5 bg-white border border-gray-200">
                        <h3 class="text-base font-semibold mb-3 flex items-center text-neutral-800">
                            Latest Submissions
                        </h3>
                        <div class="space-y-1" id="latestSubmissions">
                            <p class="text-sm text-center py-4 text-gray-500">Loading...</p>
        </div>
    </div>

                    <!-- Question Statistics -->
                    <div class="rounded-xl shadow-sm p-5 bg-white border border-gray-200 2xl:min-h-[890px] flex flex-col">
                            <!-- Column Headers -->
                        <div class="grid grid-cols-11 gap-4 items-center mb-2">
                            <div class="col-span-5 text-xl font-semibold text-neutral-800">Question Statistics</div>
                                <div class="col-span-6 text-xs text-center font-semibold uppercase text-gray-500">Response Distribution</div>
                </div>
                            <!-- Likert Scale -->
                        <div class="grid grid-cols-11 gap-4 items-center mb-3 pb-2 border-b border-gray-200">
                                <div class="col-span-5"></div>
                                <div class="col-span-6">
                                    <div class="text-[10px] text-center text-gray-500">
                                        Strongly Agree | Agree | Disagree | Strongly Disagree
            </div>
        </div>
</div>

                        <!-- Questions Container -->
                        <div class="space-y-4 mb-4 flex-1 overflow-y-auto" id="questionsList"></div>
                        
                        <!-- Pagination Controls -->
                        <div class="flex justify-end items-center space-x-2 mt-6">
                            <button id="prevPageBtn" onclick="changeQuestionPage(-1)" class="flex items-center justify-center w-9 h-9 rounded-lg transition border border-gray-200 bg-white text-neutral-800 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </button>
                            <button id="nextPageBtn" onclick="changeQuestionPage(1)" class="flex items-center justify-center w-9 h-9 rounded-lg transition border border-gray-200 bg-white text-neutral-800 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
            </div>
        </div>
    </div>
        </div>
        </main>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    loadDashboardData();
    initializeCharts();
    loadQuestionStatistics();
});

function loadDashboardData() {
    // Clear temporary/mock latest submissions
    const container = document.getElementById('latestSubmissions');
    container.innerHTML = '<p class="text-sm text-center py-4 text-gray-500">Unavailable</p>';
}

function initializeCharts() {
    const chartConfig = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: { 
                    font: { size: 12 }, 
                    padding: 10,
                    boxWidth: 15
                }
            }
        }
    };

    // Burnout Categories Chart (cleared temporary/dynamic data)
    new Chart(document.getElementById('burnoutChart'), {
        type: 'doughnut',
        data: {
            labels: ['High', 'Exhausted', 'Disengaged', 'Low'],
            datasets: [{
                data: [0, 0, 0, 0],
                backgroundColor: ['#6366f1', '#818cf8', '#a5b4fc', '#c7d2fe']
            }]
        },
        options: chartConfig
    });

    // Age Chart (cleared temporary data)
    new Chart(document.getElementById('ageChart'), {
        type: 'doughnut',
        data: {
            labels: ['18-20', '21-23', '24-26', '27+'],
            datasets: [{
                data: [0, 0, 0, 0],
                backgroundColor: ['#6366f1', '#818cf8', '#a5b4fc', '#c7d2fe']
            }]
        },
        options: chartConfig
    });

    // Gender Chart (cleared temporary data)
    new Chart(document.getElementById('genderChart'), {
        type: 'doughnut',
        data: {
            labels: ['Male', 'Female'],
            datasets: [{
                data: [0, 0],
                backgroundColor: ['#6366f1', '#c7d2fe']
            }]
        },
        options: chartConfig
    });

    // Program Chart (cleared temporary data)
    new Chart(document.getElementById('programChart'), {
        type: 'doughnut',
        data: {
            labels: ['CS', 'Eng', 'Bus', 'Other'],
            datasets: [{
                data: [0, 0, 0, 0],
                backgroundColor: ['#6366f1', '#818cf8', '#a5b4fc', '#c7d2fe']
            }]
        },
        options: chartConfig
    });

    // Year Level Chart (cleared temporary data)
    new Chart(document.getElementById('yearChart'), {
        type: 'doughnut',
        data: {
            labels: ['1st', '2nd', '3rd', '4th', '5th'],
            datasets: [{
                data: [0, 0, 0, 0, 0],
                backgroundColor: ['#6366f1', '#818cf8', '#a5b4fc', '#c7d2fe', '#e0e7ff']
            }]
        },
        options: chartConfig
    });
}

// Question pagination state
let currentQuestionPage = 1;
const questionsPerPage = 10;
let allQuestions = [];

function loadQuestionStatistics() {
    // Clear temporary/mock question statistics
    const container = document.getElementById('questionsList');
    if (container) {
        container.innerHTML = '<p class="text-sm text-center py-4 text-gray-500">Unavailable</p>';
    }
}

function renderQuestionsPage() {
    const startIdx = (currentQuestionPage - 1) * questionsPerPage;
    const endIdx = startIdx + questionsPerPage;
    const pageQuestions = allQuestions.slice(startIdx, endIdx);
    
    renderStackedBarQuestions('questionsList', pageQuestions);
    updatePaginationButtons();
}

function changeQuestionPage(direction) {
    const totalPages = Math.ceil(allQuestions.length / questionsPerPage);
    currentQuestionPage += direction;
    
    if (currentQuestionPage < 1) currentQuestionPage = 1;
    if (currentQuestionPage > totalPages) currentQuestionPage = totalPages;
    
    renderQuestionsPage();
}

function updatePaginationButtons() {
    const totalPages = Math.ceil(allQuestions.length / questionsPerPage);
    const prevBtn = document.getElementById('prevPageBtn');
    const nextBtn = document.getElementById('nextPageBtn');
    
    prevBtn.disabled = currentQuestionPage === 1;
    nextBtn.disabled = currentQuestionPage === totalPages;
}

function renderStackedBarQuestions(containerId, questions) {
    const container = document.getElementById(containerId);
    const colors = ['#6366f1', '#818cf8', '#a5b4fc', '#c7d2fe'];
    const labels = ['Strongly Agree', 'Agree', 'Disagree', 'Strongly Disagree'];
    
    container.innerHTML = questions.map((q, index) => {
        // Generate random but realistic percentages
        const values = [0, 0, 0, 0];
        const total = values.reduce((a, b) => a + b, 0);
        
        // Create array of segments with their data
        const segments = values.map((v, i) => ({
            value: v,
            percentage: ((v / total) * 100).toFixed(1),
            label: labels[i],
            index: i
        }));
        
        // Sort by percentage (highest to lowest) to assign colors
        const sortedSegments = [...segments].sort((a, b) => parseFloat(b.percentage) - parseFloat(a.percentage));
        
        // Assign colors based on sorted order (highest = darkest)
        const colorMap = {};
        sortedSegments.forEach((seg, idx) => {
            colorMap[seg.index] = colors[idx];
        });
        
        return `
        <div class="grid grid-cols-11 gap-4 items-center">
            <div class="col-span-5 text-sm pr-4 text-neutral-800">
                <span class="font-semibold text-neutral-800">${q.id}.</span> ${q.text}
            </div>
            <div class="col-span-6 relative h-10 rounded-lg overflow-hidden shadow-sm bg-gray-50">
                <div class="absolute inset-0 flex">
                    ${segments.map((seg, i) => {
                        const showPct = parseFloat(seg.percentage);
                        let displayText = '';
                        let fontSize = 'text-xs';
                        
                        // Determine text color based on assigned background color
                        const assignedColor = colorMap[i];
                        const isLightBackground = assignedColor === '#a5b4fc' || assignedColor === '#c7d2fe';
                        const textColor = isLightBackground ? 'text-neutral-800' : 'text-white';
                        
                        if (showPct >= 7) {
                            displayText = seg.percentage + '%';
                        } else if (showPct >= 4) {
                            displayText = Math.round(showPct) + '%';
                            fontSize = 'text-[10px]';
                        }
                        
                        return `
                        <div class="flex items-center justify-center ${fontSize} font-semibold relative group ${textColor}" 
                             style="width: ${seg.percentage}%; background-color: ${assignedColor};"
                             title="${seg.label}: ${seg.value} responses (${seg.percentage}%)">
                            <span class="relative z-10">${displayText}</span>
                            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 hidden group-hover:block text-xs rounded py-1 px-2 whitespace-nowrap z-50 bg-neutral-800 text-white">
                                ${seg.label}<br>
                                Count: ${seg.value}<br>
                                ${seg.percentage}%
                            </div>
                        </div>
                        `;
                    }).join('')}
                </div>
            </div>
        </div>
        `;
    }).join('');
}

function generateRealisticValues() { return [0, 0, 0, 0]; }

function exportToExcel() {
    alert('Export to Excel functionality - Would export question statistics data to Excel format');
    // Implementation would use SheetJS (xlsx)
}

function exportToCSV() {
    alert('Export to CSV functionality - Would export question statistics data to CSV format');
    // Implementation would generate CSV file
}

function exportToPDF() {
    alert('Export to PDF functionality - Would export question statistics data to PDF format');
    // Implementation would use jsPDF
}
</script>
@endsection
