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

    <!-- Demographics -->
    <div class="mb-4 text-center text-sm text-gray-700">
        <span>Name: <b>
            @if(isset($name))
                {{ $name }}
            @else
                <span class="text-gray-400">Unavailable</span>
            @endif
        </b></span> |
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
    
    <!-- Risk & Confidence -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white border rounded-lg p-6 flex flex-col items-center">
            <h3 class="text-xl font-bold mb-1" style="color: black;">Overall Burnout Category</h3>
            @if(isset(
                $predictedLabel) && $predictedLabel)
                <div class="mt-1">
                    <span class="text-xl font-bold @if($predictedLabel=='High') text-red-600 @elseif($predictedLabel=='Exhausted') text-yellow-600 @elseif($predictedLabel=='Disengaged') text-yellow-600 @else text-green-600 @endif">
                        {{ $predictedLabel }}
                    </span>
                </div>
            @else
                <div class="mt-1">
                    <span class="text-xl font-bold text-gray-400">Unavailable</span>
                </div>
            @endif
            @if(isset($predictedLabel))
                <div class="text-xs text-gray-500 mt-2">
                    <span class="tooltip-hover">About Burnout Category.  
                        <span class="tooltip-box">
                            The burnout category (Low, Disengaged, Exhausted, or High) is not based on fixed scores.<br>
                            Instead, it's predicted by the Random Forest model trained on a dataset of responses.<br>
                            <b>Burnout Categories:</b><br>
                            <b>Low:</b> Low exhaustion and low disengagement (Low Burnout Risk)<br>
                            <b>Disengaged:</b> Low exhaustion and high disengagement<br>
                            <b>Exhausted:</b> High exhaustion and low disengagement<br>
                            <b>High:</b> High exhaustion and high disengagement (High Burnout Risk)
                        </span>
                    </span>
                </div>
            @endif
            @if(isset($errorMsg) && $errorMsg)
                <div class="mt-2 text-red-600 text-sm">{{ $errorMsg }}</div>
            @endif
        </div>
        <div class="bg-white border rounded-lg p-6 text-center flex flex-col justify-center">
            <h5 class="text-xl font-bold mb-1">Model Confidence</h5>
            <div class="text-center">
                @if(isset($confidence) && is_array($confidence))
                    <p class="text-2xl font-bold text-gray-800 mb-2">{{ number_format(max($confidence)*100,2) }}%</p>
                @else
                    <p class="text-2xl font-bold text-gray-400 mb-2">N/A</p>
                @endif
                <div class="text-xs text-gray-500">
                    <span class="tooltip-hover">About Model Confidence. <br>More info here.
                        <span class="tooltip-box">
                            <b>Model Confidence</b> shows how certain the trained model is about the burnout category prediction. 
                            It's based on how consistently the answers match patterns seen in the training data. 
                            Higher confidence means the model is more certain that the pattern fits a specific burnout category.
                        </span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Score Breakdown -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white border rounded-lg p-6">
            <h5 class="font-semibold mb-3 text-center">Exhaustion Score</h5>
            <div class="text-center mb-4">
                <div class="text-2xl font-bold mb-1 
                    @if(isset($exhaustionCategory) && $exhaustionCategory == 'High') text-red-600 @else text-green-600 @endif">
                    {{ $exhaustionCategory ?? 'N/A' }}
                </div>
                <div class="text-sm text-gray-600">
                    Total Score: <strong>{{ $exhaustionScore ?? 'N/A' }}</strong><br>
                    Average: <strong>{{ isset($exhaustionAverage) ? number_format($exhaustionAverage, 3) : 'N/A' }}</strong>
                </div>
            </div>
            <div class="text-xs text-gray-500 text-center">
                <span class="tooltip-hover">About Exhaustion Score. <br>More info here. 
                    <span class="tooltip-box">
                        This score reflects how emotionally and physically drained the feeling from the work or studies.<br><br>
                        It's calculated by averaging the responses to the Exhaustion-related items:<br>
                        <b>Q2, Q4, Q6, Q8, Q10, Q12, Q14, Q16</b><br><br>
                        <b>Category Threshold:</b> Mean score ≥ 2.25 = High exhaustion<br><br>
                        Some of these are reverse-scored because they are negatively worded (it will become the opposite of the chosen answer for calculations).<br>
                        <b>Reverse-scored questions:</b> Q2, Q6, Q10, Q14<br><br>
                    </span>
                </span>
            </div>
        </div>
        
        <!-- Total Score Display -->
        <div class="bg-white border rounded-lg p-6 text-center flex flex-col justify-center">
            <h5 class="font-semibold mb-3">Total Score</h5>
            <div class="text-center">
                <p class="text-3xl font-bold text-gray-800 mb-2">{{ $totalScore ?? 'N/A' }}</p>
                <div class="text-xs text-gray-500">
                    <span class="tooltip-hover">Sum of all 16 answers. <br>More info here.
                        <span class="tooltip-box">
                            The total score is the sum of the answers to all 16 questions.<br>
                            Each answer is rated from 1 (Strongly Agree) to 4 (Strongly Disagree).
                        </span>
                    </span>
                </div>
            </div>
        </div>
        
        <div class="bg-white border rounded-lg p-6">
            <h5 class="font-semibold mb-3 text-center">Disengagement Score</h5>
            <div class="text-center mb-4">
                <div class="text-2xl font-bold mb-1 
                    @if(isset($disengagementCategory) && $disengagementCategory == 'High') text-red-600 @else text-green-600 @endif">
                    {{ $disengagementCategory ?? 'N/A' }}
                </div>
                <div class="text-sm text-gray-600">
                    Total Score: <strong>{{ $disengagementScore ?? 'N/A' }}</strong><br>
                    Average: <strong>{{ isset($disengagementAverage) ? number_format($disengagementAverage, 3) : 'N/A' }}</strong>
                </div>
            </div>
            <div class="text-xs text-gray-500 text-center">
                <span class="tooltip-hover">About Disengagement Score. <br>More info here. 
                    <span class="tooltip-box">
                        This score measures how mentally distanced or disconnected the feeling from the tasks or responsibilities.<br><br>
                        It's calculated by averaging the answers to the Disengagement-related questions:<br>
                        <b>Q1, Q3, Q5, Q7, Q9, Q11, Q13, Q15</b><br><br>
                        <b>Category Threshold:</b> Mean score ≥ 2.10 = High disengagement<br><br>
                        Some of these are reverse-scored because they are negatively worded (it will become the opposite of the chosen answer for calculations).<br>
                        <b>Reverse-scored questions:</b> Q3, Q7, Q11, Q15<br><br>
                    </span>
                </span>
            </div>
        </div>
    </div>
    <div class="w-full flex justify-center mb-8">
        <div class="max-w-2xl text-xs text-gray-600 text-center">
            <b>About The Scores</b><br>
            The average, exhaustion, and disengagement scores are shown here to help understand the responses better, but they are not used to determine the actual burnout risk level.<br>
            The burnout risk categories (Low, Disengaged, Exhausted, or High) is predicted by a machine learning model trained on patterns from the dataset. The model analyzes all the individual answers, not summary scores.<br>
            Although, these scores are helpful for insights, they do not affect the actual cateogires, prediction, or its confidence.
        </div>
    </div>

    <div class="bg-white border rounded-lg p-4 mb-6">
        <div class="flex items-center mb-1">
            <h5 class="font-semibold">Answer Summary (After Reversal)</h5>
        </div>
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
                            @foreach($responses as $q => $value)
                                @php
                                    $qnum = intval(str_replace('Q', '', $q));
                                @endphp
                                <td class="px-2 py-1">
                                    {{ $value }}
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
                <div class="w-full flex justify-center mt-4">
                    <div class="bg-gray-100 border border-gray-300 rounded px-4 py-2 text-xs text-gray-700 text-center">
                        <span class="text-blue-600">(Note: This presents the processed values after reversal of negative items)</span>
                    </div>
                </div>
            @else
                <div class="text-gray-400 py-4 text-center">Unavailable.</div>
            @endif
        </div>
    </div>

    <!-- Recommendation Box -->
    <h4 class="text-lg font-bold mt-8 mb-2">Interpretation</h4>
    {{-- Remove the 'Unavailable' span that always shows when there is a prediction --}}
    @if($predictedLabel == 'Low')
        <div class="alert alert-success mt-4">
            According to the prediction, you might be showing low signs of burnout.<br>
            <div class="font-semibold mt-2 mb-1">Suggestions</div>
            Based on the assessment results, this might indicate a healthy balance between academic demands and personal well-being.
            <ul class="mt-2">
                <li>Maintain: Continue habits that promote emotional and cognitive engagement.</li>
                <li>Monitor: Stay aware of signs like persistent fatigue or loss of motivation.</li>
                <li>Support Others: Students in better mental states often play a key role in peer well-being.</li>
            </ul>
            <div class="mt-2">
                <em>Even if doing well, visiting the counseling office for check-ins or other personal concerns is always encouraged.</em>
            </div>
        </div>
    @elseif($predictedLabel == 'Disengaged')
        <div class="alert alert-warning mt-4">
            According to the prediction, you might be showing signs of disengagement from your studies.<br>
            <div class="font-semibold mt-2 mb-1">Suggestions</div>
            Based on the assessment results, this might indicate a loss of interest and motivation in academic work.
            <ul class="mt-2">
                <li>Reconnect: Try to rediscover what initially interested you in your field of study.</li>
                <li>Set Small Goals: Break down tasks into smaller, manageable steps to rebuild engagement.</li>
                <li>Seek Support: Talk to academic advisors or counselors about your academic path and motivation.</li>
                <li>Explore: Consider if your current program aligns with your interests and career goals.</li>
            </ul>
            <div class="mt-2">
                <em>Disengagement is often temporary and can be addressed with the right support and strategies.</em>
            </div>
        </div>
    @elseif($predictedLabel == 'Exhausted')
        <div class="alert alert-warning mt-4">
            According to the prediction, you might be experiencing high levels of exhaustion from your studies.<br>
            <div class="font-semibold mt-2 mb-1">Suggestions</div>
            Based on the assessment results, this might indicate emotional, cognitive, and physical fatigue from academic demands.
            <ul class="mt-2">
                <li>Rest and Recovery: Prioritize adequate sleep, breaks, and relaxation activities.</li>
                <li>Manage Workload: Consider reducing course load or requesting extensions if possible.</li>
                <li>Self-Care: Engage in activities that help you recharge (exercise, hobbies, social time).</li>
                <li>Seek Help: Visit the counseling center for stress management strategies and support.</li>
            </ul>
            <div class="mt-2">
                <em>Exhaustion is a sign that your body and mind need rest. Don't hesitate to ask for help.</em>
            </div>
        </div>
    @elseif($predictedLabel == 'High')
        <div class="alert alert-danger mt-4">
            Based on the prediction, we recommend speaking with a counselor or academic advisor immediately.<br>
            <div class="font-semibold mt-2 mb-1">Suggestions</div>
            <ul class="mt-2">
                <li>Seek Help: Prioritize scheduling a confidential session at the counseling center.</li>
                <li>Reduce Pressure: Consider temporary academic adjustments (extensions, breaks, etc.).</li>
                <li>Recovery Plan: Build a structured recovery plan with a counselor — focus on sleep, nutrition, and boundary-setting.</li>
            </ul>
            <div class="mt-2">
                <em>Burnout is not failure — it's a signal to realign. Help is available and effective.</em>
            </div>
        </div>
    @endif


</div>
@endsection 

<style>
.tooltip-hover {
  position: relative;
  display: inline-block;
  cursor: pointer;
}
.tooltip-hover .tooltip-box {
  visibility: hidden;
  opacity: 0;
  width: 340px;
  background-color: #fff;
  color: #333;
  text-align: left;
  border-radius: 0.5rem;
  border: 1px solid #d1d5db;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
  padding: 1rem;
  position: absolute;
  z-index: 50;
  left: 50%;
  transform: translateX(-50%);
  top: 120%;
  transition: opacity 0.2s;
  font-size: 0.95rem;
}
.tooltip-hover:hover .tooltip-box {
  visibility: visible;
  opacity: 1;
}
</style>

