/**
 * View JavaScript Module
 * Handles viewing assessment details, data loading, and email sending
 */

// Configuration from Blade template
let config = {
    showRoute: '',
    sendEmailRoute: '',
    csrfToken: ''
};

// State management
let currentAssessmentId = null;
let currentAssessmentData = null;

/**
 * Initialize configuration from window object
 * Gets view configuration from records.blade.php
 */
function initializeConfig() {
    if (window.viewConfig) {
        config = {
            ...config,
            ...window.viewConfig
        };
    }
}

/**
 * Open view modal for a specific assessment
 * Shows detailed assessment information in a modal view
 * 
 * @param {string} assessmentId - The ID of the assessment to view
 */
function openViewModal(assessmentId) {
    currentAssessmentId = assessmentId;
    
    // Hide table view, show view container
    const tableView = document.getElementById('tableView');
    const viewContainer = document.getElementById('viewContainer');
    
    if (tableView) tableView.classList.add('hidden');
    if (viewContainer) viewContainer.classList.remove('hidden');
    
    // Fetch assessment data
    fetchAssessmentData(assessmentId);
}

/**
 * Close view modal and return to table
 * Hides the detailed view and shows the records table again
 */
function closeViewModal() {
    // Hide view container, show table view
    const tableView = document.getElementById('tableView');
    const viewContainer = document.getElementById('viewContainer');
    
    if (tableView) tableView.classList.remove('hidden');
    if (viewContainer) viewContainer.classList.add('hidden');
    
    // Reset form
    resetViewForm();
    currentAssessmentId = null;
    currentAssessmentData = null;
}

/**
 * Show loading state in view container
 */
function showLoadingState() {
    // No loading animation needed
}

/**
 * Fetch assessment data from server
 * Retrieves detailed assessment information via API
 * 
 * @param {string} assessmentId - The ID of the assessment to fetch
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
        populateViewContainer(data);
    })
    .catch(error => {
        console.error('Error fetching assessment data:', error);
        showErrorState('Failed to load assessment data. Please try again.');
    });
}

/**
 * Show error state in view container
 * Displays error message when data fetch fails
 * 
 * @param {string} message - Error message to display
 */
function showErrorState(message) {
    const contentDiv = document.getElementById('viewContent');
    if (contentDiv) {
        contentDiv.innerHTML = `
            <div class="flex items-center justify-center py-12">
                <div class="text-center">
                    <svg class="mx-auto h-12 w-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="mt-4 text-gray-600">${message}</p>
                    <button onclick="closeViewModal()" class="mt-4 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">
                        Back to Table
                    </button>
                </div>
            </div>
        `;
    }
}

/**
 * Populate view container with assessment data
 * Renders detailed assessment information including scores, levels, and recommendations
 * 
 * @param {object} data - Assessment data object containing all assessment details
 */
function populateViewContainer(data) {
    const contentDiv = document.getElementById('viewContent');
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
            <button onclick="closeViewModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
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
            <div class="bg-white rounded-lg p-4 border border-gray-200 flex flex-col h-full">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Send an Email</h3>
                <form id="viewForm" onsubmit="handleFormSubmit(event)" class="flex flex-col flex-1">
                    <div class="space-y-4 flex-1">
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
                        
                        <!-- Message Field (Always Shown) -->
                        <div>
                            <label for="additionalMessage" class="block text-sm font-medium text-gray-700 mb-1">
                                Message <span class="text-red-500">*</span>
                            </label>
                            <textarea 
                                id="additionalMessage" 
                                name="additional_message"
                                rows="4"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="Add any personalized notes or instructions for the student..."
                            ></textarea>
                        </div>
                        
                        <!-- Schedule Appointment Checkbox -->
                        <div>
                            <label class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    id="scheduleAppointmentCheckbox"
                                    name="send_options[]"
                                    value="appointment"
                                    class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                    onchange="toggleAppointmentField()"
                                >
                                <span class="ml-2 text-sm text-gray-700">Schedule an appointment?</span>
                            </label>
                        </div>
                        
                        <!-- Appointment Date/Time Field (Conditional) -->
                        <div id="appointmentFieldContainer" class="hidden">
                            <label for="appointmentDatetime" class="block text-sm font-medium text-gray-700 mb-1">
                                Appointment Date & Time <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="datetime-local" 
                                id="appointmentDatetime" 
                                name="appointment_datetime"
                                min="${new Date().toISOString().slice(0, 16)}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            >
                        </div>
                    </div>
                    
                    <!-- Action Buttons - Fixed to Bottom -->
                    <div class="flex justify-end space-x-3 mt-6 pt-4 border-t border-gray-200">
                        <button 
                            type="button" 
                            onclick="closeViewModal()" 
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
 * Toggle appointment field visibility based on checkbox
 */
function toggleAppointmentField() {
    const checkbox = document.getElementById('scheduleAppointmentCheckbox');
    const container = document.getElementById('appointmentFieldContainer');
    const input = document.getElementById('appointmentDatetime');
    
    if (checkbox && container && input) {
        if (checkbox.checked) {
            container.classList.remove('hidden');
            input.required = true;
        } else {
            container.classList.add('hidden');
            input.required = false;
            input.value = '';
        }
    }
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
    
    const form = document.getElementById('viewForm');
    const sendButton = document.getElementById('sendButton');
    
    // Validate message field (always required)
    const message = document.getElementById('additionalMessage');
    if (!message.value.trim()) {
        alert('Please enter a message');
        message.focus();
        return;
    }
    
    // Validate appointment checkbox
    const scheduleAppointmentCheckbox = document.getElementById('scheduleAppointmentCheckbox');
    
    if (scheduleAppointmentCheckbox.checked) {
        const appointment = document.getElementById('appointmentDatetime');
        if (!appointment.value) {
            alert('Please select an appointment date and time');
            appointment.focus();
            return;
        }
    }
    
    // Disable button and show loading
    sendButton.disabled = true;
    sendButton.innerHTML = 'Sending...';
    
    // Get form data
    const formData = new FormData(form);
    
    // Message is always sent, appointment is optional
    formData.append('send_message', '1');
    if (scheduleAppointmentCheckbox.checked) {
        formData.append('send_appointment', '1');
    }
    
    formData.append('_token', config.csrfToken);
    
    const url = config.sendEmailRoute.replace(':id', currentAssessmentId);
    
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
            closeViewModal();
        } else {
            throw new Error(data.message || 'Failed to send email');
        }
    })
    .catch(error => {
        console.error('Error sending email:', error);
        alert('Failed to send email: ' + error.message);
        
        // Re-enable button
        sendButton.disabled = false;
        sendButton.innerHTML = 'Send';
    });
}

/**
 * Reset view form
 * Clears all form fields after submission
 */
function resetViewForm() {
    const form = document.getElementById('viewForm');
    if (form) {
        form.reset();
        // Hide appointment field
        const appointmentContainer = document.getElementById('appointmentFieldContainer');
        if (appointmentContainer) appointmentContainer.classList.add('hidden');
        
        // Reset appointment required attribute
        const appointment = document.getElementById('appointmentDatetime');
        if (appointment) appointment.required = false;
        
        // Message field is always shown and required, no need to reset
    }
}

/**
 * Initialize the view module
 * Sets up configuration and event listeners when page loads
 */
function initializeView() {
    initializeConfig();
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeView);
} else {
    initializeView();
}

// Export functions to global scope for onclick handlers
window.openViewModal = openViewModal;
window.closeViewModal = closeViewModal;
window.handleFormSubmit = handleFormSubmit;
window.toggleAppointmentField = toggleAppointmentField;
