const CHART_COLORS = ['#6366f1', '#818cf8', '#a5b4fc', '#c7d2fe'];
const PROGRAM_COLORS = ['#6366f1', '#818cf8', '#a5b4fc', '#c7d2fe', '#e0e7ff', '#ddd6fe', '#f3e8ff', '#fce7f3', '#fef3c7', '#ecfccb'];
const GENDER_COLORS = ['#6366f1', '#c7d2fe', '#a5b4fc'];
const YEAR_COLORS = ['#6366f1', '#818cf8', '#a5b4fc', '#c7d2fe', '#e0e7ff'];

function formatTimestamp() {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    return `${year}-${month}-${day}_${hours}-${minutes}-${seconds}`;
}

function formatDate(dateString) {
    if (!dateString) return 'All Time';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
}

function hexToRgb(hex) {
    const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result ? {
        r: parseInt(result[1], 16),
        g: parseInt(result[2], 16),
        b: parseInt(result[3], 16)
    } : null;
}

function processDistributionData(distribution) {
    return Object.keys(distribution)
        .filter(key => key && key.trim() !== '')
        .map(key => [key, distribution[key]]);
}

function exportToExcel() {
    const data = window.dashboardData;
    if (!data) {
        alert('Dashboard data not available');
        return;
    }
    
    const workbook = XLSX.utils.book_new();
    const summaryData = [
        ['Burnout Analytics Dashboard Export'],
        ['Generated on: ' + new Date().toLocaleString()],
        [],
        ['Burnout Categories Summary'],
        ['Category', 'Count'],
        ['High Burnout', data.highBurnout || 0],
        ['Exhausted', data.exhaustion || 0],
        ['Disengaged', data.disengagement || 0],
        ['Low Burnout', data.lowBurnout || 0],
        ['Total Assessments', (data.highBurnout || 0) + (data.exhaustion || 0) + (data.disengagement || 0) + (data.lowBurnout || 0)],
        [],
        ['Age Distribution'],
        ['Age Group', 'Count'],
        ...processDistributionData(data.ageDistribution || {}),
        [],
        ['Gender Distribution'],
        ['Gender', 'Count'],
        ...processDistributionData(data.genderDistribution || {}),
        [],
        ['Year Level Distribution'],
        ['Year Level', 'Count'],
        ...processDistributionData(data.yearDistribution || {}),
        [],
        ['Program Distribution'],
        ['Program', 'Count'],
        ...processDistributionData(data.programDistribution || {})
    ];
    
    const summarySheet = XLSX.utils.aoa_to_sheet(summaryData);
    XLSX.utils.book_append_sheet(workbook, summarySheet, 'Summary');
    
    const featureImportance = data.featureImportance || {};
    const questionsList = data.questionsList || [];
    const featureData = [
        ['Feature Importance'],
        ['Model Feature Importance Analysis'],
        ['Shows the relative importance of each question (Q1-Q30) in predicting burnout categories'],
        [],
        ['Question', 'Feature', 'Importance', 'Importance (%)', 'Question Text'],
        ...Object.keys(featureImportance).map(feature => {
            const qNum = parseInt(feature.replace('Q', ''));
            const questionText = questionsList[qNum - 1] || `Question ${qNum}`;
            const importance = featureImportance[feature];
            const totalImportance = Object.values(featureImportance).reduce((a, b) => a + b, 0);
            const importancePercent = totalImportance > 0 ? ((importance / totalImportance) * 100).toFixed(4) : '0.0000';
            return [
                `Q${qNum}`,
                feature,
                importance.toFixed(6),
                importancePercent + '%',
                questionText
            ];
        })
    ];
    
    const featureSheet = XLSX.utils.aoa_to_sheet(featureData);
    XLSX.utils.book_append_sheet(workbook, featureSheet, 'Feature Importance');
    
    XLSX.writeFile(workbook, `Dashboard_Export_${formatTimestamp()}.xlsx`);
}

async function exportToPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('p', 'mm', 'a4');
    const data = window.dashboardData;
    const charts = window.dashboardCharts;
    
    if (!data) {
        alert('Dashboard data not available');
        return;
    }
    
    const pageWidth = doc.internal.pageSize.width;
    const pageHeight = doc.internal.pageSize.height;
    const margin = 20;
    const indigoRgb = hexToRgb('#6366f1');
    
    function checkPageBreak(neededSpace = 10) {
        if (yPos + neededSpace > pageHeight - margin) {
            doc.addPage();
            yPos = 20;
            return true;
        }
        return false;
    }
    
    function addChartImage(chart, xPos, yPos, width, height) {
        if (chart && typeof chart.toBase64Image === 'function') {
            try {
                doc.addImage(chart.toBase64Image(), 'PNG', xPos, yPos, width, height);
                return true;
            } catch (e) {
                console.error('Failed to add chart:', e);
                return false;
            }
        }
        return false;
    }
    
    // ============================================
    // PAGE 1: HEADER AND KEY METRICS
    // ============================================
    let yPos = 0;
    
    // Header with indigo background
    if (indigoRgb) {
        doc.setFillColor(indigoRgb.r, indigoRgb.g, indigoRgb.b);
        doc.rect(0, yPos, pageWidth, 40, 'F');
    }
    
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(20);
    doc.setFont(undefined, 'bold');
    doc.text('BURNALYTICS ASSESSMENT REPORT', pageWidth / 2, 15, { align: 'center' });
    
    doc.setFontSize(12);
    doc.setFont(undefined, 'normal');
    doc.text('Academic Burnout Analysis Dashboard', pageWidth / 2, 22, { align: 'center' });
    
    doc.setFontSize(10);
    const reportDate = new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
    doc.text(`Report Date: ${reportDate}`, pageWidth / 2, 28, { align: 'center' });
    
    const dateFrom = data.dateFrom ? formatDate(data.dateFrom) : 'All Time';
    const dateTo = data.dateTo ? formatDate(data.dateTo) : 'Present';
    doc.text(`Report Period: ${dateFrom} - ${dateTo}`, pageWidth / 2, 33, { align: 'center' });
    
    yPos = 50;
    
    // Section 1: Key Metrics
    const totalAssessments = data.totalAssessments || 0;
    const highBurnout = data.highBurnout || 0;
    const exhaustion = data.exhaustion || 0;
    const disengagement = data.disengagement || 0;
    const lowBurnout = data.lowBurnout || 0;
    const atRisk = highBurnout + exhaustion + disengagement;
    
    const highPercent = totalAssessments > 0 ? ((highBurnout / totalAssessments) * 100).toFixed(1) : '0.0';
    const exhaustedPercent = totalAssessments > 0 ? ((exhaustion / totalAssessments) * 100).toFixed(1) : '0.0';
    const disengagedPercent = totalAssessments > 0 ? ((disengagement / totalAssessments) * 100).toFixed(1) : '0.0';
    const lowPercent = totalAssessments > 0 ? ((lowBurnout / totalAssessments) * 100).toFixed(1) : '0.0';
    const atRiskPercent = totalAssessments > 0 ? ((atRisk / totalAssessments) * 100).toFixed(1) : '0.0';
    
    // Section 1: Key Metrics Table
    doc.setTextColor(0, 0, 0);
    const metricsTableX = margin;
    const metricsColWidths = [80, 50];
    // Reduce spacing above table
    const metricsTableY = yPos - 5;
    
    // Get breakdown data for summary
    const programBreakdown = data.programBreakdown || {};
    const genderBreakdown = data.genderBreakdown || {};
    const yearBreakdown = data.yearBreakdown || {};
    const ageBreakdown = data.ageBreakdown || {};
    
    // Find highest counts for summary
    let highestProgramCount = 0;
    let highestProgramName = 'N/A';
    Object.keys(programBreakdown).forEach(program => {
        if (programBreakdown[program].total > highestProgramCount) {
            highestProgramCount = programBreakdown[program].total;
            highestProgramName = program;
        }
    });
    
    let highestGenderCount = 0;
    let highestGenderName = 'N/A';
    Object.keys(genderBreakdown).forEach(gender => {
        if (genderBreakdown[gender].total > highestGenderCount) {
            highestGenderCount = genderBreakdown[gender].total;
            highestGenderName = gender;
        }
    });
    
    let highestYearCount = 0;
    let highestYearName = 'N/A';
    Object.keys(yearBreakdown).forEach(year => {
        if (yearBreakdown[year].total > highestYearCount) {
            highestYearCount = yearBreakdown[year].total;
            highestYearName = year;
        }
    });
    
    let highestAgeCount = 0;
    let highestAgeName = 'N/A';
    Object.keys(ageBreakdown).forEach(ageGroup => {
        if (ageBreakdown[ageGroup].total > highestAgeCount) {
            highestAgeCount = ageBreakdown[ageGroup].total;
            highestAgeName = ageGroup;
        }
    });
    
    // Table header
    doc.setFontSize(12);
    doc.setFont(undefined, 'bold');
    doc.setFillColor(240, 240, 240);
    doc.rect(metricsTableX, metricsTableY, metricsColWidths.reduce((a, b) => a + b, 0), 8, 'F');
    doc.text('Category', metricsTableX + 2, metricsTableY + 6);
    doc.text('Count', metricsTableX + metricsColWidths[0] + 2, metricsTableY + 6);
    
    // Add spacing below header
    yPos = metricsTableY + 8 + 4;
    
    // Table rows (no lines between rows)
    doc.setFontSize(11);
    const metricsRows = [
        ['TOTAL ASSESSMENTS', totalAssessments.toString()],
        ['HIGH BURNOUT', `${highBurnout} (${highPercent}%)`],
        ['EXHAUSTED', `${exhaustion} (${exhaustedPercent}%)`],
        ['DISENGAGED', `${disengagement} (${disengagedPercent}%)`],
        ['LOW BURNOUT', `${lowBurnout} (${lowPercent}%)`],
        ['AT-RISK STUDENTS', `${atRisk} (${atRiskPercent}%)`],
        ['PROGRAM', `${highestProgramName} (${highestProgramCount})`],
        ['YEAR LEVEL', `${highestYearName} (${highestYearCount})`],
        ['AGE', `${highestAgeName} (${highestAgeCount})`],
        ['GENDER', `${highestGenderName} (${highestGenderCount})`]
    ];
    
    metricsRows.forEach((row, idx) => {
        if (idx === 0) {
            doc.setFont(undefined, 'bold');
            doc.setFontSize(12);
        } else {
            doc.setFont(undefined, 'bold');
            doc.setFontSize(11);
        }
        doc.text(row[0], metricsTableX + 2, yPos);
        doc.setFont(undefined, 'normal');
        doc.text(row[1], metricsTableX + metricsColWidths[0] + 2, yPos);
    yPos += 7;
    });
    
    yPos += 10;
    
    // ============================================
    // DEMOGRAPHIC OVERVIEW (on first page)
    // ============================================
    doc.setFontSize(16);
    doc.setFont(undefined, 'bold');
    doc.text('DEMOGRAPHIC OVERVIEW', pageWidth / 2, yPos, { align: 'center' });
    yPos += 12;
    
    const chartSize = 40; // Reduced from 50
    const chartSpacing = 10;
    const leftChartX = margin;
    const rightChartX = pageWidth / 2 + 5;
    
    // Row 1: Burnout Categories and Gender
    doc.setFontSize(10);
    doc.setFont(undefined, 'bold');
    doc.text('Burnout Categories', leftChartX + chartSize / 2, yPos, { align: 'center' });
    doc.text('Gender Distribution', rightChartX + chartSize / 2, yPos, { align: 'center' });
    yPos += 5;
    
    const chartY1 = yPos;
    if (addChartImage(charts.burnoutChart, leftChartX, chartY1, chartSize, chartSize)) {
        const burnoutLabels = ['High', 'Exhausted', 'Disengaged', 'Low'];
        const burnoutCounts = [highBurnout, exhaustion, disengagement, lowBurnout];
        const burnoutTotal = burnoutCounts.reduce((a, b) => a + b, 0);
        doc.setFontSize(10);
        doc.setFont(undefined, 'normal');
        let legendY = chartY1 + chartSize + 3;
        burnoutLabels.forEach((label, idx) => {
            const count = burnoutCounts[idx];
            const percent = burnoutTotal > 0 ? ((count / burnoutTotal) * 100).toFixed(1) : '0.0';
            doc.text(`${label}: ${count} (${percent}%)`, leftChartX + 2, legendY);
            legendY += 5;
        });
    }
    
    if (addChartImage(charts.genderChart, rightChartX, chartY1, chartSize, chartSize)) {
        const genderDist = data.genderDistribution || {};
        const genderKeys = Object.keys(genderDist).filter(k => k && k.trim() !== '');
        const genderTotal = Object.values(genderDist).reduce((a, b) => a + b, 0);
        doc.setFontSize(10);
            doc.setFont(undefined, 'normal');
        let legendY = chartY1 + chartSize + 3;
        genderKeys.forEach(key => {
            const count = genderDist[key];
            const percent = genderTotal > 0 ? ((count / genderTotal) * 100).toFixed(1) : '0.0';
            doc.text(`${key}: ${count} (${percent}%)`, rightChartX + 2, legendY);
            legendY += 5;
        });
    }
    
    yPos = chartY1 + chartSize + 25;
    
    // Row 2: Year Level and Age
    doc.setFontSize(10);
    doc.setFont(undefined, 'bold');
    doc.text('Year Level', leftChartX + chartSize / 2, yPos, { align: 'center' });
    doc.text('Age Distribution', rightChartX + chartSize / 2, yPos, { align: 'center' });
    yPos += 5;
    
    const chartY2 = yPos;
    if (addChartImage(charts.yearChart, leftChartX, chartY2, chartSize, chartSize)) {
        const yearDist = data.yearDistribution || {};
        const yearKeys = Object.keys(yearDist).filter(k => k && k.trim() !== '');
        const yearTotal = Object.values(yearDist).reduce((a, b) => a + b, 0);
        doc.setFontSize(10);
            doc.setFont(undefined, 'normal');
        let legendY = chartY2 + chartSize + 3;
        yearKeys.forEach(key => {
            const count = yearDist[key];
            const percent = yearTotal > 0 ? ((count / yearTotal) * 100).toFixed(1) : '0.0';
            doc.text(`${key}: ${count} (${percent}%)`, leftChartX + 2, legendY);
            legendY += 5;
        });
    }
    
    if (addChartImage(charts.ageChart, rightChartX, chartY2, chartSize, chartSize)) {
        const ageDist = data.ageDistribution || {};
        const ageKeys = Object.keys(ageDist).filter(k => k && k.trim() !== '');
        const ageTotal = Object.values(ageDist).reduce((a, b) => a + b, 0);
        doc.setFontSize(10);
            doc.setFont(undefined, 'normal');
        let legendY = chartY2 + chartSize + 3;
        ageKeys.forEach(key => {
            const count = ageDist[key];
            const percent = ageTotal > 0 ? ((count / ageTotal) * 100).toFixed(1) : '0.0';
            doc.text(`${key}: ${count} (${percent}%)`, rightChartX + 2, legendY);
            legendY += 5;
        });
    }
    
    // ============================================
    // PAGE 4: PROGRAM BREAKDOWN
    // ============================================
    doc.addPage();
    yPos = 20;
    
    doc.setFontSize(16);
    doc.setFont(undefined, 'bold');
    doc.text('PROGRAM BREAKDOWN', pageWidth / 2, yPos, { align: 'center' });
    yPos += 15;
    
    // Program Breakdown Table
    doc.setFontSize(9);
    doc.setFont(undefined, 'bold');
    const tableStartX = margin;
    const colWidths = [70, 18, 18, 18, 18, 18];
    let tableX = tableStartX;
    
    // Header
    doc.setFillColor(240, 240, 240);
    doc.rect(tableX, yPos - 5, colWidths.reduce((a, b) => a + b, 0), 8, 'F');
    doc.text('College/Program', tableX + 2, yPos);
    tableX += colWidths[0];
    doc.text('Total', tableX + 2, yPos);
    tableX += colWidths[1];
    doc.text('High', tableX + 2, yPos);
    tableX += colWidths[2];
    doc.text('Exhaus', tableX + 2, yPos);
    tableX += colWidths[3];
    doc.text('Diseng', tableX + 2, yPos);
    tableX += colWidths[4];
    doc.text('Low', tableX + 2, yPos);
    
    yPos += 10;
    tableX = tableStartX;
    doc.setDrawColor(200, 200, 200);
    doc.line(tableX, yPos, tableX + colWidths.reduce((a, b) => a + b, 0), yPos);
    yPos += 5;
    
    // Table rows
    doc.setFont(undefined, 'normal');
    doc.setFontSize(8);
    const programs = Object.keys(programBreakdown).sort();
    programs.forEach(program => {
        checkPageBreak(8);
        const breakdown = programBreakdown[program];
        tableX = tableStartX;
        
        // Program name (truncate if too long)
        let programName = program.length > 30 ? program.substring(0, 27) + '...' : program;
        doc.text(programName, tableX + 2, yPos);
        tableX += colWidths[0];
        
        // Total
        doc.text(breakdown.total.toString(), tableX + 2, yPos);
        tableX += colWidths[1];
        
        // Percentages
        const highPct = breakdown.total > 0 ? ((breakdown.high / breakdown.total) * 100).toFixed(1) : '0.0';
        const exhPct = breakdown.total > 0 ? ((breakdown.exhausted / breakdown.total) * 100).toFixed(1) : '0.0';
        const disPct = breakdown.total > 0 ? ((breakdown.disengaged / breakdown.total) * 100).toFixed(1) : '0.0';
        const lowPct = breakdown.total > 0 ? ((breakdown.low / breakdown.total) * 100).toFixed(1) : '0.0';
        
        doc.text(highPct + '%', tableX + 2, yPos);
        tableX += colWidths[2];
        doc.text(exhPct + '%', tableX + 2, yPos);
        tableX += colWidths[3];
        doc.text(disPct + '%', tableX + 2, yPos);
        tableX += colWidths[4];
        doc.text(lowPct + '%', tableX + 2, yPos);
        
        yPos += 9;
        doc.line(tableStartX, yPos - 2, tableStartX + colWidths.reduce((a, b) => a + b, 0), yPos - 2);
    });
    
    yPos += 10;
    
    // High-Risk Programs Alert (Interpretations)
    doc.setFontSize(11);
    doc.setFont(undefined, 'bold');
    doc.text('Interpretations:', margin, yPos);
    yPos += 8;
    
    // Sort programs by high burnout rate
    const highRiskPrograms = programs
        .map(program => ({
            name: program,
            breakdown: programBreakdown[program],
            rate: programBreakdown[program].total > 0 
                ? (programBreakdown[program].high / programBreakdown[program].total) * 100 
                : 0
        }))
        .filter(p => p.breakdown.high > 0)
        .sort((a, b) => b.rate - a.rate)
        .slice(0, 3);
    
    doc.setFontSize(9);
    doc.setFont(undefined, 'normal');
    highRiskPrograms.forEach((program, idx) => {
        doc.text(`${idx + 1}. ${program.name} - ${program.rate.toFixed(1)}% High Burnout (${program.breakdown.high} students)`, margin, yPos);
        yPos += 6;
    });
    
    yPos += 10;
    
    // Section: Findings (moved here, below Interpretations)
    // Find highest burnout program
    let highestProgram = null;
    let highestProgramRate = 0;
    Object.keys(programBreakdown).forEach(program => {
        const breakdown = programBreakdown[program];
        if (breakdown.total > 0) {
            const highRate = (breakdown.high / breakdown.total) * 100;
            if (highRate > highestProgramRate) {
                highestProgramRate = highRate;
                highestProgram = { name: program, rate: highRate, count: breakdown.high };
            }
        }
    });
    
    // Find highest burnout gender
    let highestGender = null;
    let highestGenderRate = 0;
    Object.keys(genderBreakdown).forEach(gender => {
        const breakdown = genderBreakdown[gender];
        if (breakdown.total > 0) {
            const highRate = (breakdown.high / breakdown.total) * 100;
            if (highRate > highestGenderRate) {
                highestGenderRate = highRate;
                highestGender = { name: gender, rate: highRate, count: breakdown.high };
            }
        }
    });
    
    // Find highest burnout year level
    let highestYear = null;
    let highestYearRate = 0;
    Object.keys(yearBreakdown).forEach(year => {
        const breakdown = yearBreakdown[year];
        if (breakdown.total > 0) {
            const highRate = (breakdown.high / breakdown.total) * 100;
            if (highRate > highestYearRate) {
                highestYearRate = highRate;
                highestYear = { name: year, rate: highRate, count: breakdown.high };
            }
        }
    });
    
    // Find highest burnout age group
    let highestAge = null;
    let highestAgeRate = 0;
    Object.keys(ageBreakdown).forEach(ageGroup => {
        const breakdown = ageBreakdown[ageGroup];
        if (breakdown.total > 0) {
            const highRate = (breakdown.high / breakdown.total) * 100;
            if (highRate > highestAgeRate) {
                highestAgeRate = highRate;
                highestAge = { name: ageGroup, rate: highRate, count: breakdown.high };
            }
        }
    });
    
    // Calculate findings box height based on content
    let findingsLines = 2; // Base lines
    if (highestProgram) findingsLines++;
    if (highestGender) findingsLines++;
    if (highestYear) findingsLines++;
    if (highestAge) findingsLines++;
    const findingsBoxHeight = 8 + (findingsLines * 7) + 5;
    
    doc.setDrawColor(200, 200, 200);
    doc.setFillColor(255, 245, 245);
    doc.roundedRect(margin, yPos, pageWidth - (margin * 2), findingsBoxHeight, 3, 3, 'FD');
    
    doc.setFontSize(11);
    doc.setFont(undefined, 'bold');
    doc.setTextColor(200, 0, 0);
    doc.text('FINDINGS:', margin + 5, yPos + 8);
    
    doc.setFontSize(9);
    doc.setFont(undefined, 'normal');
    doc.setTextColor(0, 0, 0);
    let findingsY = yPos + 15;
    doc.text(`- ${highPercent}% of students show HIGH BURNOUT`, margin + 5, findingsY);
    findingsY += 7;
    doc.text(`- Immediate intervention needed for ${highBurnout} students`, margin + 5, findingsY);
    findingsY += 7;
    
    if (highestProgram) {
        doc.text(`- ${highestProgram.name} has highest burnout rate at ${highestProgram.rate.toFixed(1)}%`, margin + 5, findingsY);
        findingsY += 7;
    }
    
    if (highestGender) {
        doc.text(`- ${highestGender.name} has highest burnout rate at ${highestGender.rate.toFixed(1)}%`, margin + 5, findingsY);
        findingsY += 7;
    }
    
    if (highestYear) {
        doc.text(`- ${highestYear.name} Years has highest burnout rate at ${highestYear.rate.toFixed(1)}%`, margin + 5, findingsY);
        findingsY += 7;
    }
    
    if (highestAge) {
        doc.text(`- Age ${highestAge.name} has highest burnout rate at ${highestAge.rate.toFixed(1)}%`, margin + 5, findingsY);
    }
    
    yPos += findingsBoxHeight + 5;
    
    // ============================================
    // PAGE 5: FEATURE IMPORTANCE ANALYSIS
    // ============================================
    // Check if we need a new page, otherwise continue on current page
    checkPageBreak(100);
    if (yPos > pageHeight - 150) {
    doc.addPage();
    yPos = 20;
    } else {
        yPos += 10;
    }
    
    doc.setFontSize(16);
    doc.setFont(undefined, 'bold');
    doc.text('FEATURE IMPORTANCE ANALYSIS', pageWidth / 2, yPos, { align: 'center' });
    yPos += 10;
    
    doc.setFontSize(12);
    doc.setFont(undefined, 'bold');
    doc.text('Top 10 Questions Predicting Burnout', pageWidth / 2, yPos, { align: 'center' });
    yPos += 10;
    
    const featureImportance = data.featureImportance || {};
    const questionsList = data.questionsList || [];
    const features = Object.keys(featureImportance)
        .sort((a, b) => featureImportance[b] - featureImportance[a])
        .slice(0, 10);
    
    const maxImportance = Math.max(...Object.values(featureImportance));
    const barStartX = margin + 50;
    const barWidth = pageWidth - barStartX - margin;
    const barHeight = 5;
    const rowHeight = 8;
    
    if (features.length === 0) {
        doc.setFontSize(10);
        doc.text('Feature importance data not available.', margin, yPos);
    } else {
        doc.setFontSize(8);
        doc.setFont(undefined, 'bold');
        doc.text('Feature', margin, yPos);
        doc.text('Importance', barStartX, yPos);
        yPos += 6;
        
        doc.setDrawColor(200, 200, 200);
        doc.line(margin, yPos, pageWidth - margin, yPos);
        yPos += 3;
        
        features.forEach((feature, idx) => {
            checkPageBreak(rowHeight + 3);
        
        const importance = featureImportance[feature];
        const normalizedImportance = (importance / maxImportance) * 100;
        const qNum = parseInt(feature.replace('Q', ''));
        const questionText = questionsList[qNum - 1] || `Question ${qNum}`;
            const truncatedText = questionText.length > 40 ? questionText.substring(0, 40) + '...' : questionText;
        
            doc.setFontSize(7);
        doc.setFont(undefined, 'normal');
            doc.text(`${feature}: ${truncatedText}`, margin, yPos + 3);
        
            if (indigoRgb) {
                doc.setFillColor(indigoRgb.r, indigoRgb.g, indigoRgb.b);
        const segmentWidth = (normalizedImportance / 100) * barWidth;
            doc.rect(barStartX, yPos, segmentWidth, barHeight, 'F');
        }
            
        doc.setDrawColor(200, 200, 200);
        doc.rect(barStartX, yPos, barWidth, barHeight, 'S');
        
        doc.setFontSize(7);
            doc.text(`${(importance * 100).toFixed(4)}%`, barStartX + barWidth + 3, yPos + 3);
        
        yPos += rowHeight;
    });
    }
    
    // ============================================
    // FOOTER
    // ============================================
    // Check if we need a new page for footer
    checkPageBreak(50);
    if (yPos > pageHeight - 60) {
    doc.addPage();
        yPos = 20;
    } else {
        yPos += 15;
    }
    
    doc.setDrawColor(100, 100, 100);
    doc.setLineWidth(0.5);
    doc.line(margin, yPos, pageWidth - margin, yPos);
    
    yPos += 8;
    doc.setFontSize(9);
    doc.setFont(undefined, 'normal');
    doc.setTextColor(100, 100, 100);
    doc.text('This report contains confidential student data protected under:', pageWidth / 2, yPos, { align: 'center' });
    yPos += 6;
    doc.text('Republic Act 10173 (Data Privacy Act of 2012)', pageWidth / 2, yPos, { align: 'center' });
    yPos += 6;
    doc.text('All student identifiers are anonymized to protect privacy.', pageWidth / 2, yPos, { align: 'center' });
    
    yPos += 10;
    doc.line(margin, yPos, pageWidth - margin, yPos);
    
    yPos += 8;
    doc.setFontSize(10);
    doc.setFont(undefined, 'bold');
    doc.setTextColor(0, 0, 0);
    doc.text('Guidance Counseling Office', pageWidth / 2, yPos, { align: 'center' });
    yPos += 6;
    doc.text('Pamantasan ng Lungsod ng Pasig', pageWidth / 2, yPos, { align: 'center' });
    yPos += 6;
    doc.setFont(undefined, 'normal');
    doc.setFontSize(9);
    doc.text(`Generated: ${formatTimestamp().replace('_', ' ').replace(/-/g, ':')}`, pageWidth / 2, yPos, { align: 'center' });
    
    yPos += 8;
    doc.line(margin, yPos, pageWidth - margin, yPos);
    
    doc.save(`Dashboard_Export_${formatTimestamp()}.pdf`);
}

window.exportToExcel = exportToExcel;
window.exportToPDF = exportToPDF;
