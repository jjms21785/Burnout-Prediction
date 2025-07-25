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
        <span>Student ID: <b>
            @if(isset($student_id))
                {{ $student_id }}
            @else
                <span class="text-gray-400">Unavailable</span>
            @endif
        </b></span> |
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
            <h3 class="text-xl font-bold mb-1" style="color: black;">Burnout Risk Level:</h3>
            @if(isset($predictedLabel) && $predictedLabel)
                <div class="mt-1">
                    <span class="text-xl font-bold @if($predictedLabel=='High') text-red-600 @elseif($predictedLabel=='Moderate') text-yellow-600 @else text-green-600 @endif">
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
                    <span class="tooltip-hover">About Risk Level.  
                        <span class="tooltip-box">
                            The burnout risk level (Low, Moderate, or High) is not based on fixed scores.<br>
                            Instead, it's predicted by the Random Forest model trained on dataset of the responses.<br><br>
                            <b>How?</b> The model looks at all answers together and compares them with patterns it has learned from the dataset to determine burnout levels, even if two people have the same average score, their response patterns may result in different predictions.
                        </span>
                    </span>
                </div>
            @endif
            @if(isset($errorMsg) && $errorMsg)
                <div class="mt-2 text-red-600 text-sm">{{ $errorMsg }}</div>
            @endif
        </div>
        <div class="bg-white border rounded-lg p-6 flex flex-col items-center">
            <h3 class="text-xl font-bold mb-1" fstyle="color: black;">Prediction Confidence: </h3>
                @if(isset($confidence) && is_array($confidence))
                    {{ number_format(max($confidence)*100,2) }}%
                @else
                    <div class="mt-1">
                        <span class="text-xl font-bold text-gray-400">Unavailable</span>
                    </div>
                @endif
            <div class="text-xl font-bold mb-1" fstyle="color: black;">{{ isset($modelAccuracy) ? 'Model Accuracy: '.($modelAccuracy*100).'%' : '' }}</div>
            @if(isset($modelAccuracy) && isset($confidence))
                <div class="text-xs text-gray-500 mt-2">
                    <span class="tooltip-hover">About Confidence and Accuracy.
                        <span class="tooltip-box">
                            <b>Confidence</b> shows how certain the trained model is about the burnout risk level prediction. 
                            It's based on how consistently the answers match patterns seen in the training data. 
                            Higher confidence means the model is more certain that the pattern fits a specific risk level.<br><br>  
                            <b>Accuracy</b> shows how well the Machine Learning model performs on unseen data. 
                            The model has been tested on hundreds of samples and achieved around the said accuracy. 
                            That means it correctly predicted burnout risk out of 100% of cases during evaluation.
                        </span>
                    </span>
                </div>
            @endif
            @if(isset($errorMsg) && $errorMsg)
                <div class="mt-2 text-red-600 text-sm">{{ $errorMsg }}</div>
            @endif
        </div>
    </div>

    <!-- Score Breakdown -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white border rounded-lg p-4 text-center">
            <h5 class="font-semibold mb-1">Average Score</h5>
            <p class="text-lg"><strong>
                @if(isset($totalScore))
                    {{ round($totalScore/16, 2) }}
                @else
                    <span class="text-gray-400">Unavailable</span>
                @endif
            </strong></p>
            <div class="text-xs text-gray-500 mt-4">
                <span class="tooltip-hover">Mean of all 16 answers (range: 0-3). <br>More info here.
                    <span class="tooltip-box">
                        The average score is the total of the answers to all 16 questions divided by 16.<br>
                        (Total Score ÷ 16 = Average Score)<br><br>
                        Each answer is rated from 0 (Strongly Agree) to 3 (Strongly Disagree).
                    </span>
                </span>
            </div>
        </div>
        <div class="bg-white border rounded-lg p-4 text-center">
            <h5 class="font-semibold mb-1">Exhaustion Score</h5>
            <p class="text-lg"><strong>
                @if(isset($exhaustionScore))
                    {{ $exhaustionScore }}
                @else
                    <span class="text-gray-400">Unavailable</span>
                @endif
            </strong></p>
            <div class="text-xs text-gray-500 mt-4">
                <span class="tooltip-hover">Sum of OLBI-S items related to exhaustion questions. <br>More info here. 
                    <span class="tooltip-box">
                        This score reflects how emotionally and physically drained the feeling from the work or studies.<br><br>
                        It's calculated by averaging the responses to the Exhaustion-related items:<br>
                        <b>Q9, Q10, Q11, Q12, Q13, Q14, Q15, Q16</b><br><br>
                        Higher scores indicate greater emotional fatigue.
                        Some of these are reverse-scored because they are negatively worded (it will become the opposite of the chosen answer for calculations).<br>
                        <b>Reverse-scored questions:</b> Q9, Q10, Q12, Q14
                    </span>
                </span>
            </div>
        </div>
        <div class="bg-white border rounded-lg p-4 text-center">
            <h5 class="font-semibold mb-1">Disengagement Score</h5>
            <p class="text-lg"><strong>
                @if(isset($disengagementScore))
                    {{ $disengagementScore }}
                @else
                    <span class="text-gray-400">Unavailable</span>
                @endif
            </strong></p>
            <div class="text-xs text-gray-500 mt-4">
                <span class="tooltip-hover">Sum of OLBI-S items related to disengagement questions. <br>More info here. 
                    <span class="tooltip-box">
                        This score measures how mentally distanced or disconnected the feeling from the tasks or responsibilities.<br><br>
                        It's calculated by averaging the answers to the Disengagement-related questions:<br>
                        <b>Q1, Q2, Q3, Q4, Q5, Q6, Q7, Q8</b><br><br>
                        Some of these are reverse-scored because they are negatively worded (it will become the opposite of the chosen answer for calculations).<br>
                        <b>Reverse-scored questions:</b> Q2, Q3, Q5, Q6
                    </span>
                </span>
            </div>
        </div>
    </div>
    <div class="w-full flex justify-center mb-8">
        <div class="max-w-2xl text-xs text-gray-600 text-center">
            <b>About The Scores</b><br>
            The average, exhaustion, and disengagement scores are shown here to help understand the responses better, but they are not used to determine the actual burnout risk level.<br>
            The burnout risk (Low, Moderate, or High) is predicted by a machine learning model trained on patterns from the dataset. The model analyzes all the individual answers, not summary scores.<br>
            Although, these scores are helpful for insights, they do not affect the prediction or its accuracy.
        </div>
    </div>

    <div class="bg-white border rounded-lg p-4 mb-6">
        <div class="flex items-center mb-1">
            <h5 class="font-semibold">Answer Summary</h5>
        </div>
        <div class="overflow-x-auto">
            @if(isset($original_responses) && is_array($original_responses) && count($original_responses) > 0)
                <table class="min-w-full text-center text-xs">
                    <thead>
                        <tr>
                            @foreach(array_keys($original_responses) as $question)
                                <th class="px-2 py-1">{{ $question }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            @foreach($original_responses as $value)
                                <td class="px-2 py-1">
                                    {{ $value }}
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
                <div class="w-full flex justify-center mt-4">
                    <div class="bg-gray-100 border border-gray-300 rounded px-4 py-2 text-xs text-gray-700 text-center">
                        <b>3 = Strongly Agree | 2 = Agree | 1 = Disagree | 0 = Strongly Disagree</b>
                    </div>
                </div>
            @else
                <div class="text-gray-400 py-4 text-center">Unavailable.</div>
            @endif
        </div>
    </div>

    <!-- Recommendation Box -->
    <h4 class="text-lg font-bold mt-8 mb-2">Interpretation</h4>
    @if(isset($predictedLabel) && $predictedLabel)
        <span class="text-xl font-bold text-gray-400">Unavailable</span>
    @endif
    @if($predictedLabel == 'Low')
        <div class="alert alert-success mt-4">
            Currently showing low signs of burnout.<br>
            <div class="font-semibold mt-2 mb-1">Suggestions</div>
            This indicates a healthy balance between academic demands and personal well-being.
            <ul class="mt-2">
                <li>Maintain: Continue habits that promote emotional and cognitive engagement.</li>
                <li>Monitor: Stay aware of signs like persistent fatigue or loss of motivation.</li>
                <li>Support Others: Students in better mental states often play a key role in peer well-being.</li>
            </ul>
            <div class="mt-2">
                <em>Even if doing well, visiting the counseling office for check-ins or other personal concerns is always encouraged.</em>
            </div>
        </div>
    @elseif($predictedLabel == 'Moderate')
        <div class="alert alert-warning mt-4">
            Moderate risk often means emotional fatigue is rising and motivation may be fluctuating.
            <div class="font-semibold mt-2 mb-1">Suggestions</div>
            <ul class="mt-2">
                <li>Self-Audit: Track time spent on academic work, sleep, social activity, and rest. Recognize imbalance.</li>
                <li>Recalibrate: Adjust workloads and integrate breaks, even short ones. </li>
                <li>Talk: Share with a peer and visit the counseling office for support and strategy.</li>
            </ul>
            <div class="mt-2">
                <em>Early intervention helps prevent chronic burnout. Reach out to Guidance and Counseling Office.</em>
            </div>
        </div>
    @elseif($predictedLabel == 'High')
        <div class="alert alert-danger mt-4">
            We recommend speaking with a counselor or academic advisor immediately.<br>
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

    <div class="flex justify-center mt-8">
        <a href="{{ route('assessment.index') }}" class="bg-green-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-green-700 transition">New Assessment</a>
    </div>
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

