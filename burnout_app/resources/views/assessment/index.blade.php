@extends('layouts.app')

@section('title', 'OLBI-S Assessment Survey - Burnalytix')
@section('subtitle', 'Assessment Survey')

@section('content')
<!-- OLBI-S Info Modal -->
<div id="olbiModal" class="fixed inset-0 z-50 flex items-center justify-center backdrop-blur-sm" style="background: transparent; display: flex;">
    <div class="bg-white rounded-lg shadow-lg w-[90vw] max-w-5xl h-auto p-8 relative flex flex-col justify-center items-center" style="min-width:600px;">
        <h2 class="text-2xl font-bold text-green-700 mb-4">About the OLBI-S Assessment</h2>
        <p class="mb-2 text-gray-700">
            The Oldenburg Burnout Inventory – Student Version (OLBI-S) is a scientifically validated tool used to assess academic burnout among students. It focuses on two key dimensions of burnout:
        </p>
        <ul class="list-disc pl-6 mb-2 text-gray-700">
            <b>Exhaustion:</b> emotional, cognitive, and physical fatigue caused by study demands.<br>
            <b>Disengagement:</b> withdrawal and detachment from academic work and lack of motivation.
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
        <button id="continueBtn" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-medium w-40 mt-2">Continue</button>
    </div>
</div>

<!-- Assessment Header -->
<div class="bg-green-600 text-white py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-3xl font-bold mb-4">OLBI-S Assessment Survey</h1>
        <p class="text-green-100">
            Please answer all 16 questions honestly based on your recent experiences with studies. Your responses will help assess your current burnout risk level.
        </p>
    </div>
</div>

<!-- Assessment Form -->
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white border border-gray-200 rounded-lg shadow-lg">
        <form action="{{ route('assessment.result') }}" method="POST" id="assessmentForm">
            @csrf
            <div id="demographicStep" class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-gray-700 font-medium mb-2">Name</label>
                        <input type="text" name="name" id="name" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-200" maxlength="255" pattern="^[A-Za-z ]*$">
                        @error('name')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label for="age" class="block text-gray-700 font-medium mb-2">Age</label>
                        <input type="number" name="age" id="age" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-200" min="10" max="100" required>
                        @error('age')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label for="gender" class="block text-gray-700 font-medium mb-2">Gender</label>
                        <select name="gender" id="gender" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-200" required>
                            <option value="" disabled selected>Select gender</option>
                            @foreach($genders as $gender)
                                <option value="{{ $gender }}">{{ $gender }}</option>
                            @endforeach
                        </select>
                        @error('gender')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label for="program" class="block text-gray-700 font-medium mb-2">Program</label>
                        <select name="program" id="program" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-200" required>
                            <option value="" disabled selected>Select program</option>
                            @foreach($programs as $program)
                                <option value="{{ $program }}">{{ $program }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="program_other" id="program_other" class="w-full border border-gray-300 rounded-lg px-4 py-2 mt-2 focus:outline-none focus:ring-2 focus:ring-green-200" placeholder="Please specify program" style="display:none;">
                        @error('program')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label for="year_level" class="block text-gray-700 font-medium mb-2">Year Level</label>
                        <select name="year_level" id="year_level" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-200" required>
                            <option value="" disabled selected>Select year level</option>
                            @foreach($year_levels as $level)
                                <option value="{{ $level }}">{{ $level }}</option>
                            @endforeach
                        </select>
                        @error('year_level')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="text-center pt-8">
                    <button type="button" id="nextBtn" class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg font-medium w-40 transition-colors">Next</button>
                </div>
            </div>

            <div id="questionsStep" style="display:none;">
                <div class="border-b p-6">
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <p class="text-green-600 font-medium">
                                Progress: <span id="progressText">0/16</span> questions completed
                            </p>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div id="progressBar" class="bg-green-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                        </div>
                    </div>
                </div>
                <div class="m-4">
                    @php
                        $olbi_options = [
                            ['value' => 1, 'label' => 'Strongly Agree'],
                            ['value' => 2, 'label' => 'Agree'],
                            ['value' => 3, 'label' => 'Disagree'],
                            ['value' => 4, 'label' => 'Strongly Disagree'],
                        ];
                    @endphp
                    @foreach($olbi_questions as $index => $question)
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ $index + 1 }}. {{ $question }}</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                            @foreach($olbi_options as $option)
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
                    @endforeach
                    <div class="text-center pt-8">
                        <button type="submit" 
                                id="submitBtn"
                                class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg font-medium transition-colors opacity-50 cursor-not-allowed"
                                disabled>
                            Submit
                        </button>
                        <p class="text-gray-500 text-sm mt-2">Please answer all 16 questions to submit the survey</p>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

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

    // Progress logic
    const totalQuestions = 16;
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

    // Handle radio button changes
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('answer-radio')) {
            const name = e.target.name;
            const questionIndex = parseInt(name.match(/answers\[(\d+)\]/)[1]);
            answers[questionIndex] = e.target.value;
            // Update styling for selected option
            const container = e.target.closest('.mb-8');
            container.querySelectorAll('.answer-option').forEach(option => {
                option.classList.remove('border-green-500', 'bg-green-50');
                option.classList.add('border-gray-200');
            });
            e.target.closest('.answer-option').classList.remove('border-gray-200');
            e.target.closest('.answer-option').classList.add('border-green-500', 'bg-green-50');
            updateProgress();
        }
    });

    // Initialize
    updateProgress();

    // Before submit, map answers[0-15] to Q1-Q16 hidden inputs
    document.getElementById('assessmentForm').addEventListener('submit', function(e) {
        // Remove any previous hidden inputs
        document.querySelectorAll('.olbi-hidden-q').forEach(el => el.remove());
        for (let i = 0; i < 16; i++) {
            const val = answers[i];
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'Q' + (i+1);
            input.value = val;
            input.className = 'olbi-hidden-q';
            this.appendChild(input);
        }
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