@extends('layouts.app')

@section('title', 'OLBI-S Assessment Survey - Burnalytics')
@section('subtitle', 'Assessment Survey')

@section('content')
<!-- OLBI-S Info Modal -->
<div id="olbiModal" class="fixed inset-0 z-50 flex items-center justify-center backdrop-blur-sm" style="background: transparent; display: flex;">
    <div class="bg-white rounded-lg shadow-lg w-[90vw] max-w-5xl h-auto p-8 relative flex flex-col justify-center items-center" style="min-width:600px;">
        <h2 class="text-lg text-indigo-600 mb-4">About the OLBI-S Assessment</h2>
        <p class="mb-2 text-gray-700">
            The Oldenburg Burnout Inventory – Student Version (OLBI-S) is a scientifically validated tool used to assess academic burnout among students. It focuses on two key dimensions of burnout:
        </p>
        <ul class="list-disc pl-6 mb-2 text-gray-700">
            Exhaustion: emotional, cognitive, and physical fatigue caused by study demands.<br>
            Disengagement: withdrawal and detachment from academic work and lack of motivation.
        </ul>
        <p class="mb-2 text-gray-700">
            The OLBI-S is adapted from the original OLBI created by Evangelia Demerouti and colleagues, which is a research-based tool used to explore patterns of academic stress, exhaustion, and disengagement among students. It has been widely used in burnout studies involving students from various disciplines and countries.
        </p>
        <p class="mb-2 text-gray-700">
            This assessment is provided for educational and self-reflection purposes only. It is not a diagnostic tool and does not replace professional mental health evaluation, counseling, or treatment. By continuing, you acknowledge and agree to the following:
        </p>
        <ul class="list-disc pl-6 mb-2 text-gray-700">
            <li>Your responses are used only for academic and research purposes.</li>
            <li>The results are not a medical diagnosis, and any concerns should be discussed with a licensed mental health professional or school counselor.</li>
            <li>If you are experiencing high levels of stress, emotional distress, or academic difficulties, it is strongly encourage you to reach out to your school's guidance office or a qualified professional.</li>
        </ul>
        <button id="continueBtn" class="bg-indigo-500 hover:bg-indigo-600 text-white px-6 py-2 rounded-lg font-medium w-40 mt-2">Continue</button>
    </div>
</div>

<!-- Assessment Form -->
<form action="{{ route('assessment.result') }}" method="POST" id="assessmentForm">
    @csrf
    
    <!-- Demographic Section -->
    <div class="max-w-xl mx-auto sm:px-6 lg:px-8 mt-8">
        <div class="bg-white rounded-lg shadow-lg">
            <div id="demographicStep" class="p-8">
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label for="name" class="block text-gray-700 text-sm mb-2">Name</label>
                        <input type="text" name="name" id="name" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-200" maxlength="255" pattern="^[A-Za-z ]*$">
                        @error('name')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label for="age" class="block text-gray-700 text-sm mb-2">Age</label>
                        <input type="number" name="age" id="age" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-200" min="10" max="100" required>
                        @error('age')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label for="gender" class="block text-gray-700 text-sm mb-2">Gender</label>
                        <select name="gender" id="gender" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-200" required>
                            <option value="" disabled selected>Select gender</option>
                            @foreach($genders as $gender)
                                <option value="{{ $gender }}">{{ $gender }}</option>
                            @endforeach
                        </select>
                        @error('gender')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label for="program" class="block text-gray-700 text-sm mb-2">Program</label>
                        <select name="program" id="program" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-200" required>
                            <option value="" disabled selected>Select program</option>
                            @foreach($programs as $program)
                                <option value="{{ $program }}">{{ $program }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="program_other" id="program_other" class="w-full border border-gray-300 rounded-lg px-4 py-2 mt-2 focus:outline-none focus:ring-2 focus:ring-indigo-200" placeholder="Please specify program" style="display:none;">
                        @error('program')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label for="year_level" class="block text-gray-700 text-sm mb-2">Year Level</label>
                        <select name="year_level" id="year_level" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-200" required>
                            <option value="" disabled selected>Select year level</option>
                            @foreach($year_levels as $level)
                                <option value="{{ $level }}">{{ $level }}</option>
                            @endforeach
                        </select>
                        @error('year_level')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="text-center pt-8">
                    <button type="button" id="nextBtn" class="bg-indigo-500 hover:bg-indigo-600 text-white px-8 py-3 rounded-lg font-medium w-40 transition-colors">Next</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Questions-->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-lg">
            <div id="questionsStep" style="display:none;">
                <div class="border-b border-gray-400 p-6">
                    <div class="space-y-4">
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div id="progressBar" class="bg-indigo-500 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                        </div>
                    </div>
                </div>
                <div class="m-4">
                    <div class="mb-8 px-6">
                        <h2 class="text-lg text-gray-800 mb-6">Choose the option that best describes your experience.</h2>
                        
                        <!-- Section 1: Questions 1-3 -->
                        <div id="section1" class="question-section">
                        <div class="mb-8">
                            <h3 class="text-base text-gray-900 mb-4">1. How would you rate your grades last semester?</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
                                @php
                                    $grade_options = [
                                        ['value' => 1, 'label' => 'Excellent'],
                                        ['value' => 2, 'label' => 'Very Good'],
                                        ['value' => 3, 'label' => 'Good'],
                                        ['value' => 4, 'label' => 'Fair'],
                                        ['value' => 5, 'label' => 'Poor'],
                                    ];
                                @endphp
                                @foreach($grade_options as $option)
                                    <label class="flex items-center space-x-3 p-4 rounded-lg border-2 border-gray-200 hover:border-indigo-300 cursor-pointer transition-colors answer-option">
                                        <input type="radio" 
                                               name="answers[0]" 
                                               value="{{ $option['value'] }}" 
                                               class="text-indigo-500 focus:ring-indigo-500 answer-radio">
                                        <span class="flex-1 text-gray-700 text-sm">{{ $option['label'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="mb-8">
                            <h3 class="text-base text-gray-900 mb-4">2. I am confident that compared to last semester, my grades this semester is</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
                                @php
                                    $comparison_options = [
                                        ['value' => 1, 'label' => 'Much better'],
                                        ['value' => 2, 'label' => 'Somewhat better'],
                                        ['value' => 3, 'label' => 'About the same'],
                                        ['value' => 4, 'label' => 'Somewhat worse'],
                                        ['value' => 5, 'label' => 'Much worse'],
                                    ];
                                @endphp
                                @foreach($comparison_options as $option)
                                    <label class="flex items-center space-x-3 p-4 rounded-lg border-2 border-gray-200 hover:border-indigo-300 cursor-pointer transition-colors answer-option">
                                        <input type="radio" 
                                               name="answers[1]" 
                                               value="{{ $option['value'] }}" 
                                               class="text-indigo-500 focus:ring-indigo-500 answer-radio">
                                        <span class="flex-1 text-gray-700 text-sm">{{ $option['label'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="mb-8">
                            <h3 class="text-base text-gray-900 mb-4">3. How often have you felt that you were unable to control the important things in your life?</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
                                @php
                                    $stress_options = [
                                        ['value' => 1, 'label' => 'Very Often'],
                                        ['value' => 2, 'label' => 'Fairly Often'],
                                        ['value' => 3, 'label' => 'Sometimes'],
                                        ['value' => 4, 'label' => 'Almost Never'],
                                        ['value' => 5, 'label' => 'Never'],
                                    ];
                                @endphp
                                @foreach($stress_options as $option)
                                    <label class="flex items-center space-x-3 p-4 rounded-lg border-2 border-gray-200 hover:border-indigo-300 cursor-pointer transition-colors answer-option">
                                        <input type="radio" 
                                               name="answers[2]" 
                                               value="{{ $option['value'] }}" 
                                               class="text-indigo-500 focus:ring-indigo-500 answer-radio">
                                        <span class="flex-1 text-gray-700 text-sm">{{ $option['label'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        </div>
                        <!-- End Section 1 -->

                        <!-- Section 2: Questions 4-6 -->
                        <div id="section2" class="question-section" style="display:none;">
                        <div class="mb-8">
                            <h3 class="text-base text-gray-900 mb-4">4. How often have you felt confident about your ability to handle your personal problems?</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
                                @foreach($stress_options as $option)
                                    <label class="flex items-center space-x-3 p-4 rounded-lg border-2 border-gray-200 hover:border-indigo-300 cursor-pointer transition-colors answer-option">
                                        <input type="radio" 
                                               name="answers[3]" 
                                               value="{{ $option['value'] }}" 
                                               class="text-indigo-500 focus:ring-indigo-500 answer-radio">
                                        <span class="flex-1 text-gray-700 text-sm">{{ $option['label'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="mb-8">
                            <h3 class="text-base text-gray-900 mb-4">5. How often have you felt that things were going your way?</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
                                @foreach($stress_options as $option)
                                    <label class="flex items-center space-x-3 p-4 rounded-lg border-2 border-gray-200 hover:border-indigo-300 cursor-pointer transition-colors answer-option">
                                        <input type="radio" 
                                               name="answers[4]" 
                                               value="{{ $option['value'] }}" 
                                               class="text-indigo-500 focus:ring-indigo-500 answer-radio">
                                        <span class="flex-1 text-gray-700 text-sm">{{ $option['label'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="mb-8">
                            <h3 class="text-base text-gray-900 mb-4">6. How often have you felt difficulties were piling up so high that you could not overcome them?</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
                                @foreach($stress_options as $option)
                                    <label class="flex items-center space-x-3 p-4 rounded-lg border-2 border-gray-200 hover:border-indigo-300 cursor-pointer transition-colors answer-option">
                                        <input type="radio" 
                                               name="answers[5]" 
                                               value="{{ $option['value'] }}" 
                                               class="text-indigo-500 focus:ring-indigo-500 answer-radio">
                                        <span class="flex-1 text-gray-700 text-sm">{{ $option['label'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        </div>
                        <!-- End Section 2 -->

                        <!-- Section 3: Questions 7-9 -->
                        <div id="section3" class="question-section" style="display:none;">
                        <div class="mb-8">
                            <h3 class="text-base text-gray-900 mb-4">7. How long does it take you to fall asleep?</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
                                @php
                                    $sleep_time_options = [
                                        ['value' => 1, 'label' => '0 to 15 mins'],
                                        ['value' => 2, 'label' => '16 to 30 mins'],
                                        ['value' => 3, 'label' => '31 to 45 mins'],
                                        ['value' => 4, 'label' => '46 to 60 mins'],
                                        ['value' => 5, 'label' => 'Greater than 60 mins'],
                                    ];
                                @endphp
                                @foreach($sleep_time_options as $option)
                                    <label class="flex items-center space-x-3 p-4 rounded-lg border-2 border-gray-200 hover:border-indigo-300 cursor-pointer transition-colors answer-option">
                                        <input type="radio" 
                                               name="answers[6]" 
                                               value="{{ $option['value'] }}" 
                                               class="text-indigo-500 focus:ring-indigo-500 answer-radio">
                                        <span class="flex-1 text-gray-700 text-sm">{{ $option['label'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="mb-8">
                            <h3 class="text-base text-gray-900 mb-4">8. If you then wake up during the night, how long are you awake for in total minutes?</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
                                @foreach($sleep_time_options as $option)
                                    <label class="flex items-center space-x-3 p-4 rounded-lg border-2 border-gray-200 hover:border-indigo-300 cursor-pointer transition-colors answer-option">
                                        <input type="radio" 
                                               name="answers[7]" 
                                               value="{{ $option['value'] }}" 
                                               class="text-indigo-500 focus:ring-indigo-500 answer-radio">
                                        <span class="flex-1 text-gray-700 text-sm">{{ $option['label'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="mb-8">
                            <h3 class="text-base text-gray-900 mb-4">9. How many nights a week do you have a problem with your sleep?</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                                @php
                                    $nights_options = [
                                        ['value' => 1, 'label' => '0 to 1'],
                                        ['value' => 2, 'label' => '2'],
                                        ['value' => 3, 'label' => '3'],
                                        ['value' => 4, 'label' => '5 to 7'],
                                    ];
                                @endphp
                                @foreach($nights_options as $option)
                                    <label class="flex items-center space-x-3 p-4 rounded-lg border-2 border-gray-200 hover:border-indigo-300 cursor-pointer transition-colors answer-option">
                                        <input type="radio" 
                                               name="answers[8]" 
                                               value="{{ $option['value'] }}" 
                                               class="text-indigo-500 focus:ring-indigo-500 answer-radio">
                                        <span class="flex-1 text-gray-700 text-sm">{{ $option['label'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        </div>
                        <!-- End Section 3 -->

                        <!-- Section 4: Questions 10-12 -->
                        <div id="section4" class="question-section" style="display:none;">
                        <div class="mb-8">
                            <h3 class="text-base text-gray-900 mb-4">10. How would you rate your sleep quality?</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
                                @php
                                    $quality_options = [
                                        ['value' => 1, 'label' => 'Very good'],
                                        ['value' => 2, 'label' => 'Good'],
                                        ['value' => 3, 'label' => 'Average'],
                                        ['value' => 4, 'label' => 'Poor'],
                                        ['value' => 5, 'label' => 'Very Poor'],
                                    ];
                                @endphp
                                @foreach($quality_options as $option)
                                    <label class="flex items-center space-x-3 p-4 rounded-lg border-2 border-gray-200 hover:border-indigo-300 cursor-pointer transition-colors answer-option">
                                        <input type="radio" 
                                               name="answers[9]" 
                                               value="{{ $option['value'] }}" 
                                               class="text-indigo-500 focus:ring-indigo-500 answer-radio">
                                        <span class="flex-1 text-gray-700 text-sm">{{ $option['label'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="mb-8">
                            <h3 class="text-base text-gray-900 mb-4">11. To what extent has poor sleep troubled you in general?</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
                                @php
                                    $extent_options = [
                                        ['value' => 1, 'label' => 'Not at all'],
                                        ['value' => 2, 'label' => 'A little'],
                                        ['value' => 3, 'label' => 'Somewhat'],
                                        ['value' => 4, 'label' => 'Much'],
                                        ['value' => 5, 'label' => 'Very much'],
                                    ];
                                @endphp
                                @foreach($extent_options as $option)
                                    <label class="flex items-center space-x-3 p-4 rounded-lg border-2 border-gray-200 hover:border-indigo-300 cursor-pointer transition-colors answer-option">
                                        <input type="radio" 
                                               name="answers[10]" 
                                               value="{{ $option['value'] }}" 
                                               class="text-indigo-500 focus:ring-indigo-500 answer-radio">
                                        <span class="flex-1 text-gray-700 text-sm">{{ $option['label'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="mb-8">
                            <h3 class="text-base text-gray-900 mb-4">12. To what extent has poor sleep affected your mood, energy, or relationships?</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
                                @foreach($extent_options as $option)
                                    <label class="flex items-center space-x-3 p-4 rounded-lg border-2 border-gray-200 hover:border-indigo-300 cursor-pointer transition-colors answer-option">
                                        <input type="radio" 
                                               name="answers[11]" 
                                               value="{{ $option['value'] }}" 
                                               class="text-indigo-500 focus:ring-indigo-500 answer-radio">
                                        <span class="flex-1 text-gray-700 text-sm">{{ $option['label'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        </div>
                        <!-- End Section 4 -->

                        <!-- Section 5: Questions 13-15 -->
                        <div id="section5" class="question-section" style="display:none;">
                        <div class="mb-8">
                            <h3 class="text-base text-gray-900 mb-4">13. To what extent has poor sleep affected your concentration, productivity, or ability to stay awake?</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
                                @foreach($extent_options as $option)
                                    <label class="flex items-center space-x-3 p-4 rounded-lg border-2 border-gray-200 hover:border-indigo-300 cursor-pointer transition-colors answer-option">
                                        <input type="radio" 
                                               name="answers[12]" 
                                               value="{{ $option['value'] }}" 
                                               class="text-indigo-500 focus:ring-indigo-500 answer-radio">
                                        <span class="flex-1 text-gray-700 text-sm">{{ $option['label'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="mb-8">
                            <h3 class="text-base text-gray-900 mb-4">14. How long have you had a problem with your sleep?</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
                                @php
                                    $duration_options = [
                                        ['value' => 1, 'label' => 'I don\'t have a problem / Less than 1 month'],
                                        ['value' => 2, 'label' => '1 - 2 months'],
                                        ['value' => 3, 'label' => '3 - 6 months'],
                                        ['value' => 4, 'label' => '7 - 12 months'],
                                        ['value' => 5, 'label' => 'More than 1 year'],
                                    ];
                                @endphp
                                @foreach($duration_options as $option)
                                    <label class="flex items-center space-x-3 p-4 rounded-lg border-2 border-gray-200 hover:border-indigo-300 cursor-pointer transition-colors answer-option">
                                        <input type="radio" 
                                               name="answers[13]" 
                                               value="{{ $option['value'] }}" 
                                               class="text-indigo-500 focus:ring-indigo-500 answer-radio">
                                        <span class="flex-1 text-gray-700 text-sm">{{ $option['label'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                    <!-- OLBI-S Questions Section -->

                    @php
                        $olbi_options = [
                            ['value' => 1, 'label' => 'Strongly Agree'],
                            ['value' => 2, 'label' => 'Agree'],
                            ['value' => 3, 'label' => 'Disagree'],
                            ['value' => 4, 'label' => 'Strongly Disagree'],
                        ];
                    @endphp
                    @foreach($olbi_questions as $index => $question)
                        @if($index == 0)
                            <!-- First OLBI question (15) goes in Section 5 - no section change needed -->
                        @elseif($index == 1)
                            </div>
                            <!-- End Section 5 -->
                            
                            <!-- Section 6: OLBI Questions 16-18 -->
                            <div id="section6" class="question-section" style="display:none;">
                        @elseif($index == 4)
                            </div>
                            <!-- End Section 6 -->
                            
                            <!-- Section 7: OLBI Questions 19-21 -->
                            <div id="section7" class="question-section" style="display:none;">
                        @elseif($index == 7)
                            </div>
                            <!-- End Section 7 -->
                            
                            <!-- Section 8: OLBI Questions 22-24 -->
                            <div id="section8" class="question-section" style="display:none;">
                        @elseif($index == 10)
                            </div>
                            <!-- End Section 8 -->
                            
                            <!-- Section 9: OLBI Questions 25-27 -->
                            <div id="section9" class="question-section" style="display:none;">
                        @elseif($index == 13)
                            </div>
                            <!-- End Section 9 -->
                            
                            <!-- Section 10: OLBI Questions 28-30 -->
                            <div id="section10" class="question-section" style="display:none;">
                        @endif
                    <div class="mb-8">
                        <h3 class="text-base text-gray-900 mb-4">{{ $index + 15 }}. {{ $question }}</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                            @foreach($olbi_options as $option)
                                <label class="flex items-center space-x-3 p-4 rounded-lg border-2 border-gray-200 hover:border-indigo-300 cursor-pointer transition-colors answer-option">
                                    <input type="radio" 
                                           name="answers[{{ $index + 14 }}]" 
                                           value="{{ $option['value'] }}" 
                                           class="text-indigo-500 focus:ring-indigo-500 answer-radio">
                                    <span class="flex-1 text-gray-700 text-sm">{{ $option['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                    </div>
                    <!-- End Section 10 -->
                    
                    <!-- Navigation Buttons -->
                    <div class="text-center pb-4">
                        <div class="flex justify-center gap-4">
                            <button type="button" 
                                    id="prevBtn"
                                    class="bg-gray-500 hover:bg-gray-600 text-white px-8 py-3 rounded-lg font-medium transition-colors"
                                    style="display:none;">
                                Previous
                            </button>
                            <button type="button" 
                                    id="nextSectionBtn"
                                    class="bg-indigo-500 hover:bg-indigo-600 text-white px-8 py-3 rounded-lg font-medium transition-colors relative"
                                    title="Please answer all questions in this section">
                                Next
                            </button>
                            <button type="button" 
                                    id="resetBtn"
                                    class="bg-gray-500 hover:bg-gray-600 text-white px-8 py-3 rounded-lg font-medium transition-colors"
                                    style="display:none;">
                                Reset
                            </button>
                            <button type="submit" 
                                    id="submitBtn"
                                    class="bg-indigo-500 hover:bg-indigo-600 text-white px-8 py-3 rounded-lg font-medium transition-colors opacity-50 cursor-not-allowed"
                                    disabled
                                    style="display:none;">
                                Submit
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Modal logic
    const modal = document.getElementById('olbiModal');
    const continueBtn = document.getElementById('continueBtn');
    if (modal && continueBtn) {
        continueBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });
    }

    // Two-step form logic
    const demographicStep = document.getElementById('demographicStep');
    const questionsStep = document.getElementById('questionsStep');
    const nextBtn = document.getElementById('nextBtn');
    if (nextBtn && demographicStep && questionsStep) {
        nextBtn.addEventListener('click', function() {
            // Validate demographic fields before proceeding
            const name = document.getElementById('name');
            const age = document.getElementById('age');
            const gender = document.getElementById('gender');
            const program = document.getElementById('program');
            const yearLevel = document.getElementById('year_level');
            let valid = true;
            [name, age, gender, program, yearLevel].forEach(field => {
                if (!field.value) {
                    field.classList.add('border-red-500');
                    valid = false;
                } else {
                    field.classList.remove('border-red-500');
                }
            });
            if (valid) {
                demographicStep.style.display = 'none';
                questionsStep.style.display = 'block';
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    }

    // Show/hide program_other field
    const programSelect = document.getElementById('program');
    const programOther = document.getElementById('program_other');
    if (programSelect && programOther) {
        programSelect.addEventListener('change', function() {
            if (programSelect.value === 'Other') {
                programOther.style.display = 'block';
                programOther.required = true;
            } else {
                programOther.style.display = 'none';
                programOther.required = false;
            }
        });
    }

    // Pagination logic
    const totalQuestions = 30;
    const questionsPerSection = 3;
    const totalSections = 10; // 10 sections total (30 questions / 3 per section)
    let currentSection = 1;
    let answers = {};

    function updateProgress() {
        const completed = Object.keys(answers).length;
        const percentage = (completed / totalQuestions) * 100;
        document.getElementById('progressBar').style.width = `${percentage}%`;
    }

    function showSection(sectionNumber) {
        // Hide all sections
        for (let i = 1; i <= totalSections; i++) {
            const section = document.getElementById(`section${i}`);
            if (section) section.style.display = 'none';
        }
        
        // Show current section
        const currentSectionEl = document.getElementById(`section${sectionNumber}`);
        if (currentSectionEl) currentSectionEl.style.display = 'block';
        
        // Update navigation buttons
        const prevBtn = document.getElementById('prevBtn');
        const nextSectionBtn = document.getElementById('nextSectionBtn');
        const submitBtn = document.getElementById('submitBtn');
        const resetBtn = document.getElementById('resetBtn');
        
        // Show/hide Previous button (hidden on first section)
        prevBtn.style.display = sectionNumber > 1 ? 'inline-block' : 'none';
        
        // Show/hide Reset button (only on last section)
        resetBtn.style.display = sectionNumber === totalSections ? 'inline-block' : 'none';
        
        // Check if current section is complete
        const sectionComplete = checkSectionComplete(sectionNumber);
        
        // Next button is always visible except on last section
        // Enable/disable based on section completion
        if (sectionNumber < totalSections) {
            nextSectionBtn.style.display = 'inline-block';
            if (sectionComplete) {
                nextSectionBtn.disabled = false;
                nextSectionBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                nextSectionBtn.classList.add('hover:bg-indigo-600');
                nextSectionBtn.title = '';
            } else {
                nextSectionBtn.disabled = true;
                nextSectionBtn.classList.add('opacity-50', 'cursor-not-allowed');
                nextSectionBtn.classList.remove('hover:bg-indigo-600');
                nextSectionBtn.title = 'Please answer all questions in this section';
            }
        } else {
            nextSectionBtn.style.display = 'none';
        }
        
        // Show/hide Submit button (only on last section if all questions answered)
        if (sectionNumber === totalSections) {
            const allComplete = Object.keys(answers).length === totalQuestions;
            if (allComplete) {
                submitBtn.style.display = 'inline-block';
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                submitBtn.style.display = 'none';
            }
        } else {
            submitBtn.style.display = 'none';
        }
    }

    function checkSectionComplete(sectionNumber) {
        // Calculate question range for this section (3 questions per section)
        const startQuestion = (sectionNumber - 1) * 3;
        const endQuestion = Math.min(startQuestion + 2, totalQuestions - 1);
        
        // Check if all questions in range are answered
        for (let i = startQuestion; i <= endQuestion; i++) {
            if (!answers[i]) {
                return false;
            }
        }
        return true;
    }

    function autoAdvanceSection() {
        if (currentSection < totalSections && checkSectionComplete(currentSection)) {
            setTimeout(() => {
                currentSection++;
                showSection(currentSection);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }, 500);
        }
    }

    // Handle radio button changes
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('answer-radio')) {
            const name = e.target.name;
            const questionIndex = parseInt(name.match(/answers\[(\d+)\]/)[1]);
            answers[questionIndex] = e.target.value;
            // Update styling for selected option
            const container = e.target.closest('.mb-8');
            container.querySelectorAll('.answer-option').forEach(option => {
                option.classList.remove('border-indigo-500', 'bg-indigo-50');
                option.classList.add('border-gray-200');
            });
            e.target.closest('.answer-option').classList.remove('border-gray-200');
            e.target.closest('.answer-option').classList.add('border-indigo-500', 'bg-indigo-50');
            updateProgress();
            showSection(currentSection); // Update button visibility
            autoAdvanceSection(); // Auto-advance when section complete
        }
    });

    // Navigation button handlers
    const prevBtn = document.getElementById('prevBtn');
    const nextSectionBtn = document.getElementById('nextSectionBtn');
    
    if (prevBtn) {
        prevBtn.addEventListener('click', function() {
            if (currentSection > 1) {
                currentSection--;
                showSection(currentSection);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    }
    
    if (nextSectionBtn) {
        nextSectionBtn.addEventListener('click', function() {
            if (currentSection < totalSections && checkSectionComplete(currentSection)) {
                currentSection++;
                showSection(currentSection);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    }

    // Reset button functionality
    const resetBtn = document.getElementById('resetBtn');
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            // Clear all radio button selections
            document.querySelectorAll('.answer-radio').forEach(radio => {
                radio.checked = false;
            });
            
            // Reset answer tracking object
            answers = {};
            
            // Reset visual styling for all options
            document.querySelectorAll('.answer-option').forEach(option => {
                option.classList.remove('border-indigo-500', 'bg-indigo-50');
                option.classList.add('border-gray-200');
            });
            
            // Update progress
            updateProgress();
            
            // Reset to first section
            currentSection = 1;
            showSection(1);
            
            // Scroll to top of questions
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // Initialize
    updateProgress();
    showSection(1);

    // Before submit, map answers[0-29] to Q1-Q30 hidden inputs
    document.getElementById('assessmentForm').addEventListener('submit', function(e) {
        // Check if all questions are answered
        if (Object.keys(answers).length !== totalQuestions) {
            e.preventDefault();
            alert('Please answer all questions before submitting.');
            return false;
        }
        
        // Remove any previous hidden inputs
        document.querySelectorAll('.olbi-hidden-q').forEach(el => el.remove());
        
        // Create hidden inputs for all answers
        for (let i = 0; i < 30; i++) {
            const val = answers[i];
            if (val) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'Q' + (i+1);
                input.value = val;
                input.className = 'olbi-hidden-q';
                this.appendChild(input);
            }
        }
        
        // Allow form to submit
        return true;
    });
});
</script>
@endsection

<style>
#olbiModal {
    background: rgba(0,0,0,0.3);
    backdrop-filter: blur(8px);
}
</style>