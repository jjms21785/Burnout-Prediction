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
    
    const allQuestions = window.allQuestions || [];
    const questionData = [
        ['Response Distribution'],
        ['Question Response Statistics'],
        [],
        ['Question', 'Strongly Agree', 'Strongly Agree %', 'Agree', 'Agree %', 'Disagree', 'Disagree %', 'Strongly Disagree', 'Strongly Disagree %', 'Total Responses'],
        ...allQuestions.map(q => [
            `Q${q.id}: ${q.text}`,
            q.responses[0],
            q.total > 0 ? (q.responses[0] / q.total * 100).toFixed(1) + '%' : '0.0%',
            q.responses[1],
            q.total > 0 ? (q.responses[1] / q.total * 100).toFixed(1) + '%' : '0.0%',
            q.responses[2],
            q.total > 0 ? (q.responses[2] / q.total * 100).toFixed(1) + '%' : '0.0%',
            q.responses[3],
            q.total > 0 ? (q.responses[3] / q.total * 100).toFixed(1) + '%' : '0.0%',
            q.total
        ])
    ];
    
    const questionSheet = XLSX.utils.aoa_to_sheet(questionData);
    XLSX.utils.book_append_sheet(workbook, questionSheet, 'Response Distribution');
    
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
    doc.text('Burnout Analytics Dashboard', margin, yPos);
    
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
    doc.text('Response Distribution', margin, yPos);
    yPos += 8;
    
    doc.setFontSize(7);
    doc.setFont(undefined, 'normal');
    const barStartX = 85;
    const barWidth = pageWidth - barStartX - margin;
    const colWidth = barWidth / 4;
    const headerLabels = ['Strongly Agree', 'Agree', 'Disagree', 'Strongly Disagree'];
    headerLabels.forEach((label, idx) => {
        doc.text(label, barStartX + (colWidth * idx) + (colWidth / 2), yPos, { align: 'center' });
    });
    yPos += 6;
    
    const allQuestions = window.allQuestions || [];
    const barColors = ['#6366f1', '#818cf8', '#a5b4fc', '#c7d2fe'];
    
    function drawStackedBar(question, xStart, y, totalWidth, height) {
        const percentages = question.percentages || [0, 0, 0, 0];
        let currentX = xStart;
        
        percentages.forEach((pct, idx) => {
            if (pct > 0) {
                const segmentWidth = (pct / 100) * totalWidth;
                const rgb = hexToRgb(barColors[idx]);
                if (rgb) {
                    doc.setFillColor(rgb.r, rgb.g, rgb.b);
                    doc.rect(currentX, y, segmentWidth, height, 'F');
                }
                
                if (pct >= 8) {
                    doc.setFontSize(6);
                    doc.setTextColor(255, 255, 255);
                    doc.text(`${pct.toFixed(1)}%`, currentX + (segmentWidth / 2), y + (height / 2) + 1, { align: 'center' });
                    doc.setTextColor(0, 0, 0);
                }
                
                currentX += segmentWidth;
            }
        });
        
        doc.setDrawColor(200, 200, 200);
        doc.rect(xStart, y, totalWidth, height, 'S');
    }
    
    const responseLabels = ['Strongly Agree', 'Agree', 'Disagree', 'Strongly Disagree'];
    allQuestions.forEach(q => {
        checkPageBreak(20);
        
        doc.setFontSize(7);
        doc.setFont(undefined, 'bold');
        const questionText = `${q.id}. ${q.text}`;
        const truncatedText = questionText.length > 50 ? questionText.substring(0, 50) + '...' : questionText;
        doc.text(truncatedText, margin, yPos + 4);
        
        const barHeight = 7;
        drawStackedBar(q, barStartX, yPos, barWidth, barHeight);
        yPos += barHeight + 3;
        
        doc.setFontSize(6);
        doc.setFont(undefined, 'normal');
        const textY = yPos;
        responseLabels.forEach((label, idx) => {
            doc.text(`${label}: ${q.responses[idx]} (${q.percentages[idx].toFixed(1)}%)`, margin + 5, textY + (idx * 3));
        });
        doc.text(`Total Responses: ${q.total}`, margin + 5, textY + 12);
        yPos += 15;
    });
    
    doc.save(`Dashboard_Export_${formatTimestamp()}.pdf`);
}

window.exportToExcel = exportToExcel;
window.exportToPDF = exportToPDF;
