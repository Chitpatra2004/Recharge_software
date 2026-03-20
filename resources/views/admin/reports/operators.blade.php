@extends('layouts.admin')

@section('title', 'Operator Report')
@section('page-title', 'Operator Report')

@section('content')

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
    <div>
        <h1 style="font-size:20px;font-weight:700;color:var(--text-primary)">Operator Report</h1>
        <div class="breadcrumb" style="margin-bottom:0;margin-top:3px">
            <a href="/admin/dashboard">Dashboard</a>
            <svg class="breadcrumb-sep" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            <span>Reports</span>
            <svg class="breadcrumb-sep" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            <span>Operator Report</span>
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
            <div>
                <label style="display:block;font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Category</label>
                <select id="f-category" style="border:1px solid var(--border);border-radius:8px;padding:7px 12px;font-size:13px;color:var(--text-primary);background:#fff;outline:none;min-width:130px">
                    <option value="">All Categories</option>
                    <option value="prepaid">Prepaid</option>
                    <option value="postpaid">Postpaid</option>
                    <option value="dth">DTH</option>
                    <option value="broadband">Broadband</option>
                </select>
            </div>
            <button class="btn btn-primary btn-sm" onclick="loadReport()">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                Apply Filters
            </button>
            <button class="btn btn-outline btn-sm" onclick="clearFilters()">Clear</button>
        </div>
    </div>
</div>

{{-- Operators Summary Table --}}
<div class="card" style="margin-bottom:20px">
    <div class="card-header" style="justify-content:space-between">
        <div class="card-title">Operator Performance</div>
        <span id="op-count" style="font-size:12px;color:var(--text-muted)">—</span>
    </div>
    <div class="card-body" style="padding:0">
        <div id="ops-loading" class="loading-overlay"><div class="spinner"></div> Loading operators…</div>
        <div class="table-wrap" id="ops-table-wrap" style="display:none">
            <table>
                <thead>
                    <tr>
                        <th>Operator</th>
                        <th>Category</th>
                        <th>Total</th>
                        <th>Success</th>
                        <th>Failure</th>
                        <th>Pending</th>
                        <th>Total Amount</th>
                        <th>Success Rate</th>
                        <th>Avg Response</th>
                    </tr>
                </thead>
                <tbody id="ops-tbody">
                    <tr><td colspan="9" style="text-align:center;color:var(--text-muted);padding:24px">No data</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Route Performance Table --}}
<div class="card">
    <div class="card-header" style="justify-content:space-between">
        <div class="card-title">Route Performance</div>
        <span id="route-count" style="font-size:12px;color:var(--text-muted)">—</span>
    </div>
    <div class="card-body" style="padding:0">
        <div id="route-loading" class="loading-overlay"><div class="spinner"></div> Loading routes…</div>
        <div class="table-wrap" id="route-table-wrap" style="display:none">
            <table>
                <thead>
                    <tr>
                        <th>Route</th>
                        <th>Operator</th>
                        <th>Total Txns</th>
                        <th>Success</th>
                        <th>Failure</th>
                        <th>Success Rate</th>
                        <th>Avg Response (ms)</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="route-tbody">
                    <tr><td colspan="8" style="text-align:center;color:var(--text-muted);padding:24px">No route data</td></tr>
                </tbody>
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
        category:      document.getElementById('f-category').value,
    };
}

function buildQuery(p) {
    return Object.entries(p).filter(([,v]) => v).map(([k,v]) => k+'='+encodeURIComponent(v)).join('&');
}

function clearFilters() {
    ['f-date-from','f-date-to','f-operator'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('f-category').value = '';
    loadReport();
}

async function loadReport() {
    await Promise.allSettled([loadOperators(), loadRoutes()]);
}

async function loadOperators() {
    document.getElementById('ops-loading').style.display = 'flex';
    document.getElementById('ops-table-wrap').style.display = 'none';

    const q = buildQuery(getFilters());
    const res = await apiFetch('/api/v1/admin/reports/operators?' + q);
    if (!res) return;
    const json = await res.json();
    const ops = json.data?.operators || json.operators || json.data || [];

    document.getElementById('ops-loading').style.display = 'none';
    document.getElementById('ops-table-wrap').style.display = 'block';
    document.getElementById('op-count').textContent = ops.length + ' operators';

    const tbody = document.getElementById('ops-tbody');
    if (!ops.length) {
        tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;color:var(--text-muted);padding:24px">No operator data found</td></tr>';
        return;
    }
    tbody.innerHTML = ops.map(op => {
        const rate = op.success_rate ?? 0;
        const rc = rate >= 90 ? '#10b981' : rate >= 70 ? '#f59e0b' : '#ef4444';
        return `<tr>
            <td><strong>${op.name || op.operator_name || op.code || '—'}</strong></td>
            <td><span style="background:var(--bg-page);padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600">${op.category || '—'}</span></td>
            <td>${fmtNum(op.total)}</td>
            <td style="color:#10b981;font-weight:600">${fmtNum(op.success)}</td>
            <td style="color:#ef4444;font-weight:600">${fmtNum(op.failure)}</td>
            <td style="color:#f59e0b;font-weight:600">${fmtNum(op.pending)}</td>
            <td>${fmtAmt(op.total_amount ?? op.amount)}</td>
            <td>
                <div style="display:flex;align-items:center;gap:8px">
                    <div class="progress" style="flex:1;min-width:60px"><div class="progress-bar" style="width:${Math.min(100,rate)}%;background:${rc}"></div></div>
                    <span style="font-size:12px;font-weight:600;color:${rc}">${Number(rate).toFixed(1)}%</span>
                </div>
            </td>
            <td style="color:var(--text-muted)">${op.avg_response_time ? Math.round(op.avg_response_time)+'ms' : '—'}</td>
        </tr>`;
    }).join('');
}

async function loadRoutes() {
    document.getElementById('route-loading').style.display = 'flex';
    document.getElementById('route-table-wrap').style.display = 'none';

    const q = buildQuery(getFilters());
    const res = await apiFetch('/api/v1/admin/reports/operators?' + q);
    if (!res) return;
    const json = await res.json();
    const routes = json.data?.route_performance || json.route_performance || [];

    document.getElementById('route-loading').style.display = 'none';
    document.getElementById('route-table-wrap').style.display = 'block';
    document.getElementById('route-count').textContent = routes.length + ' routes';

    const tbody = document.getElementById('route-tbody');
    if (!routes.length) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:var(--text-muted);padding:24px">No route performance data</td></tr>';
        return;
    }
    tbody.innerHTML = routes.map(r => {
        const rate = r.success_rate ?? 0;
        const rc = rate >= 90 ? '#10b981' : rate >= 70 ? '#f59e0b' : '#ef4444';
        const status = (r.status || 'active').toLowerCase();
        const stBg = status === 'active' ? '#d1fae5' : '#fee2e2';
        const stC  = status === 'active' ? '#059669' : '#dc2626';
        return `<tr>
            <td style="font-weight:600">${r.route || r.route_name || '—'}</td>
            <td>${r.operator || r.operator_name || '—'}</td>
            <td>${fmtNum(r.total)}</td>
            <td style="color:#10b981;font-weight:600">${fmtNum(r.success)}</td>
            <td style="color:#ef4444;font-weight:600">${fmtNum(r.failure)}</td>
            <td><span style="color:${rc};font-weight:600">${Number(rate).toFixed(1)}%</span></td>
            <td style="color:var(--text-muted)">${r.avg_response_time ? Math.round(r.avg_response_time) : '—'}</td>
            <td><span style="background:${stBg};color:${stC};font-size:11px;font-weight:600;padding:2px 8px;border-radius:20px">${status}</span></td>
        </tr>`;
    }).join('');
}

const today = new Date();
const week  = new Date(today); week.setDate(week.getDate() - 7);
document.getElementById('f-date-to').value   = today.toISOString().slice(0,10);
document.getElementById('f-date-from').value = week.toISOString().slice(0,10);

document.addEventListener('DOMContentLoaded', loadReport);
</script>
@endpush
