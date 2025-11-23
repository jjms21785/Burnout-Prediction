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

let assessmentsData = [];
let currentPage = 1;
let entriesPerPage = 10;
let filteredData = [];
let editingId = null;
let currentCategoryFilter = 'all';
let currentSearchTerm = '';
let currentSortField = 'id';
let currentSortOrder = 'asc';

let config = {
    recordsRoute: '',
    updateRoute: '',
    deleteRoute: '',
    csrfToken: ''
};

function initializeConfig() {
    if (window.recordsConfig) {
        config = {
            ...config,
            ...window.recordsConfig
        };
    }
}

function transformAssessmentData(item) {
    const name = item.name || 'Unavailable';
    const nameParts = name.split(' ');
    const firstName = nameParts[0] || 'Unavailable';
    const lastName = nameParts.slice(1).join(' ') || 'Unavailable';
    
    let category = 'Unavailable';
    const risk = item.risk;
    
    if (risk === '0' || risk === 0) {
        category = 'Low Burnout';
    } else if (risk === '1' || risk === 1) {
        category = 'Exhausted';
    } else if (risk === '2' || risk === 2) {
        category = 'Disengaged';
    } else if (risk === '3' || risk === 3) {
        category = 'High Burnout';
    } else {
        const riskLower = String(risk || '').toLowerCase();
        if (riskLower === 'high' || riskLower === '3') {
            category = 'High Burnout';
        } else if (riskLower === 'low' || riskLower === '0') {
            category = 'Low Burnout';
        } else if (riskLower === '1') {
            category = 'Exhausted';
        } else if (riskLower === '2') {
            category = 'Disengaged';
        }
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

function loadAssessments() {
    fetch(config.recordsRoute, {
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

function generateYearLevelDropdown(item) {
    let html = `<select id="edit_yearLevel_${item.id}" class="w-full px-2 py-1 text-xs border border-indigo-300 rounded focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">`;
    YEAR_LEVELS.forEach(year => {
        html += `<option value="${year}" ${item.yearLevel === year ? 'selected' : ''}>${year}</option>`;
    });
    html += `</select>`;
    return html;
}

function generateCategoryDropdown(item) {
    let html = `<select id="edit_category_${item.id}" class="w-full px-2 py-1 text-xs border border-indigo-300 rounded focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">`;
    CATEGORIES.forEach(category => {
        html += `<option value="${category}" ${item.category === category ? 'selected' : ''}>${category}</option>`;
    });
    html += `</select>`;
    return html;
}

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
                    <button onclick="openViewModal('${item.id}')" class="text-xs font-medium px-2 py-1 rounded transition text-white bg-indigo-500 hover:bg-indigo-600">View</button>
                    <button onclick="startEdit('${item.id}')" class="text-xs font-medium px-2 py-1 rounded transition text-neutral-800 bg-gray-100 hover:bg-gray-200">Edit</button>
                    <button onclick="deleteAssessment('${item.id}')" class="text-xs font-medium px-2 py-1 rounded transition text-white bg-red-500 hover:bg-red-600">Delete</button>
                </div>
            </td>
        </tr>
    `;
}

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

function updatePagination() {
    const totalPages = Math.ceil(filteredData.length / entriesPerPage);
    
    const startBtn = document.getElementById('startBtn');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const endBtn = document.getElementById('endBtn');
    
    const activeClass = 'flex items-center justify-center w-9 h-9 rounded-lg transition border border-gray-200 bg-indigo-500 text-white hover:bg-indigo-600';
    const inactiveClass = 'flex items-center justify-center w-9 h-9 rounded-lg transition border border-gray-200 bg-white text-neutral-800 hover:bg-indigo-600 hover:text-white';
    const disabledClass = 'flex items-center justify-center w-9 h-9 rounded-lg transition border border-gray-200 bg-gray-100 text-gray-400 cursor-not-allowed';
    
    if (startBtn) {
        if (currentPage === 1 || totalPages === 0) {
            startBtn.className = disabledClass;
            startBtn.disabled = true;
        } else {
            startBtn.className = inactiveClass;
            startBtn.disabled = false;
        }
    }
    
    if (prevBtn) {
        if (currentPage === 1 || totalPages === 0) {
            prevBtn.className = disabledClass;
            prevBtn.disabled = true;
        } else {
            prevBtn.className = inactiveClass;
            prevBtn.disabled = false;
        }
    }
    
    if (nextBtn) {
        if (currentPage === totalPages || totalPages === 0) {
            nextBtn.className = disabledClass;
            nextBtn.disabled = true;
        } else {
            nextBtn.className = inactiveClass;
            nextBtn.disabled = false;
        }
    }
    
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

function toggleSortByDropdown() {
    const dropdown = document.getElementById('sortByDropdown');
    if (dropdown) dropdown.classList.toggle('hidden');
}

function setSortOrder(order) {
    currentSortOrder = order;
    
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
    
    if (currentSortField) {
        sortBy(currentSortField);
    }
}

function sortBy(field) {
    currentSortField = field;
    
    filteredData.sort((a, b) => {
        let aVal = a[field];
        let bVal = b[field];
        
        if (field === 'id' || field === 'age') {
            aVal = parseInt(aVal);
            bVal = parseInt(bVal);
        } else {
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

function toggleCategoryDropdown() {
    const dropdown = document.getElementById('categoryDropdown');
    if (dropdown) dropdown.classList.toggle('hidden');
}

function filterByCategory(category) {
    currentCategoryFilter = category;
    
    const btnText = document.getElementById('categoryBtnText');
    if (btnText) {
        btnText.textContent = category === 'all' ? 'Category' : category;
    }
    
    applyFilters();
    toggleCategoryDropdown();
}

function applyFilters() {
    filteredData = assessmentsData.filter(item => {
        const searchMatch = currentSearchTerm === '' || 
            String(item.id || '').toLowerCase().includes(currentSearchTerm) ||
            String(item.firstName || '').toLowerCase().includes(currentSearchTerm) ||
            String(item.lastName || '').toLowerCase().includes(currentSearchTerm) ||
            String(item.gender || '').toLowerCase().includes(currentSearchTerm) ||
            String(item.age || '').toLowerCase().includes(currentSearchTerm) ||
            String(item.program || '').toLowerCase().includes(currentSearchTerm) ||
            String(item.yearLevel || '').toLowerCase().includes(currentSearchTerm) ||
            String(item.category || '').toLowerCase().includes(currentSearchTerm);
        
        const categoryMatch = currentCategoryFilter === 'all' || 
            item.category === currentCategoryFilter;
        
        return searchMatch && categoryMatch;
    });
    
    currentPage = 1;
    renderTable();
}

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
                const index = assessmentsData.findIndex(item => String(item.id) === String(id));
                if (index !== -1) {
                    assessmentsData.splice(index, 1);
                }
                
                const filteredIndex = filteredData.findIndex(item => String(item.id) === String(id));
                if (filteredIndex !== -1) {
                    filteredData.splice(filteredIndex, 1);
                }
                
                renderTable();
                alert('Assessment deleted successfully');
            } else {
                alert('Failed to delete assessment: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Delete error:', error);
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

function startEdit(id) {
    editingId = String(id);
    renderTable();
}

function cancelEdit() {
    editingId = null;
    renderTable();
}

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

function categoryToMLValue(category) {
    if (category === 'Low Burnout') return '0';
    if (category === 'Exhausted') return '1';
    if (category === 'Disengaged') return '2';
    if (category === 'High Burnout') return '3';
    return null;
}

function saveEdit(id) {
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
    const burnoutCategory = categoryToMLValue(category);
    
    const formData = new FormData();
    formData.append('_token', config.csrfToken);
    formData.append('name', firstName + ' ' + lastName);
    formData.append('gender', gender);
    formData.append('age', age);
    formData.append('program', program);
    formData.append('year_level', yearLevel);
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
            
            if (data.success) {
                alert('Assessment updated successfully');
            }
        }
    })
    .catch(error => {
        console.error('Update error:', error);
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

function initializeEventListeners() {
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
    
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            currentSearchTerm = e.target.value.toLowerCase();
            applyFilters();
        });
    }
}

function initializeRecords() {
    initializeConfig();
    initializeEventListeners();
    loadAssessments();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeRecords);
} else {
    initializeRecords();
}

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
