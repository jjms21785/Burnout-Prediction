@extends('layouts.app')

@section('title', 'Questions - Burnalytics')

@section('content')
<!-- Main Content Area -->
<main class="flex-1 overflow-y-auto p-3">
    @if(session('success'))
            <div class="mb-3 rounded-lg p-3 bg-green-100 border border-green-200">
                <p class="text-xs font-medium text-green-800">{{ session('success') }}</p>
            </div>
            @endif

            @if(session('error'))
            <div class="mb-3 rounded-lg p-3 bg-red-100 border border-red-200">
                <p class="text-xs font-medium text-red-800">{{ session('error') }}</p>
            </div>
            @endif

            <form id="questionsForm" method="POST" action="{{ route('admin.questions.update') }}">
                @csrf
                
                <!-- All Questions (Arranged Numerically) -->
                <div class="rounded-xl shadow-sm p-4 mb-4 bg-white border border-gray-200">
                    <div class="flex items-center justify-between mb-3 pb-3 border-b border-gray-200">
                        <!-- Description -->
                        <p class="text-xs font-bold text-gray-500 flex-1 mr-4">
                            Manage and edit all assessment questions. Click on any question text to modify it.
                        </p>
                        <!-- Buttons -->
                        <div class="flex items-center space-x-2">
                            <button type="button" id="cancelBtn" onclick="cancelChanges()" style="display: none;" class="px-3 py-1.5 text-xs font-medium rounded-lg transition text-neutral-800 bg-gray-100 border border-gray-200 hover:bg-gray-200">
                                Cancel
                            </button>
                            <button type="button" onclick="resetForm()" class="px-3 py-1.5 text-xs font-medium rounded-lg transition text-neutral-800 bg-gray-100 border border-gray-200 hover:bg-gray-200">
                                Reset
                            </button>
                            <button type="submit" form="questionsForm" class="px-3 py-1.5 text-xs font-medium rounded-lg transition text-white bg-indigo-500 hover:bg-indigo-600">
                                Save
                            </button>
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        @php
                            // Ensure questions are sorted by their numeric ID (Q1-Q30)
                            $allQuestions = $questions;
                            usort($allQuestions, function($a, $b) {
                                $aNum = intval(preg_replace('/[^0-9]/', '', $a['id']));
                                $bNum = intval(preg_replace('/[^0-9]/', '', $b['id']));
                                return $aNum - $bNum;
                            });
                        @endphp
                        
                        @foreach($allQuestions as $index => $question)
                        <div class="flex items-start space-x-2">
                            <div class="flex-shrink-0 pt-1.5">
                                <span class="text-xs font-semibold text-gray-600">
                                    {{ preg_replace('/[^0-9]/', '', $question['id']) }}.
                                </span>
                            </div>
                            <div class="flex-1">
                                <textarea 
                                    name="questions[{{ $index }}][text]"
                                    rows="1"
                                    class="question-textarea w-full px-2 py-1.5 text-xs rounded-lg border border-gray-200 bg-white text-neutral-800 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    data-original="{{ $question['text'] }}"
                                >{{ $question['text'] }}</textarea>
                                <input type="hidden" name="questions[{{ $index }}][id]" value="{{ $question['id'] }}">
                                <input type="hidden" name="questions[{{ $index }}][type]" value="{{ $question['type'] ?? 'neutral' }}">
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </form>
</main>

<script>
let isEditing = false;

// Detect when user starts editing
document.addEventListener('DOMContentLoaded', function() {
    const textareas = document.querySelectorAll('.question-textarea');
    const cancelBtn = document.getElementById('cancelBtn');
    
    textareas.forEach(textarea => {
        textarea.addEventListener('input', function() {
            if (!isEditing) {
                isEditing = true;
                cancelBtn.style.display = 'inline-block';
            }
        });
    });
});

function resetForm() {
    if (confirm('Reset all changes to default values?')) {
        window.location.reload();
    }
}

function cancelChanges() {
    if (confirm('Are you sure you want to cancel? All unsaved changes will be lost.')) {
        window.location.reload();
    }
}

// Form submission is handled by Laravel automatically
// No JavaScript intervention needed
</script>
@endsection

