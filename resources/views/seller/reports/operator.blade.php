@extends('layouts.seller')
@section('title','Operator Report')
@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Operator-wise Report</h1>
        <p class="page-sub">Sales, discounts &amp; net amounts broken down by operator</p>
    </div>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom:20px">
    <div style="padding:16px 20px;display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap">
        <div style="display:flex;flex-direction:column;gap:4px">
            <label style="font-size:11.5px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.4px">From Date</label>
            <input type="date" id="f-from" class="form-control" style="width:150px">
        </div>
        <div style="display:flex;flex-direction:column;gap:4px">
            <label style="font-size:11.5px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.4px">To Date</label>
            <input type="date" id="f-to" class="form-control" style="width:150px">
        </div>
        <button onclick="loadData()" style="background:#2563eb;color:#fff;border:none;padding:9px 20px;border-radius:9px;font-size:13.5px;font-weight:600;cursor:pointer;height:38px">Apply</button>
        <button onclick="document.getElementById('f-from').value='';document.getElementById('f-to').value='';loadData()" style="background:#f1f5f9;color:#374151;border:1px solid #e2e8f0;padding:9px 16px;border-radius:9px;font-size:13px;cursor:pointer;height:38px">Reset</button>
    </div>
</div>

<!-- Summary Stats -->
<div style="display:grid;grid-template-columns:repeat(5,1fr);gap:14px;margin-bottom:18px">
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(59,130,246,.15)">
            <svg fill="none" viewBox="0 0 24 24" stroke="#3b82f6" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        </div>
        <div class="stat-body"><div class="stat-label">Total Txns</div><div class="stat-value" id="sTotal">—</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(16,185,129,.15)">
            <svg fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="stat-body"><div class="stat-label">Successful</div><div class="stat-value" id="sSuccess">—</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(239,68,68,.15)">
            <svg fill="none" viewBox="0 0 24 24" stroke="#ef4444" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="stat-body"><div class="stat-label">Failed</div><div class="stat-value" id="sFailure">—</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(245,158,11,.15)">
            <svg fill="none" viewBox="0 0 24 24" stroke="#f59e0b" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 1v8m0 0v1"/></svg>
        </div>
        <div class="stat-body"><div class="stat-label">Total Sale</div><div class="stat-value" id="sVolume">—</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(16,185,129,.15)">
            <svg fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
        </div>
        <div class="stat-body"><div class="stat-label">Total Discount</div><div class="stat-value" id="sDiscount" style="color:#10b981">—</div></div>
    </div>
</div>

<!-- Chart + Table -->
<div style="display:grid;grid-template-columns:340px 1fr;gap:16px;align-items:start">

    <!-- Donut Chart -->
    <div class="card">
        <div class="card-header"><h3 class="card-title">Sale Share by Operator</h3></div>
        <div style="padding:16px;display:flex;flex-direction:column;align-items:center;gap:12px">
            <canvas id="opChart" width="260" height="260"></canvas>
            <div id="chart-legend" style="width:100%;font-size:12.5px"></div>
        </div>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="card-header"><h3 class="card-title">Operator-wise Breakdown</h3></div>
        <div id="op-wrap" style="overflow-x:auto;padding:0">
            <div style="text-align:center;padding:40px;color:#64748b">Loading…</div>
        </div>
    </div>
</div>

<style>
.op-table { width:100%; border-collapse:collapse; font-size:13px; }
.op-table th { background:#f8fafc; color:#475569; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; padding:10px 14px; border-bottom:2px solid #e2e8f0; white-space:nowrap; }
.op-table td { padding:10px 14px; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
.op-table tr:last-child td { border-bottom:none; }
.op-table tr:hover td { background:#f8fafc; }
.prog-bar { flex:1; height:6px; background:#f1f5f9; border-radius:3px; overflow:hidden; min-width:50px; }
.prog-fill { height:100%; border-radius:3px; }
</style>

<script>
const COLORS = ['#2563eb','#10b981','#f59e0b','#ef4444','#7c3aed','#0891b2','#db2777','#65a30d','#ea580c','#0d9488'];

let chartInstance = null;

function drawDonut(labels, values) {
    const canvas = document.getElementById('opChart');
    const ctx = canvas.getContext('2d');
    const W = canvas.width, H = canvas.height;
    const cx = W / 2, cy = H / 2, r = Math.min(W, H) / 2 - 16, inner = r * 0.58;
    ctx.clearRect(0, 0, W, H);

    const total = values.reduce((a, b) => a + b, 0);
    if (!total) return;

    let angle = -Math.PI / 2;
    values.forEach((v, i) => {
        const slice = (v / total) * 2 * Math.PI;
        ctx.beginPath();
        ctx.moveTo(cx, cy);
        ctx.arc(cx, cy, r, angle, angle + slice);
        ctx.closePath();
        ctx.fillStyle = COLORS[i % COLORS.length];
        ctx.fill();
        angle += slice;
    });

    // Hole
    ctx.beginPath();
    ctx.arc(cx, cy, inner, 0, 2 * Math.PI);
    ctx.fillStyle = '#fff';
    ctx.fill();

    // Center text
    ctx.fillStyle = '#1e293b';
    ctx.font = 'bold 15px Inter,sans-serif';
    ctx.textAlign = 'center';
    ctx.fillText(labels.length + ' operators', cx, cy - 6);
    ctx.font = '12px Inter,sans-serif';
    ctx.fillStyle = '#64748b';
    ctx.fillText('₹' + fmtMoney(total), cx, cy + 14);
}

function loadData() {
    const from = document.getElementById('f-from').value;
    const to   = document.getElementById('f-to').value;
    const params = new URLSearchParams();
    if (from) params.set('date_from', from);
    if (to)   params.set('date_to', to);

    document.getElementById('op-wrap').innerHTML = '<div style="text-align:center;padding:40px;color:#64748b">Loading…</div>';
    ['sTotal','sSuccess','sFailure','sVolume','sDiscount'].forEach(id => document.getElementById(id).textContent = '—');

    apiFetch('/api/v1/seller/reports/operator?' + params).then(d => {
        const rows = d.operators || [];

        if (!rows.length) {
            document.getElementById('op-wrap').innerHTML = '<div style="text-align:center;padding:40px;color:#64748b;font-size:14px">No operator data for selected period.</div>';
            ['sTotal','sSuccess','sFailure'].forEach(id => document.getElementById(id).textContent = '0');
            ['sVolume','sDiscount'].forEach(id => document.getElementById(id).textContent = '₹0.00');
            return;
        }

        // Summary totals
        const totTxn      = rows.reduce((s, r) => s + (r.count    || 0), 0);
        const totSucc     = rows.reduce((s, r) => s + (r.success  || 0), 0);
        const totFail     = rows.reduce((s, r) => s + (r.failed   || 0), 0);
        const totVol      = rows.reduce((s, r) => s + parseFloat(r.total_amount   || 0), 0);
        const totDiscount = rows.reduce((s, r) => s + parseFloat(r.total_discount || 0), 0);

        document.getElementById('sTotal').textContent    = totTxn;
        document.getElementById('sSuccess').textContent  = totSucc;
        document.getElementById('sFailure').textContent  = totFail;
        document.getElementById('sVolume').textContent   = '₹' + fmtMoney(totVol);
        document.getElementById('sDiscount').textContent = '₹' + fmtMoney(totDiscount);

        // Draw donut chart
        drawDonut(rows.map(r => r.operator), rows.map(r => parseFloat(r.total_amount || 0)));

        // Legend
        let legend = '<div style="display:flex;flex-direction:column;gap:6px;margin-top:4px">';
        rows.forEach((r, i) => {
            const pct = totVol > 0 ? ((parseFloat(r.total_amount || 0) / totVol) * 100).toFixed(1) : '0.0';
            legend += `<div style="display:flex;align-items:center;gap:8px">
                <div style="width:10px;height:10px;border-radius:50%;background:${COLORS[i % COLORS.length]};flex-shrink:0"></div>
                <span style="flex:1;color:#374151;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${r.operator}</span>
                <span style="color:#64748b;font-size:11.5px">${pct}%</span>
            </div>`;
        });
        legend += '</div>';
        document.getElementById('chart-legend').innerHTML = legend;

        // Table
        let html = `<table class="op-table"><thead><tr>
            <th>#</th>
            <th>Operator</th>
            <th>Total Txns</th>
            <th>Success</th>
            <th>Failed</th>
            <th>Sale Amount</th>
            <th>Discount Earned</th>
            <th>Net Amount</th>
            <th>Success Rate</th>
        </tr></thead><tbody>`;

        rows.forEach((r, idx) => {
            const total     = r.count   || 0;
            const succ      = r.success || 0;
            const rate      = total > 0 ? Math.round((succ / total) * 100) : 0;
            const rateClr   = rate >= 90 ? '#10b981' : rate >= 75 ? '#f59e0b' : '#ef4444';
            const saleAmt   = parseFloat(r.total_amount   || 0);
            const discount  = parseFloat(r.total_discount || 0);
            const netAmt    = parseFloat(r.net_amount     || 0);
            const discPct   = saleAmt > 0 ? ((discount / saleAmt) * 100).toFixed(2) : '0.00';

            html += `<tr>
                <td style="color:#94a3b8;font-size:12px">${idx + 1}</td>
                <td>
                    <div style="display:flex;align-items:center;gap:8px">
                        <div style="width:8px;height:8px;border-radius:50%;background:${COLORS[idx % COLORS.length]};flex-shrink:0"></div>
                        <span style="font-weight:700;font-size:13.5px">${r.operator || '—'}</span>
                    </div>
                </td>
                <td style="font-weight:600">${total}</td>
                <td><span class="badge-success">${succ}</span></td>
                <td><span class="badge-danger">${r.failed || 0}</span></td>
                <td style="font-weight:700;color:#1e293b">₹${fmtMoney(saleAmt)}</td>
                <td>
                    <div style="color:#10b981;font-weight:700">+₹${fmtMoney(discount)}</div>
                    <div style="font-size:11px;color:#94a3b8">${discPct}% discount</div>
                </td>
                <td style="font-weight:700;color:#2563eb">₹${fmtMoney(netAmt)}</td>
                <td>
                    <div style="display:flex;align-items:center;gap:8px">
                        <div class="prog-bar"><div class="prog-fill" style="width:${rate}%;background:${rateClr}"></div></div>
                        <span style="font-size:12px;font-weight:700;color:${rateClr};min-width:34px">${rate}%</span>
                    </div>
                </td>
            </tr>`;
        });

        // Total row
        const totNet = rows.reduce((s, r) => s + parseFloat(r.net_amount || 0), 0);
        const totDiscPct = totVol > 0 ? ((totDiscount / totVol) * 100).toFixed(2) : '0.00';
        html += `<tr style="background:#f8fafc;font-weight:700;border-top:2px solid #e2e8f0">
            <td colspan="2" style="color:#374151;font-size:13px;padding:12px 14px">TOTAL</td>
            <td>${totTxn}</td>
            <td><span class="badge-success">${totSucc}</span></td>
            <td><span class="badge-danger">${totFail}</span></td>
            <td style="font-weight:800;color:#1e293b">₹${fmtMoney(totVol)}</td>
            <td>
                <div style="color:#10b981;font-weight:800">+₹${fmtMoney(totDiscount)}</div>
                <div style="font-size:11px;color:#94a3b8">${totDiscPct}% avg</div>
            </td>
            <td style="font-weight:800;color:#2563eb">₹${fmtMoney(totNet)}</td>
            <td></td>
        </tr>`;

        html += '</tbody></table>';
        document.getElementById('op-wrap').innerHTML = html;

    }).catch(() => {
        document.getElementById('op-wrap').innerHTML = '<div style="text-align:center;padding:40px;color:#ef4444;font-size:13.5px">Failed to load operator data. Please try again.</div>';
    });
}

loadData();
</script>
@endsection
