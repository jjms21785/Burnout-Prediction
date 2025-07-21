@extends('layouts.app')

@section('title', 'Assessment Results - Burnalytix')
@section('subtitle', 'Assessment Results')

@section('content')
<!-- Results Header -->
<div class="bg-green-600 text-white py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-3xl font-bold mb-4">Your Burnout Assessment Result</h1>
        <p class="text-green-100">Based on your responses to 22 psychometric questions using MBI-HSS scoring</p>
    </div>
</div>

<!-- Results Content -->
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Overall Risk and Confidence -->
    <div class="grid md:grid-cols-2 gap-6 mb-8">
        <!-- Burnout Risk Level -->
        <div class="bg-{{ $assessment->overall_risk === 'high' ? 'red' : ($assessment->overall_risk === 'moderate' ? 'orange' : 'green') }}-50 border border-{{ $assessment->overall_risk === 'high' ? 'red' : ($assessment->overall_risk === 'moderate' ? 'orange' : 'green') }}-200 rounded-lg p-6">
            <div class="text-center">
                <div class="w-16 h-16 bg-{{ $assessment->overall_risk === 'high' ? 'red' : ($assessment->overall_risk === 'moderate' ? 'orange' : 'green') }}-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    @if($assessment->overall_risk === 'high')
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    @elseif($assessment->overall_risk === 'moderate')
                        <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    @else
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    @endif
                </div>
                <h2 class="text-2xl font-bold text-{{ $assessment->overall_risk === 'high' ? 'red' : ($assessment->overall_risk === 'moderate' ? 'orange' : 'green') }}-800 mb-2">
                    Burnout Risk Level: {{ ucfirst($assessment->overall_risk) }}
                </h2>
                <p class="text-{{ $assessment->overall_risk === 'high' ? 'red' : ($assessment->overall_risk === 'moderate' ? 'orange' : 'green') }}-600">
                    Based on MBI-HSS scoring system
                </p>
            </div>
        </div>

        <!-- Prediction Confidence -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <div class="text-center">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
                        <div class="w-4 h-4 bg-white rounded-full"></div>
                    </div>
                </div>
                <h2 class="text-2xl font-bold text-blue-800 mb-2">Prediction Confidence</h2>
                <p class="text-blue-600">{{ $assessment->confidence ?? 'N/A' }}% Accuracy</p>
                <div class="mt-4">
                    <div class="bg-gray-200 rounded-full h-3">
                        <div class="bg-blue-600 h-3 rounded-full" style="width: {{ $assessment->confidence ?? 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MBI-HSS Score Breakdown -->
    <div class="bg-white border border-gray-200 rounded-lg p-6 mb-8">
        <h2 class="text-2xl font-bold text-green-800 mb-6">📊 MBI-HSS Score Breakdown</h2>
        <div class="grid md:grid-cols-3 gap-6">
            <!-- Emotional Exhaustion -->
            <div class="text-center">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Emotional Exhaustion (EE)</h3>
                <div class="relative w-32 h-32 mx-auto mb-4">
                    <svg class="w-32 h-32 transform -rotate-90" viewBox="0 0 36 36">
                        <path class="text-gray-300" stroke="currentColor" stroke-width="3" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"></path>
                        <path class="text-{{ isset($assessment->ee_score) && $assessment->ee_score >= 27 ? 'red' : (isset($assessment->ee_score) && $assessment->ee_score >= 17 ? 'orange' : 'green') }}-600" stroke="currentColor" stroke-width="3" fill="none" stroke-linecap="round" stroke-dasharray="{{ isset($assessment->ee_score) ? ($assessment->ee_score / 54) * 100 : 0 }}, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"></path>
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-xl font-bold">{{ $assessment->ee_score ?? 'N/A' }}/54</span>
                    </div>
                </div>
                @php
                    $eeInterpretation = isset($assessment->ee_score) && $assessment->ee_score >= 27 ? ['level' => 'High', 'description' => 'Emotionally drained', 'color' => 'red'] : 
                                       (isset($assessment->ee_score) && $assessment->ee_score >= 17 ? ['level' => 'Moderate', 'description' => 'Some emotional strain', 'color' => 'orange'] : 
                                       ['level' => 'Low', 'description' => 'Emotionally stable', 'color' => 'green']);
                @endphp
                <div class="bg-{{ $eeInterpretation['color'] }}-50 border border-{{ $eeInterpretation['color'] }}-200 rounded-lg p-3">
                    <p class="font-semibold text-{{ $eeInterpretation['color'] }}-800">{{ $eeInterpretation['level'] }}</p>
                    <p class="text-sm text-{{ $eeInterpretation['color'] }}-600">{{ $eeInterpretation['description'] }}</p>
                </div>
            </div>

            <!-- Depersonalization -->
            <div class="text-center">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Depersonalization (DP)</h3>
                <div class="relative w-32 h-32 mx-auto mb-4">
                    <svg class="w-32 h-32 transform -rotate-90" viewBox="0 0 36 36">
                        <path class="text-gray-300" stroke="currentColor" stroke-width="3" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"></path>
                        <path class="text-{{ isset($assessment->dp_score) && $assessment->dp_score >= 13 ? 'red' : (isset($assessment->dp_score) && $assessment->dp_score >= 7 ? 'orange' : 'green') }}-600" stroke="currentColor" stroke-width="3" fill="none" stroke-linecap="round" stroke-dasharray="{{ isset($assessment->dp_score) ? ($assessment->dp_score / 30) * 100 : 0 }}, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"></path>
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-xl font-bold">{{ $assessment->dp_score ?? 'N/A' }}/30</span>
                    </div>
                </div>
                @php
                    $dpInterpretation = isset($assessment->dp_score) && $assessment->dp_score >= 13 ? ['level' => 'High', 'description' => 'Detached from studies', 'color' => 'red'] : 
                                       (isset($assessment->dp_score) && $assessment->dp_score >= 7 ? ['level' => 'Moderate', 'description' => 'Some detachment', 'color' => 'orange'] : 
                                       ['level' => 'Low', 'description' => 'Engaged with studies', 'color' => 'green']);
                @endphp
                <div class="bg-{{ $dpInterpretation['color'] }}-50 border border-{{ $dpInterpretation['color'] }}-200 rounded-lg p-3">
                    <p class="font-semibold text-{{ $dpInterpretation['color'] }}-800">{{ $dpInterpretation['level'] }}</p>
                    <p class="text-sm text-{{ $dpInterpretation['color'] }}-600">{{ $dpInterpretation['description'] }}</p>
                </div>
            </div>

            <!-- Personal Accomplishment -->
            <div class="text-center">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Personal Accomplishment (PA)</h3>
                <div class="relative w-32 h-32 mx-auto mb-4">
                    <svg class="w-32 h-32 transform -rotate-90" viewBox="0 0 36 36">
                        <path class="text-gray-300" stroke="currentColor" stroke-width="3" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"></path>
                        <path class="text-{{ isset($assessment->pa_score) && $assessment->pa_score <= 31 ? 'red' : (isset($assessment->pa_score) && $assessment->pa_score <= 36 ? 'orange' : 'green') }}-600" stroke="currentColor" stroke-width="3" fill="none" stroke-linecap="round" stroke-dasharray="{{ isset($assessment->pa_score) ? ($assessment->pa_score / 48) * 100 : 0 }}, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"></path>
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-xl font-bold">{{ $assessment->pa_score ?? 'N/A' }}/48</span>
                    </div>
                </div>
                @php
                    $paInterpretation = isset($assessment->pa_score) && $assessment->pa_score <= 31 ? ['level' => 'Low', 'description' => 'Feels unaccomplished', 'color' => 'red'] : 
                                       (isset($assessment->pa_score) && $assessment->pa_score <= 36 ? ['level' => 'Moderate', 'description' => 'Moderate accomplishment', 'color' => 'orange'] : 
                                       ['level' => 'High', 'description' => 'Feels accomplished', 'color' => 'green']);
                @endphp
                <div class="bg-{{ $paInterpretation['color'] }}-50 border border-{{ $paInterpretation['color'] }}-200 rounded-lg p-3">
                    <p class="font-semibold text-{{ $paInterpretation['color'] }}-800">{{ $paInterpretation['level'] }}</p>
                    <p class="text-sm text-{{ $paInterpretation['color'] }}-600">{{ $paInterpretation['description'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Result Interpretation -->
    <div class="bg-white border border-gray-200 rounded-lg p-6 mb-8">
        <h2 class="text-2xl font-bold text-green-800 mb-4">💡 Result Interpretation</h2>
        <div class="bg-{{ $assessment->overall_risk === 'high' ? 'red' : ($assessment->overall_risk === 'moderate' ? 'orange' : 'green') }}-50 border-l-4 border-{{ $assessment->overall_risk === 'high' ? 'red' : ($assessment->overall_risk === 'moderate' ? 'orange' : 'green') }}-400 p-4 rounded">
            <p class="text-{{ $assessment->overall_risk === 'high' ? 'red' : ($assessment->overall_risk === 'moderate' ? 'orange' : 'green') }}-800">
                @if($assessment->overall_risk === 'high')
                    Your assessment indicates a <strong>high risk</strong> of academic burnout. This suggests significant levels of emotional exhaustion, cynicism, and reduced academic efficacy. Immediate intervention and support are recommended. Please consider scheduling a counseling session and implementing comprehensive stress management strategies.
                @elseif($assessment->overall_risk === 'moderate')
                    Your assessment indicates a <strong>moderate risk</strong> of academic burnout. This suggests you may be experiencing some signs of emotional exhaustion, cynicism, or reduced academic efficacy. While not at a critical level, it's important to take proactive steps to manage stress and prevent further escalation.
                @else
                    Your assessment indicates a <strong>low risk</strong> of academic burnout. This suggests you are currently managing your academic demands well with minimal signs of burnout. Continue maintaining healthy study habits and stress management practices.
                @endif
            </p>
        </div>
    </div>

    <!-- Recommended Actions -->
    <div class="bg-white border border-gray-200 rounded-lg p-6 mb-8">
        <h2 class="text-2xl font-bold text-green-800 mb-4">💡 Recommended Actions Panel</h2>
        <div class="space-y-4">
            @if($assessment->overall_risk === 'high')
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <h3 class="font-semibold text-red-800 mb-3">High Priority Actions:</h3>
                    <ul class="space-y-2 text-red-700">
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-red-500 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                            Schedule immediate one-on-one counseling session
                        </li>
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-red-500 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                            Consider reducing academic workload temporarily
                        </li>
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-red-500 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                            Implement daily stress reduction techniques
                        </li>
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-red-500 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                            Seek support from family, friends, or support groups
                        </li>
                    </ul>
                    <div class="mt-4 space-x-4">
                        <button class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">Book Counseling Session</button>
                        <button class="border border-red-600 text-red-600 px-4 py-2 rounded-lg hover:bg-red-50">Send Alert to Counselor</button>
                    </div>
                </div>
            @elseif($assessment->overall_risk === 'moderate')
                <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                    <h3 class="font-semibold text-orange-800 mb-3">Moderate Priority Actions:</h3>
                    <ul class="space-y-2 text-orange-700">
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-orange-500 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                            Attend stress management workshop or seminar
                        </li>
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-orange-500 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                            Implement better time management strategies
                        </li>
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-orange-500 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                            Set realistic academic goals and expectations
                        </li>
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-orange-500 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                            Take regular breaks and practice self-care
                        </li>
                    </ul>
                    <div class="mt-4 space-x-4">
                        <button class="bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700">Join Workshop</button>
                        <button class="border border-orange-600 text-orange-600 px-4 py-2 rounded-lg hover:bg-orange-50">Schedule Check-in</button>
                    </div>
                </div>
            @else
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <h3 class="font-semibold text-green-800 mb-3">Maintenance Actions:</h3>
                    <ul class="space-y-2 text-green-700">
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-green-500 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                            Continue current healthy study habits
                        </li>
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-green-500 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                            Maintain work-life balance and engage in hobbies
                        </li>
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-green-500 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                            Regular monitoring; retake assessment in 3 months
                        </li>
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-green-500 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                            Share strategies with peers who may need support
                        </li>
                    </ul>
                    <div class="mt-4 space-x-4">
                        <button class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">Set Reminder</button>
                        <button class="border border-green-600 text-green-600 px-4 py-2 rounded-lg hover:bg-green-50">Share Resources</button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <a href="{{ route('assessment.index') }}" class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg font-medium text-center transition-colors">
            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            Take Assessment Again
        </a>
        <button class="border border-green-600 text-green-600 hover:bg-green-50 px-8 py-3 rounded-lg font-medium transition-colors">
            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Download Burnout Report (PDF)
        </button>
    </div>
</div>
@endsection