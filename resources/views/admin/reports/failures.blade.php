@extends('layouts.admin')

@section('title', 'Failure Report')
@section('page-title', 'Failure Report')

@section('content')

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
    <div>
        <h1 style="font-size:20px;font-weight:700;color:var(--text-primary)">Failure Report</h1>
        <div class="breadcrumb" style="margin-bottom:0;margin-top:3px">
            <a href="/admin/dashboard">Dashboard</a>
            <svg class="breadcrumb-sep" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            <span>Reports</span>
            <svg class="breadcrumb-sep" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            <span>Failure Report</span>
        </div>
    </div>
    <button class="btn btn-outline btn-sm" onclick="loadReport()">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        Refresh
    </button>
</div>

{{-- Filter Bar --}}
<div class="card" style="margin-bottom:20px">
    <div class="card-body" style="padding:16px 20px">
        <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end">
            <div>
                <label style="display:block;font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Date From</label>
                <input type="date" id="f-date-from" style="border:1px solid var(--border);border-radius:8px;padding:7px 12px;font-size:13px;color:var(--text-primary);background:#fff;outline:none">
            </div>
            <div>
                <label style="display:block;font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Date To</label>
                <input type="date" id="f-date-to" style="border:1px solid var(--border);border-radius:8px;padding:7px 12px;font-size:13px;color:var(--text-primary);background:#fff;outline:none">
            </div>
            <div>
                <label style="display:block;font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Operator Code</label>
                <input type="text" id="f-operator" placeholder="e.g. JIO" style="border:1px solid var(--border);border-radius:8px;padding:7px 12px;font-size:13px;color:var(--text-primary);background:#fff;outline:none;width:120px">
            </div>
            <button class="btn btn-primary btn-sm" onclick="loadReport()">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                Apply Filters
            </button>
            <button class="btn btn-outline btn-sm" onclick="clearFilters()">Clear</button>
        </div>
    </div>
</div>

{{-- Two-column layout for by_reason and by_operator --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px">
    {{-- By Reason --}}
    <div class="card">
        <div class="card-header"><div class="card-title">Failures by Reason</div></div>
        <div class="card-body" style="padding:0">
            <div id="reason-loading" class="loading-overlay"><div class="spinner"></div> Loading…</div>
            <div class="table-wrap" id="reason-table-wrap" style="display:none">
                <table>
                    <thead><tr><th>Reason</th><th>Count</th><th>Amount</th><th>Share</th></tr></thead>
                    <tbody id="reason-tbody"></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- By Operator --}}
    <div class="card">
        <div class="card-header"><div class="card-title">Failures by Operator</div></div>
        <div class="card-body" style="padding:0">
            <div id="opfail-loading" class="loading-overlay"><div class="spinner"></div> Loading…</div>
            <div class="table-wrap" id="opfail-table-wrap" style="display:none">
                <table>
                    <thead><tr><th>Operator</th><th>Failed</th><th>Total</th><th>Failure Rate</th></tr></thead>
                    <tbody id="opfail-tbody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Hourly Heatmap Legend --}}
<div class="card" style="margin-bottom:20px">
    <div class="card-header" style="justify-content:space-between">
        <div class="card-title">Hourly Failure Heatmap</div>
        <div style="display:flex;align-items:center;gap:8px;font-size:11px;color:var(--text-muted)">
            <span>Low</span>
            <div style="display:flex;gap:3px">
                <div style="width:16px;height:16px;border-radius:3px;background:#fef3c7"></div>
                <div style="width:16px;height:16px;border-radius:3px;background:#fde68a"></div>
                <div style="width:16px;height:16px;border-radius:3px;background:#fb923c"></div>
                <div style="width:16px;height:16px;border-radius:3px;background:#ef4444"></div>
                <div style="width:16px;height:16px;border-radius:3px;background:#991b1b"></div>
            </div>
            <span>High</span>
        </div>
    </div>
    <div class="card-body">
        <div id="heatmap-loading" class="loading-overlay"><div class="spinner"></div> Loading heatmap…</div>
        <div id="heatmap-wrap" style="display:none">
            <div style="display:grid;grid-template-columns:repeat(24,1fr);gap:4px;margin-bottom:8px" id="heatmap-grid"></div>
            <div style="display:flex;justify-content:space-between;font-size:10px;color:var(--text-muted)">
                <span>12AM</span><span>6AM</span><span>12PM</span><span>6PM</span><span>11PM</span>
            </div>
        </div>
    </div>
</div>

{{-- Failure Details Table --}}
<div class="card">
    <div class="card-header" style="justify-content:space-between">
        <div class="card-title">Failure Transaction Details</div>
        <span id="details-count" style="font-size:12px;color:var(--text-muted)">—</span>
    </div>
    <div class="card-body" style="padding:0">
        <div id="details-loading" class="loading-overlay"><div class="spinner"></div> Loading…</div>
        <div class="table-wrap" id="details-table-wrap" style="display:none">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Mobile</th>
                        <th>Operator</th>
                        <th>Amount</th>
                        <th>Failure Reason</th>
                        <th>Ref ID</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody id="details-tbody"></tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function getFilters() {
    return {
        date_from:     document.getElementById('f-date-from').value,
        date_to:       document.getElementById('f-date-to').value,
        operator_code: document.getElementById('f-operator').value,
    };
}

function buildQuery(p) {
    return Object.entries(p).filter(([,v]) => v).map(([k,v]) => k+'='+encodeURIComponent(v)).join('&');
}

function clearFilters() {
    ['f-date-from','f-date-to','f-operator'].forEach(id => document.getElementById(id).value = '');
    loadReport();
}

async function loadReport() {
    const q = buildQuery(getFilters());
    const res = await apiFetch('/api/v1/admin/reports/failures?' + q);
    if (!res) return;
    const json = await res.json();
    const d = json.data || {};

    renderByReason(d.by_reason || []);
    renderByOperator(d.by_operator || []);
    renderHeatmap(d.hourly || []);
    renderDetails(d.details || d.transactions || []);
}

function renderByReason(rows) {
    document.getElementById('reason-loading').style.display = 'none';
    document.getElementById('reason-table-wrap').style.display = 'block';
    const total = rows.reduce((s, r) => s + (r.count || 0), 0);
    const tbody = document.getElementById('reason-tbody');
    if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;color:var(--text-muted);padding:24px">No data</td></tr>';
        return;
    }
    tbody.innerHTML = rows.map(r => {
        const pct = total ? ((r.count / total) * 100).toFixed(1) : '0.0';
        return `<tr>
            <td style="font-weight:500">${r.reason || r.failure_reason || '—'}</td>
            <td style="color:#ef4444;font-weight:600">${fmtNum(r.count)}</td>
            <td>${fmtAmt(r.amount)}</td>
            <td>
                <div style="display:flex;align-items:center;gap:6px">
                    <div class="progress" style="flex:1;min-width:50px"><div class="progress-bar" style="width:${pct}%;background:#ef4444"></div></div>
                    <span style="font-size:11px;color:var(--text-muted);min-width:35px">${pct}%</span>
                </div>
            </td>
        </tr>`;
    }).join('');
}

function renderByOperator(rows) {
    document.getElementById('opfail-loading').style.display = 'none';
    document.getElementById('opfail-table-wrap').style.display = 'block';
    const tbody = document.getElementById('opfail-tbody');
    if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;color:var(--text-muted);padding:24px">No data</td></tr>';
        return;
    }
    tbody.innerHTML = rows.map(r => {
        const rate = r.failure_rate ?? (r.total ? ((r.failed / r.total) * 100) : 0);
        const rc = rate > 30 ? '#ef4444' : rate > 15 ? '#f59e0b' : '#10b981';
        return `<tr>
            <td style="font-weight:600">${r.operator || r.operator_name || '—'}</td>
            <td style="color:#ef4444;font-weight:600">${fmtNum(r.failed || r.failure)}</td>
            <td>${fmtNum(r.total)}</td>
            <td><span style="color:${rc};font-weight:600">${Number(rate).toFixed(1)}%</span></td>
        </tr>`;
    }).join('');
}

function renderHeatmap(hourly) {
    document.getElementById('heatmap-loading').style.display = 'none';
    document.getElementById('heatmap-wrap').style.display = 'block';
    const max = Math.max(...hourly.map(h => h.count || h || 0), 1);
    const grid = document.getElementById('heatmap-grid');
    grid.innerHTML = '';
    const hours = Array.isArray(hourly) ? hourly : Array(24).fill(0);
    hours.slice(0, 24).forEach((h, i) => {
        const count = typeof h === 'object' ? (h.count || 0) : (h || 0);
        const ratio = count / max;
        const bg = ratio === 0 ? '#f1f5f9' : ratio < .25 ? '#fef3c7' : ratio < .5 ? '#fde68a' : ratio < .75 ? '#fb923c' : ratio < .9 ? '#ef4444' : '#991b1b';
        const cell = document.createElement('div');
        cell.style.cssText = `height:32px;border-radius:4px;background:${bg};cursor:default;transition:transform .1s`;
        cell.title = `${i}:00 — ${fmtNum(count)} failures`;
        cell.addEventListener('mouseenter', () => cell.style.transform = 'scale(1.1)');
        cell.addEventListener('mouseleave', () => cell.style.transform = '');
        grid.appendChild(cell);
    });
}

function renderDetails(rows) {
    document.getElementById('details-loading').style.display = 'none';
    document.getElementById('details-table-wrap').style.display = 'block';
    document.getElementById('details-count').textContent = fmtNum(rows.length) + ' records';
    const tbody = document.getElementById('details-tbody');
    if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:24px">No failure records found</td></tr>';
        return;
    }
    tbody.innerHTML = rows.map((r, i) => `<tr>
        <td style="color:var(--text-muted)">${i+1}</td>
        <td style="font-weight:600">${r.mobile || r.mobile_number || '—'}</td>
        <td>${r.operator_name || r.operator_code || '—'}</td>
        <td style="font-weight:600">${fmtAmt(r.amount)}</td>
        <td style="color:#ef4444">${r.failure_reason || r.reason || '—'}</td>
        <td style="font-size:12px;color:var(--text-muted)">${r.reference_id || r.ref_id || '—'}</td>
        <td style="font-size:12px;color:var(--text-muted)">${r.created_at ? new Date(r.created_at).toLocaleString('en-IN',{day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:true}) : '—'}</td>
    </tr>`).join('');
}

const today = new Date();
const week  = new Date(today); week.setDate(week.getDate() - 7);
document.getElementById('f-date-to').value   = today.toISOString().slice(0,10);
document.getElementById('f-date-from').value = week.toISOString().slice(0,10);

document.addEventListener('DOMContentLoaded', loadReport);
</script>
@endpush
