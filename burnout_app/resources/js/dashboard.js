/**
 * Dashboard JavaScript Module
 * Handles all dashboard chart initialization and question statistics
 */

// Chart configuration helper
function createChartConfig(showLegend = true) {
    return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: showLegend,
                position: 'bottom',
                labels: { 
                    font: { size: 12 }, 
                    padding: 10,
                    boxWidth: 15
                }
            }
        }
    };
}

// Helper function to create doughnut chart
function createDoughnutChart(canvasId, labels, data, colors) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return null;
    
    return new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: labels.length > 0 ? labels : ['No Data'],
            datasets: [{
                data: data.length > 0 ? data : [0],
                backgroundColor: colors
            }]
        },
        options: createChartConfig(true)
    });
}

// Initialize all charts
function initializeCharts(dashboardData) {
    const colors = ['#6366f1', '#818cf8', '#a5b4fc', '#c7d2fe'];
    
    // Burnout Categories Chart
    const burnoutLabels = ['High Burnout', 'Exhausted', 'Disengaged', 'Low Burnout'];
    const burnoutData = [
        dashboardData.highBurnout || 0,
        dashboardData.exhaustion || 0,
        dashboardData.disengagement || 0,
        dashboardData.lowBurnout || 0
    ];
    createDoughnutChart('burnoutChart', burnoutLabels, burnoutData, colors);

    // Age Chart
    const ageLabels = ['18-20', '21-23', '24-26', '27+'];
    const ageData = ageLabels.map(label => dashboardData.ageDistribution?.[label] || 0);
    createDoughnutChart('ageChart', ageLabels, ageData, colors);

    // Gender Chart
    const genderData = dashboardData.genderDistribution || {};
    console.log('Gender Data:', genderData);
    const genderKeys = Object.keys(genderData).filter(key => key && key.trim() !== '');
    const hasGenderData = genderKeys.length > 0 && genderKeys.some(key => genderData[key] > 0);
    
    let genderLabels, genderValues;
    if (hasGenderData) {
        genderLabels = genderKeys.filter(key => key && key.trim() !== '');
        genderValues = genderLabels.map(g => parseInt(genderData[g]) || 0);
    } else {
        genderLabels = ['No Data'];
        genderValues = [1];
    }
    console.log('Gender Labels:', genderLabels, 'Values:', genderValues);
    createDoughnutChart('genderChart', genderLabels, genderValues, ['#6366f1', '#c7d2fe', '#a5b4fc']);

    // Program Chart
    const programData = dashboardData.programDistribution || {};
    console.log('Program Data:', programData);
    const programKeys = Object.keys(programData).filter(key => key && key.trim() !== '');
    const hasProgramData = programKeys.length > 0 && programKeys.some(key => programData[key] > 0);
    
    let programLabels, programValues;
    if (hasProgramData) {
        programLabels = programKeys.slice(0, 10).filter(key => key && key.trim() !== '');
        programValues = programLabels.map(p => parseInt(programData[p]) || 0);
    } else {
        programLabels = ['No Data'];
        programValues = [1];
    }
    console.log('Program Labels:', programLabels, 'Values:', programValues);
    
    const programColors = ['#6366f1', '#818cf8', '#a5b4fc', '#c7d2fe', '#e0e7ff', '#ddd6fe', '#f3e8ff', '#fce7f3', '#fef3c7', '#ecfccb'];
    
    const programChartConfig = createChartConfig(false);
    const programCanvas = document.getElementById('programChart');
    if (programCanvas) {
        new Chart(programCanvas, {
            type: 'doughnut',
            data: {
                labels: programLabels,
                datasets: [{
                    data: programValues,
                    backgroundColor: programColors
                }]
            },
            options: programChartConfig
        });
    }
    
    // Display custom legend for program chart
    const programLegend = document.getElementById('programLegend');
    if (programLegend) {
        if (hasProgramData && programLabels.length > 0) {
            programLegend.innerHTML = programLabels.map((label, index) => {
                const color = programColors[index] || '#6366f1';
                const value = programValues[index] || 0;
                return `
                    <div class="flex items-center py-1.5 mb-1">
                        <div class="w-3 h-3 rounded-full mr-2" style="background-color: ${color};"></div>
                        <span class="text-xs text-neutral-800">${label} (${value})</span>
                    </div>
                `;
            }).join('');
        } else {
            programLegend.innerHTML = '<p class="text-xs text-center text-gray-500">No Data</p>';
        }
    }

    // Year Level Chart
    const yearData = dashboardData.yearDistribution || {};
    console.log('Year Data:', yearData);
    const yearKeys = Object.keys(yearData).filter(key => key && key.trim() !== '');
    const hasYearData = yearKeys.length > 0 && yearKeys.some(key => yearData[key] > 0);
    
    let yearLabels, yearValues;
    if (hasYearData) {
        yearLabels = yearKeys.filter(key => key && key.trim() !== '');
        yearValues = yearLabels.map(y => parseInt(yearData[y]) || 0);
    } else {
        yearLabels = ['No Data'];
        yearValues = [1];
    }
    console.log('Year Labels:', yearLabels, 'Values:', yearValues);
    createDoughnutChart('yearChart', yearLabels, yearValues, ['#6366f1', '#818cf8', '#a5b4fc', '#c7d2fe', '#e0e7ff']);
}

// Map answer format (handles Q1-Q14 and Q15-Q30 differently)
function mapAnswerToResponseIndex(answer, questionIndex) {
    if (questionIndex >= 14) {
        // Q15-Q30: OLBI format (1-4), convert to 0-3 index
        if (answer >= 1 && answer <= 4) {
            return answer - 1; // 1->0, 2->1, 3->2, 4->3
        } else if (answer >= 0 && answer <= 3) {
            return answer;
        }
    } else {
        // Q1-Q14: Map 1-5 scale to 0-3 scale for display
        if (answer >= 1 && answer <= 5) {
            if (answer <= 2) return 0;      // Strongly Agree
            if (answer === 3) return 1;     // Agree
            if (answer === 4) return 2;      // Disagree
            return 3;                        // Strongly Disagree
        } else if (answer >= 0 && answer <= 3) {
            return answer;
        }
    }
    return null;
}

// Process question statistics
function processQuestionStatistics(questionStats, questionsList) {
    const questions = questionsList.slice(0, 30);
    
    return questions.map((q, idx) => {
        const responses = [0, 0, 0, 0]; // Strongly Agree, Agree, Disagree, Strongly Disagree
        let total = 0;
        
        questionStats.forEach(answers => {
            if (Array.isArray(answers) && answers[idx] !== undefined) {
                const answer = parseInt(answers[idx]);
                if (isNaN(answer)) return;
                
                const mappedIndex = mapAnswerToResponseIndex(answer, idx);
                if (mappedIndex !== null) {
                    responses[mappedIndex]++;
                    total++;
                }
            }
        });
        
        return {
            id: idx + 1,
            text: q,
            responses: responses,
            total: total,
            percentages: responses.map(r => total > 0 ? (r / total) * 100 : 0)
        };
    });
}

// Question pagination state
let currentQuestionPage = 1;
const questionsPerPage = 10;
let allQuestions = [];

function loadQuestionStatistics(dashboardData) {
    const container = document.getElementById('questionsList');
    if (!container) return;
    
    const questionStats = dashboardData.questionStats || [];
    const questionsList = dashboardData.questionsList || [];
    
    if (questionStats.length === 0) {
        container.innerHTML = '<p class="text-sm text-center py-4 text-gray-500">Unavailable</p>';
        return;
    }
    
    if (questionsList.length === 0) {
        container.innerHTML = '<p class="text-sm text-center py-4 text-gray-500">No questions available</p>';
        return;
    }
    
    allQuestions = processQuestionStatistics(questionStats, questionsList);
    renderQuestionsPage();
}

function renderQuestionsPage() {
    const startIdx = (currentQuestionPage - 1) * questionsPerPage;
    const endIdx = startIdx + questionsPerPage;
    const pageQuestions = allQuestions.slice(startIdx, endIdx);
    
    renderStackedBarQuestions('questionsList', pageQuestions);
    updatePaginationButtons();
}

function changeQuestionPage(direction) {
    const totalPages = Math.ceil(allQuestions.length / questionsPerPage);
    currentQuestionPage += direction;
    
    if (currentQuestionPage < 1) currentQuestionPage = 1;
    if (currentQuestionPage > totalPages) currentQuestionPage = totalPages;
    
    renderQuestionsPage();
}

function updatePaginationButtons() {
    const totalPages = Math.ceil(allQuestions.length / questionsPerPage);
    const prevBtn = document.getElementById('prevPageBtn');
    const nextBtn = document.getElementById('nextPageBtn');
    
    if (prevBtn) prevBtn.disabled = currentQuestionPage === 1;
    if (nextBtn) nextBtn.disabled = currentQuestionPage === totalPages;
}

function renderStackedBarQuestions(containerId, questions) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    const colors = ['#6366f1', '#818cf8', '#a5b4fc', '#c7d2fe'];
    const labels = ['Strongly Agree', 'Agree', 'Disagree', 'Strongly Disagree'];
    
    container.innerHTML = questions.map((q) => {
        const values = q.responses || [0, 0, 0, 0];
        const percentages = q.percentages || [0, 0, 0, 0];
        const total = values.reduce((a, b) => a + b, 0);
        
        // Create array of segments with their data
        const segments = values.map((v, i) => ({
            value: v,
            percentage: total > 0 ? parseFloat(percentages[i] || 0).toFixed(1) : '0.0',
            label: labels[i],
            index: i
        }));
        
        // Sort by percentage (highest to lowest) to assign colors
        const sortedSegments = [...segments].sort((a, b) => parseFloat(b.percentage) - parseFloat(a.percentage));
        
        // Assign colors based on sorted order (highest = darkest)
        const colorMap = {};
        sortedSegments.forEach((seg, idx) => {
            colorMap[seg.index] = colors[idx];
        });
        
        return `
        <div class="grid grid-cols-11 gap-4 items-center">
            <div class="col-span-5 text-sm pr-4 text-neutral-800">
                <span class="font-semibold text-neutral-800">${q.id}.</span> ${q.text}
            </div>
            <div class="col-span-6 relative h-10 rounded-lg overflow-hidden shadow-sm bg-gray-50">
                <div class="absolute inset-0 flex">
                    ${segments.map((seg, i) => {
                        const showPct = parseFloat(seg.percentage);
                        let displayText = '';
                        let fontSize = 'text-xs';
                        
                        const assignedColor = colorMap[i];
                        const isLightBackground = assignedColor === '#a5b4fc' || assignedColor === '#c7d2fe';
                        const textColor = isLightBackground ? 'text-neutral-800' : 'text-white';
                        
                        if (showPct >= 7) {
                            displayText = seg.percentage + '%';
                        } else if (showPct >= 4) {
                            displayText = Math.round(showPct) + '%';
                            fontSize = 'text-[10px]';
                        }
                        
                        return `
                        <div class="flex items-center justify-center ${fontSize} font-semibold relative group ${textColor}" 
                             style="width: ${seg.percentage}%; background-color: ${assignedColor};"
                             title="${seg.label}: ${seg.value} responses (${seg.percentage}%)">
                            <span class="relative z-10">${displayText}</span>
                            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 hidden group-hover:block text-xs rounded py-1 px-2 whitespace-nowrap z-50 bg-neutral-800 text-white">
                                ${seg.label}<br>
                                Count: ${seg.value}<br>
                                ${seg.percentage}%
                            </div>
                        </div>
                        `;
                    }).join('')}
                </div>
            </div>
        </div>
        `;
    }).join('');
}

// Export functions (stubbed for future implementation)
function exportToExcel() {
    alert('Export to Excel functionality - Would export question statistics data to Excel format');
}

function exportToCSV() {
    alert('Export to CSV functionality - Would export question statistics data to CSV format');
}

function exportToPDF() {
    alert('Export to PDF functionality - Would export question statistics data to PDF format');
}

// Initialize dashboard when DOM is ready
function initDashboard(dashboardData) {
    if (typeof Chart === 'undefined') {
        console.error('Chart.js is not loaded');
        return;
    }
    
    initializeCharts(dashboardData);
    loadQuestionStatistics(dashboardData);
}

// Expose functions globally for inline event handlers
window.changeQuestionPage = changeQuestionPage;
window.exportToExcel = exportToExcel;
window.exportToCSV = exportToCSV;
window.exportToPDF = exportToPDF;

// Auto-initialize when DOM and data are ready
document.addEventListener('DOMContentLoaded', function() {
    // Wait for dashboardData to be available (set by inline script)
    if (typeof window.dashboardData !== 'undefined') {
        initDashboard(window.dashboardData);
    } else {
        // Fallback: wait a bit longer if data isn't ready yet
        setTimeout(function() {
            if (typeof window.dashboardData !== 'undefined') {
                initDashboard(window.dashboardData);
            } else {
                console.error('Dashboard data not found');
            }
        }, 100);
    }
});

