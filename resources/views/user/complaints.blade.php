@extends('layouts.user')
@section('title','My Complaints')
@section('page-title','Complaints')

@section('content')
<div class="breadcrumb"><a href="/user/dashboard">Dashboard</a><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg><span>Complaints</span></div>

<div style="display:grid;grid-template-columns:320px 1fr;gap:16px;align-items:start">
    {{-- Raise Complaint --}}
    <div class="card">
        <div class="card-header"><span class="card-title">Raise a Complaint</span></div>
        <div class="card-body" style="display:flex;flex-direction:column;gap:14px">
            <div>
                <label style="font-size:12px;font-weight:600;color:var(--muted);display:block;margin-bottom:5px">Type</label>
                <select id="c-type" style="width:100%;background:var(--card2);border:1px solid var(--border2);border-radius:8px;padding:9px 12px;font-size:13px;color:var(--text);outline:none">
                    <option value="recharge_failed">Recharge Failed</option>
                    <option value="balance_deducted">Balance Deducted</option>
                    <option value="wrong_recharge">Wrong Recharge</option>
                    <option value="refund">Refund Request</option>
                    <option value="operator_delay">Operator Delay</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div>
                <label style="font-size:12px;font-weight:600;color:var(--muted);display:block;margin-bottom:5px">Transaction ID (optional)</label>
                <input type="text" id="c-txn" placeholder="TXN..." style="width:100%;background:var(--card2);border:1px solid var(--border2);border-radius:8px;padding:9px 12px;font-size:13px;color:var(--text);outline:none;font-family:inherit">
            </div>
            <div>
                <label style="font-size:12px;font-weight:600;color:var(--muted);display:block;margin-bottom:5px">Description</label>
                <textarea id="c-desc" rows="4" placeholder="Describe your issue in detail…"
                    style="width:100%;background:var(--card2);border:1px solid var(--border2);border-radius:8px;padding:9px 12px;font-size:13px;color:var(--text);outline:none;font-family:inherit;resize:vertical"></textarea>
            </div>
            <div id="c-msg" style="display:none;font-size:13px;border-radius:8px;padding:10px 12px"></div>
            <button class="btn btn-primary" style="width:100%;justify-content:center" onclick="submitComplaint()" id="c-btn">Submit Complaint</button>
        </div>
    </div>

    {{-- Complaint List --}}
    <div class="card">
        <div class="card-header"><span class="card-title">My Complaints</span></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>ID</th><th>Type</th><th>Status</th><th>Priority</th><th>Raised</th></tr></thead>
                <tbody id="comp-tbody"><tr><td colspan="5"><div class="loading"><div class="spinner"></div> Loading…</div></td></tr></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
async function loadComplaints() {
    const res = await apiFetch('/api/v1/complaints?per_page=20');
    if (!res) return;
    const data = await res.json();
    const items = data.data || data.complaints || [];
    const pColor = { low:'var(--muted)', medium:'var(--orange)', high:'var(--red)', critical:'#a78bfa' };
    document.getElementById('comp-tbody').innerHTML = items.length
        ? items.map(c=>`<tr>
            <td style="font-family:monospace;font-size:11px;color:var(--muted)">${c.id||'—'}</td>
            <td style="font-size:12px">${(c.type||'').replace(/_/g,' ')}</td>
            <td><span class="badge ${c.status==='resolved'||c.status==='closed'?'success':c.status==='escalated'?'failure':'pending'}">${(c.status||'').replace(/_/g,' ')}</span></td>
            <td style="font-size:12px;font-weight:600;color:${pColor[c.priority]||'#fff'}">${c.priority||'—'}</td>
            <td>${fmtAgo(c.created_at)}</td>
          </tr>`).join('')
        : '<tr><td colspan="5" style="text-align:center;color:var(--muted);padding:24px">No complaints raised yet</td></tr>';
}

async function submitComplaint() {
    const type        = document.getElementById('c-type').value;
    const description = document.getElementById('c-desc').value.trim();
    const txn_id      = document.getElementById('c-txn').value.trim();
    const msg         = document.getElementById('c-msg');

    msg.style.display = 'none';
    if (!description) { showMsg('Please describe your issue.','error'); return; }

    document.getElementById('c-btn').disabled = true;
    const body = { type, description };
    if (txn_id) body.recharge_transaction_id = txn_id;
    const res = await apiFetch('/api/v1/complaints', { method:'POST', body: JSON.stringify(body) });
    document.getElementById('c-btn').disabled = false;
    if (!res) return;
    if (res.ok) {
        showMsg('Complaint submitted successfully!','success');
        document.getElementById('c-desc').value = '';
        document.getElementById('c-txn').value  = '';
        loadComplaints();
    } else {
        const d = await res.json();
        showMsg(d.message||'Failed to submit complaint.','error');
    }
}

function showMsg(txt, type) {
    const m = document.getElementById('c-msg');
    m.textContent = txt;
    m.style.cssText = `display:block;font-size:13px;border-radius:8px;padding:10px 12px;background:${type==='success'?'rgba(16,185,129,.1)':'rgba(239,68,68,.1)'};border:1px solid ${type==='success'?'rgba(16,185,129,.25)':'rgba(239,68,68,.25)'};color:${type==='success'?'#6ee7b7':'#fca5a5'}`;
}

document.addEventListener('DOMContentLoaded', () => loadComplaints());
</script>
@endpush
