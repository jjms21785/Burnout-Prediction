window.dashboardCharts = {
    burnoutChart: null,
    ageChart: null,
    genderChart: null,
    yearChart: null,
    programChart: null
};

const CHART_COLORS = ['#6366f1', '#818cf8', '#a5b4fc', '#c7d2fe'];
const PROGRAM_COLORS = ['#6366f1', '#818cf8', '#a5b4fc', '#c7d2fe', '#e0e7ff', '#ddd6fe', '#f3e8ff', '#fce7f3', '#fef3c7', '#ecfccb'];
const GENDER_COLORS = ['#6366f1', '#c7d2fe', '#a5b4fc'];
const YEAR_COLORS = ['#6366f1', '#818cf8', '#a5b4fc', '#c7d2fe', '#e0e7ff'];

function createChartConfig(showLegend = true) {
    return {
        responsive: true,
        maintainAspectRatio: true,
        aspectRatio: 1,
        plugins: {
            legend: {
                display: showLegend,
                position: 'bottom',
                labels: { 
                    font: { size: 12 }, 
                    padding: 10,
                    boxWidth: 15
                }
            },
            tooltip: { enabled: true },
            datalabels: { display: false }
        }
    };
}

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

function processDistributionData(distribution, defaultColors, maxItems = null) {
    const keys = Object.keys(distribution).filter(key => key && key.trim() !== '');
    const hasData = keys.length > 0 && keys.some(key => distribution[key] > 0);
    
    if (!hasData) {
        return { labels: ['No Data'], values: [1], hasData: false };
    }
    
    let filteredKeys = keys.filter(key => key.trim() !== '' && distribution[key] > 0);
    if (maxItems) filteredKeys = filteredKeys.slice(0, maxItems);
    
    return {
        labels: filteredKeys,
        values: filteredKeys.map(key => parseInt(distribution[key]) || 0),
        hasData: true
    };
}

function initializeCharts(dashboardData) {
    const burnoutLabels = ['High Burnout', 'Exhausted', 'Disengaged', 'Low Burnout'];
    const burnoutData = [
        dashboardData.highBurnout || 0,
        dashboardData.exhaustion || 0,
        dashboardData.disengagement || 0,
        dashboardData.lowBurnout || 0
    ];
    window.dashboardCharts.burnoutChart = createDoughnutChart('burnoutChart', burnoutLabels, burnoutData, CHART_COLORS);

    const ageData = processDistributionData(dashboardData.ageDistribution || {}, CHART_COLORS);
    window.dashboardCharts.ageChart = createDoughnutChart('ageChart', ageData.labels, ageData.values, CHART_COLORS);

    const genderData = processDistributionData(dashboardData.genderDistribution || {}, GENDER_COLORS);
    window.dashboardCharts.genderChart = createDoughnutChart('genderChart', genderData.labels, genderData.values, GENDER_COLORS);

    const programData = processDistributionData(dashboardData.programDistribution || {}, PROGRAM_COLORS, 10);
    const programCanvas = document.getElementById('programChart');
    if (programCanvas) {
        window.dashboardCharts.programChart = new Chart(programCanvas, {
            type: 'doughnut',
            data: {
                labels: programData.labels,
                datasets: [{
                    data: programData.values,
                    backgroundColor: PROGRAM_COLORS
                }]
            },
            options: createChartConfig(false)
        });
    }
    
    const programLegend = document.getElementById('programLegend');
    if (programLegend) {
        if (programData.hasData && programData.labels.length > 0) {
            programLegend.innerHTML = programData.labels.map((label, index) => {
                const color = PROGRAM_COLORS[index] || '#6366f1';
                return `<div class="flex items-center py-1.5 mb-1">
                    <div class="w-3 h-3 rounded-full mr-2" style="background-color: ${color};"></div>
                    <span class="text-xs text-neutral-800">${label}</span>
                </div>`;
            }).join('');
        } else {
            programLegend.innerHTML = '<p class="text-xs text-center text-gray-500">No Data</p>';
        }
    }

    const yearData = processDistributionData(dashboardData.yearDistribution || {}, YEAR_COLORS);
    window.dashboardCharts.yearChart = createDoughnutChart('yearChart', yearData.labels, yearData.values, YEAR_COLORS);
}

function mapAnswerToResponseIndex(answer, questionIndex) {
    if (questionIndex >= 14) {
        if (answer >= 1 && answer <= 4) return answer - 1;
        if (answer >= 0 && answer <= 3) return answer;
    } else {
        if (answer >= 1 && answer <= 5) {
            if (answer <= 2) return 0;
            if (answer === 3) return 1;
            if (answer === 4) return 2;
            return 3;
        }
        if (answer >= 0 && answer <= 3) return answer;
    }
    return null;
}

function processQuestionStatistics(questionStats, questionsList) {
    const questions = questionsList.slice(0, 30);
    return questions.map((q, idx) => {
        const responses = [0, 0, 0, 0];
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

let currentQuestionPage = 1;
const QUESTIONS_PER_PAGE = 10;
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
    window.allQuestions = allQuestions;
    renderQuestionsPage();
}

function renderQuestionsPage() {
    const startIdx = (currentQuestionPage - 1) * QUESTIONS_PER_PAGE;
    const pageQuestions = allQuestions.slice(startIdx, startIdx + QUESTIONS_PER_PAGE);
    renderStackedBarQuestions('questionsList', pageQuestions);
    updatePaginationButtons();
}

function changeQuestionPage(direction) {
    const totalPages = Math.ceil(allQuestions.length / QUESTIONS_PER_PAGE);
    currentQuestionPage = Math.max(1, Math.min(totalPages, currentQuestionPage + direction));
    renderQuestionsPage();
}

function updatePaginationButtons() {
    const totalPages = Math.ceil(allQuestions.length / QUESTIONS_PER_PAGE);
    const prevBtn = document.getElementById('prevPageBtn');
    const nextBtn = document.getElementById('nextPageBtn');
    if (prevBtn) prevBtn.disabled = currentQuestionPage === 1;
    if (nextBtn) nextBtn.disabled = currentQuestionPage === totalPages;
}

const RESPONSE_LABELS = ['Strongly Agree', 'Agree', 'Disagree', 'Strongly Disagree'];
const RESPONSE_COLORS = ['#6366f1', '#818cf8', '#a5b4fc', '#c7d2fe'];

function renderStackedBarQuestions(containerId, questions) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    container.innerHTML = questions.map(q => {
        const values = q.responses || [0, 0, 0, 0];
        const percentages = q.percentages || [0, 0, 0, 0];
        const total = values.reduce((a, b) => a + b, 0);
        
        const segments = values.map((v, i) => ({
            value: v,
            percentage: total > 0 ? parseFloat(percentages[i] || 0).toFixed(1) : '0.0',
            label: RESPONSE_LABELS[i],
            index: i
        }));
        
        const sortedSegments = segments.slice().sort((a, b) => parseFloat(b.percentage) - parseFloat(a.percentage));
        const colorMap = {};
        sortedSegments.forEach((seg, idx) => {
            colorMap[seg.index] = RESPONSE_COLORS[idx];
        });
        
        return `<div class="grid grid-cols-11 gap-4 items-center">
            <div class="col-span-5 text-sm pr-4 text-neutral-800">
                <span class="font-semibold text-neutral-800">${q.id}.</span> ${q.text}
            </div>
            <div class="col-span-6 relative h-10 rounded-lg overflow-hidden shadow-sm bg-gray-50">
                <div class="absolute inset-0 flex">
                    ${segments.map((seg, i) => {
                        const showPct = parseFloat(seg.percentage);
                        const assignedColor = colorMap[i];
                        const isLightBackground = assignedColor === '#a5b4fc' || assignedColor === '#c7d2fe';
                        const textColor = isLightBackground ? 'text-neutral-800' : 'text-white';
                        let displayText = '';
                        let fontSize = 'text-xs';
                        
                        if (showPct >= 7) {
                            displayText = seg.percentage + '%';
                        } else if (showPct >= 4) {
                            displayText = Math.round(showPct) + '%';
                            fontSize = 'text-[10px]';
                        }
                        
                        return `<div class="flex items-center justify-center ${fontSize} font-semibold relative group ${textColor}" 
                             style="width: ${seg.percentage}%; background-color: ${assignedColor};"
                             title="${seg.label}: ${seg.value} responses (${seg.percentage}%)">
                            <span class="relative z-10">${displayText}</span>
                            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 hidden group-hover:block text-xs rounded py-1 px-2 whitespace-nowrap z-50 bg-neutral-800 text-white">
                                ${seg.label}<br>
                                Count: ${seg.value}<br>
                                ${seg.percentage}%
                            </div>
                        </div>`;
                    }).join('')}
                </div>
            </div>
        </div>`;
    }).join('');
}

function initDashboard(dashboardData) {
    if (typeof Chart === 'undefined') {
        console.error('Chart.js is not loaded');
        return;
    }
    
    initializeCharts(dashboardData);
    loadQuestionStatistics(dashboardData);
}

window.changeQuestionPage = changeQuestionPage;

document.addEventListener('DOMContentLoaded', () => {
    if (typeof window.dashboardData !== 'undefined') {
        initDashboard(window.dashboardData);
    } else {
        const checkData = setInterval(() => {
            if (typeof window.dashboardData !== 'undefined') {
                clearInterval(checkData);
                initDashboard(window.dashboardData);
            }
        }, 50);
        
        setTimeout(() => {
            clearInterval(checkData);
            if (typeof window.dashboardData === 'undefined') {
                console.error('Dashboard data not found');
            }
        }, 1000);
    }
});
