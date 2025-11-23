/**
 * Records JavaScript Module
 * Handles all assessment records table functionality including:
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
let currentDateFrom = '';
let currentDateTo = '';

// Configuration (will be set from Blade template)
let config = {
    recordsRoute: '',
    updateRoute: '',
    csrfToken: ''
};

/**
 * Initialize configuration from window object
 */
function initializeConfig() {
    if (window.recordsConfig) {
        config = {
            ...config,
            ...window.recordsConfig
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
    
    // Map risk (Burnout_Category) to category - matches ML model prediction
    let category = 'Unavailable';
    const risk = item.risk;
    
    // Handle numeric risk values (0, 1, 2, 3) from ML model
    // ML model: 0="Non-Burnout", 1="Exhausted", 2="Disengaged", 3="BURNOUT"
    if (risk === '0' || risk === 0) {
        category = 'Low Burnout';  // ML: "Non-Burnout"
    } else if (risk === '1' || risk === 1) {
        category = 'Exhausted';    // ML: "Exhausted" (matches ML model)
    } else if (risk === '2' || risk === 2) {
        category = 'Disengaged';   // ML: "Disengaged" (matches ML model)
    } else if (risk === '3' || risk === 3) {
        category = 'High Burnout'; // ML: "BURNOUT"
    } else {
        // Fallback for string values or unknown
        const riskLower = String(risk || '').toLowerCase();
        if (riskLower === 'high' || riskLower === '3') {
            category = 'High Burnout';
        } else if (riskLower === 'low' || riskLower === '0') {
            category = 'Low Burnout';
        } else if (riskLower === '1') {
            category = 'Exhausted';    // ML: "Exhausted"
        } else if (riskLower === '2') {
            category = 'Disengaged';   // ML: "Disengaged"
        }
        // If still unavailable, category remains 'Unavailable'
    }
    
    return {
        id: String(item.id || 'Unavailable'),
        firstName: firstName,
        lastName: lastName,
        gender: item.gender || 'Unavailable',
        age: item.age || 'Unavailable',
        program: item.program || 'Unavailable',
        yearLevel: item.grade || 'Unavailable',
        category: category,
        timestamp: item.timestamp || 'Unavailable',
        status: item.status || 'new',
        dateFrom: item.timestamp ? new Date(item.timestamp) : null,
        dateTo: item.timestamp ? new Date(item.timestamp) : null
    };
}

/**
 * Load assessments from server
 */
function loadAssessments() {
    const url = new URL(config.recordsRoute, window.location.origin);
    if (currentDateFrom) {
        url.searchParams.append('date_from', currentDateFrom);
    }
    if (currentDateTo) {
        url.searchParams.append('date_to', currentDateTo);
    }
    
    fetch(url, {
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
 * Generate HTML for status dropdown
 */
function generateStatusDropdown(item) {
    const statuses = ['new', 'ongoing', 'resolved'];
    let html = `<select id="edit_status_${item.id}" class="w-full px-2 py-1 text-xs border border-indigo-300 rounded focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">`;
    statuses.forEach(status => {
        html += `<option value="${status}" ${item.status === status ? 'selected' : ''}>${status.charAt(0).toUpperCase() + status.slice(1)}</option>`;
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
            <td class="px-4 py-2">
                ${generateStatusDropdown(item)}
            </td>
            <td class="px-4 py-2 text-xs text-neutral-800">${formatTimestamp(item.timestamp)}</td>
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
 * Get category badge HTML with colors (matching result page)
 */
function getCategoryBadge(category) {
    const categoryLower = (category || '').toLowerCase();
    let badgeClass = 'bg-gray-100 text-gray-800';
    
    if (categoryLower.includes('low burnout') || categoryLower.includes('low')) {
        badgeClass = 'bg-green-100 text-green-800';
    } else if (categoryLower.includes('exhausted')) {
        badgeClass = 'bg-yellow-100 text-yellow-800';
    } else if (categoryLower.includes('disengaged')) {
        badgeClass = 'bg-orange-100 text-orange-800';
    } else if (categoryLower.includes('high burnout') || categoryLower.includes('high')) {
        badgeClass = 'bg-red-100 text-red-800';
    }
    
    return `<span class="px-2 py-0.5 text-xs font-medium rounded ${badgeClass}">${category || 'Unavailable'}</span>`;
}

/**
 * Get status badge HTML with colors
 */
function getStatusBadge(status) {
    const statusLower = (status || 'new').toLowerCase();
    let badgeClass = 'bg-blue-100 text-blue-800';
    let statusText = 'New';
    
    if (statusLower === 'ongoing') {
        badgeClass = 'bg-yellow-100 text-yellow-800';
        statusText = 'Ongoing';
    } else if (statusLower === 'resolved') {
        badgeClass = 'bg-green-100 text-green-800';
        statusText = 'Resolved';
    }
    
    return `<span class="px-2 py-0.5 text-xs font-medium rounded ${badgeClass}">${statusText}</span>`;
}

/**
 * Format timestamp
 */
function formatTimestamp(timestamp) {
    if (!timestamp || timestamp === 'Unavailable') {
        return 'Unavailable';
    }
    try {
        const date = new Date(timestamp);
        return date.toLocaleString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch (e) {
        return timestamp;
    }
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
            <td class="px-4 py-3 text-neutral-800">${getCategoryBadge(item.category)}</td>
            <td class="px-4 py-3 text-neutral-800">${getStatusBadge(item.status)}</td>
            <td class="px-4 py-3 text-neutral-800 text-xs">${formatTimestamp(item.timestamp)}</td>
            <td class="px-4 py-3 text-center">
                <div class="flex items-center justify-center space-x-1">
                    <button onclick="openViewModal('${item.id}')" class="text-xs font-medium px-2 py-1 rounded transition text-white bg-indigo-500 hover:bg-indigo-600">View</button>
                    <button onclick="startEdit('${item.id}')" class="text-xs font-medium px-2 py-1 rounded transition text-neutral-800 bg-gray-100 hover:bg-gray-200">Edit</button>
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
                <td colspan="11" class="px-4 py-8 text-center text-gray-500">
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
    
    // Get all pagination buttons
    const startBtn = document.getElementById('startBtn');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const endBtn = document.getElementById('endBtn');
    
    // Base classes for buttons (same as sorting buttons)
    const activeClass = 'flex items-center justify-center w-9 h-9 rounded-lg transition border border-gray-200 bg-indigo-500 text-white hover:bg-indigo-600';
    const inactiveClass = 'flex items-center justify-center w-9 h-9 rounded-lg transition border border-gray-200 bg-white text-neutral-800 hover:bg-indigo-600 hover:text-white';
    const disabledClass = 'flex items-center justify-center w-9 h-9 rounded-lg transition border border-gray-200 bg-gray-100 text-gray-400 cursor-not-allowed';
    
    // Update start button
    if (startBtn) {
        if (currentPage === 1 || totalPages === 0) {
            startBtn.className = disabledClass;
            startBtn.disabled = true;
        } else {
            startBtn.className = inactiveClass;
            startBtn.disabled = false;
        }
    }
    
    // Update prev button
    if (prevBtn) {
        if (currentPage === 1 || totalPages === 0) {
            prevBtn.className = disabledClass;
            prevBtn.disabled = true;
        } else {
            prevBtn.className = inactiveClass;
            prevBtn.disabled = false;
        }
    }
    
    // Update next button
    if (nextBtn) {
        if (currentPage === totalPages || totalPages === 0) {
            nextBtn.className = disabledClass;
            nextBtn.disabled = true;
        } else {
            nextBtn.className = inactiveClass;
            nextBtn.disabled = false;
        }
    }
    
    // Update end button
    if (endBtn) {
        if (currentPage === totalPages || totalPages === 0) {
            endBtn.className = disabledClass;
            endBtn.disabled = true;
        } else {
            endBtn.className = inactiveClass;
            endBtn.disabled = false;
        }
    }
}

/**
 * Change page
 */
function changePage(direction) {
    const totalPages = Math.ceil(filteredData.length / entriesPerPage);
    
    if (totalPages === 0) return;
    
    if (direction === 'start') {
        currentPage = 1;
    } else if (direction === 'prev' && currentPage > 1) {
        currentPage--;
    } else if (direction === 'next' && currentPage < totalPages) {
        currentPage++;
    } else if (direction === 'end') {
        currentPage = totalPages;
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
        
        // Handle date sorting for dateFrom and dateTo
        if (field === 'dateFrom' || field === 'dateTo') {
            aVal = a.dateFrom || a.dateTo || null;
            bVal = b.dateFrom || b.dateTo || null;
            if (!aVal && !bVal) return 0;
            if (!aVal) return 1;
            if (!bVal) return -1;
            if (currentSortOrder === 'asc') {
                return aVal - bVal;
            } else {
                return bVal - aVal;
            }
        }
        
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
 * Apply all filters (search, category, and date range)
 */
function applyFilters() {
    filteredData = assessmentsData.filter(item => {
        // Apply search filter - search across all columns
        const searchMatch = currentSearchTerm === '' || 
            String(item.id || '').toLowerCase().includes(currentSearchTerm) ||
            String(item.firstName || '').toLowerCase().includes(currentSearchTerm) ||
            String(item.lastName || '').toLowerCase().includes(currentSearchTerm) ||
            String(item.gender || '').toLowerCase().includes(currentSearchTerm) ||
            String(item.age || '').toLowerCase().includes(currentSearchTerm) ||
            String(item.program || '').toLowerCase().includes(currentSearchTerm) ||
            String(item.yearLevel || '').toLowerCase().includes(currentSearchTerm) ||
            String(item.category || '').toLowerCase().includes(currentSearchTerm);
        
        // Apply category filter
        const categoryMatch = currentCategoryFilter === 'all' || 
            item.category === currentCategoryFilter;
        
        // Apply date range filter
        let dateMatch = true;
        if (currentDateFrom || currentDateTo) {
            const itemDate = item.dateFrom || item.dateTo;
            if (itemDate) {
                if (currentDateFrom) {
                    const fromDate = new Date(currentDateFrom);
                    fromDate.setHours(0, 0, 0, 0);
                    if (itemDate < fromDate) {
                        dateMatch = false;
                    }
                }
                if (currentDateTo && dateMatch) {
                    const toDate = new Date(currentDateTo);
                    toDate.setHours(23, 59, 59, 999);
                    if (itemDate > toDate) {
                        dateMatch = false;
                    }
                }
            } else {
                dateMatch = false;
            }
        }
        
        return searchMatch && categoryMatch && dateMatch;
    });
    
    currentPage = 1;
    renderTable();
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
 * Map category label to ML prediction value (0,1,2,3) for database
 * Returns numeric value that matches ML model prediction
 * ML model: 0="Non-Burnout", 1="Exhausted", 2="Disengaged", 3="BURNOUT"
 */
function categoryToMLValue(category) {
    if (category === 'Low Burnout') return '0';      // ML: "Non-Burnout"
    if (category === 'Exhausted') return '1';        // ML: "Exhausted" (matches ML model)
    if (category === 'Disengaged') return '2';       // ML: "Disengaged" (matches ML model)
    if (category === 'High Burnout') return '3';     // ML: "BURNOUT"
    return null;
}

// Track if save is in progress to prevent duplicate calls
let isSaving = false;

/**
 * Save edited assessment
 */
function saveEdit(id) {
    // Prevent duplicate calls
    if (isSaving) {
        return;
    }
    
    isSaving = true;
    
    // Get values from input fields
    const firstNameEl = document.getElementById(`edit_firstName_${id}`);
    const lastNameEl = document.getElementById(`edit_lastName_${id}`);
    const genderEl = document.getElementById(`edit_gender_${id}`);
    const ageEl = document.getElementById(`edit_age_${id}`);
    const programSelectEl = document.getElementById(`edit_program_${id}`);
    const programCustomEl = document.getElementById(`edit_program_custom_${id}`);
    const yearLevelEl = document.getElementById(`edit_yearLevel_${id}`);
    const categoryEl = document.getElementById(`edit_category_${id}`);
    const statusEl = document.getElementById(`edit_status_${id}`);
    
    if (!firstNameEl || !lastNameEl || !genderEl || !ageEl || !programSelectEl || !yearLevelEl || !categoryEl || !statusEl) {
        isSaving = false;
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
    const status = statusEl.value;
    const burnoutCategory = categoryToMLValue(category);
    
    // Make AJAX call to update the database
    const formData = new FormData();
    formData.append('_token', config.csrfToken);
    formData.append('name', firstName + ' ' + lastName);
    formData.append('gender', gender);
    formData.append('age', age);
    formData.append('program', program);
    formData.append('year_level', yearLevel);
    formData.append('status', status);
    if (burnoutCategory !== null) {
        formData.append('burnout_category', burnoutCategory);
    }
    
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
            const currentItem = assessmentsData[index];
            assessmentsData[index] = {
                id: id,
                firstName: firstName || 'Unavailable',
                lastName: lastName || 'Unavailable',
                gender: gender || 'Unavailable',
                age: age || 'Unavailable',
                program: program || 'Unavailable',
                yearLevel: yearLevel || 'Unavailable',
                category: category || 'Unavailable',
                status: status || 'new',
                timestamp: currentItem.timestamp || 'Unavailable',
                dateFrom: currentItem.dateFrom,
                dateTo: currentItem.dateTo
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
            
            // Show success message
            if (data && data.success) {
                alert('Assessment updated successfully');
            }
            
            // Reset saving flag
            isSaving = false;
        } else {
            isSaving = false;
        }
    })
    .catch(error => {
        console.error('Update error:', error);
        isSaving = false;
        // Fallback: update local data even if API fails
        const index = assessmentsData.findIndex(item => String(item.id) === String(id));
        if (index !== -1) {
            const currentItem = assessmentsData[index];
            assessmentsData[index] = {
                id: id,
                firstName: firstName || 'Unavailable',
                lastName: lastName || 'Unavailable',
                gender: gender || 'Unavailable',
                age: age || 'Unavailable',
                program: program || 'Unavailable',
                yearLevel: yearLevel || 'Unavailable',
                category: category || 'Unavailable',
                status: status || 'new',
                timestamp: currentItem.timestamp || 'Unavailable',
                dateFrom: currentItem.dateFrom,
                dateTo: currentItem.dateTo
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
        const dateFilterDropdown = document.getElementById('dateFilterDropdown');
        const dateFilterButton = document.getElementById('dateFilterBtn');
        
        if (sortByDropdown && sortByButton && !sortByDropdown.contains(event.target) && !sortByButton.contains(event.target)) {
            sortByDropdown.classList.add('hidden');
        }
        
        if (categoryDropdown && categoryButton && !categoryDropdown.contains(event.target) && !categoryButton.contains(event.target)) {
            categoryDropdown.classList.add('hidden');
        }
        
        if (dateFilterDropdown && dateFilterButton && !dateFilterDropdown.contains(event.target) && !dateFilterButton.contains(event.target)) {
            dateFilterDropdown.classList.add('hidden');
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
    
    // Use event delegation for table buttons to handle dynamically created elements
    const tableBody = document.getElementById('tableBody');
    if (tableBody) {
        tableBody.addEventListener('click', function(event) {
            const target = event.target;
            const button = target.closest('button');
            
            if (!button) return;
            
            // Handle View button - check onclick attribute
            const onclickAttr = button.getAttribute('onclick');
            if (onclickAttr && onclickAttr.includes('openViewModal')) {
                const match = onclickAttr.match(/openViewModal\(['"]([^'"]+)['"]\)/);
                if (match && match[1] && typeof window.openViewModal === 'function') {
                    event.preventDefault();
                    event.stopPropagation();
                    window.openViewModal(match[1]);
                    return;
                }
            }
            
            // Handle Edit button
            if (onclickAttr && onclickAttr.includes('startEdit')) {
                const match = onclickAttr.match(/startEdit\(['"]([^'"]+)['"]\)/);
                if (match && match[1]) {
                    event.preventDefault();
                    event.stopPropagation();
                    startEdit(match[1]);
                    return;
                }
            }
            
            // Handle Save button - let onclick handle it, just prevent event bubbling
            if (onclickAttr && onclickAttr.includes('saveEdit')) {
                // Don't handle here - let the onclick attribute handle it
                // This prevents double execution
                return;
            }
            
            // Handle Cancel button - let onclick handle it
            if (onclickAttr && onclickAttr.includes('cancelEdit')) {
                // Don't handle here - let the onclick attribute handle it
                return;
            }
        });
    }
}

/**
 * Initialize the records module
 */
function initializeRecords() {
    initializeConfig();
    initializeEventListeners();
    loadAssessments();
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeRecords);
} else {
    initializeRecords();
}

// Export functions to global scope for onclick handlers
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
window.toggleDateFilterDropdown = toggleDateFilterDropdown;
window.applyDateFilter = applyDateFilter;
window.resetDateFilter = resetDateFilter;

/**
 * Toggle date filter dropdown
 */
function toggleDateFilterDropdown() {
    const dropdown = document.getElementById('dateFilterDropdown');
    if (dropdown) dropdown.classList.toggle('hidden');
}

/**
 * Apply date filter
 */
function applyDateFilter() {
    const dateFromInput = document.getElementById('dateFromInput');
    const dateToInput = document.getElementById('dateToInput');
    
    currentDateFrom = dateFromInput ? dateFromInput.value : '';
    currentDateTo = dateToInput ? dateToInput.value : '';
    
    // Update button text
    const btnText = document.getElementById('dateFilterBtnText');
    if (btnText) {
        if (currentDateFrom || currentDateTo) {
            let text = 'Date Filter';
            if (currentDateFrom && currentDateTo) {
                text = `${currentDateFrom} to ${currentDateTo}`;
            } else if (currentDateFrom) {
                text = `From ${currentDateFrom}`;
            } else if (currentDateTo) {
                text = `To ${currentDateTo}`;
            }
            btnText.textContent = text;
        } else {
            btnText.textContent = 'Date Filter';
        }
    }
    
    loadAssessments();
    toggleDateFilterDropdown();
}

/**
 * Reset date filter
 */
function resetDateFilter() {
    const dateFromInput = document.getElementById('dateFromInput');
    const dateToInput = document.getElementById('dateToInput');
    
    if (dateFromInput) dateFromInput.value = '';
    if (dateToInput) dateToInput.value = '';
    
    currentDateFrom = '';
    currentDateTo = '';
    
    // Update button text
    const btnText = document.getElementById('dateFilterBtnText');
    if (btnText) {
        btnText.textContent = 'Date Filter';
    }
    
    loadAssessments();
    toggleDateFilterDropdown();
}

