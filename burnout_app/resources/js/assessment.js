document.addEventListener('DOMContentLoaded', function() {
    // Handle "Others" option for gender, program, and year_level
    function setupOthersOption(selectId, inputId) {
        const select = document.getElementById(selectId);
        const input = document.getElementById(inputId);
        
        if (select && input) {
            select.addEventListener('change', function() {
                if (this.value === 'Others') {
                    input.classList.remove('hidden');
                    input.required = true;
                } else {
                    input.classList.add('hidden');
                    input.required = false;
                    input.value = '';
                }
            });
        }
    }
    
    setupOthersOption('gender', 'gender_other');
    setupOthersOption('program', 'program_other');
    setupOthersOption('year_level', 'year_level_other');
    
    // Two-step form logic
    const demographicStep = document.getElementById('demographicStep');
    const questionsStep = document.getElementById('questionsStep');
    const nextBtn = document.getElementById('nextBtn');
    if (nextBtn && demographicStep && questionsStep) {
        nextBtn.addEventListener('click', function() {
            // Validate demographic fields before proceeding
            const firstName = document.getElementById('first_name');
            const lastName = document.getElementById('last_name');
            const age = document.getElementById('age');
            const gender = document.getElementById('gender');
            const program = document.getElementById('program');
            const yearLevel = document.getElementById('year_level');
            const genderOther = document.getElementById('gender_other');
            const programOther = document.getElementById('program_other');
            const yearLevelOther = document.getElementById('year_level_other');
            
            let valid = true;
            
            // Validate gender
            if (!gender.value) {
                gender.classList.add('border-red-500');
                valid = false;
            } else {
                gender.classList.remove('border-red-500');
                if (gender.value === 'Others' && !genderOther.value.trim()) {
                    genderOther.classList.add('border-red-500');
                    valid = false;
                } else {
                    genderOther.classList.remove('border-red-500');
                }
            }
            
            // Validate program
            if (!program.value) {
                program.classList.add('border-red-500');
                valid = false;
            } else {
                program.classList.remove('border-red-500');
                if (program.value === 'Others' && !programOther.value.trim()) {
                    programOther.classList.add('border-red-500');
                    valid = false;
                } else {
                    programOther.classList.remove('border-red-500');
                }
            }
            
            // Validate year level
            if (!yearLevel.value) {
                yearLevel.classList.add('border-red-500');
                valid = false;
            } else {
                yearLevel.classList.remove('border-red-500');
                if (yearLevel.value === 'Others' && !yearLevelOther.value.trim()) {
                    yearLevelOther.classList.add('border-red-500');
                    valid = false;
                } else {
                    yearLevelOther.classList.remove('border-red-500');
                }
            }
            
            // Validate age
            if (!age.value) {
                age.classList.add('border-red-500');
                valid = false;
            } else {
                age.classList.remove('border-red-500');
            }
            
            if (valid) {
                demographicStep.style.display = 'none';
                questionsStep.style.display = 'block';
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    }

    // Pagination logic
    const totalQuestions = 30;
    const questionsPerSection = 3;
    const totalSections = 10;
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
            console.log('Last section reached. Answers count:', Object.keys(answers).length, 'Required:', totalQuestions, 'Complete:', allComplete);
            if (allComplete) {
                submitBtn.style.display = 'inline-block';
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                console.log('Submit button enabled');
            } else {
                submitBtn.style.display = 'none';
                console.log('Submit button hidden - not all questions answered');
            }
        } else {
            submitBtn.style.display = 'none';
        }
    }

    function checkSectionComplete(sectionNumber) {
        const startQuestion = (sectionNumber - 1) * 3;
        const endQuestion = Math.min(startQuestion + 2, totalQuestions - 1);
        
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
            const match = name.match(/answers\[(\d+)\]/);
            
            if (match && match[1] !== undefined) {
                const questionIndex = parseInt(match[1]);
                answers[questionIndex] = e.target.value;
                
                // Update styling for selected option
                const container = e.target.closest('.mb-8');
                if (container) {
                    container.querySelectorAll('.answer-option').forEach(option => {
                        option.classList.remove('border-indigo-500', 'bg-indigo-50');
                        option.classList.add('border-gray-200');
                    });
                    const selectedOption = e.target.closest('.answer-option');
                    if (selectedOption) {
                        selectedOption.classList.remove('border-gray-200');
                        selectedOption.classList.add('border-indigo-500', 'bg-indigo-50');
                    }
                }
                
                updateProgress();
                showSection(currentSection);
                autoAdvanceSection();
                
                // Debug: Log when all questions are answered
                if (Object.keys(answers).length === totalQuestions) {
                    console.log('All 30 questions answered! Submit button should be enabled.');
                }
            } else {
                console.error('Could not parse question index from name:', name);
            }
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
            document.querySelectorAll('.answer-radio').forEach(radio => {
                radio.checked = false;
            });
            
            answers = {};
            
            document.querySelectorAll('.answer-option').forEach(option => {
                option.classList.remove('border-indigo-500', 'bg-indigo-50');
                option.classList.add('border-gray-200');
            });
            
            updateProgress();
            currentSection = 1;
            showSection(1);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // Initialize
    updateProgress();
    showSection(1);

    // Submit button validation on click (don't interfere with natural form submission)
    const submitBtn = document.getElementById('submitBtn');
    if (submitBtn) {
        submitBtn.addEventListener('click', function(e) {
            console.log('Submit button clicked');
            console.log('Answers count:', Object.keys(answers).length);
            
            // Only validate, don't prevent default - let form submit naturally
            if (Object.keys(answers).length !== totalQuestions) {
                e.preventDefault();
                e.stopPropagation();
                alert('Please answer all 30 questions before submitting. You have answered ' + Object.keys(answers).length + ' out of 30 questions.');
                return false;
            }
            
            // If validation passes, let the button's type="submit" handle the submission
            console.log('Submit button validation passed - form will submit');
        });
    }

    // Form submit handler - validate and ensure all radio buttons are checked
    const form = document.getElementById('assessmentForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            console.log('Form submit event triggered');
            console.log('Answers object:', answers);
            console.log('Answers count:', Object.keys(answers).length);
            
            // Handle "Others" option values - replace "Others" with the text input value
            const gender = document.getElementById('gender');
            const program = document.getElementById('program');
            const yearLevel = document.getElementById('year_level');
            
            if (gender && gender.value === 'Others') {
                const genderOther = document.getElementById('gender_other');
                if (genderOther && genderOther.value.trim()) {
                    // Create a hidden input with the actual value
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'gender';
                    hiddenInput.value = genderOther.value.trim();
                    form.appendChild(hiddenInput);
                    gender.disabled = true; // Disable the select to prevent it from being submitted
                }
            }
            
            if (program && program.value === 'Others') {
                const programOther = document.getElementById('program_other');
                if (programOther && programOther.value.trim()) {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'program';
                    hiddenInput.value = programOther.value.trim();
                    form.appendChild(hiddenInput);
                    program.disabled = true;
                }
            }
            
            if (yearLevel && yearLevel.value === 'Others') {
                const yearLevelOther = document.getElementById('year_level_other');
                if (yearLevelOther && yearLevelOther.value.trim()) {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'year_level';
                    hiddenInput.value = yearLevelOther.value.trim();
                    form.appendChild(hiddenInput);
                    yearLevel.disabled = true;
                }
            }
            
            // Validate all questions are answered
            if (Object.keys(answers).length !== totalQuestions) {
                e.preventDefault();
                alert('Please answer all 30 questions before submitting. You have answered ' + Object.keys(answers).length + ' out of 30 questions.');
                return false;
            }
            
            // Ensure all radio buttons corresponding to answers are checked
            // This ensures the form data is properly submitted
            let allChecked = true;
            let missingAnswers = [];
            for (let i = 0; i < 30; i++) {
                if (answers[i] === undefined || answers[i] === null || answers[i] === '') {
                    missingAnswers.push(i + 1);
                } else {
                    // Ensure the corresponding radio button is checked
                    const radioName = 'answers[' + i + ']';
                    const radio = form.querySelector(`input[name="${radioName}"][value="${answers[i]}"]`);
                    if (radio) {
                        radio.checked = true;
                    } else {
                        console.error('Radio button not found for question', i + 1, 'with name', radioName, 'and value', answers[i]);
                        missingAnswers.push(i + 1);
                        allChecked = false;
                    }
                }
            }
            
            if (missingAnswers.length > 0) {
                e.preventDefault();
                alert('Please answer all 30 questions before submitting. Missing answers for questions: ' + missingAnswers.join(', ') + '.');
                return false;
            }
            
            if (!allChecked) {
                e.preventDefault();
                alert('Error: Some answers could not be found. Please refresh the page and try again.');
                return false;
            }
            
            console.log('Form submitting with', Object.keys(answers).length, 'answers');
            console.log('All validation passed - form will submit to server');
            // Don't prevent default - allow form to submit naturally
        });
    }
});

