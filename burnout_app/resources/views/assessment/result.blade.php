@extends('layouts.app')

@section('title', 'Burnout Assessment Results - Burnalytics')

@section('content')
@php
    // Data is processed in ResultController, use processed values
    $hasData = $dataAvailable ?? false;
    $errorMessage = $errorMsg ?? null;
    
    // Get processed result data from controller
    $categoryName = $categoryName ?? 'Results Unavailable';
    $categoryCode = $categoryCode ?? ($errorMessage ?? 'Assessment data not available. Please ensure the Flask API is running.');
    $exhaustionPercent = $exhaustionPercent ?? 0;
    $disengagementPercent = $disengagementPercent ?? 0;
    $academicPercent = $academicPercent ?? 0;
    $stressPercent = $stressPercent ?? 0;
    $sleepPercent = $sleepPercent ?? 0;
@endphp

<div class="min-h-screen bg-gradient-to-b from-indigo-50 to-white py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Result Header -->
        <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg p-8 mb-6 text-white relative overflow-hidden shadow-lg">
            <div class="absolute top-0 right-0 w-96 h-96 bg-white opacity-10 rounded-full -mr-48 -mt-48"></div>
            
            <!-- Action Buttons - Top Right -->
            <div class="absolute top-4 right-4 flex flex-col gap-2 z-20">
                <a href="{{ route('assessment.index') }}" class="inline-flex items-center px-2 py-1.5 bg-white text-indigo-600 text-xs font-medium rounded-lg shadow hover:bg-indigo-50 hover:shadow-md transition-all duration-200">
                    <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Retake
                </a>
                <button onclick="exportToPDF()" class="inline-flex items-center px-2 py-1.5 bg-white text-indigo-600 text-xs font-medium rounded-lg shadow hover:bg-indigo-50 hover:shadow-md transition-all duration-200">
                    <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export PDF
                </button>
            </div>

            <p class="text-sm font-semibold opacity-95 relative z-10">The result is:</p>
            <h1 class="text-4xl font-bold mb-2 relative z-10">{{ $categoryName }}</h1>
            <p class="text-[10px] opacity-95 relative z-10 mb-4">{{ $categoryCode }}</p>
            
            @if($hasData && isset($interpretations['combined_result']))
                <div class="relative z-10 mt-4 pt-4 border-t border-white/20">
                    <p class="text-sm opacity-95">{{ $interpretations['combined_result']['text'] ?? '' }}</p>
                </div>
            @endif
        </div>
                
        <!-- Section 1: Full Width at Top -->
        <div class="mb-6">
            <div class="bg-white rounded-xl p-7 shadow-sm border border-indigo-100">
                <!-- Assessment Breakdown by Category - Two Column Layout -->
                <div class="mt-2 grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
                    <!-- Left Column: Interpretations -->
                    <div class="flex flex-col">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Interpretation</h3>
                        @if($hasData && isset($interpretations) && is_array($interpretations))
                            <div class="space-y-4 flex-1">
                                <!-- Exhaustion -->
                                @if(isset($interpretations['top_card']['exhaustion']))
                                    <div class="mb-3">
                                        <h5 class="font-semibold text-gray-900 mb-1 text-sm">{{ $interpretations['top_card']['exhaustion']['title'] ?? 'Exhaustion' }}</h5>
                                        <p class="text-gray-700 text-xs">{{ $interpretations['top_card']['exhaustion']['text'] ?? 'Unavailable' }}</p>
                                    </div>
                                @endif
                                
                                <!-- Disengagement -->
                                @if(isset($interpretations['top_card']['disengagement']))
                                    <div class="mb-3">
                                        <h5 class="font-semibold text-gray-900 mb-1 text-sm">{{ $interpretations['top_card']['disengagement']['title'] ?? 'Disengagement' }}</h5>
                                        <p class="text-gray-700 text-xs">{{ $interpretations['top_card']['disengagement']['text'] ?? 'Unavailable' }}</p>
                                    </div>
                                @endif
                                
                                <!-- Academic Performance -->
                                @if(isset($interpretations['breakdown']['academic']))
                                    <div class="mb-3">
                                        <h5 class="font-semibold text-gray-900 mb-1 text-sm">{{ $interpretations['breakdown']['academic']['title'] ?? 'Academic Performance' }}</h5>
                                        <p class="text-gray-700 text-xs">{{ $interpretations['breakdown']['academic']['text'] ?? 'Unavailable' }}</p>
                                    </div>
                                @endif
                                
                                <!-- Stress -->
                                @if(isset($interpretations['breakdown']['stress']))
                                    <div class="mb-3">
                                        <h5 class="font-semibold text-gray-900 mb-1 text-sm">{{ $interpretations['breakdown']['stress']['title'] ?? 'Stress Level' }}</h5>
                                        <p class="text-gray-700 text-xs">{{ $interpretations['breakdown']['stress']['text'] ?? 'Unavailable' }}</p>
                                    </div>
                                @endif
                                
                                <!-- Sleep -->
                                @if(isset($interpretations['breakdown']['sleep']))
                                    <div class="mb-3">
                                        <h5 class="font-semibold text-gray-900 mb-1 text-sm">{{ $interpretations['breakdown']['sleep']['title'] ?? 'Sleep Quality' }}</h5>
                                        <p class="text-gray-700 text-xs">{{ $interpretations['breakdown']['sleep']['text'] ?? 'Unavailable' }}</p>
                                    </div>
                                @endif
                            </div>
                        @else
                            <p class="text-gray-600 text-sm">Unavailable</p>
                        @endif
                    </div>

                    <!-- Right Column: Chart -->
                    <div class="flex flex-col h-full">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Breakdown by Category</h3>
                        <div class="relative flex-1 w-full min-h-[300px]">
                            @if($hasData && isset($barGraph) && is_array($barGraph))
                                <canvas id="assessmentBreakdownChart"></canvas>
                            @else
                                <div class="flex items-center justify-center h-full">
                                    <p class="text-gray-500 text-lg">Unavailable</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 4: Full Width at Bottom -->
        <div>
            <div class="bg-white rounded-xl p-7 shadow-sm border border-indigo-100">
                <h2 class="text-2xl font-bold text-gray-900 mb-2">
                    Recommendation
                </h2>
                    
                @if($hasData && isset($recommendations) && is_array($recommendations) && !empty($recommendations))
                    <div class="space-y-4">
                        @if(isset($recommendations['exhaustion']) && !empty($recommendations['exhaustion']))
                            <div class="mb-4">
                                <p class="text-gray-700 text-sm">{{ $recommendations['exhaustion'] }}</p>
                            </div>
                        @endif
                        
                        @if(isset($recommendations['disengagement']) && !empty($recommendations['disengagement']))
                            <div class="mb-4">
                                <p class="text-gray-700 text-sm">{{ $recommendations['disengagement'] }}</p>
                            </div>
                        @endif
                        
                        @if(isset($interpretations) && is_array($interpretations) && isset($interpretations['breakdown']))
                            @if(isset($interpretations['breakdown']['academic']['recommendation']) && !empty($interpretations['breakdown']['academic']['recommendation']))
                                <div class="mb-4">
                                    <p class="text-gray-700 text-sm">{{ $interpretations['breakdown']['academic']['recommendation'] }}</p>
                                </div>
                            @endif
                            
                            @if(isset($interpretations['breakdown']['stress']['recommendation']) && !empty($interpretations['breakdown']['stress']['recommendation']))
                                <div class="mb-4">
                                    <p class="text-gray-700 text-sm">{{ $interpretations['breakdown']['stress']['recommendation'] }}</p>
                                </div>
                            @endif
                            
                            @if(isset($interpretations['breakdown']['sleep']['recommendation']) && !empty($interpretations['breakdown']['sleep']['recommendation']))
                                <div class="mb-4">
                                    <p class="text-gray-700 text-sm">{{ $interpretations['breakdown']['sleep']['recommendation'] }}</p>
                                </div>
                            @endif
                        @endif
                    </div>
                @else
                    <p class="text-gray-600 text-sm">Unavailable</p>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
// PDF Export function
function exportToPDF() {
    window.print();
}

@if($hasData && isset($barGraph) && is_array($barGraph))
document.addEventListener('DOMContentLoaded', function() {
    // Use actual data from Blade variables
    const exhaustionPercent = {{ $exhaustionPercent }};
    const disengagementPercent = {{ $disengagementPercent }};
    const academicPercent = {{ $academicPercent }};
    const stressPercent = {{ $stressPercent }};
    const sleepPercent = {{ $sleepPercent }};

    const ctx = document.getElementById('assessmentBreakdownChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Exhausted', 'Disengaged', 'Academic Performance', 'Stress', 'Sleep'],
            datasets: [{
                label: 'Percentage (%)',
                data: [
                    exhaustionPercent,
                    disengagementPercent,
                    academicPercent,
                    stressPercent,
                    sleepPercent
                ],
                backgroundColor: [
                    'rgba(99, 102, 241, 0.8)',
                    'rgba(99, 102, 241, 0.8)',
                    'rgba(99, 102, 241, 0.8)',
                    'rgba(99, 102, 241, 0.8)',
                    'rgba(99, 102, 241, 0.8)'
                ],
                borderColor: [
                    'rgb(99, 102, 241)',
                    'rgb(99, 102, 241)',
                    'rgb(99, 102, 241)',
                    'rgb(99, 102, 241)',
                    'rgb(99, 102, 241)'
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
@endif
</script>
@endsection
