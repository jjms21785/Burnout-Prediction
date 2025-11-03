/**
 * Report JavaScript Module
 * Handles all assessment report table functionality including:
 * - Data loading and transformation
 * - Table rendering with inline editing
 * - Sorting, filtering, and pagination
 * - CRUD operations
 */

// Constants
const PROGRAMS = [
    'College of Business and Accountancy',
    'College of Computer Studies',
    'College of Education',
    'College of Engineering',
    'College of Hospitality Management',
    'College of Nursing',
    'College of Art and Science'
];

const CATEGORIES = ['High Burnout', 'Exhausted', 'Disengaged', 'Low Burnout'];
const YEAR_LEVELS = ['First', 'Second', 'Third', 'Fourth'];

// State management
let assessmentsData = [];
let currentPage = 1;
let entriesPerPage = 10;
let filteredData = [];
let editingId = null;
let currentCategoryFilter = 'all';
let currentSearchTerm = '';
let currentSortField = 'id';
let currentSortOrder = 'asc';

// Configuration (will be set from Blade template)
let config = {
    reportRoute: '',
    updateRoute: '',
    deleteRoute: '',
    csrfToken: ''
};

/**
 * Initialize configuration from window object
 */
function initializeConfig() {
    if (window.reportConfig) {
        config = {
            ...config,
            ...window.reportConfig
        };
    }
}

/**
 * Transform server data to UI format
 */
function transformAssessmentData(item) {
    const name = item.name || 'Unavailable';
    const nameParts = name.split(' ');
    const firstName = nameParts[0] || 'Unavailable';
    const lastName = nameParts.slice(1).join(' ') || 'Unavailable';
    
    // Map overall_risk to category
    let category = 'Unavailable';
    const risk = (item.risk || '').toLowerCase();
    if (risk === 'high') {
        category = 'High Burnout';
    } else if (risk === 'moderate') {
        // Determine if Exhausted or Disengaged based on scores
        const exhaustionScore = item.exhaustion_score ?? 0;
        const disengagementScore = item.disengagement_score ?? 0;
        // Threshold: 16 for high exhaustion/disengagement
        if (exhaustionScore >= 16 && disengagementScore < 16) {
            category = 'Exhausted';
        } else if (disengagementScore >= 16 && exhaustionScore < 16) {
            category = 'Disengaged';
        } else {
            // If both or neither meet threshold, default to Exhausted
            category = 'Exhausted';
        }
    } else if (risk === 'low') {
        category = 'Low Burnout';
    }
    
    return {
        id: String(item.id || 'Unavailable'),
        firstName: firstName,
        lastName: lastName,
        gender: item.gender || 'Unavailable',
        age: item.age || 'Unavailable',
        program: item.program || 'Unavailable',
        yearLevel: item.grade || 'Unavailable',
        category: category
    };
}

/**
 * Load assessments from server
 */
function loadAssessments() {
    fetch(config.reportRoute, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        assessmentsData = data.map(transformAssessmentData);
        filteredData = [...assessmentsData];
        renderTable();
    })
    .catch(error => {
        console.error('Error loading assessments:', error);
        assessmentsData = [];
        filteredData = [];
        renderTable();
    });
}

/**
 * Generate HTML for program dropdown (edit mode)
 */
function generateProgramDropdown(item) {
    const isCustom = !PROGRAMS.includes(item.program);
    const selectedProgram = isCustom ? '__custom__' : item.program;
    
    let html = `
        <select id="edit_program_${item.id}" onchange="toggleCustomProgram(${item.id})" class="w-full px-2 py-1 text-xs border border-indigo-300 rounded focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
    `;
    
    PROGRAMS.forEach(program => {
        html += `<option value="${program}" ${item.program === program ? 'selected' : ''}>${program}</option>`;
    });
    
    html += `<option value="__custom__" ${isCustom ? 'selected' : ''}>Other (Specify)</option>`;
    html += `</select>`;
    html += `
        <input type="text" id="edit_program_custom_${item.id}" value="${isCustom ? item.program : ''}" 
               placeholder="Enter program name" 
               class="w-full px-2 py-1 text-xs border border-indigo-300 rounded focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 mt-1 ${isCustom ? '' : 'hidden'}" />
    `;
    
    return html;
}

/**
 * Generate HTML for year level dropdown
 */
function generateYearLevelDropdown(item) {
    let html = `<select id="edit_yearLevel_${item.id}" class="w-full px-2 py-1 text-xs border border-indigo-300 rounded focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">`;
    YEAR_LEVELS.forEach(year => {
        html += `<option value="${year}" ${item.yearLevel === year ? 'selected' : ''}>${year}</option>`;
    });
    html += `</select>`;
    return html;
}

/**
 * Generate HTML for category dropdown
 */
function generateCategoryDropdown(item) {
    let html = `<select id="edit_category_${item.id}" class="w-full px-2 py-1 text-xs border border-indigo-300 rounded focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">`;
    CATEGORIES.forEach(category => {
        html += `<option value="${category}" ${item.category === category ? 'selected' : ''}>${category}</option>`;
    });
    html += `</select>`;
    return html;
}

/**
 * Render table row in edit mode
 */
function renderEditRow(item) {
    return `
        <tr class="border-b border-gray-200 bg-indigo-50">
            <td class="px-4 py-3 text-neutral-800">${item.id}</td>
            <td class="px-4 py-2">
                <input type="text" id="edit_firstName_${item.id}" value="${item.firstName}" 
                       class="w-full px-2 py-1 text-xs border border-indigo-300 rounded focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </td>
            <td class="px-4 py-2">
                <input type="text" id="edit_lastName_${item.id}" value="${item.lastName}" 
                       class="w-full px-2 py-1 text-xs border border-indigo-300 rounded focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </td>
            <td class="px-4 py-2">
                <select id="edit_gender_${item.id}" class="w-full px-2 py-1 text-xs border border-indigo-300 rounded focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="Male" ${item.gender === 'Male' ? 'selected' : ''}>Male</option>
                    <option value="Female" ${item.gender === 'Female' ? 'selected' : ''}>Female</option>
                </select>
            </td>
            <td class="px-4 py-2">
                <input type="number" id="edit_age_${item.id}" value="${item.age}" min="18" max="100" 
                       class="w-full px-2 py-1 text-xs border border-indigo-300 rounded focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </td>
            <td class="px-4 py-2">
                <div>${generateProgramDropdown(item)}</div>
            </td>
            <td class="px-4 py-2">
                ${generateYearLevelDropdown(item)}
            </td>
            <td class="px-4 py-2">
                ${generateCategoryDropdown(item)}
            </td>
            <td class="px-4 py-3 text-center">
                <div class="flex items-center justify-center space-x-1">
                    <button onclick="saveEdit('${item.id}')" class="text-xs font-medium px-2 py-1 rounded transition text-white bg-indigo-500 hover:bg-indigo-600">Save</button>
                    <button onclick="cancelEdit()" class="text-xs font-medium px-2 py-1 rounded transition text-neutral-800 bg-gray-200 hover:bg-gray-300">Cancel</button>
                </div>
            </td>
        </tr>
    `;
}

/**
 * Render table row in view mode
 */
function renderViewRow(item) {
    return `
        <tr class="border-b border-gray-200 hover:bg-gray-50 transition">
            <td class="px-4 py-3 text-neutral-800">${item.id || 'Unavailable'}</td>
            <td class="px-4 py-3 text-neutral-800">${item.firstName || 'Unavailable'}</td>
            <td class="px-4 py-3 text-neutral-800">${item.lastName || 'Unavailable'}</td>
            <td class="px-4 py-3 text-neutral-800">${item.gender || 'Unavailable'}</td>
            <td class="px-4 py-3 text-neutral-800">${item.age || 'Unavailable'}</td>
            <td class="px-4 py-3 text-neutral-800">${item.program || 'Unavailable'}</td>
            <td class="px-4 py-3 text-neutral-800">${item.yearLevel || 'Unavailable'}</td>
            <td class="px-4 py-3 text-neutral-800">${item.category || 'Unavailable'}</td>
            <td class="px-4 py-3 text-center">
                <div class="flex items-center justify-center space-x-1">
                    <button onclick="startEdit('${item.id}')" class="text-xs font-medium px-2 py-1 rounded transition text-neutral-800 bg-gray-100 hover:bg-gray-200">Edit</button>
                    <button onclick="deleteAssessment('${item.id}')" class="text-xs font-medium px-2 py-1 rounded transition text-white bg-red-500 hover:bg-red-600">Delete</button>
                </div>
            </td>
        </tr>
    `;
}

/**
 * Render the entire table
 */
function renderTable() {
    const start = (currentPage - 1) * entriesPerPage;
    const end = start + entriesPerPage;
    const pageData = filteredData.slice(start, end);
    
    const tbody = document.getElementById('tableBody');
    
    if (pageData.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                    Unavailable
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = pageData.map(item => {
        const isEditing = String(editingId) === String(item.id);
        return isEditing ? renderEditRow(item) : renderViewRow(item);
    }).join('');
    
    updatePagination();
}

/**
 * Update pagination controls
 */
function updatePagination() {
    const totalPages = Math.ceil(filteredData.length / entriesPerPage);
    
    // Disable/enable buttons
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    if (prevBtn) prevBtn.disabled = currentPage === 1;
    if (nextBtn) nextBtn.disabled = currentPage === totalPages;
}

/**
 * Change page
 */
function changePage(direction) {
    const totalPages = Math.ceil(filteredData.length / entriesPerPage);
    if (direction === 'prev' && currentPage > 1) {
        currentPage--;
    } else if (direction === 'next' && currentPage < totalPages) {
        currentPage++;
    }
    renderTable();
}

/**
 * Toggle sort by dropdown
 */
function toggleSortByDropdown() {
    const dropdown = document.getElementById('sortByDropdown');
    if (dropdown) dropdown.classList.toggle('hidden');
}

/**
 * Set sort order and re-sort
 */
function setSortOrder(order) {
    currentSortOrder = order;
    
    // Update button styles
    const ascBtn = document.getElementById('sortAscBtn');
    const descBtn = document.getElementById('sortDescBtn');
    
    if (ascBtn && descBtn) {
        if (order === 'asc') {
            ascBtn.className = 'flex items-center justify-center w-9 h-9 rounded-lg transition border border-gray-200 bg-indigo-500 text-white hover:bg-indigo-600';
            descBtn.className = 'flex items-center justify-center w-9 h-9 rounded-lg transition border border-gray-200 bg-white text-neutral-800 hover:bg-gray-50';
        } else {
            descBtn.className = 'flex items-center justify-center w-9 h-9 rounded-lg transition border border-gray-200 bg-indigo-500 text-white hover:bg-indigo-600';
            ascBtn.className = 'flex items-center justify-center w-9 h-9 rounded-lg transition border border-gray-200 bg-white text-neutral-800 hover:bg-gray-50';
        }
    }
    
    // Re-sort if a field is selected
    if (currentSortField) {
        sortBy(currentSortField);
    }
}

/**
 * Sort data by field
 */
function sortBy(field) {
    currentSortField = field;
    
    filteredData.sort((a, b) => {
        let aVal = a[field];
        let bVal = b[field];
        
        // Handle numeric sorting for id and age
        if (field === 'id' || field === 'age') {
            aVal = parseInt(aVal);
            bVal = parseInt(bVal);
        } else {
            // Convert to lowercase for string comparison
            aVal = String(aVal).toLowerCase();
            bVal = String(bVal).toLowerCase();
        }
        
        if (currentSortOrder === 'asc') {
            if (field === 'id' || field === 'age') return aVal - bVal;
            return aVal > bVal ? 1 : aVal < bVal ? -1 : 0;
        } else {
            if (field === 'id' || field === 'age') return bVal - aVal;
            return bVal > aVal ? 1 : bVal < aVal ? -1 : 0;
        }
    });
    
    renderTable();
    toggleSortByDropdown();
}

/**
 * Toggle category dropdown
 */
function toggleCategoryDropdown() {
    const dropdown = document.getElementById('categoryDropdown');
    if (dropdown) dropdown.classList.toggle('hidden');
}

/**
 * Filter by category
 */
function filterByCategory(category) {
    currentCategoryFilter = category;
    
    // Update button text
    const btnText = document.getElementById('categoryBtnText');
    if (btnText) {
        btnText.textContent = category === 'all' ? 'Category' : category;
    }
    
    // Apply filter
    applyFilters();
    
    // Close dropdown
    toggleCategoryDropdown();
}

/**
 * Apply all filters (search and category)
 */
function applyFilters() {
    filteredData = assessmentsData.filter(item => {
        // Apply search filter
        const searchMatch = currentSearchTerm === '' || 
            item.firstName.toLowerCase().includes(currentSearchTerm) ||
            item.lastName.toLowerCase().includes(currentSearchTerm) ||
            item.program.toLowerCase().includes(currentSearchTerm);
        
        // Apply category filter
        const categoryMatch = currentCategoryFilter === 'all' || 
            item.category === currentCategoryFilter;
        
        return searchMatch && categoryMatch;
    });
    
    currentPage = 1;
    renderTable();
}


/**
 * Delete assessment
 */
function deleteAssessment(id) {
    if (confirm('Are you sure you want to delete this assessment? This action cannot be undone.')) {
        const formData = new FormData();
        formData.append('_token', config.csrfToken);
        
        fetch(config.deleteRoute.replace(':id', id), {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove from local data array
                const index = assessmentsData.findIndex(item => String(item.id) === String(id));
                if (index !== -1) {
                    assessmentsData.splice(index, 1);
                }
                
                // Remove from filtered data
                const filteredIndex = filteredData.findIndex(item => String(item.id) === String(id));
                if (filteredIndex !== -1) {
                    filteredData.splice(filteredIndex, 1);
                }
                
                // Re-render table
                renderTable();
                alert('Assessment deleted successfully');
            } else {
                alert('Failed to delete assessment: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Delete error:', error);
            // Fallback: remove from local data even if API fails
            const index = assessmentsData.findIndex(item => String(item.id) === String(id));
            if (index !== -1) {
                assessmentsData.splice(index, 1);
            }
            const filteredIndex = filteredData.findIndex(item => String(item.id) === String(id));
            if (filteredIndex !== -1) {
                filteredData.splice(filteredIndex, 1);
            }
            renderTable();
            alert('Assessment removed from view (API call failed, but removed locally)');
        });
    }
}

/**
 * Start editing a row
 */
function startEdit(id) {
    editingId = String(id);
    renderTable();
}

/**
 * Cancel editing
 */
function cancelEdit() {
    editingId = null;
    renderTable();
}

/**
 * Toggle custom program input visibility
 */
function toggleCustomProgram(id) {
    const select = document.getElementById(`edit_program_${id}`);
    const customInput = document.getElementById(`edit_program_custom_${id}`);
    
    if (select && customInput) {
        if (select.value === '__custom__') {
            customInput.classList.remove('hidden');
            customInput.focus();
        } else {
            customInput.classList.add('hidden');
            customInput.value = '';
        }
    }
}

/**
 * Map category to overall_risk for database
 */
function categoryToRisk(category) {
    if (category === 'High Burnout') return 'high';
    if (category === 'Exhausted' || category === 'Disengaged') return 'moderate';
    if (category === 'Low Burnout') return 'low';
    return null;
}

/**
 * Save edited assessment
 */
function saveEdit(id) {
    // Get values from input fields
    const firstNameEl = document.getElementById(`edit_firstName_${id}`);
    const lastNameEl = document.getElementById(`edit_lastName_${id}`);
    const genderEl = document.getElementById(`edit_gender_${id}`);
    const ageEl = document.getElementById(`edit_age_${id}`);
    const programSelectEl = document.getElementById(`edit_program_${id}`);
    const programCustomEl = document.getElementById(`edit_program_custom_${id}`);
    const yearLevelEl = document.getElementById(`edit_yearLevel_${id}`);
    const categoryEl = document.getElementById(`edit_category_${id}`);
    
    if (!firstNameEl || !lastNameEl || !genderEl || !ageEl || !programSelectEl || !yearLevelEl || !categoryEl) {
        alert('Error: Could not find form fields');
        return;
    }
    
    const firstName = firstNameEl.value.trim();
    const lastName = lastNameEl.value.trim();
    const gender = genderEl.value;
    const age = parseInt(ageEl.value);
    
    // Handle program - check if custom or predefined
    let program;
    const programSelect = programSelectEl.value;
    if (programSelect === '__custom__') {
        program = programCustomEl ? programCustomEl.value.trim() : '';
        if (!program) {
            alert('Please enter a program name');
            return;
        }
    } else {
        program = programSelect;
    }
    
    const yearLevel = yearLevelEl.value;
    const category = categoryEl.value;
    const overallRisk = categoryToRisk(category);
    
    // Make AJAX call to update the database
    const formData = new FormData();
    formData.append('_token', config.csrfToken);
    formData.append('name', firstName + ' ' + lastName);
    formData.append('gender', gender);
    formData.append('age', age);
    formData.append('program', program);
    formData.append('year_level', yearLevel);
    formData.append('overall_risk', overallRisk);
    
    fetch(config.updateRoute.replace(':id', id), {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        // Update local data array
        const index = assessmentsData.findIndex(item => String(item.id) === String(id));
        if (index !== -1) {
            assessmentsData[index] = {
                id: id,
                firstName: firstName || 'Unavailable',
                lastName: lastName || 'Unavailable',
                gender: gender || 'Unavailable',
                age: age || 'Unavailable',
                program: program || 'Unavailable',
                yearLevel: yearLevel || 'Unavailable',
                category: category || 'Unavailable'
            };
            
            // Update filtered data as well
            const filteredIndex = filteredData.findIndex(item => String(item.id) === String(id));
            if (filteredIndex !== -1) {
                filteredData[filteredIndex] = {...assessmentsData[index]};
            }
            
            // Reset editing state
            editingId = null;
            
            // Re-render table
            renderTable();
            
            if (data.success) {
                alert('Assessment updated successfully');
            }
        }
    })
    .catch(error => {
        console.error('Update error:', error);
        // Fallback: update local data even if API fails
        const index = assessmentsData.findIndex(item => String(item.id) === String(id));
        if (index !== -1) {
            assessmentsData[index] = {
                id: id,
                firstName: firstName || 'Unavailable',
                lastName: lastName || 'Unavailable',
                gender: gender || 'Unavailable',
                age: age || 'Unavailable',
                program: program || 'Unavailable',
                yearLevel: yearLevel || 'Unavailable',
                category: category || 'Unavailable'
            };
            const filteredIndex = filteredData.findIndex(item => String(item.id) === String(id));
            if (filteredIndex !== -1) {
                filteredData[filteredIndex] = {...assessmentsData[index]};
            }
            editingId = null;
            renderTable();
            alert('Assessment updated in view (API call failed, but updated locally)');
        }
    });
}

/**
 * Initialize event listeners
 */
function initializeEventListeners() {
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        const sortByDropdown = document.getElementById('sortByDropdown');
        const sortByButton = document.getElementById('sortByBtn');
        const categoryDropdown = document.getElementById('categoryDropdown');
        const categoryButton = document.getElementById('categoryBtn');
        
        if (sortByDropdown && sortByButton && !sortByDropdown.contains(event.target) && !sortByButton.contains(event.target)) {
            sortByDropdown.classList.add('hidden');
        }
        
        if (categoryDropdown && categoryButton && !categoryDropdown.contains(event.target) && !categoryButton.contains(event.target)) {
            categoryDropdown.classList.add('hidden');
        }
    });
    
    // Search input event listener
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            currentSearchTerm = e.target.value.toLowerCase();
            applyFilters();
        });
    }
}

/**
 * Initialize the report module
 */
function initializeReport() {
    initializeConfig();
    initializeEventListeners();
    loadAssessments();
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeReport);
} else {
    initializeReport();
}

// Export functions to global scope for onclick handlers
window.deleteAssessment = deleteAssessment;
window.startEdit = startEdit;
window.cancelEdit = cancelEdit;
window.toggleCustomProgram = toggleCustomProgram;
window.saveEdit = saveEdit;
window.changePage = changePage;
window.toggleSortByDropdown = toggleSortByDropdown;
window.setSortOrder = setSortOrder;
window.sortBy = sortBy;
window.toggleCategoryDropdown = toggleCategoryDropdown;
window.filterByCategory = filterByCategory;

