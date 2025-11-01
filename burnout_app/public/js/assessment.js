document.addEventListener('DOMContentLoaded', function() {
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
            let valid = true;
            [age, gender, program, yearLevel].forEach(field => {
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
            showSection(currentSection);
            autoAdvanceSection();
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

    // Before submit, map answers[0-29] to Q1-Q30 hidden inputs
    document.getElementById('assessmentForm').addEventListener('submit', function(e) {
        if (Object.keys(answers).length !== totalQuestions) {
            e.preventDefault();
            alert('Please answer all questions before submitting.');
            return false;
        }
        
        document.querySelectorAll('.olbi-hidden-q').forEach(el => el.remove());
        
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
        
        return true;
    });
});

