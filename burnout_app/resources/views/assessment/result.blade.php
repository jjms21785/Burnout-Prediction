@extends('layouts.app')

@section('title', 'Burnout Assessment Results - Burnalytics')

@section('content')
@php
    // Data is processed in ResultController, use processed values
    $hasData = $dataAvailable ?? false;
    $errorMessage = $errorMsg ?? null;
    
    // Get processed result data from controller
    $categoryName = $categoryName ?? 'Results Unavailable';
    $exhaustionPercent = $exhaustionPercent ?? 0;
    $disengagementPercent = $disengagementPercent ?? 0;
    $academicPercent = $academicPercent ?? 0;
    $stressPercent = $stressPercent ?? 0;
    $sleepPercent = $sleepPercent ?? 0;
    
    // Determine header color based on category
    $headerColor = 'from-indigo-500 to-indigo-600';
    $categoryLower = strtolower($categoryName);
    if (strpos($categoryLower, 'low') !== false) {
        $headerColor = 'from-green-500 to-green-600';
    } elseif (strpos($categoryLower, 'exhausted') !== false || strpos($categoryLower, 'disengaged') !== false) {
        $headerColor = 'from-yellow-500 to-yellow-600';
    } elseif (strpos($categoryLower, 'high') !== false) {
        $headerColor = 'from-red-500 to-red-600';
    }
@endphp

<div class="min-h-screen bg-gradient-to-b from-indigo-50 to-white py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Result Header -->
        <div class="bg-gradient-to-r {{ $headerColor }} rounded-lg p-8 mb-6 text-white relative overflow-hidden shadow-lg">
            <div class="absolute top-0 right-0 w-96 h-96 bg-white opacity-10 rounded-full -mr-48 -mt-48"></div>
            
            <!-- Action Buttons - Top Right -->
            <div class="absolute top-4 right-4 flex flex-col gap-2 z-20">
                <button onclick="exportToPDF()" class="inline-flex items-center px-2 py-1.5 bg-white text-gray-700 text-xs font-medium rounded-lg shadow hover:bg-gray-50 hover:shadow-md transition-all duration-200">
                    <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export PDF
                </button>
            </div>

            <p class="text-sm font-semibold opacity-95 relative z-10">The result is:</p>
            <h1 class="text-4xl font-bold mb-2 relative z-10">{{ $categoryName }}</h1>
            
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

    const dataValues = [
        Math.max(0, exhaustionPercent),
        Math.max(0, disengagementPercent),
        Math.max(0, academicPercent),
        Math.max(0, stressPercent),
        Math.max(0, sleepPercent)
    ];
    
    const maxValue = Math.max(...dataValues, 1);
    const minValue = Math.min(...dataValues);
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Exhausted', 'Disengaged', 'Academic Performance', 'Stress', 'Sleep'],
            datasets: [{
                label: 'Score (%)',
                data: dataValues,
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
                },
                minBarLength: 2
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
                            const actualValue = dataValues[context.dataIndex];
                            return actualValue.toFixed(1) + '%';
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
                    min: 0,
                    ticks: {
                    stepSize: function(context) {
                        const dataValues = context.chart.data.datasets[0].data;
                        const maxVal = Math.max(...dataValues);
                        if (maxVal <= 10) return 2;
                        if (maxVal <= 20) return 5;
                        if (maxVal <= 50) return 10;
                        return 25;
                    },
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
                        text: 'Score (%)',
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
