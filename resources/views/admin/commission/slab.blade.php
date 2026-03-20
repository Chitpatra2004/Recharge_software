@extends('layouts.admin')
@section('title', 'Commission Slab')
@section('page-title', 'Commission Slab')

@section('content')
<div class="breadcrumb">
    <a href="/admin/dashboard">Dashboard</a>
    <svg class="breadcrumb-sep" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <span>Commission Slab</span>
</div>

<div style="display:flex;justify-content:flex-end;margin-bottom:16px">
    <button class="btn btn-primary btn-sm" onclick="openModal()">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:15px;height:15px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Add Slab
    </button>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Operator</th>
                    <th>Category</th>
                    <th>Min Amount (₹)</th>
                    <th>Max Amount (₹)</th>
                    <th>Commission %</th>
                    <th>Flat Fee (₹)</th>
                    <th>Effective From</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="slab-tbody">
                <tr><td colspan="9"><div class="loading-overlay"><div class="spinner"></div> Loading…</div></td></tr>
            </tbody>
        </table>
    </div>
</div>

{{-- Modal --}}
<div id="slab-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:200;align-items:center;justify-content:center">
    <div style="background:#fff;border-radius:var(--radius);padding:28px;width:480px;max-width:95vw;box-shadow:var(--shadow-lg)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
            <h3 style="font-size:15px;font-weight:700" id="modal-title">Add Commission Slab</h3>
            <button onclick="closeModal()" style="background:none;border:none;cursor:pointer;color:var(--text-muted)">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:18px;height:18px"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
            <div>
                <label style="font-size:12px;font-weight:600;color:var(--text-secondary);display:block;margin-bottom:4px">Operator Code</label>
                <input type="text" id="m-operator" placeholder="AIRTEL" style="width:100%;border:1px solid var(--border);border-radius:6px;padding:8px 12px;font-size:13px">
            </div>
            <div>
                <label style="font-size:12px;font-weight:600;color:var(--text-secondary);display:block;margin-bottom:4px">Category</label>
                <select id="m-category" style="width:100%;border:1px solid var(--border);border-radius:6px;padding:8px 12px;font-size:13px">
                    <option value="mobile">Mobile</option>
                    <option value="dth">DTH</option>
                    <option value="broadband">Broadband</option>
                </select>
            </div>
            <div>
                <label style="font-size:12px;font-weight:600;color:var(--text-secondary);display:block;margin-bottom:4px">Min Amount (₹)</label>
                <input type="number" id="m-min" placeholder="0" min="0" style="width:100%;border:1px solid var(--border);border-radius:6px;padding:8px 12px;font-size:13px">
            </div>
            <div>
                <label style="font-size:12px;font-weight:600;color:var(--text-secondary);display:block;margin-bottom:4px">Max Amount (₹)</label>
                <input type="number" id="m-max" placeholder="10000" min="0" style="width:100%;border:1px solid var(--border);border-radius:6px;padding:8px 12px;font-size:13px">
            </div>
            <div>
                <label style="font-size:12px;font-weight:600;color:var(--text-secondary);display:block;margin-bottom:4px">Commission %</label>
                <input type="number" id="m-percent" placeholder="2.5" step="0.01" min="0" max="100" style="width:100%;border:1px solid var(--border);border-radius:6px;padding:8px 12px;font-size:13px">
            </div>
            <div>
                <label style="font-size:12px;font-weight:600;color:var(--text-secondary);display:block;margin-bottom:4px">Flat Fee (₹)</label>
                <input type="number" id="m-flat" placeholder="0" step="0.01" min="0" style="width:100%;border:1px solid var(--border);border-radius:6px;padding:8px 12px;font-size:13px">
            </div>
            <div style="grid-column:1/-1">
                <label style="font-size:12px;font-weight:600;color:var(--text-secondary);display:block;margin-bottom:4px">Effective From</label>
                <input type="date" id="m-effective" style="width:100%;border:1px solid var(--border);border-radius:6px;padding:8px 12px;font-size:13px">
            </div>
        </div>
        <div style="display:flex;gap:10px;margin-top:18px">
            <button class="btn btn-primary" style="flex:1" onclick="saveSlab()">Save Slab</button>
            <button class="btn btn-outline" onclick="closeModal()">Cancel</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let slabs = [];
let editingId = null;

// Load from localStorage (no dedicated API for slabs yet — store locally)
function loadSlabs() {
    slabs = JSON.parse(localStorage.getItem('commission_slabs') || '[]');
    renderSlabs();
}

function renderSlabs() {
    const tbody = document.getElementById('slab-tbody');
    if (!slabs.length) {
        tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;color:var(--text-muted);padding:24px">No slabs configured yet.</td></tr>';
        return;
    }
    tbody.innerHTML = slabs.map((s, i) => `<tr>
        <td style="color:var(--text-muted)">${i+1}</td>
        <td><strong>${s.operator}</strong></td>
        <td>${s.category}</td>
        <td>${fmtAmt(s.min_amount)}</td>
        <td>${fmtAmt(s.max_amount)}</td>
        <td><strong style="color:var(--accent-green)">${s.commission_pct}%</strong></td>
        <td>${fmtAmt(s.flat_fee)}</td>
        <td>${s.effective_from||'—'}</td>
        <td style="display:flex;gap:6px">
            <button class="btn btn-outline btn-sm" onclick="openModal(${i})">Edit</button>
            <button class="btn btn-outline btn-sm" style="color:var(--accent-red)" onclick="deleteSlab(${i})">Del</button>
        </td>
    </tr>`).join('');
}

function openModal(idx = null) {
    editingId = idx;
    const s = idx !== null ? slabs[idx] : null;
    document.getElementById('modal-title').textContent = s ? 'Edit Commission Slab' : 'Add Commission Slab';
    document.getElementById('m-operator').value  = s?.operator       || '';
    document.getElementById('m-category').value  = s?.category       || 'mobile';
    document.getElementById('m-min').value        = s?.min_amount     || '';
    document.getElementById('m-max').value        = s?.max_amount     || '';
    document.getElementById('m-percent').value    = s?.commission_pct || '';
    document.getElementById('m-flat').value       = s?.flat_fee       || '0';
    document.getElementById('m-effective').value  = s?.effective_from || new Date().toISOString().slice(0,10);
    document.getElementById('slab-modal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('slab-modal').style.display = 'none';
}

function saveSlab() {
    const operator = document.getElementById('m-operator').value.trim().toUpperCase();
    const min_amount = parseFloat(document.getElementById('m-min').value);
    const max_amount = parseFloat(document.getElementById('m-max').value);
    const commission_pct = parseFloat(document.getElementById('m-percent').value);
    if (!operator || isNaN(commission_pct)) { alert('Operator code and commission % are required.'); return; }
    const slab = {
        operator,
        category:       document.getElementById('m-category').value,
        min_amount:     isNaN(min_amount) ? 0 : min_amount,
        max_amount:     isNaN(max_amount) ? 0 : max_amount,
        commission_pct,
        flat_fee:       parseFloat(document.getElementById('m-flat').value) || 0,
        effective_from: document.getElementById('m-effective').value,
    };
    if (editingId !== null) slabs[editingId] = slab;
    else slabs.push(slab);
    localStorage.setItem('commission_slabs', JSON.stringify(slabs));
    closeModal();
    renderSlabs();
}

function deleteSlab(idx) {
    if (!confirm('Delete this commission slab?')) return;
    slabs.splice(idx, 1);
    localStorage.setItem('commission_slabs', JSON.stringify(slabs));
    renderSlabs();
}

document.addEventListener('DOMContentLoaded', loadSlabs);
</script>
@endpush
