@extends('layouts.app')

@section('title', 'View Data - Burnalytics')

@section('content')
<!-- Main Content Area -->
<main class="flex-1 overflow-y-auto p-3">
    <!-- Assessments Data Table -->
            <div class="rounded-xl shadow-sm p-6 mb-6 bg-white border border-gray-200">
                <!-- Controls Row -->
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        <!-- Search -->
                        <input type="text" id="searchInput" placeholder="Search..." class="px-4 py-2 text-sm rounded-lg border border-gray-200 bg-white w-48">
                    </div>
                    
                    <div class="flex items-center space-x-2">
                        <!-- Category Filter -->
                        <div class="relative">
                            <button id="categoryBtn" onclick="toggleCategoryDropdown()" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-neutral-800 hover:bg-gray-50 transition">
                                <span id="categoryBtnText">Category</span>
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div id="categoryDropdown" class="hidden absolute right-0 mt-2 w-56 rounded-lg shadow-lg z-50 bg-white border border-gray-200">
                                <div class="py-2">
                                    <button onclick="filterByCategory('all')" class="w-full px-4 py-2 text-sm text-left hover:bg-gray-50 transition text-neutral-800">
                                        All Categories
                                    </button>
                                    <button onclick="filterByCategory('High Burnout')" class="w-full px-4 py-2 text-sm text-left hover:bg-gray-50 transition text-neutral-800">
                                        High Burnout
                                    </button>
                                    <button onclick="filterByCategory('Exhaustion')" class="w-full px-4 py-2 text-sm text-left hover:bg-gray-50 transition text-neutral-800">
                                        Exhaustion
                                    </button>
                                    <button onclick="filterByCategory('Disengagement')" class="w-full px-4 py-2 text-sm text-left hover:bg-gray-50 transition text-neutral-800">
                                        Disengagement
                                    </button>
                                    <button onclick="filterByCategory('Low Burnout')" class="w-full px-4 py-2 text-sm text-left hover:bg-gray-50 transition text-neutral-800">
                                        Low Burnout
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Sort By Dropdown -->
                        <div class="relative">
                            <button id="sortByBtn" onclick="toggleSortByDropdown()" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-neutral-800 hover:bg-gray-50 transition">
                                Sort by
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div id="sortByDropdown" class="hidden absolute right-0 mt-2 w-48 rounded-lg shadow-lg z-50 bg-white border border-gray-200">
                                <div class="py-2">
                                    <button onclick="sortBy('id')" class="w-full px-4 py-2 text-sm text-left hover:bg-gray-50 transition text-neutral-800">
                                        ID
                                    </button>
                                    <button onclick="sortBy('firstName')" class="w-full px-4 py-2 text-sm text-left hover:bg-gray-50 transition text-neutral-800">
                                        First Name
                                    </button>
                                    <button onclick="sortBy('lastName')" class="w-full px-4 py-2 text-sm text-left hover:bg-gray-50 transition text-neutral-800">
                                        Last Name
                                    </button>
                                    <button onclick="sortBy('gender')" class="w-full px-4 py-2 text-sm text-left hover:bg-gray-50 transition text-neutral-800">
                                        Gender
                                    </button>
                                    <button onclick="sortBy('age')" class="w-full px-4 py-2 text-sm text-left hover:bg-gray-50 transition text-neutral-800">
                                        Age
                                    </button>
                                    <button onclick="sortBy('program')" class="w-full px-4 py-2 text-sm text-left hover:bg-gray-50 transition text-neutral-800">
                                        Program
                                    </button>
                                    <button onclick="sortBy('yearLevel')" class="w-full px-4 py-2 text-sm text-left hover:bg-gray-50 transition text-neutral-800">
                                        Year Level
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Sort Order Arrows -->
                        <button id="sortAscBtn" onclick="setSortOrder('asc')" class="flex items-center justify-center w-9 h-9 rounded-lg transition border border-gray-200 bg-indigo-500 text-white hover:bg-indigo-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                            </svg>
                        </button>
                        <button id="sortDescBtn" onclick="setSortOrder('desc')" class="flex items-center justify-center w-9 h-9 rounded-lg transition border border-gray-200 bg-white text-neutral-800 hover:bg-gray-50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b-2 border-gray-200">
                                <th class="px-4 py-3 text-left font-semibold text-gray-500">ID</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-500">First Name</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-500">Last Name</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-500">Gender</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-500">Age</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-500">Program</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-500">Year Level</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-500">Category</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-500">Action</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <!-- Data will be populated by JavaScript -->
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="flex items-center justify-end mt-4">
                    <div class="flex items-center space-x-2">
                        <button id="prevBtn" class="flex items-center justify-center w-9 h-9 rounded-lg transition text-gray-600 bg-gray-100 border border-gray-200 hover:bg-gray-200" onclick="changePage('prev')">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>
                        <div id="pageNumbers" class="flex items-center space-x-1">
                            <!-- Page numbers will be populated by JavaScript -->
                        </div>
                        <button id="nextBtn" class="flex items-center justify-center w-9 h-9 rounded-lg transition text-white bg-indigo-500 hover:bg-indigo-600" onclick="changePage('next')">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
</main>

<script>
// Cleared temporary/mock data for assessments (awaiting real backend data)
const assessmentsData = [];

let currentPage = 1;
let entriesPerPage = 10;
let filteredData = [...assessmentsData];
let editingId = null;
let currentCategoryFilter = 'all';
let currentSearchTerm = '';

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
        const isEditing = editingId === item.id;
        
        if (isEditing) {
            // Editing mode - show input fields
            return `
                <tr class="border-b border-gray-200 bg-indigo-50">
                    <td class="px-4 py-3 text-neutral-800">${item.id}</td>
                    <td class="px-4 py-2"><input type="text" id="edit_firstName_${item.id}" value="${item.firstName}" class="w-full px-2 py-1 text-xs border border-indigo-300 rounded focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></td>
                    <td class="px-4 py-2"><input type="text" id="edit_lastName_${item.id}" value="${item.lastName}" class="w-full px-2 py-1 text-xs border border-indigo-300 rounded focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></td>
                    <td class="px-4 py-2">
                        <select id="edit_gender_${item.id}" class="w-full px-2 py-1 text-xs border border-indigo-300 rounded focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="Male" ${item.gender === 'Male' ? 'selected' : ''}>Male</option>
                            <option value="Female" ${item.gender === 'Female' ? 'selected' : ''}>Female</option>
                        </select>
                    </td>
                    <td class="px-4 py-2"><input type="number" id="edit_age_${item.id}" value="${item.age}" min="18" max="100" class="w-full px-2 py-1 text-xs border border-indigo-300 rounded focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></td>
                    <td class="px-4 py-2">
                        <div>
                            <select id="edit_program_${item.id}" onchange="toggleCustomProgram(${item.id})" class="w-full px-2 py-1 text-xs border border-indigo-300 rounded focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="Computer Studies" ${item.program === 'Computer Studies' ? 'selected' : ''}>Computer Studies</option>
                                <option value="Business" ${item.program === 'Business' ? 'selected' : ''}>Business</option>
                                <option value="Education" ${item.program === 'Education' ? 'selected' : ''}>Education</option>
                                <option value="Engineering" ${item.program === 'Engineering' ? 'selected' : ''}>Engineering</option>
                                <option value="Hospitality" ${item.program === 'Hospitality' ? 'selected' : ''}>Hospitality</option>
                                <option value="Nursing" ${item.program === 'Nursing' ? 'selected' : ''}>Nursing</option>
                                <option value="Arts & Science" ${item.program === 'Arts & Science' ? 'selected' : ''}>Arts & Science</option>
                                <option value="__custom__" ${!['Computer Studies', 'Business', 'Education', 'Engineering', 'Hospitality', 'Nursing', 'Arts & Science'].includes(item.program) ? 'selected' : ''}>Other (Specify)</option>
                            </select>
                            <input type="text" id="edit_program_custom_${item.id}" value="${!['Computer Studies', 'Business', 'Education', 'Engineering', 'Hospitality', 'Nursing', 'Arts & Science'].includes(item.program) ? item.program : ''}" placeholder="Enter program name" class="w-full px-2 py-1 text-xs border border-indigo-300 rounded focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 mt-1 ${!['Computer Studies', 'Business', 'Education', 'Engineering', 'Hospitality', 'Nursing', 'Arts & Science'].includes(item.program) ? '' : 'hidden'}" />
                        </div>
                    </td>
                    <td class="px-4 py-2">
                        <select id="edit_yearLevel_${item.id}" class="w-full px-2 py-1 text-xs border border-indigo-300 rounded focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="1st Year" ${item.yearLevel === '1st Year' ? 'selected' : ''}>1st Year</option>
                            <option value="2nd Year" ${item.yearLevel === '2nd Year' ? 'selected' : ''}>2nd Year</option>
                            <option value="3rd Year" ${item.yearLevel === '3rd Year' ? 'selected' : ''}>3rd Year</option>
                            <option value="4th Year" ${item.yearLevel === '4th Year' ? 'selected' : ''}>4th Year</option>
                        </select>
                    </td>
                    <td class="px-4 py-2">
                        <select id="edit_category_${item.id}" class="w-full px-2 py-1 text-xs border border-indigo-300 rounded focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="High Burnout" ${item.category === 'High Burnout' ? 'selected' : ''}>High Burnout</option>
                            <option value="Exhaustion" ${item.category === 'Exhaustion' ? 'selected' : ''}>Exhaustion</option>
                            <option value="Disengagement" ${item.category === 'Disengagement' ? 'selected' : ''}>Disengagement</option>
                            <option value="Low Burnout" ${item.category === 'Low Burnout' ? 'selected' : ''}>Low Burnout</option>
                        </select>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center space-x-1">
                            <button onclick="saveEdit(${item.id})" class="text-xs font-medium px-2 py-1 rounded transition text-white bg-indigo-500 hover:bg-indigo-600">Save</button>
                            <button onclick="cancelEdit()" class="text-xs font-medium px-2 py-1 rounded transition text-neutral-800 bg-gray-200 hover:bg-gray-300">Cancel</button>
                        </div>
                    </td>
                </tr>
            `;
        } else {
            // Normal view mode
            return `
                <tr class="border-b border-gray-200 hover:bg-gray-50 transition">
                    <td class="px-4 py-3 text-neutral-800">${item.id || 'Unavailable'}</td>
                    <td class="px-4 py-3 text-neutral-800">${item.firstName || 'Unavailable'}</td>
                    <td class="px-4 py-3 text-neutral-800">${item.lastName || 'Unavailable'}</td>
                    <td class="px-4 py-3 text-neutral-800">${item.gender || 'Unavailable'}</td>
                    <td class="px-4 py-3 text-neutral-800">${item.age || 'Unavailable'}</td>
                    <td class="px-4 py-3 text-neutral-800">${item.program || 'Unavailable'}</td>
                    <td class="px-4 py-3 text-neutral-800">${item.yearLevel || 'Unavailable'}</td>
                    <td class="px-4 py-3 text-neutral-800">
                        ${item.category || 'Unavailable'}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center space-x-1">
                            <button onclick="viewAssessment(${item.id})" class="text-xs font-medium px-2 py-1 rounded transition text-neutral-800 bg-gray-100 hover:bg-gray-200">View</button>
                            <button onclick="startEdit(${item.id})" class="text-xs font-medium px-2 py-1 rounded transition text-neutral-800 bg-gray-100 hover:bg-gray-200">Edit</button>
                            <button onclick="deleteAssessment(${item.id})" class="text-xs font-medium px-2 py-1 rounded transition text-white bg-red-500 hover:bg-red-600">Delete</button>
                        </div>
                    </td>
                </tr>
            `;
        }
    }).join('');
    
    updatePagination();
}

function updatePagination() {
    const totalPages = Math.ceil(filteredData.length / entriesPerPage);
    
    // Page numbers
    const pageNumbers = document.getElementById('pageNumbers');
    pageNumbers.innerHTML = Array.from({length: Math.min(totalPages, 5)}, (_, i) => {
        const pageNum = i + 1;
        const isActive = pageNum === currentPage;
        return `
            <button onclick="goToPage(${pageNum})" class="px-3 py-1.5 text-sm font-medium rounded-lg transition ${isActive ? 'text-white bg-indigo-500 hover:bg-indigo-600' : 'text-gray-600 bg-gray-100 border border-gray-200 hover:bg-gray-200'}">
                ${pageNum}
            </button>
        `;
    }).join('');
    
    // Disable/enable buttons
    document.getElementById('prevBtn').disabled = currentPage === 1;
    document.getElementById('nextBtn').disabled = currentPage === totalPages;
}

function changePage(direction) {
    const totalPages = Math.ceil(filteredData.length / entriesPerPage);
    if (direction === 'prev' && currentPage > 1) {
        currentPage--;
    } else if (direction === 'next' && currentPage < totalPages) {
        currentPage++;
    }
    renderTable();
}

function goToPage(page) {
    currentPage = page;
    renderTable();
}

// Sort By Dropdown Functions
let currentSortField = 'id';
let currentSortOrder = 'asc';

function toggleSortByDropdown() {
    const dropdown = document.getElementById('sortByDropdown');
    dropdown.classList.toggle('hidden');
}

function setSortOrder(order) {
    currentSortOrder = order;
    
    // Update button styles
    const ascBtn = document.getElementById('sortAscBtn');
    const descBtn = document.getElementById('sortDescBtn');
    
    if (order === 'asc') {
        ascBtn.className = 'flex items-center justify-center w-9 h-9 rounded-lg transition border border-gray-200 bg-indigo-500 text-white hover:bg-indigo-600';
        descBtn.className = 'flex items-center justify-center w-9 h-9 rounded-lg transition border border-gray-200 bg-white text-neutral-800 hover:bg-gray-50';
    } else {
        descBtn.className = 'flex items-center justify-center w-9 h-9 rounded-lg transition border border-gray-200 bg-indigo-500 text-white hover:bg-indigo-600';
        ascBtn.className = 'flex items-center justify-center w-9 h-9 rounded-lg transition border border-gray-200 bg-white text-neutral-800 hover:bg-gray-50';
    }
    
    // Re-sort if a field is selected
    if (currentSortField) {
        sortBy(currentSortField);
    }
}

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

// Category Filter Functions
function toggleCategoryDropdown() {
    const dropdown = document.getElementById('categoryDropdown');
    dropdown.classList.toggle('hidden');
}

function filterByCategory(category) {
    currentCategoryFilter = category;
    
    // Update button text
    const btnText = document.getElementById('categoryBtnText');
    if (category === 'all') {
        btnText.textContent = 'Category';
    } else {
        btnText.textContent = category;
    }
    
    // Apply filter
    applyFilters();
    
    // Close dropdown
    toggleCategoryDropdown();
}

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

// Close dropdowns when clicking outside
document.addEventListener('click', function(event) {
    const sortByDropdown = document.getElementById('sortByDropdown');
    const sortByButton = document.getElementById('sortByBtn');
    const categoryDropdown = document.getElementById('categoryDropdown');
    const categoryButton = document.getElementById('categoryBtn');
    
    if (!sortByDropdown.contains(event.target) && !sortByButton.contains(event.target)) {
        sortByDropdown.classList.add('hidden');
    }
    
    if (!categoryDropdown.contains(event.target) && !categoryButton.contains(event.target)) {
        categoryDropdown.classList.add('hidden');
    }
});

// Event listeners
document.getElementById('searchInput').addEventListener('input', (e) => {
    currentSearchTerm = e.target.value.toLowerCase();
    applyFilters();
});

// View Assessment Function
function viewAssessment(id) {
    alert('View assessment details for ID: ' + id);
    // This will be implemented to show assessment details
}

// Delete Assessment Function
function deleteAssessment(id) {
    if (confirm('Are you sure you want to delete this assessment? This action cannot be undone.')) {
        // Find and remove from main data
        const index = assessmentsData.findIndex(item => item.id === id);
        if (index !== -1) {
            assessmentsData.splice(index, 1);
        }
        
        // Remove from filtered data
        const filteredIndex = filteredData.findIndex(item => item.id === id);
        if (filteredIndex !== -1) {
            filteredData.splice(filteredIndex, 1);
        }
        
        // Re-render table
        renderTable();
    }
}

// Inline Edit Functions
function startEdit(id) {
    editingId = id;
    renderTable();
}

function cancelEdit() {
    editingId = null;
    renderTable();
}

function toggleCustomProgram(id) {
    const select = document.getElementById(`edit_program_${id}`);
    const customInput = document.getElementById(`edit_program_custom_${id}`);
    
    if (select.value === '__custom__') {
        customInput.classList.remove('hidden');
        customInput.focus();
    } else {
        customInput.classList.add('hidden');
        customInput.value = '';
    }
}

function saveEdit(id) {
    // Get values from input fields
    const firstName = document.getElementById(`edit_firstName_${id}`).value;
    const lastName = document.getElementById(`edit_lastName_${id}`).value;
    const gender = document.getElementById(`edit_gender_${id}`).value;
    const age = parseInt(document.getElementById(`edit_age_${id}`).value);
    
    // Handle program - check if custom or predefined
    let program;
    const programSelect = document.getElementById(`edit_program_${id}`).value;
    if (programSelect === '__custom__') {
        program = document.getElementById(`edit_program_custom_${id}`).value.trim();
        if (!program) {
            alert('Please enter a program name');
            return;
        }
    } else {
        program = programSelect;
    }
    
    const yearLevel = document.getElementById(`edit_yearLevel_${id}`).value;
    const category = document.getElementById(`edit_category_${id}`).value;
    
    // Find and update in main data
    const index = assessmentsData.findIndex(item => item.id === id);
    if (index !== -1) {
        assessmentsData[index] = {
            id: id,
            firstName: firstName,
            lastName: lastName,
            gender: gender,
            age: age,
            program: program,
            yearLevel: yearLevel,
            category: category
        };
        
        // Update filtered data as well
        const filteredIndex = filteredData.findIndex(item => item.id === id);
        if (filteredIndex !== -1) {
            filteredData[filteredIndex] = {...assessmentsData[index]};
        }
        
        // Reset editing state
        editingId = null;
        
        // Re-render table
        renderTable();
    }
}

// Initialize on load
document.addEventListener('DOMContentLoaded', () => {
    renderTable();
});
</script>
@endsection
