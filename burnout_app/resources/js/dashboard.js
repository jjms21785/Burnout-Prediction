window.dashboardCharts = {
    burnoutChart: null,
    ageChart: null,
    genderChart: null,
    yearChart: null,
    programChart: null,
    featureImportanceChart: null
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

function createFeatureImportanceChart(dashboardData) {
    const canvas = document.getElementById('featureImportanceChart');
    if (!canvas) return null;
    
    const featureImportance = dashboardData.featureImportance || {};
    const questionsList = dashboardData.questionsList || [];
    
    if (Object.keys(featureImportance).length === 0) {
        canvas.parentElement.innerHTML = '<p class="text-sm text-center py-4 text-gray-500">Feature importance data not available. Please train the model first.</p>';
        return null;
    }
    
    const features = Object.keys(featureImportance);
    const importances = features.map(f => featureImportance[f]);
    
    const featureLabels = features.map(f => {
        const qNum = parseInt(f.replace('Q', ''));
        const questionText = questionsList[qNum - 1] || f;
        const truncated = questionText.length > 50 ? questionText.substring(0, 50) + '...' : questionText;
        return `${f}: ${truncated}`;
    });
    
    const maxImportance = Math.max(...importances);
    const normalizedImportances = importances.map(imp => (imp / maxImportance) * 100);
    
    const colors = normalizedImportances.map(imp => {
        if (imp >= 80) return '#6366f1';
        if (imp >= 60) return '#818cf8';
        if (imp >= 40) return '#a5b4fc';
        if (imp >= 20) return '#c7d2fe';
        return '#e0e7ff';
    });
    
    return new Chart(canvas, {
        type: 'bar',
        data: {
            labels: featureLabels,
            datasets: [{
                label: 'Feature Importance',
                data: normalizedImportances,
                backgroundColor: colors,
                borderColor: '#6366f1',
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: true,
            aspectRatio: 1.5,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const feature = features[context.dataIndex];
                            const importance = importances[context.dataIndex];
                            return `${feature}: ${(importance * 100).toFixed(4)}%`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    max: 100,
                    title: {
                        display: true,
                        text: 'Normalized Importance (%)',
                        font: {
                            size: 12,
                            weight: 'bold'
                        }
                    },
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                },
                y: {
                    ticks: {
                        font: {
                            size: 9
                        },
                        crossAlign: 'near',
                        padding: 5
                    }
                }
            }
        }
    });
}

function initDashboard(dashboardData) {
    if (typeof Chart === 'undefined') {
        console.error('Chart.js is not loaded');
        return;
    }
    
    initializeCharts(dashboardData);
    window.dashboardCharts.featureImportanceChart = createFeatureImportanceChart(dashboardData);
}

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
