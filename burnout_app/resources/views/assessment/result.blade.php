@extends('layouts.app')

@section('title', 'Burnout Assessment Results - Burnalytics')

@section('content')
@php
    // Sample data - replace with actual assessment data
    $exhaustionCategory = request('exhaustion', 'High'); // High or Low
    $disengagementCategory = request('disengagement', 'High'); // High or Low
    
    // Determine burnout category
    if ($exhaustionCategory == 'Low' && $disengagementCategory == 'Low') {
        $category = 'low';
        $categoryName = 'Low Burnout';
        $categoryCode = 'Healthy Functioning';
    } elseif ($exhaustionCategory == 'High' && $disengagementCategory == 'Low') {
        $category = 'exhausted';
        $categoryName = 'Exhausted';
        $categoryCode = 'High Exhaustion + Low Disengagement';
    } elseif ($exhaustionCategory == 'Low' && $disengagementCategory == 'High') {
        $category = 'disengaged';
        $categoryName = 'Disengaged';
        $categoryCode = 'Low Exhaustion + High Disengagement';
    } else {
        $category = 'high';
        $categoryName = 'High Burnout';
        $categoryCode = 'High Exhaustion + High Disengagement';
    }
@endphp

<div class="min-h-screen bg-gradient-to-b from-indigo-50 to-white py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Result Header -->
        <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg p-8 mb-6 text-white relative overflow-hidden shadow-lg">
            <div class="absolute top-0 right-0 w-96 h-96 bg-white opacity-10 rounded-full -mr-48 -mt-48"></div>
            
            <!-- Action Buttons - Top Right -->
            <div class="absolute top-4 right-4 flex flex-col gap-2 z-20">
                <a href="{{ route('assessment.index') }}" class="inline-flex items-center px-4 py-2 bg-white text-indigo-600 font-semibold rounded-lg shadow-lg hover:bg-indigo-50 hover:shadow-xl transition-all duration-200 transform hover:scale-105">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Retake Assessment
                </a>
                <button onclick="exportToPDF()" class="inline-flex items-center px-4 py-2 bg-white text-indigo-600 font-semibold rounded-lg shadow-lg hover:bg-indigo-50 hover:shadow-xl transition-all duration-200 transform hover:scale-105">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export to PDF
                </button>
            </div>
            
            <h1 class="text-4xl font-bold mb-2 relative z-10">{{ $categoryName }}</h1>
            <p class="text-sm opacity-95 relative z-10">{{ $categoryCode }}</p>
        </div>
                
        <!-- Section 1: Full Width at Top -->
        <div class="mb-6">
                <div class="bg-white rounded-xl p-7 shadow-sm border border-indigo-100">
                <h2 class="text-2xl font-bold text-gray-900 mb-2">
                    Assessment Result
                    </h2>

                <!-- Assessment Breakdown by Category - Two Column Layout -->
                <div class="mt-2 grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Left Column: Empty or for future content -->
                            <div>
                            </div>

                    <!-- Right Column: Description and Chart -->
                            <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Breakdown by Category</h3>
                        <div class="relative" style="height: 350px;">
                            <canvas id="assessmentBreakdownChart"></canvas>
                            </div>
                        </div>
                </div>
            </div>
                </div>

        <!-- Section 4: Full Width at Bottom -->
        <div>
                <div class="bg-white rounded-xl p-7 shadow-sm border border-indigo-100">
                <h2 class="text-2xl font-bold text-gray-900 mb-2">
                        Recommendations
                    </h2>
            </div>
        </div>
    </div>
</div>

<script>
// PDF Export function (placeholder - implement with jsPDF or similar library)
function exportToPDF() {
    // TODO: Implement PDF export functionality
    // You can use libraries like jsPDF, html2pdf.js, or window.print()
    alert('PDF export functionality will be implemented here. You can use libraries like jsPDF or html2pdf.js to generate PDFs.');
    // Example: window.print() for browser print dialog
    // Or use: html2pdf().from(document.body).save();
}

document.addEventListener('DOMContentLoaded', function() {
    // Get assessment data - using sample data for now, replace with actual data
    const exhaustionScore = {{ request('exhaustion_score', 65) }};
    const disengagementScore = {{ request('disengagement_score', 55) }};
    const academicPerformanceScore = {{ request('academic_performance_score', 72) }};
    const stressScore = {{ request('stress_score', 62) }};
    const sleepScore = {{ request('sleep_score', 45) }};

    const ctx = document.getElementById('assessmentBreakdownChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Exhausted', 'Disengaged', 'Academic Performance', 'Stress', 'Sleep'],
            datasets: [{
                label: 'Percentage (%)',
                data: [
                    exhaustionScore,
                    disengagementScore,
                    academicPerformanceScore,
                    stressScore,
                    sleepScore
                ],
                backgroundColor: [
                    'rgba(251, 146, 60, 0.8)',  // Orange for Exhausted
                    'rgba(234, 179, 8, 0.8)',   // Yellow for Disengaged
                    'rgba(236, 72, 153, 0.8)',  // Pink/Magenta for Academic Performance
                    'rgba(239, 68, 68, 0.8)',   // Red for Stress
                    'rgba(147, 51, 234, 0.8)'   // Purple for Sleep
                ],
                borderColor: [
                    'rgb(251, 146, 60)',
                    'rgb(234, 179, 8)',
                    'rgb(236, 72, 153)',
                    'rgb(239, 68, 68)',
                    'rgb(147, 51, 234)'
                ],
                borderWidth: 2,
                borderRadius: {
                    topLeft: 8,
                    topRight: 8
                }
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.parsed.y + '%';
                        }
                    },
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        stepSize: 25,
                        callback: function(value) {
                            return value + '%';
                        },
                        font: {
                            size: 11
                        },
                        color: '#6B7280'
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)',
                        lineWidth: 1,
                        drawBorder: false
                    },
                    title: {
                        display: true,
                        text: 'Percentage (%)',
                        font: {
                            size: 12,
                            weight: '600'
                        },
                        color: '#374151',
                        padding: {
                            bottom: 10
                        }
                    }
                },
                x: {
                    ticks: {
                        font: {
                            size: 11
                        },
                        color: '#6B7280',
                        maxRotation: 45,
                        minRotation: 45
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
});
</script>
@endsection
