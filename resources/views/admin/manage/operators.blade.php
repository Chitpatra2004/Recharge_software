@extends('layouts.admin')

@section('title', 'Manage Operators')
@section('page-title', 'Manage Operators')

@section('content')

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
    <div>
        <h1 style="font-size:20px;font-weight:700;color:var(--text-primary)">Manage Operators</h1>
        <div class="breadcrumb" style="margin-bottom:0;margin-top:3px">
            <a href="/admin/dashboard">Dashboard</a>
            <svg class="breadcrumb-sep" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            <span>Manage</span>
            <svg class="breadcrumb-sep" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            <span>Operators</span>
        </div>
    </div>
    <button class="btn btn-primary btn-sm" onclick="openModal()">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Add Operator
    </button>
</div>

{{-- Operators Table --}}
<div class="card">
    <div class="card-header" style="justify-content:space-between">
        <div class="card-title">Operators</div>
        <div style="display:flex;gap:10px;align-items:center">
            <input type="text" id="f-search" placeholder="Search operators…" oninput="filterTable()" style="border:1px solid var(--border);border-radius:8px;padding:6px 12px;font-size:13px;color:var(--text-primary);background:#fff;outline:none;width:200px">
            <button class="btn btn-outline btn-sm" onclick="loadOperators()">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Refresh
            </button>
        </div>
    </div>
    <div class="card-body" style="padding:0">
        <div id="ops-loading" class="loading-overlay"><div class="spinner"></div> Loading operators…</div>
        <div class="table-wrap" id="ops-table-wrap" style="display:none">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Success Rate</th>
                        <th>Commission %</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="ops-tbody"></tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add/Edit Modal --}}
<div id="modal-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:200;display:none;align-items:center;justify-content:center">
    <div style="background:#fff;border-radius:var(--radius);padding:28px;width:480px;max-width:95vw;box-shadow:var(--shadow-lg);position:relative">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
            <h2 style="font-size:16px;font-weight:700" id="modal-title">Add Operator</h2>
            <button onclick="closeModal()" style="background:none;border:none;cursor:pointer;color:var(--text-muted);width:28px;height:28px;display:flex;align-items:center;justify-content:center;border-radius:6px" onmouseover="this.style.background='var(--bg-page)'" onmouseout="this.style.background='none'">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:18px;height:18px"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div style="display:flex;flex-direction:column;gap:14px">
            <input type="hidden" id="edit-id">
            <div>
                <label style="display:block;font-size:12px;font-weight:600;color:var(--text-secondary);margin-bottom:5px">Operator Name *</label>
                <input type="text" id="op-name" placeholder="e.g. Jio Mobile" style="width:100%;border:1px solid var(--border);border-radius:8px;padding:9px 12px;font-size:13px;outline:none;color:var(--text-primary)">
            </div>
            <div>
                <label style="display:block;font-size:12px;font-weight:600;color:var(--text-secondary);margin-bottom:5px">Operator Code *</label>
                <input type="text" id="op-code" placeholder="e.g. JIO" style="width:100%;border:1px solid var(--border);border-radius:8px;padding:9px 12px;font-size:13px;outline:none;color:var(--text-primary)">
            </div>
            <div>
                <label style="display:block;font-size:12px;font-weight:600;color:var(--text-secondary);margin-bottom:5px">Category *</label>
                <select id="op-category" style="width:100%;border:1px solid var(--border);border-radius:8px;padding:9px 12px;font-size:13px;outline:none;color:var(--text-primary);background:#fff">
                    <option value="">Select category</option>
                    <option value="prepaid">Prepaid</option>
                    <option value="postpaid">Postpaid</option>
                    <option value="dth">DTH</option>
                    <option value="broadband">Broadband</option>
                    <option value="landline">Landline</option>
                </select>
            </div>
            <div>
                <label style="display:block;font-size:12px;font-weight:600;color:var(--text-secondary);margin-bottom:5px">Status</label>
                <select id="op-status" style="width:100%;border:1px solid var(--border);border-radius:8px;padding:9px 12px;font-size:13px;outline:none;color:var(--text-primary);background:#fff">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="maintenance">Maintenance</option>
                </select>
            </div>
        </div>
        <div id="modal-error" style="display:none;background:#fee2e2;color:#dc2626;padding:10px 14px;border-radius:8px;font-size:13px;margin-top:14px"></div>
        <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px">
            <button class="btn btn-outline btn-sm" onclick="closeModal()">Cancel</button>
            <button class="btn btn-primary btn-sm" id="save-btn" onclick="saveOperator()">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                Save Operator
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let allOperators = [];

async function loadOperators() {
    document.getElementById('ops-loading').style.display = 'flex';
    document.getElementById('ops-table-wrap').style.display = 'none';

    const res = await apiFetch('/api/v1/operators');
    if (!res) return;
    const json = await res.json();
    allOperators = json.data || json.operators || json || [];

    document.getElementById('ops-loading').style.display = 'none';
    document.getElementById('ops-table-wrap').style.display = 'block';
    renderTable(allOperators);
}

function filterTable() {
    const q = document.getElementById('f-search').value.toLowerCase();
    const filtered = allOperators.filter(op =>
        (op.name || '').toLowerCase().includes(q) ||
        (op.code || '').toLowerCase().includes(q) ||
        (op.category || '').toLowerCase().includes(q)
    );
    renderTable(filtered);
}

function renderTable(ops) {
    const tbody = document.getElementById('ops-tbody');
    if (!ops.length) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:var(--text-muted);padding:24px">No operators found</td></tr>';
        return;
    }
    tbody.innerHTML = ops.map((op, i) => {
        const st = (op.status || 'active').toLowerCase();
        const stBg = st === 'active' ? '#d1fae5' : st === 'inactive' ? '#fee2e2' : '#fef3c7';
        const stC  = st === 'active' ? '#059669' : st === 'inactive' ? '#dc2626' : '#d97706';
        const rate = op.success_rate ?? 0;
        const rc = rate >= 90 ? '#10b981' : rate >= 70 ? '#f59e0b' : '#ef4444';
        return `<tr>
            <td style="color:var(--text-muted)">${i+1}</td>
            <td><strong>${op.name || '—'}</strong></td>
            <td><code style="background:var(--bg-page);padding:2px 6px;border-radius:4px;font-size:12px">${op.code || '—'}</code></td>
            <td><span style="background:var(--bg-page);padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600">${op.category || '—'}</span></td>
            <td><span style="background:${stBg};color:${stC};font-size:11px;font-weight:600;padding:2px 8px;border-radius:20px">${st}</span></td>
            <td><span style="color:${rc};font-weight:600">${Number(rate).toFixed(1)}%</span></td>
            <td>${op.commission_percent != null ? Number(op.commission_percent).toFixed(2)+'%' : '—'}</td>
            <td>
                <div style="display:flex;gap:6px">
                    <button class="btn btn-outline btn-sm" onclick="editOperator(${JSON.stringify(op).replace(/"/g,'&quot;')})">Edit</button>
                    <button class="btn btn-sm" style="background:#fee2e2;color:#dc2626;border:none;cursor:pointer;border-radius:8px;padding:6px 12px;font-size:12px;font-weight:600" onclick="toggleStatus(${op.id}, '${st}')">
                        ${st === 'active' ? 'Disable' : 'Enable'}
                    </button>
                </div>
            </td>
        </tr>`;
    }).join('');
}

function openModal(op) {
    document.getElementById('modal-title').textContent = op ? 'Edit Operator' : 'Add Operator';
    document.getElementById('edit-id').value     = op?.id || '';
    document.getElementById('op-name').value     = op?.name || '';
    document.getElementById('op-code').value     = op?.code || '';
    document.getElementById('op-category').value = op?.category || '';
    document.getElementById('op-status').value   = op?.status || 'active';
    document.getElementById('modal-error').style.display = 'none';
    document.getElementById('modal-overlay').style.display = 'flex';
}

function editOperator(op) { openModal(op); }

function closeModal() {
    document.getElementById('modal-overlay').style.display = 'none';
}

async function saveOperator() {
    const id   = document.getElementById('edit-id').value;
    const name = document.getElementById('op-name').value.trim();
    const code = document.getElementById('op-code').value.trim();
    const cat  = document.getElementById('op-category').value;
    const stat = document.getElementById('op-status').value;

    if (!name || !code || !cat) {
        document.getElementById('modal-error').textContent = 'Name, Code and Category are required.';
        document.getElementById('modal-error').style.display = 'block';
        return;
    }

    const btn = document.getElementById('save-btn');
    btn.disabled = true; btn.textContent = 'Saving…';

    const method = id ? 'PUT' : 'POST';
    const url    = id ? `/api/v1/operators/${id}` : '/api/v1/operators';

    try {
        const res = await apiFetch(url, {
            method,
            body: JSON.stringify({ name, code, category: cat, status: stat }),
        });
        if (!res) return;
        if (res.ok) {
            closeModal();
            loadOperators();
        } else {
            const err = await res.json();
            document.getElementById('modal-error').textContent = err.message || 'Failed to save.';
            document.getElementById('modal-error').style.display = 'block';
        }
    } finally {
        btn.disabled = false; btn.textContent = 'Save Operator';
    }
}

async function toggleStatus(id, currentStatus) {
    const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
    if (!confirm(`Set operator status to ${newStatus}?`)) return;
    const res = await apiFetch(`/api/v1/operators/${id}`, {
        method: 'PATCH',
        body: JSON.stringify({ status: newStatus }),
    });
    if (res?.ok) loadOperators();
}

// Close modal on overlay click
document.getElementById('modal-overlay').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

document.addEventListener('DOMContentLoaded', loadOperators);
</script>
@endpush
