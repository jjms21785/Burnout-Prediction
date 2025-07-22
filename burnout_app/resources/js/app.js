import './bootstrap';
import Chart from 'chart.js/auto';

document.addEventListener('DOMContentLoaded', function() {
    // Data Monitoring filter/search logic
    function getDMParams() {
        return {
            search: document.getElementById('dmSearchInput')?.value || '',
            grade: document.getElementById('dmGradeFilter')?.value || '',
            age: document.getElementById('dmAgeFilter')?.value || '',
            gender: document.getElementById('dmGenderFilter')?.value || '',
            program: document.getElementById('dmDeptFilter')?.value || '',
            risk: document.getElementById('dmRiskFilter')?.value || '',
            time: document.getElementById('dmTimeFilter')?.value || '',
            olbi_sort: document.getElementById('dmOlbiSort')?.value || '',
            conf_sort: document.getElementById('dmConfSort')?.value || ''
        };
    }
    function attachDMListeners() {
        const ids = [
            'dmSearchInput','dmGradeFilter','dmAgeFilter','dmGenderFilter','dmDeptFilter','dmRiskFilter','dmTimeFilter','dmOlbiSort','dmConfSort'
        ];
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
        const url = new URL('/admin/data-monitoring', window.location.origin);
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
    // Expose for dashboard template
    window.loadDataMonitoring = function() {
        attachDMListeners();
        reloadDMTable();
        // Populate department filter
        fetch('/admin/data-monitoring/programs')
            .then(res => res.json())
            .then(programs => {
                const deptSel = document.getElementById('dmDeptFilter');
                if(deptSel && Array.isArray(programs)) {
                    deptSel.innerHTML = '<option value="">Department / Program</option>' +
                        programs.map(p => `<option value="${p}">${p}</option>`).join('');
                }
            });
    };
});