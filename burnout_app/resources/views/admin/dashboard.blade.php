@extends('layouts.app')

@section('title', 'Dashboard - Burnalytics')

@section('header-actions')
    <div class="flex items-center gap-2">
        <button onclick="exportToExcel()" class="flex items-center gap-1.5 px-2.5 py-1.5 text-xs bg-indigo-500 text-white rounded-md hover:bg-indigo-400 transition">
            <span>Export Excel</span>
        </button>
        <button onclick="exportToPDF()" class="flex items-center gap-1.5 px-2.5 py-1.5 text-xs bg-indigo-500 text-white rounded-md hover:bg-indigo-400 transition">
            <span>Export PDF</span>
        </button>
    </div>
@endsection

@section('content')
<main class="flex-1 overflow-y-auto p-3">
    <div class="grid grid-cols-12 gap-6">
        <div class="col-span-5 space-y-4">
            <div class="rounded-xl shadow-sm p-6 bg-white border border-gray-200">
                <div class="flex items-center gap-6">
                    <div class="flex-1 flex flex-col items-center justify-center border-r border-gray-300">
                        <div class="w-16 h-16 rounded-full flex items-center justify-center mb-3 bg-indigo-100">
                            <svg class="w-8 h-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-sm mb-1 text-gray-500">Total Assessments</h3>
                        <p class="text-2xl font-bold text-neutral-800">{{ $totalAssessments ?? 0 }}</p>
                    </div>
                    
                    <div class="flex-1 flex flex-col justify-center">
                        <h3 class="text-sm font-semibold mb-3 text-neutral-800">Total Each Categories</h3>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between py-1">
                                <span class="text-xs text-neutral-800">High Burnout</span>
                                <span class="text-xs font-semibold text-neutral-800">{{ $highBurnout ?? 0 }}</span>
                            </div>
                            <div class="flex items-center justify-between py-1">
                                <span class="text-xs text-neutral-800">Exhausted</span>
                                <span class="text-xs font-semibold text-neutral-800">{{ $exhaustion ?? 0 }}</span>
                            </div>
                            <div class="flex items-center justify-between py-1">
                                <span class="text-xs text-neutral-800">Disengaged</span>
                                <span class="text-xs font-semibold text-neutral-800">{{ $disengagement ?? 0 }}</span>
                            </div>
                            <div class="flex items-center justify-between py-1">
                                <span class="text-xs text-neutral-800">Low Burnout</span>
                                <span class="text-xs font-semibold text-neutral-800">{{ $lowBurnout ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="rounded-xl shadow-sm p-6 bg-white border border-gray-200">
                    <h4 class="text-sm font-semibold mb-4 text-center text-neutral-800">Burnout Categories</h4>
                    <canvas id="burnoutChart" class="max-h-45"></canvas>
                </div>

                <div class="rounded-xl shadow-sm p-6 bg-white border border-gray-200">
                    <h4 class="text-sm font-semibold mb-4 text-center text-neutral-800">Age</h4>
                    <canvas id="ageChart" class="max-h-45"></canvas>
                </div>

                <div class="rounded-xl shadow-sm p-6 bg-white border border-gray-200">
                    <h4 class="text-sm font-semibold mb-4 text-center text-neutral-800">Gender</h4>
                    <canvas id="genderChart" class="max-h-45"></canvas>
                </div>

                <div class="rounded-xl shadow-sm p-6 bg-white border border-gray-200">
                    <h4 class="text-sm font-semibold mb-4 text-center text-neutral-800">Year Level</h4>
                    <canvas id="yearChart" class="max-h-45"></canvas>
                </div>

                <div class="col-span-2 rounded-xl shadow-sm p-6 bg-white border border-gray-200">
                    <h4 class="text-sm font-semibold mb-4 text-center text-neutral-800">Program</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex items-center justify-center">
                            <canvas id="programChart" class="max-h-45"></canvas>
                        </div>
                        <div class="flex flex-col justify-center" id="programLegend"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-span-7 space-y-4">
            <div class="rounded-xl shadow-sm p-3 bg-white border border-gray-200">
                <h3 class="text-base font-semibold flex items-center text-neutral-800">Latest Submissions</h3>
                <div class="space-y-1" id="latestSubmissions">
                    @if(isset($latestSubmissions) && $latestSubmissions->count() > 0)
                        <div class="grid grid-cols-12 gap-4 items-center p-1 border-b-1 border-gray-200 bg-gray-50">
                            <div class="col-span-4">
                                <p class="text-xs font-semibold text-neutral-800">Name / Program</p>
                            </div>
                            <div class="col-span-2">
                                <p class="text-xs font-semibold text-neutral-800">Year Level</p>
                            </div>
                            <div class="col-span-2 text-center">
                                <p class="text-xs font-semibold text-neutral-800">Category</p>
                            </div>
                            <div class="col-span-4 text-right">
                                <p class="text-xs font-semibold text-neutral-800">Date</p>
                            </div>
                        </div>
                        @foreach($latestSubmissions as $item)
                        <div class="grid grid-cols-12 gap-4 items-center p-2 border-b border-gray-100 hover:bg-gray-50">
                            <div class="col-span-4 flex flex-col">
                                <p class="text-xs font-medium text-neutral-800 truncate">{{ $item['assessment']->name ?? 'Unavailable' }}</p>
                                <p class="text-[10px] text-gray-500 truncate">{{ $item['assessment']->program ?? 'Unavailable' }}</p>
                            </div>
                            <div class="col-span-2 text-[10px] text-gray-500">{{ $item['assessment']->year_level ?? 'Unavailable' }}</div>
                            <div class="col-span-2 flex justify-center">
                                <span class="px-2 py-0.5 text-[10px] font-medium rounded {{ $item['categoryColor'] }}">
                                    {{ $item['category'] }}
                                </span>
                            </div>
                            <div class="col-span-4 text-[10px] text-gray-500 text-right">{{ $item['assessment']->created_at ? $item['assessment']->created_at->format('M d, Y') : 'Unavailable' }}</div>
                        </div>
                        @endforeach
                    @else
                        <p class="text-sm text-center py-4 text-gray-500">Unavailable</p>
                    @endif
                </div>
            </div>

            <div class="rounded-xl shadow-sm px-5 pt-5 pb-2 bg-white border border-gray-200 flex flex-col">
                <div class="grid grid-cols-11 gap-4 items-center mb-3 pb-2 border-b border-gray-200">
                    <div class="col-span-5 text-xl font-semibold text-neutral-800">Response Distribution</div>
                    <div class="col-span-6 flex">
                        <div class="flex-1 text-xs text-center text-gray-700 border-r border-gray-200">Strongly Agree</div>
                        <div class="flex-1 text-xs text-center text-gray-700 border-r border-gray-200">Agree</div>
                        <div class="flex-1 text-xs text-center text-gray-700 border-r border-gray-200">Disagree</div>
                        <div class="flex-1 text-xs text-center text-gray-700">Strongly Disagree</div>
                    </div>
                </div>
                <div class="grid grid-cols-11 gap-4 items-center mb-3">
                    <div class="col-span-5"></div>
                    <div class="col-span-6 text-[10px] text-center text-gray-500">Hover on the bar for exact detail. If missing, it has minimal or no value.</div>
                </div>

                <div class="space-y-4 mb-4 flex-1 overflow-y-auto" id="questionsList"></div>
                
                <div class="flex justify-end items-center space-x-2">
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
window.dashboardData = {
    highBurnout: {{ $highBurnout ?? 0 }},
    exhaustion: {{ $exhaustion ?? 0 }},
    disengagement: {{ $disengagement ?? 0 }},
    lowBurnout: {{ $lowBurnout ?? 0 }},
    ageDistribution: @json($ageDistribution ?? []),
    genderDistribution: @json($genderDistribution ?? []),
    yearDistribution: @json($yearDistribution ?? []),
    programDistribution: @json($programDistribution ?? []),
    questionStats: @json($questionStats ?? []),
    questionsList: @json($questionsList ?? [])
};
</script>

@vite('resources/js/dashboard.js')
@vite('resources/js/export.js')
@endsection
