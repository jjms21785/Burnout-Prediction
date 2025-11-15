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
    
    let yPos = 20;
    const lineHeight = 7;
    const pageHeight = doc.internal.pageSize.height;
    const pageWidth = doc.internal.pageSize.width;
    const margin = 20;
    const smallChartWidth = 60;
    const smallChartHeight = 40;
    
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
    
    function renderChartLegend(doc, labels, counts, x, startY, spacing = 5) {
        doc.setFontSize(9);
        doc.setFont(undefined, 'normal');
        labels.forEach((label, idx) => {
            doc.text(`${label}: ${counts[idx]}`, x, startY + (idx * spacing));
        });
    }
    
    function renderChartLegendWithColors(doc, labels, counts, colors, x, startY, spacing = 6.5) {
        doc.setFontSize(11);
        doc.setFont(undefined, 'normal');
        labels.forEach((label, idx) => {
            const color = colors[idx] || '#6366f1';
            const rgb = hexToRgb(color);
            if (rgb) {
                doc.setFillColor(rgb.r, rgb.g, rgb.b);
                doc.rect(x, startY + (idx * spacing) - 2, 3.5, 3.5, 'F');
            }
            doc.text(`${label}: ${counts[idx]}`, x + 8, startY + (idx * spacing));
        });
    }
    
    doc.setFontSize(18);
    doc.setFont(undefined, 'bold');
    doc.text('Burnout Analytics Dashboard', pageWidth / 2, yPos, { align: 'center' });
    
    doc.setFontSize(10);
    doc.setFont(undefined, 'normal');
    doc.text(new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }), pageWidth - margin, yPos, { align: 'right' });
    yPos += 15;
    
    const totalAssessments = (data.highBurnout || 0) + (data.exhaustion || 0) + (data.disengagement || 0) + (data.lowBurnout || 0);
    const summaryLeftX = margin;
    const summaryRightX = margin + 90;
    
    let summaryTextY = yPos;
    let summaryChartY = yPos;
    
    doc.setFontSize(12);
    doc.setFont(undefined, 'bold');
    doc.text('Total Assessments', summaryLeftX, summaryTextY);
    summaryTextY += 8;
    
    doc.setFontSize(10);
    doc.setFont(undefined, 'normal');
    doc.text(`${totalAssessments}`, summaryLeftX, summaryTextY);
    summaryTextY += 10;
    
    doc.setFontSize(12);
    doc.setFont(undefined, 'bold');
    doc.text('Total Each Categories', summaryLeftX, summaryTextY);
    summaryTextY += 8;
    
    doc.setFontSize(10);
    doc.setFont(undefined, 'normal');
    const categories = [
        ['High Burnout', data.highBurnout || 0],
        ['Exhausted', data.exhaustion || 0],
        ['Disengaged', data.disengagement || 0],
        ['Low Burnout', data.lowBurnout || 0]
    ];
    categories.forEach(([label, count]) => {
        doc.text(`${label}: ${count}`, summaryLeftX, summaryTextY);
        summaryTextY += lineHeight;
    });
    summaryTextY += 10;
    
    doc.setFontSize(10);
    doc.setFont(undefined, 'bold');
    doc.text('Burnout Categories', summaryRightX + (smallChartWidth / 2), summaryChartY, { align: 'center' });
    const burnoutChartStartY = summaryChartY + 5;
    
    if (addChartImage(charts.burnoutChart, summaryRightX, burnoutChartStartY, smallChartWidth, smallChartHeight)) {
        const burnoutLabels = ['High Burnout', 'Exhausted', 'Disengaged', 'Low Burnout'];
        const burnoutCounts = [data.highBurnout || 0, data.exhaustion || 0, data.disengagement || 0, data.lowBurnout || 0];
        renderChartLegend(doc, burnoutLabels, burnoutCounts, summaryRightX + smallChartWidth + 2, burnoutChartStartY);
        summaryChartY = burnoutChartStartY + smallChartHeight + 3;
    } else {
        summaryChartY = burnoutChartStartY + smallChartHeight + 3;
    }
    
    yPos = Math.max(summaryTextY, summaryChartY) + 5;
    const leftColX = margin;
    const rightColX = margin + 100;
    
    function renderChartWithLegend(chart, title, data, colors, x, y, isRight = false) {
        doc.setFontSize(10);
        doc.setFont(undefined, 'bold');
        doc.text(title, x + (smallChartWidth / 2), y, { align: 'center' });
        const chartStartY = y + 5;
        
        if (addChartImage(chart, x, chartStartY, smallChartWidth, smallChartHeight)) {
            const keys = Object.keys(data).filter(key => key && key.trim() !== '');
            const limitedKeys = isRight ? keys.slice(0, 10) : keys.slice(0, 5);
            const labels = limitedKeys;
            const counts = limitedKeys.map(key => data[key] || 0);
            const legendStartY = chartStartY + smallChartHeight + 2;
            
            if (isRight && title === 'Program') {
                const programLegendStartY = legendStartY + 5;
                renderChartLegendWithColors(doc, labels, counts, colors, x + 2, programLegendStartY);
                y = programLegendStartY + (limitedKeys.length * 6.5) + 6;
            } else {
                renderChartLegend(doc, labels, counts, x + 2, legendStartY, 5);
                y = legendStartY + (limitedKeys.length * 5) + 6;
            }
        } else {
            y = chartStartY + smallChartHeight + 3;
        }
        return y;
    }
    
    let leftColY = yPos;
    let rightColY = yPos;
    
    leftColY = renderChartWithLegend(charts.ageChart, 'Age', data.ageDistribution || {}, CHART_COLORS, leftColX, leftColY);
    rightColY = renderChartWithLegend(charts.genderChart, 'Gender', data.genderDistribution || {}, GENDER_COLORS, rightColX, rightColY);
    
    yPos = Math.max(leftColY, rightColY);
    leftColY = yPos;
    rightColY = yPos;
    
    leftColY = renderChartWithLegend(charts.yearChart, 'Year Level', data.yearDistribution || {}, YEAR_COLORS, leftColX, leftColY);
    rightColY = renderChartWithLegend(charts.programChart, 'Program', data.programDistribution || {}, PROGRAM_COLORS, rightColX, rightColY, true);
    
    doc.addPage();
    yPos = 20;
    
    doc.setFontSize(14);
    doc.setFont(undefined, 'bold');
    doc.text('Feature Importance', pageWidth / 2, yPos, { align: 'center' });
    yPos += 8;
    
    doc.setFontSize(9);
    doc.setFont(undefined, 'normal');
    doc.text('Shows the relative importance of each question (Q1-Q30) in predicting burnout categories based on the Random Forest model.', margin, yPos);
    yPos += 10;
    
    const featureImportance = data.featureImportance || {};
    const questionsList = data.questionsList || [];
    const features = Object.keys(featureImportance).sort((a, b) => featureImportance[b] - featureImportance[a]);
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
            
            const rgb = hexToRgb('#6366f1');
            if (rgb) {
                doc.setFillColor(rgb.r, rgb.g, rgb.b);
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
    
    doc.save(`Dashboard_Export_${formatTimestamp()}.pdf`);
}

window.exportToExcel = exportToExcel;
window.exportToPDF = exportToPDF;
