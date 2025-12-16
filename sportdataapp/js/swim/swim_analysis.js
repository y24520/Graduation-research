/* =====================
   ヘルパー: 時間パース/フォーマット
   - 入力は秒 (number) または "m:ss.xx" の文字列などを想定
===================== */
function parseTimeInput(v) {
    if (v === null || v === undefined) return null;
    if (typeof v === 'number') return v;
    if (typeof v === 'string') {
        v = v.trim();
        if (v === '') return null;
        if (v.indexOf(':') !== -1) {
            const parts = v.split(':');
            const m = parseInt(parts[0], 10) || 0;
            const s = parseFloat(parts[1]) || 0;
            return m * 60 + s;
        }
        const f = parseFloat(v);
        return isNaN(f) ? null : f;
    }
    return null;
}

function formatTime(sec) {
    if (sec === null || sec === undefined || isNaN(sec)) return '---';
    const total = Number(sec);
    const m = Math.floor(total / 60);
    const s = (total % 60).toFixed(2).padStart(5, '0');
    return m > 0 ? `${m}:${s}` : s;
}

/* =====================
   比較表示 (DOM 安全チェック含む)
===================== */
const elPrevNow = document.getElementById('prev-now');
const elPrevThen = document.getElementById('prev-then');
const elBestNow = document.getElementById('best-now');
const elBestThen = document.getElementById('best-then');
const elDiffPrev = document.getElementById('diff-prev');
const elDiffBest = document.getElementById('diff-best');

const nowSec  = parseTimeInput(NOW_TIME);
const prevSec = parseTimeInput(PREV_TIME);
const bestSec = parseTimeInput(BEST_TIME);

if (elPrevNow) elPrevNow.textContent = nowSec !== null ? formatTime(nowSec) : '---';
if (elPrevThen) elPrevThen.textContent = prevSec !== null ? formatTime(prevSec) : 'N/A';
if (elBestNow) elBestNow.textContent = nowSec !== null ? formatTime(nowSec) : '---';
if (elBestThen) elBestThen.textContent = bestSec !== null ? formatTime(bestSec) : 'N/A';

function formatSignedSeconds(diff) {
    if (diff === null || diff === undefined || isNaN(diff)) return '---';
    const s = Math.abs(diff).toFixed(2);
    // show + when worse (positive diff), - when improved (negative diff)
    const sign = diff > 0 ? '+' : (diff < 0 ? '-' : '+');
    // arrow: ▲ worse (slower), ▼ better (faster), = no change
    const arrow = diff > 0 ? '▲' : (diff < 0 ? '▼' : '＝');
    return `${arrow} ${sign}${s} 秒`;
}

if (elDiffPrev) {
    if (prevSec !== null && nowSec !== null) {
        const d = nowSec - prevSec;
        // show arrow + signed value, and add title for exact seconds
        elDiffPrev.innerHTML = '<span class="diff-arrow">' + formatSignedSeconds(d) + '</span>';
        elDiffPrev.title = (d < 0 ? '今回が速い（改善）' : (d > 0 ? '今回が遅い（悪化）' : '変化なし'));
        elDiffPrev.style.color = d < 0 ? '#2e7d32' : (d > 0 ? '#c62828' : '#333');
    } else {
        elDiffPrev.textContent = '---';
    }
}

if (elDiffBest) {
    if (bestSec !== null && nowSec !== null) {
        const d = nowSec - bestSec;
        elDiffBest.innerHTML = '<span class="diff-arrow">' + formatSignedSeconds(d) + '</span>';
    elDiffBest.title = (d < 0 ? '今回がベストより速い（更新）' : (d > 0 ? '今回がベストより遅い' : 'ベストと同等'));
        elDiffBest.style.color = d < 0 ? '#2e7d32' : (d > 0 ? '#c62828' : '#333');
    } else {
        elDiffBest.textContent = '---';
    }
}

/* =====================
   小チャート: 前回 vs 今回, PB vs 今回
===================== */
function renderSmallLineChart(canvasId, labels, data, color) {
    const c = document.getElementById(canvasId);
    if (!c) return null;
    const newCanvas = c.cloneNode(true);
    c.parentNode.replaceChild(newCanvas, c);
    // plugin: draw value labels above points
    const valueLabelPlugin = {
        id: 'valueLabels',
        afterDatasetsDraw: function(chart, args, options) {
            const ctx = chart.ctx;
            chart.data.datasets.forEach((dataset, datasetIndex) => {
                const meta = chart.getDatasetMeta(datasetIndex);
                meta.data.forEach((element, index) => {
                    const val = dataset.data[index];
                    if (val === null || val === undefined) return;
                    const pos = element.tooltipPosition();
                    ctx.save();
                    ctx.fillStyle = '#333';
                    ctx.font = '12px Arial';
                    const label = formatTime(val);
                    const w = ctx.measureText(label).width;
                    ctx.fillText(label, pos.x - w/2, pos.y - 10);
                    ctx.restore();
                });
            });
        }
    };

    return new Chart(newCanvas, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: canvasId,
                data,
                borderColor: color,
                backgroundColor: color,
                tension: 0.2,
                fill: false,
                pointRadius: 6,
                spanGaps: false
            }]
        },
        plugins: [valueLabelPlugin],
        options: {
            plugins: { legend: { display: false }, tooltip: { callbacks: { label: function(ctx){ const v = ctx.raw; return (v === null || v === undefined) ? 'N/A' : formatTime(v) + ' 秒'; } } } },
            scales: { y: { beginAtZero: false, ticks: { callback: function(v){ return formatTime(v); } } } },
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

// 前回 vs 今回 (線グラフ)
if (document.getElementById('prevNowChart')) {
    const labelsPN = ['前回', '今回'];
    const dataPN = [ prevSec !== null ? prevSec : null, nowSec !== null ? nowSec : null ];
    renderSmallLineChart('prevNowChart', labelsPN, dataPN, '#1976d2');
}

// PB vs 今回 (線グラフ)
if (document.getElementById('bestNowChart')) {
    const labelsBN = ['ベスト', '今回'];
    const dataBN = [ bestSec !== null ? bestSec : null, nowSec !== null ? nowSec : null ];
    renderSmallLineChart('bestNowChart', labelsBN, dataBN, '#d32f2f');
}

/* =====================
   Chart.js 推移グラフ
===================== */
const chartCanvas = document.getElementById('timeChart');
if (!chartCanvas) {
    console.warn('timeChart canvas not found');
} else {
    const labels = Array.isArray(HISTORY) ? HISTORY.map(h => h.swim_date) : [];
    const times  = Array.isArray(HISTORY) ? HISTORY.map(h => {
        const v = parseTimeInput(h.total_time);
        return v === null ? null : v;
    }) : [];

    if (labels.length === 0) {
        // データ無し時は canvas を非表示にしてメッセージを表示
        chartCanvas.style.display = 'none';
        const p = document.createElement('p');
        p.textContent = '推移データがありません';
        chartCanvas.parentNode.insertBefore(p, chartCanvas);
    } else {
        const datasets = [
            {
                label: '記録',
                data: times,
                tension: 0.2,
                borderColor: '#1976d2',
                backgroundColor: '#1976d2',
                spanGaps: true,
                pointRadius: 3
            }
        ];

        if (bestSec !== null) {
            datasets.push({
                label: 'ベスト',
                data: labels.map(() => bestSec),
                borderDash: [5,5],
                borderColor: '#d32f2f',
                pointRadius: 0,
                fill: false
            });
        }

        new Chart(chartCanvas, {
            type: 'line',
            data: {
                labels,
                datasets
            },
            options: {
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const v = context.raw;
                                if (v === null || v === undefined) return 'データなし';
                                return formatTime(v) + ' 秒';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        reverse: true,
                        ticks: {
                            callback: function(value) { return formatTime(value); }
                        }
                    }
                }
            }
        });
    }
}
