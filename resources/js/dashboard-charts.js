import Chart from 'chart.js/auto';

const STATUS_MAP = {
    application: { label: 'Application',  color: '#378ADD' },
    wip:         { label: 'In Progress',  color: '#ba7517' },
    outdoc:      { label: 'Outstanding',  color: '#D85A30' },
    approved:    { label: 'Approved',     color: '#8b5cf6' },
    settled:     { label: 'Settled',      color: '#1d9e75' },
    declined:    { label: 'Declined',     color: '#E24B4A' },
    deferred:    { label: 'Deferred',     color: '#6b7280' },
};

const PURPOSE_COLORS = ['#534AB7','#378ADD','#1d9e75','#ba7517','#888780','#D85A30','#E24B4A'];

const CHART_DEFAULTS = {
    responsive: true,
    maintainAspectRatio: false,
    animation: { duration: 600 },
};

function formatLabel(str) {
    if (!str) return 'Unknown';
    return str
        .replace(/_/g, ' ')
        .replace(/\b\w/g, c => c.toUpperCase());
}

function buildLegend(containerId, items) {
    const el = document.getElementById(containerId);
    if (!el) return;
    el.innerHTML = items.map(i =>
        `<span style="display:flex;align-items:center;gap:5px;font-size:11px;color:var(--color-text-secondary)">
            <span style="width:8px;height:8px;border-radius:2px;background:${i.color};flex-shrink:0"></span>
            ${i.label}
         </span>`
    ).join('');
}

function fmtCurrency(v) {
    if (!v) return '—';
    if (v >= 1_000_000) return '$' + (v / 1_000_000).toFixed(1) + 'M';
    if (v >= 1_000)     return '$' + Math.round(v / 1_000) + 'k';
    return '$' + Math.round(v);
}

function populateMetrics(data) {
    const metrics = data.metrics ?? {};
    const total    = metrics.total    ?? 0;
    const settled = metrics.settled ?? 0;
    const avg      = metrics.avg_loan ?? 0;
    const pending  = metrics.pending  ?? 0;

    const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };

    set('m-total',    total);
    set('m-settled', settled);  // Changed from 'approved'
    set('m-settled-pct', total ? Math.round(settled / total * 100) + '% settlement rate' : '—');  // Updated
    set('m-avg',      fmtCurrency(avg));
    set('m-pending', metrics.pending ?? 0);
}

function initPurposeChart(data) {
    const el = document.getElementById('purposeChart');
    if (!el || !data.loanPurposeData?.length) return;

    const labels = data.loanPurposeData.map(d => d.loan_purpose);
    const counts  = data.loanPurposeData.map(d => d.count);

    buildLegend('legend-purpose', labels.map((l, i) => ({
        label: l,
        color: PURPOSE_COLORS[i % PURPOSE_COLORS.length],
    })));

    new Chart(el, {
        type: 'doughnut',
        data: {
            labels,
            datasets: [{
                data: counts,
                backgroundColor: PURPOSE_COLORS,
                borderColor: 'transparent',
                borderWidth: 3,
                hoverOffset: 4,
            }],
        },
        options: {
            ...CHART_DEFAULTS,
            cutout: '65%',
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.raw}` } },
            },
        },
    });
}

function initStatusChart(data) {
    const el = document.getElementById('statusChart');
    if (!el || !data.statusData?.length) return;

    const items = data.statusData.map(d => ({
        ...(STATUS_MAP[d.status] ?? { label: formatLabel(d.status), color: '#888780' }),
        count: d.count,
    }));

    buildLegend('legend-status', items);

    new Chart(el, {
        type: 'bar',
        data: {
            labels: items.map(i => i.label),
            datasets: [{
                data: items.map(i => i.count),
                backgroundColor: items.map(i => i.color),
                borderRadius: 4,
                borderSkipped: false,
            }],
        },
        options: {
            ...CHART_DEFAULTS,
            indexAxis: 'y',
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: ctx => ` ${ctx.raw} applications` } },
            },
            scales: {
                x: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 11 } }, grid: { color: 'rgba(128,128,128,.1)' } },
                y: { ticks: { font: { size: 11 } }, grid: { display: false } },
            },
        },
    });
}

function initAmountChart(data) {
    const el = document.getElementById('amountChart');
    if (!el || !data.loanAmountData?.length) return;

    new Chart(el, {
        type: 'bar',
        data: {
            labels: data.loanAmountData.map(d => d.range),
            datasets: [{
                label: 'Applications',
                data: data.loanAmountData.map(d => d.count),
                backgroundColor: '#378ADD',
                borderRadius: 4,
                borderSkipped: false,
            }],
        },
        options: {
            ...CHART_DEFAULTS,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 11 } }, grid: { color: 'rgba(128,128,128,.1)' } },
                x: { ticks: { font: { size: 10 } }, grid: { display: false } },
            },
        },
    });
}

function initTrendChart(data) {
    const el = document.getElementById('trendChart');
    if (!el || !data.trendData?.length) return;

    new Chart(el, {
        type: 'line',
        data: {
            labels: data.trendData.map(d =>
                new Date(d.date).toLocaleDateString('en-AU', { month: 'short', day: 'numeric' })
            ),
            datasets: [{
                label: 'Applications',
                data: data.trendData.map(d => d.count),
                borderColor: '#534AB7',
                backgroundColor: 'rgba(83,74,183,.08)',
                fill: true,
                tension: 0.4,
                borderWidth: 2,
                pointRadius: 0,
                pointHoverRadius: 4,
                pointBackgroundColor: '#534AB7',
            }],
        },
        options: {
            ...CHART_DEFAULTS,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 11 } }, grid: { color: 'rgba(128,128,128,.1)' } },
                x: { ticks: { maxTicksLimit: 7, font: { size: 10 } }, grid: { display: false } },
            },
        },
    });
}

export function initCharts(chartData) {
    if (!chartData) return;
    populateMetrics(chartData);
    initPurposeChart(chartData);
    initStatusChart(chartData);
    initAmountChart(chartData);
    initTrendChart(chartData);
}

document.addEventListener('DOMContentLoaded', () => {
    if (window.dashboardChartData) initCharts(window.dashboardChartData);
});