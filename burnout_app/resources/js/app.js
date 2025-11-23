import './bootstrap';

function checkAuthentication() {
    return fetch('/auth/check', {
        method: 'GET',
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => data.authenticated)
    .catch(() => false);
}

function initUserMenu() {
    const userMenuButton = document.getElementById('userMenuButton');
    const userMenuDropdown = document.getElementById('userMenuDropdown');
    
    if (userMenuButton && userMenuDropdown) {
        userMenuButton.addEventListener('click', function(e) {
            e.stopPropagation();
            const isVisible = userMenuDropdown.classList.contains('opacity-100');
            
            if (isVisible) {
                userMenuDropdown.classList.remove('opacity-100', 'visible');
                userMenuDropdown.classList.add('opacity-0', 'invisible');
            } else {
                userMenuDropdown.classList.remove('opacity-0', 'invisible');
                userMenuDropdown.classList.add('opacity-100', 'visible');
            }
        });
        
        document.addEventListener('click', function(e) {
            const container = document.getElementById('userMenuContainer');
            if (container && !container.contains(e.target)) {
                userMenuDropdown.classList.remove('opacity-100', 'visible');
                userMenuDropdown.classList.add('opacity-0', 'invisible');
            }
        });
    }
}

function getDMParams() {
    return {
        search: document.getElementById('dmSearchInput')?.value || '',
        grade: document.getElementById('dmGradeFilter')?.value || '',
        age: document.getElementById('dmAgeFilter')?.value || '',
        gender: document.getElementById('dmGenderFilter')?.value || '',
        program: document.getElementById('dmDeptFilter')?.value || '',
        risk: document.getElementById('dmRiskFilter')?.value || '',
        time: document.getElementById('dmTimeFilter')?.value || ''
    };
}

function attachDMListeners() {
    const ids = ['dmSearchInput','dmGradeFilter','dmAgeFilter','dmGenderFilter','dmDeptFilter','dmRiskFilter','dmTimeFilter'];
    ids.forEach(id => {
        const el = document.getElementById(id);
        if(el) {
            el.addEventListener('change', reloadDMTable);
            if(id === 'dmSearchInput') {
                el.addEventListener('input', debounce(reloadDMTable, 400));
            }
        }
    });
}

function reloadDMTable() {
    const params = getDMParams();
    const url = new URL('/admin/records', window.location.origin);
    Object.entries(params).forEach(([k,v]) => { if(v) url.searchParams.append(k,v); });
    fetch(url)
        .then(res => res.json())
        .then(data => {
            if(typeof renderDMTable === 'function') renderDMTable(data);
        })
        .catch(() => {
            const tbody = document.getElementById('dmTableBody');
            if(tbody) tbody.innerHTML = '<tr><td colspan="11" class="text-center text-red-400 py-8">Failed to load data.</td></tr>';
        });
}

function debounce(fn, ms) {
    let timer;
    return function(...args) {
        clearTimeout(timer);
        timer = setTimeout(() => fn.apply(this, args), ms);
    };
}

window.loadDataMonitoring = function() {
    attachDMListeners();
    reloadDMTable();
    fetch('/admin/records/programs')
        .then(res => res.json())
        .then(programs => {
            const deptSel = document.getElementById('dmDeptFilter');
            if(deptSel && Array.isArray(programs)) {
                deptSel.innerHTML = '<option value="">Department / Program</option>' +
                    programs.map(p => `<option value="${p}">${p}</option>`).join('');
            }
        });
};

document.addEventListener('DOMContentLoaded', function() {
    checkAuthentication().then(isAuthenticated => {
        if (isAuthenticated) {
            if (window.location.pathname === '/login') {
                window.location.href = '/dashboard';
            }
        } else {
            const adminRoutes = ['/dashboard', '/records', '/questions', '/files', '/settings'];
            const currentPath = window.location.pathname;
            if (adminRoutes.some(route => currentPath.startsWith(route))) {
                window.location.href = '/login';
            }
        }
    }).catch(() => {});
    
    initUserMenu();
});