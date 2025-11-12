@extends('layouts.app')

@section('title', 'OLBI-S Assessment Survey - Burnalytics')
@section('subtitle', 'Assessment Survey')

@section('content')
<!-- Assessment Form -->
<form action="{{ route('assessment.result') }}" method="POST" id="assessmentForm">
    @csrf
    
    <!-- Demographic Section -->
    <div class="max-w-xl mx-auto sm:px-6 lg:px-8 mt-8">
        @if(session('error'))
        <div class="mb-4 rounded-lg p-4 bg-red-100 border border-red-300">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
            </div>
        </div>
        @endif
        
        <div class="bg-white rounded-lg shadow-lg">
            <div id="demographicStep" class="p-6">
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label for="first_name" class="block text-gray-700 text-sm mb-2">First Name</label>
                        <input type="text" name="first_name" id="first_name" class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200" maxlength="255" pattern="^[A-Za-z ]*$">
                        @error('first_name')<span class="text-red-600 text-xs">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label for="last_name" class="block text-gray-700 text-sm mb-2">Last Name</label>
                        <input type="text" name="last_name" id="last_name" class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200" maxlength="255" pattern="^[A-Za-z ]*$">
                        @error('last_name')<span class="text-red-600 text-xs">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label for="age" class="block text-gray-700 text-sm mb-2">Age</label>
                        <input type="number" name="age" id="age" class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200" min="10" max="100" required>
                        @error('age')<span class="text-red-600 text-xs">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label for="gender" class="block text-gray-700 text-sm mb-2">Gender</label>
                        <select name="gender" id="gender" class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200" required>
                            <option value="" disabled selected>Select gender</option>
                            @foreach($genders as $gender)
                                <option value="{{ $gender }}">{{ $gender }}</option>
                            @endforeach
                        </select>
                        @error('gender')<span class="text-red-600 text-xs">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label for="program" class="block text-gray-700 text-sm mb-2">College</label>
                        <select name="program" id="program" class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200" required>
                            <option value="" disabled selected>Select college</option>
                            @foreach($programs as $program)
                                <option value="{{ $program }}">{{ $program }}</option>
                            @endforeach
                            <option value="Others">Others</option>
                        </select>
                        <input type="text" name="program_other" id="program_other" placeholder="Please specify" class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200 mt-2 hidden">
                        @error('program')<span class="text-red-600 text-xs">{{ $message }}</span>@enderror
                        @error('program_other')<span class="text-red-600 text-xs">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label for="year_level" class="block text-gray-700 text-sm mb-2">Year Level</label>
                        <select name="year_level" id="year_level" class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200" required>
                            <option value="" disabled selected>Select year level</option>
                            @foreach($year_levels as $level)
                                <option value="{{ $level }}">{{ $level }}</option>
                            @endforeach
                        </select>
                        @error('year_level')<span class="text-red-600 text-xs">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="text-center pt-6">
                    <button type="button" id="nextBtn" class="bg-indigo-500 hover:bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm font-medium w-32 transition-colors">Next</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Questions Section -->
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
                        
                        @php
                            $totalSections = 10;
                            $questionsPerSection = 3;
                                @endphp
                        
                        @for($section = 1; $section <= $totalSections; $section++)
                            <div id="section{{ $section }}" class="question-section" style="display: {{ $section === 1 ? 'block' : 'none' }};">
                                @php
                                    $startIndex = ($section - 1) * $questionsPerSection;
                                    $endIndex = min($startIndex + $questionsPerSection - 1, count($questions) - 1);
                                @endphp
                                @for($i = $startIndex; $i <= $endIndex; $i++)
                                    @if(isset($questions[$i]))
                        <div class="mb-8">
                                            <h3 class="text-base text-gray-900 mb-4">{{ $questions[$i]['number'] }}. {{ $questions[$i]['text'] }}</h3>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 {{ $questions[$i]['gridCols'] }} gap-4">
                                                @foreach($questions[$i]['options'] as $option)
                                    <label class="flex items-center space-x-3 p-4 rounded-lg border-2 border-gray-200 hover:border-indigo-300 cursor-pointer transition-colors answer-option">
                                        <input type="radio" 
                                                               name="{{ $questions[$i]['name'] }}" 
                                               value="{{ $option['value'] }}" 
                                               class="text-indigo-500 focus:ring-indigo-500 answer-radio">
                                        <span class="flex-1 text-gray-700 text-sm">{{ $option['label'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                                    @endif
                                @endfor
                            </div>
                        @endfor
                    
                     <!-- Navigation Buttons -->
                     <div class="pb-4">
                        <div class="flex justify-between items-center gap-4">
                            <button type="button" 
                                    id="prevBtn"
                                    class="bg-gray-500 hover:bg-gray-600 text-white px-8 py-3 rounded-lg font-medium transition-colors"
                                    style="display:none;">
                                Previous
                            </button>
                            <div class="flex gap-4 ml-auto">
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
    </div>
</form>

@vite(['resources/js/assessment.js'])
@endsection