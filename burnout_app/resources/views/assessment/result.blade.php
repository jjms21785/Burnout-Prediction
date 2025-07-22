@php
    if (!isset($predictedLabel)) $predictedLabel = null;
@endphp
@extends('layouts.app')

@section('title', 'Burnout Assessment Result')

@section('content')
<div class="max-w-3xl mx-auto py-8 px-4">
    <!-- Top Card -->
    <div class="rounded-lg p-6 mb-6 text-center bg-green-600 shadow">
        <h2 class="text-2xl font-bold text-white mb-2">Summary Result</h2>
        <p class="text-white">Based on the responses to 16 questions</p>
    </div>

    <!-- Risk & Confidence -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white border rounded-lg p-6 flex flex-col items-center">
            <h3 class="text-xl font-bold mb-1" style="color: black;">Burnout Risk Level: 
                @if(isset($predictedLabel) && $predictedLabel)
                    <span class="@if($predictedLabel=='High') text-red-600 @elseif($predictedLabel=='Moderate') text-yellow-600 @else text-green-600 @endif">
                        {{ $predictedLabel }}
                    </span>
                @else
                    <span class="text-gray-400">Unavailable</span>
                @endif
            </h3>
            <div class="mb-2" style="color: black;">
                Average score: 
                @if(isset($totalScore))
                    {{ round($totalScore/16, 2) }}
                @else
                    <span class="text-gray-400">Unavailable</span>
                @endif
            </div>
            <div class="text-xs text-gray-500">About Risk Level and Average Score. ⓘ </div>
            @if(isset($errorMsg) && $errorMsg)
                <div class="mt-2 text-red-600 text-sm">{{ $errorMsg }}</div>
            @endif
        </div>
        <div class="bg-white border rounded-lg p-6 flex flex-col items-center">
            <h3 class="text-xl font-bold mb-1" style="color: black;">Prediction Confidence</h3>
            <div class="mb-2" style="color: black;">{{ isset($modelAccuracy) ? 'Model Accuracy: '.($modelAccuracy*100).'%' : '' }}</div>
            <div class="text-xs text-gray-500">About Confidence and Accuracy. ⓘ </div>
        </div>
    </div>

    <!-- Demographics -->
    <div class="mb-4 text-center text-sm text-gray-700">
        <span>Age: <b>
            @if(isset($age))
                {{ $age }}
            @else
                <span class="text-gray-400">Unavailable</span>
            @endif
        </b></span> |
        <span>Gender: <b>
            @if(isset($gender))
                {{ $gender }}
            @else
                <span class="text-gray-400">Unavailable</span>
            @endif
        </b></span> |
        <span>Program: <b>
            @if(isset($program))
                {{ $program }}
            @else
                <span class="text-gray-400">Unavailable</span>
            @endif
        </b></span>
    </div>

    <!-- Score Breakdown -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="bg-white border rounded-lg p-4 text-center">
            <h5 class="font-semibold mb-1">Exhaustion Score</h5>
            <p class="text-lg">
                @if(isset($exhaustionScore))
                    {{ $exhaustionScore }}
                @else
                    <span class="text-gray-400">Unavailable</span>
                @endif
            </p>
            <div class="text-xs text-gray-500">Sum of OLBI-S items related to exhaustion. <br> Learn More. ⓘ </div>
        </div>
        <div class="bg-white border rounded-lg p-4 text-center">
            <h5 class="font-semibold mb-1">Disengagement Score</h5>
            <p class="text-lg">
                @if(isset($disengagementScore))
                    {{ $disengagementScore }}
                @else
                    <span class="text-gray-400">Unavailable</span>
                @endif
            </p>
            <div class="text-xs text-gray-500">Sum of OLBI-S items related to disengagement. <br> Learn More. ⓘ </div>
        </div>
    </div>

    <!-- Answer Summary Table -->
    <div class="bg-white border rounded-lg p-4 mb-6">
        <h5 class="font-semibold mb-1">Answer Summary</h5>
        <div class="overflow-x-auto">
            @if(isset($responses) && is_array($responses) && count($responses) > 0)
                <table class="min-w-full text-center text-xs">
                    <thead>
                        <tr>
                            @foreach(array_keys($responses) as $question)
                                <th class="px-2 py-1">{{ $question }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            @foreach($responses as $value)
                                <td class="px-2 py-1">
                                    {{ $value }}
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
                <div class="mt-2 text-xs text-gray-600 text-left">
                    <div>3 = Strongly Agree</div>
                    <div>2 = Agree</div>
                    <div>1 = Disagree</div>
                    <div>0 = Strongly Disagree</div>
                </div>
            @else
                <div class="text-gray-400 py-4 text-center">No responses available.</div>
            @endif
        </div>
    </div>

    <!-- Recommendation Box -->
    @if($predictedLabel == 'Low')
        <div class="alert alert-success mt-4">
            <strong>You're currently showing low signs of burnout.</strong><br>
            This indicates a healthy balance between academic demands and personal well-being.
            <ul class="mt-2">
                <li><strong>Maintain:</strong> Continue habits that promote your emotional and cognitive engagement.</li>
                <li><strong>Monitor:</strong> Stay aware of signs like persistent fatigue or loss of motivation.</li>
                <li><strong>Support Others:</strong> Students in better mental states often play a key role in peer well-being.</li>
            </ul>
            <div class="mt-2">
                <em>Even if you're doing well, visiting the counseling office for check-ins or other personal concerns is always encouraged.</em>
                <br><a href="https://www.mind.org.uk/information-support/types-of-mental-health-problems/burnout/" target="_blank">Learn more about recognizing burnout</a>
            </div>
        </div>
    @elseif($predictedLabel == 'Moderate')
        <div class="alert alert-warning mt-4">
            Moderate risk often means emotional fatigue is rising and motivation may be fluctuating.
            <ul class="mt-2">
                <li><strong>Self-Audit:</strong> Track time spent on academic work, sleep, social activity, and rest. Recognize imbalance.</li>
                <li><strong>Recalibrate:</strong> Adjust workloads and integrate breaks, even short ones. Use the <a href='https://pomofocus.io/' target='_blank'>Pomodoro Technique</a> or similar tools.</li>
                <li><strong>Talk:</strong> Share with a peer or visit the counseling office for support and strategy.</li>
            </ul>
            <div class="mt-2">
                <em>Early intervention helps prevent chronic burnout. Reach out to your campus wellness center.</em>
                <br><a href="https://www.ncbi.nlm.nih.gov/pmc/articles/PMC6320571/" target="_blank">Research on student burnout interventions</a>
            </div>
        </div>
    @elseif($predictedLabel == 'High')
        <div class="alert alert-danger mt-4">
            <strong>We recommend speaking with a counselor or academic advisor immediately.</strong><br>
            You may be showing signs of sustained emotional exhaustion and disengagement — common but serious indicators of academic burnout.
            <ul class="mt-2">
                <li><strong>Seek Help:</strong> Prioritize scheduling a confidential session at the counseling center.</li>
                <li><strong>Reduce Pressure:</strong> Consider temporary academic adjustments (extensions, breaks, etc.).</li>
                <li><strong>Recovery Plan:</strong> Build a structured recovery plan with a counselor — focus on sleep, nutrition, and boundary-setting.</li>
            </ul>
            <div class="mt-2">
                <em>Burnout is not failure — it’s a signal to realign. Help is available and effective.</em>
                <br><a href="https://www.who.int/news-room/fact-sheets/detail/mental-health-strengthening-our-response" target="_blank">WHO: Mental Health Response and Support</a>
                <br><a href="https://adaa.org/understanding-anxiety/burnout" target="_blank">Anxiety & Depression Association of America on Burnout</a>
            </div>
        </div>
    @endif

    <div class="flex justify-center mt-8">
        <a href="{{ route('assessment.index') }}" class="bg-green-100 border border-green-400 text-green-700 px-6 py-2 rounded-lg font-semibold hover:bg-green-200 transition">New Assessment</a>
    </div>
</div>
@endsection 