/**
 * Intervention JavaScript Module
 * Handles intervention view, data loading, and email sending
 */

// Configuration from Blade
let config = {
    showRoute: '',
    sendRoute: '',
    csrfToken: ''
};

// State
let currentAssessmentId = null;
let currentAssessmentData = null;

/**
 * Initialize configuration from window object
 */
function initializeConfig() {
    if (window.interventionConfig) {
        config = {
            ...config,
            ...window.interventionConfig
        };
    }
}

/**
 * Open intervention view for a specific assessment
 */
function openInterventionModal(assessmentId) {
    currentAssessmentId = assessmentId;
    
    // Hide table view, show intervention view
    const tableView = document.getElementById('tableView');
    const interventionView = document.getElementById('interventionView');
    
    if (tableView) tableView.classList.add('hidden');
    if (interventionView) interventionView.classList.remove('hidden');
    
    // Fetch assessment data
    fetchAssessmentData(assessmentId);
}

/**
 * Close intervention view and return to table
 */
function closeInterventionModal() {
    // Hide intervention view, show table view
    const tableView = document.getElementById('tableView');
    const interventionView = document.getElementById('interventionView');
    
    if (tableView) tableView.classList.remove('hidden');
    if (interventionView) interventionView.classList.add('hidden');
    
    // Reset form
    resetInterventionForm();
    currentAssessmentId = null;
    currentAssessmentData = null;
}

/**
 * Show loading state in intervention view
 */
function showLoadingState() {
    // No loading animation needed
}

/**
 * Fetch assessment data from server
 */
function fetchAssessmentData(assessmentId) {
    const url = config.showRoute.replace(':id', assessmentId);
    
    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Failed to fetch assessment data');
        }
        return response.json();
    })
    .then(data => {
        currentAssessmentData = data;
        populateInterventionView(data);
    })
    .catch(error => {
        console.error('Error fetching assessment data:', error);
        showErrorState('Failed to load assessment data. Please try again.');
    });
}

/**
 * Show error state in intervention view
 */
function showErrorState(message) {
    const contentDiv = document.getElementById('interventionContent');
    if (contentDiv) {
        contentDiv.innerHTML = `
            <div class="flex items-center justify-center py-12">
                <div class="text-center">
                    <svg class="mx-auto h-12 w-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="mt-4 text-gray-600">${message}</p>
                    <button onclick="closeInterventionModal()" class="mt-4 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">
                        Back to Table
                    </button>
                </div>
            </div>
        `;
    }
}

/**
 * Populate intervention view with assessment data
 */
function populateInterventionView(data) {
    const contentDiv = document.getElementById('interventionContent');
    if (!contentDiv) return;
    
    // Determine category badge color
    let categoryColorClass = 'bg-green-100 text-green-800';
    if (data.category === 'High Burnout') {
        categoryColorClass = 'bg-red-100 text-red-800';
    } else if (data.category === 'Exhausted' || data.category === 'Disengaged') {
        categoryColorClass = 'bg-orange-100 text-orange-800';
    }
    
    // Format interpretations
    let interpretationsHtml = '';
    if (data.interpretations) {
        const interpretations = data.interpretations;
        
        // Exhaustion interpretation
        if (interpretations.top_card && interpretations.top_card.exhaustion) {
            const exhaustion = interpretations.top_card.exhaustion;
            interpretationsHtml += `
                <div class="mb-4">
                    <h4 class="font-semibold text-gray-800 mb-1">${exhaustion.title || 'Exhaustion'}</h4>
                    <p class="text-sm text-gray-700">${exhaustion.text || ''}</p>
                </div>
            `;
        }
        
        // Disengagement interpretation
        if (interpretations.top_card && interpretations.top_card.disengagement) {
            const disengagement = interpretations.top_card.disengagement;
            interpretationsHtml += `
                <div class="mb-4">
                    <h4 class="font-semibold text-gray-800 mb-1">${disengagement.title || 'Disengagement'}</h4>
                    <p class="text-sm text-gray-700">${disengagement.text || ''}</p>
                </div>
            `;
        }
        
        // Breakdown interpretations (Academic, Stress, Sleep)
        if (interpretations.breakdown) {
            if (interpretations.breakdown.academic) {
                const academic = interpretations.breakdown.academic;
                interpretationsHtml += `
                    <div class="mb-4">
                        <h4 class="font-semibold text-gray-800 mb-1">${academic.title || 'Academic Performance'}</h4>
                        <p class="text-sm text-gray-700">${academic.text || ''}</p>
                    </div>
                `;
            }
            
            if (interpretations.breakdown.stress) {
                const stress = interpretations.breakdown.stress;
                interpretationsHtml += `
                    <div class="mb-4">
                        <h4 class="font-semibold text-gray-800 mb-1">${stress.title || 'Stress Level'}</h4>
                        <p class="text-sm text-gray-700">${stress.text || ''}</p>
                    </div>
                `;
            }
            
            if (interpretations.breakdown.sleep) {
                const sleep = interpretations.breakdown.sleep;
                interpretationsHtml += `
                    <div class="mb-4">
                        <h4 class="font-semibold text-gray-800 mb-1">${sleep.title || 'Sleep Quality'}</h4>
                        <p class="text-sm text-gray-700">${sleep.text || ''}</p>
                    </div>
                `;
            }
        }
    }
    
    if (!interpretationsHtml) {
        interpretationsHtml = '<p class="text-gray-500 text-sm">No interpretations available</p>';
    }
    
    // Format recommendations
    let recommendationsHtml = '';
    if (data.recommendations) {
        const recommendations = data.recommendations;
        
        // Exhaustion recommendation
        if (recommendations.exhaustion) {
            recommendationsHtml += `
                <div class="mb-3">
                    <p class="text-sm text-gray-700">${recommendations.exhaustion}</p>
                </div>
            `;
        }
        
        // Disengagement recommendation
        if (recommendations.disengagement) {
            recommendationsHtml += `
                <div class="mb-3">
                    <p class="text-sm text-gray-700">${recommendations.disengagement}</p>
                </div>
            `;
        }
        
        // Combined recommendation
        if (recommendations.combined) {
            recommendationsHtml += `
                <div class="mb-3">
                    <p class="text-sm text-gray-700">${recommendations.combined}</p>
                </div>
            `;
        }
        
        // Breakdown recommendations (from interpretations.breakdown)
        if (data.interpretations && data.interpretations.breakdown) {
            if (data.interpretations.breakdown.academic && data.interpretations.breakdown.academic.recommendation) {
                recommendationsHtml += `
                    <div class="mb-3">
                        <p class="text-sm text-gray-700">${data.interpretations.breakdown.academic.recommendation}</p>
                    </div>
                `;
            }
            
            if (data.interpretations.breakdown.stress && data.interpretations.breakdown.stress.recommendation) {
                recommendationsHtml += `
                    <div class="mb-3">
                        <p class="text-sm text-gray-700">${data.interpretations.breakdown.stress.recommendation}</p>
                    </div>
                `;
            }
            
            if (data.interpretations.breakdown.sleep && data.interpretations.breakdown.sleep.recommendation) {
                recommendationsHtml += `
                    <div class="mb-3">
                        <p class="text-sm text-gray-700">${data.interpretations.breakdown.sleep.recommendation}</p>
                    </div>
                `;
            }
        }
    }
    
    if (!recommendationsHtml) {
        recommendationsHtml = '<p class="text-gray-500 text-sm">No recommendations available</p>';
    }
    
    contentDiv.innerHTML = `
        <!-- Header with Back Button -->
        <div class="flex justify-between items-center mb-6 pb-4 border-b border-gray-200">
            <h2 class="text-2xl font-semibold text-gray-800">Results</h2>
            <button onclick="closeInterventionModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                ← Back to Table
            </button>
        </div>
        
        <!-- Two Columns: Student Information | Assessment Results -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Left Column: Student Information -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Student Information</h3>
                <div class="space-y-3 text-sm">
                    <div>
                        <span class="text-gray-500">Name:</span>
                        <span class="ml-2 text-gray-800 font-medium">${data.name}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Email:</span>
                        <span class="ml-2 text-gray-800">${data.email || 'Not provided'}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Age:</span>
                        <span class="ml-2 text-gray-800">${data.age}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Gender:</span>
                        <span class="ml-2 text-gray-800">${data.gender}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Program:</span>
                        <span class="ml-2 text-gray-800">${data.program}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Year Level:</span>
                        <span class="ml-2 text-gray-800">${data.yearLevel}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Assessment ID:</span>
                        <span class="ml-2 text-gray-800">${data.id}</span>
                    </div>
                </div>
            </div>
            
            <!-- Right Column: Assessment Results -->
            <div class="bg-blue-50 rounded-lg p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Assessment Results</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Burnout Category:</span>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold ${categoryColorClass}">${data.category}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Exhaustion Level:</span>
                        <span class="text-gray-800 font-medium">${data.exhaustionLevel}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Disengagement Level:</span>
                        <span class="text-gray-800 font-medium">${data.disengagementLevel}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Academic Performance:</span>
                        <span class="text-gray-800 font-medium">${data.academicLevel || 'Unavailable'}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Stress Level:</span>
                        <span class="text-gray-800 font-medium">${data.stressLevel || 'Unavailable'}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Sleep Quality:</span>
                        <span class="text-gray-800 font-medium">${data.sleepLevel || 'Unavailable'}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Assessment Date:</span>
                        <span class="text-gray-800">${data.assessmentDate}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Interpretations Section -->
        <div class="bg-white rounded-lg p-4 border border-gray-200 mb-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Interpretation</h3>
            <div class="space-y-4">
                ${interpretationsHtml}
            </div>
        </div>
        
        <!-- Two Columns: Recommended Interventions | Send an email -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Left Column: Recommended Interventions -->
            <div class="bg-indigo-50 rounded-lg p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Recommended Interventions</h3>
                <div class="space-y-3">
                    ${recommendationsHtml}
                </div>
            </div>
            
            <!-- Right Column: Send an email form -->
            <div class="bg-white rounded-lg p-4 border border-gray-200">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Send an Email</h3>
                <form id="interventionForm" onsubmit="handleFormSubmit(event)">
                    <div class="space-y-4">
                        <!-- Email Address -->
                        <div>
                            <label for="studentEmail" class="block text-sm font-medium text-gray-700 mb-1">
                                Student Email Address <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="email" 
                                id="studentEmail" 
                                name="email" 
                                value="${data.email || ''}"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="student@example.com"
                            >
                        </div>
                        
                        <!-- Appointment Date/Time -->
                        <div>
                            <label for="appointmentDatetime" class="block text-sm font-medium text-gray-700 mb-1">
                                Appointment Date & Time
                            </label>
                            <input 
                                type="datetime-local" 
                                id="appointmentDatetime" 
                                name="appointment_datetime"
                                min="${new Date().toISOString().slice(0, 16)}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            >
                        </div>
                        
                        <!-- Additional Message -->
                        <div>
                            <label for="additionalMessage" class="block text-sm font-medium text-gray-700 mb-1">
                                Message
                            </label>
                            <textarea 
                                id="additionalMessage" 
                                name="additional_message"
                                rows="4"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="Add any personalized notes or instructions for the student..."
                            ></textarea>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex justify-end space-x-3 mt-6 pt-4 border-t border-gray-200">
                        <button 
                            type="button" 
                            onclick="closeInterventionModal()" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit" 
                            id="sendButton"
                            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Send
                        </button>
                    </div>
                </form>
            </div>
        </div>
    `;
}

/**
 * Handle form submission
 */
function handleFormSubmit(event) {
    event.preventDefault();
    
    if (!currentAssessmentId) {
        alert('Error: No assessment ID found');
        return;
    }
    
    const form = document.getElementById('interventionForm');
    const sendButton = document.getElementById('sendButton');
    
    // Disable button and show loading
    sendButton.disabled = true;
    sendButton.innerHTML = 'Sending...';
    
    // Get form data
    const formData = new FormData(form);
    formData.append('_token', config.csrfToken);
    
    const url = config.sendRoute.replace(':id', currentAssessmentId);
    
    // Send request
    fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(data => {
                throw new Error(data.message || 'Failed to send email');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert('Email sent successfully!');
            closeInterventionModal();
        } else {
            throw new Error(data.message || 'Failed to send email');
        }
    })
    .catch(error => {
        console.error('Error sending email:', error);
        alert('Failed to send email: ' + error.message);
        
        // Re-enable button
        sendButton.disabled = false;
        sendButton.innerHTML = 'Send Email';
    });
}

/**
 * Reset form
 */
function resetInterventionForm() {
    const form = document.getElementById('interventionForm');
    if (form) {
        form.reset();
    }
}

/**
 * Initialize the intervention module
 */
function initializeIntervention() {
    initializeConfig();
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeIntervention);
} else {
    initializeIntervention();
}

// Export functions to global scope
window.openInterventionModal = openInterventionModal;
window.closeInterventionModal = closeInterventionModal;
window.handleFormSubmit = handleFormSubmit;
