@extends('layouts.app')

@section('title', 'Assessment Survey - Burnalytix')
@section('subtitle', 'Assessment Survey')

@section('content')
<!-- Assessment Header -->
<div class="bg-green-600 text-white py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-3xl font-bold mb-4">Assessment Survey</h1>
        <p class="text-green-100">
            Please answer all 22 questions honestly based on your recent experiences with studies. Your responses will
            help assess your current burnout risk level.
        </p>
    </div>
</div>

<!-- Assessment Form -->
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white border border-gray-200 rounded-lg shadow-lg">
        <form action="{{ route('assessment.store') }}" method="POST" id="assessmentForm">
            @csrf
            <div class="border-b p-6">
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <p class="text-green-600 font-medium">
                            Progress: <span id="progressText">0/{{ count($questions) }}</span> questions completed
                        </p>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div id="progressBar" class="bg-green-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                </div>
            </div>

            <div class="p-8">
                @foreach($questions as $index => $question)
                <div class="question-container {{ $index === 0 ? 'block' : 'hidden' }}" data-question="{{ $index }}">
                    <div class="space-y-8">
                        <div class="flex justify-between items-center">
                            <h2 class="text-green-600 font-medium text-lg">
                                Question {{ $index + 1 }} of {{ count($questions) }}
                            </h2>
                            <div class="flex space-x-2">
                                <button type="button" 
                                        class="prev-btn border border-green-600 text-green-600 hover:bg-green-50 px-4 py-2 rounded-md transition-colors {{ $index === 0 ? 'opacity-50 cursor-not-allowed' : '' }}"
                                        {{ $index === 0 ? 'disabled' : '' }}>
                                    ← Previous
                                </button>
                                <button type="button" 
                                        class="next-btn bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md transition-colors opacity-50 cursor-not-allowed"
                                        disabled>
                                    {{ $index === count($questions) - 1 ? 'Submit' : 'Next →' }}
                                </button>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <h3 class="text-xl font-medium text-gray-900">{{ $question }}</h3>

                            <div class="space-y-3">
                                @php
                                    $options = [
                                        ['value' => 0, 'label' => 'Never'],
                                        ['value' => 1, 'label' => 'A few times a year'],
                                        ['value' => 2, 'label' => 'Once a month or less'],
                                        ['value' => 3, 'label' => 'A few times a month'],
                                        ['value' => 4, 'label' => 'Once a week'],
                                        ['value' => 5, 'label' => 'A few times a week'],
                                        ['value' => 6, 'label' => 'Everyday']
                                    ];
                                @endphp

                                @foreach($options as $option)
                                <label class="flex items-center space-x-3 p-4 rounded-lg border-2 border-gray-200 hover:border-green-300 cursor-pointer transition-colors answer-option">
                                    <input type="radio" 
                                           name="answers[{{ $index }}]" 
                                           value="{{ $option['value'] }}" 
                                           class="text-green-600 focus:ring-green-500 answer-radio"
                                           required>
                                    <span class="flex-1 text-gray-700">{{ $option['label'] }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach

                <div class="text-center pt-8">
                    <button type="submit" 
                            id="submitBtn"
                            class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg font-medium transition-colors opacity-50 cursor-not-allowed"
                            disabled>
                        Predict Burnout Level
                    </button>
                    <p class="text-gray-500 text-sm mt-2">Please answer all {{ count($questions) }} questions to submit the survey</p>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const questions = document.querySelectorAll('.question-container');
    const totalQuestions = questions.length;
    let currentQuestion = 0;
    let answers = {};

    function updateProgress() {
        const completed = Object.keys(answers).length;
        const percentage = (completed / totalQuestions) * 100;
        
        document.getElementById('progressText').textContent = `${completed}/${totalQuestions}`;
        document.getElementById('progressBar').style.width = `${percentage}%`;
        
        // Enable submit button if all questions answered
        const submitBtn = document.getElementById('submitBtn');
        if (completed === totalQuestions) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        } else {
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
        }
    }

    function showQuestion(index) {
        questions.forEach((q, i) => {
            q.classList.toggle('hidden', i !== index);
        });
        
        const prevBtn = questions[index].querySelector('.prev-btn');
        const nextBtn = questions[index].querySelector('.next-btn');
        
        // Update previous button
        if (index === 0) {
            prevBtn.disabled = true;
            prevBtn.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            prevBtn.disabled = false;
            prevBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
        
        // Update next button based on current question answer
        const hasAnswer = answers.hasOwnProperty(index);
        if (hasAnswer) {
            nextBtn.disabled = false;
            nextBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        } else {
            nextBtn.disabled = true;
            nextBtn.classList.add('opacity-50', 'cursor-not-allowed');
        }
    }

    // Handle radio button changes
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('answer-radio')) {
            const questionIndex = parseInt(e.target.closest('.question-container').dataset.question);
            answers[questionIndex] = e.target.value;
            
            // Update styling for selected option
            const container = e.target.closest('.question-container');
            container.querySelectorAll('.answer-option').forEach(option => {
                option.classList.remove('border-green-500', 'bg-green-50');
                option.classList.add('border-gray-200');
            });
            
            e.target.closest('.answer-option').classList.remove('border-gray-200');
            e.target.closest('.answer-option').classList.add('border-green-500', 'bg-green-50');
            
            // Enable next button
            const nextBtn = container.querySelector('.next-btn');
            nextBtn.disabled = false;
            nextBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            
            updateProgress();
        }
    });

    // Handle navigation buttons
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('next-btn') && !e.target.disabled) {
            if (currentQuestion < totalQuestions - 1) {
                currentQuestion++;
                showQuestion(currentQuestion);
            } else {
                // Submit form
                document.getElementById('assessmentForm').submit();
            }
        }
        
        if (e.target.classList.contains('prev-btn') && !e.target.disabled) {
            if (currentQuestion > 0) {
                currentQuestion--;
                showQuestion(currentQuestion);
            }
        }
    });

    // Initialize
    showQuestion(0);
    updateProgress();
});
</script>
@endsection