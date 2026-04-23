@extends('layouts.admin')
@section('title', 'User Detail')
@section('page-title', 'User Detail')

@push('head')
<style>
.stat-card .val{font-size:22px;font-weight:700;margin-bottom:2px;color:#1e293b}
.stat-card .lbl{font-size:11.5px;color:#64748b}
.stat-card.purple{border-left:4px solid var(--accent-purple)}
.info-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
@media(max-width:700px){.info-grid{grid-template-columns:1fr}}
.info-row{display:flex;flex-direction:column;gap:3px}
.info-label{font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px}
.info-value{font-size:13.5px;font-weight:500;color:var(--text-primary)}
.section-tabs{display:flex;gap:0;border-bottom:2px solid var(--border);margin-bottom:18px}
.stab{padding:9px 18px;font-size:13px;font-weight:600;cursor:pointer;border:none;background:none;color:var(--text-secondary);border-bottom:3px solid transparent;margin-bottom:-2px;transition:all .15s;font-family:inherit}
.stab:hover{color:var(--text-primary)}
.stab.active{color:var(--accent-blue);border-bottom-color:var(--accent-blue)}
.tab-pane{display:none}
.tab-pane.active{display:block}
</style>
@endpush

@section('content')
<div style="margin-bottom:18px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
    <div class="breadcrumb" style="margin-bottom:0">
        <a href="/admin/dashboard">Dashboard</a>
        <svg class="breadcrumb-sep" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <a href="/admin/users">Users</a>
        <svg class="breadcrumb-sep" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span id="bc-name">Loading…</span>
    </div>
    <a href="/admin/users" class="btn btn-outline btn-sm">← Back to Users</a>
</div>

{{-- Profile Header --}}
<div class="card" style="margin-bottom:20px;padding:24px" id="profile-header">
    <div style="display:flex;align-items:center;gap:18px;flex-wrap:wrap">
        <div id="user-avatar" style="width:64px;height:64px;border-radius:16px;background:linear-gradient(135deg,var(--accent-blue),#7c3aed);display:flex;align-items:center;justify-content:center;font-size:26px;font-weight:800;color:#fff;flex-shrink:0">—</div>
        <div style="flex:1;min-width:0">
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                <h2 id="user-name" style="font-size:20px;font-weight:700;color:var(--text-primary)">Loading…</h2>
                <span id="user-status-badge"></span>
                <span id="user-role-badge"></span>
            </div>
            <div id="user-email" style="font-size:13px;color:var(--text-secondary);margin-top:3px"></div>
            <div id="user-mobile" style="font-size:13px;color:var(--text-muted);margin-top:2px"></div>
        </div>
        <div style="text-align:right">
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:2px">Wallet Balance</div>
            <div id="wallet-balance" style="font-size:26px;font-weight:800;color:#10b981">—</div>
        </div>
    </div>
</div>

{{-- Stat Strip --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px">
    <div class="stat-card blue"><div class="val" id="st-total">—</div><div class="lbl">Total Recharges</div></div>
    <div class="stat-card green"><div class="val" id="st-success">—</div><div class="lbl">Successful</div></div>
    <div class="stat-card red"><div class="val" id="st-failed">—</div><div class="lbl">Failed</div></div>
    <div class="stat-card purple"><div class="val" id="st-amount">—</div><div class="lbl">Total Amount</div></div>
</div>

{{-- Tabs --}}
<div class="card">
    <div style="padding:0 20px">
        <div class="section-tabs">
            <button class="stab active" onclick="showTab('info')">Profile Info</button>
            <button class="stab" onclick="showTab('recharges')">Recharges</button>
            <button class="stab" onclick="showTab('wallet')">Wallet Txns</button>
            <button class="stab" onclick="showTab('payments')">Payment Requests</button>
        </div>
    </div>

    {{-- Info Tab --}}
    <div class="tab-pane active" id="tab-info" style="padding:20px">
        <div class="info-grid" id="info-grid">
            <div class="info-row"><span class="info-label">Full Name</span><span class="info-value" id="inf-name">—</span></div>
            <div class="info-row"><span class="info-label">Email</span><span class="info-value" id="inf-email">—</span></div>
            <div class="info-row"><span class="info-label">Mobile</span><span class="info-value" id="inf-mobile">—</span></div>
            <div class="info-row"><span class="info-label">Role</span><span class="info-value" id="inf-role">—</span></div>
            <div class="info-row"><span class="info-label">Status</span><span class="info-value" id="inf-status">—</span></div>
            <div class="info-row"><span class="info-label">Approval Status</span><span class="info-value" id="inf-approval">—</span></div>
            <div class="info-row"><span class="info-label">PAN</span><span class="info-value" id="inf-pan">—</span></div>
            <div class="info-row"><span class="info-label">GST</span><span class="info-value" id="inf-gst">—</span></div>
            <div class="info-row"><span class="info-label">City</span><span class="info-value" id="inf-city">—</span></div>
            <div class="info-row"><span class="info-label">State</span><span class="info-value" id="inf-state">—</span></div>
            <div class="info-row"><span class="info-label">Registered</span><span class="info-value" id="inf-created">—</span></div>
            <div class="info-row"><span class="info-label">Last Updated</span><span class="info-value" id="inf-updated">—</span></div>
        </div>
    </div>

    {{-- Recharges Tab --}}
    <div class="tab-pane" id="tab-recharges">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th>Mobile</th>
                        <th>Operator</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody id="recharge-tbody">
                    <tr><td colspan="6" style="text-align:center;padding:24px;color:var(--text-muted)">Loading…</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Wallet Txns Tab --}}
    <div class="tab-pane" id="tab-wallet">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Balance After</th>
                        <th>Description</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody id="wallet-tbody">
                    <tr><td colspan="6" style="text-align:center;padding:24px;color:var(--text-muted)">Loading…</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Payment Requests Tab --}}
    <div class="tab-pane" id="tab-payments">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody id="payments-tbody">
                    <tr><td colspan="5" style="text-align:center;padding:24px;color:var(--text-muted)">Loading…</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const USER_ID = {{ $userId }};

function fmtMoney(n){ return '₹'+Number(n||0).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2}); }
function fmtDate(d){ return d ? new Date(d).toLocaleString('en-IN',{day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'}) : '—'; }

function showTab(name) {
    document.querySelectorAll('.stab').forEach((b,i) => b.classList.remove('active'));
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
    document.getElementById('tab-' + name).classList.add('active');
    event.target.classList.add('active');
}

function statusBadge(s) {
    const map = {active:'#d1fae5:#065f46',inactive:'#fef3c7:#92400e',suspended:'#fee2e2:#991b1b'};
    const [bg,c] = (map[s]||'#f1f5f9:#64748b').split(':');
    return `<span style="background:${bg};color:${c};padding:3px 10px;border-radius:20px;font-size:11.5px;font-weight:600">${s}</span>`;
}
function roleBadge(r) {
    const map = {api_user:'#ede9fe:#5b21b6',retailer:'#dbeafe:#1e40af',distributor:'#fef3c7:#92400e',buyer:'#d1fae5:#065f46',admin:'#fee2e2:#991b1b'};
    const [bg,c] = (map[r]||'#f1f5f9:#64748b').split(':');
    return `<span style="background:${bg};color:${c};padding:3px 10px;border-radius:20px;font-size:11.5px;font-weight:600">${r||'—'}</span>`;
}
function txnStatus(s) {
    const map = {success:'#d1fae5:#059669',failed:'#fee2e2:#dc2626',pending:'#fef3c7:#d97706',refunded:'#ede9fe:#7c3aed'};
    const [bg,c] = (map[s]||'#f1f5f9:#64748b').split(':');
    return `<span style="background:${bg};color:${c};padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600">${s}</span>`;
}

async function loadUserDetail() {
    const res = await apiFetch(`/api/v1/employee/users/${USER_ID}`);
    if (!res) return;
    const d = await res.json();

    if (!res.ok) {
        document.getElementById('profile-header').innerHTML = `<div style="padding:30px;text-align:center;color:#ef4444">${d.message || 'User not found.'}</div>`;
        return;
    }

    const u = d.user;
    const bal = d.wallet_balance || 0;
    const rs  = d.recharge_stats || {};

    // Header
    const init = (u.name || '?').charAt(0).toUpperCase();
    document.getElementById('user-avatar').textContent     = init;
    document.getElementById('user-name').textContent       = u.name || '—';
    document.getElementById('bc-name').textContent         = u.name || 'User';
    document.getElementById('user-status-badge').innerHTML = statusBadge(u.status || 'inactive');
    document.getElementById('user-role-badge').innerHTML   = roleBadge(u.role);
    document.getElementById('user-email').textContent      = u.email || '';
    document.getElementById('user-mobile').textContent     = u.mobile ? '📱 ' + u.mobile : '';
    document.getElementById('wallet-balance').textContent  = fmtMoney(bal);

    // Stats
    document.getElementById('st-total').textContent   = rs.total || 0;
    document.getElementById('st-success').textContent = rs.success_count || 0;
    document.getElementById('st-failed').textContent  = rs.failed_count  || 0;
    document.getElementById('st-amount').textContent  = fmtMoney(rs.total_amount || 0);

    // Info tab
    document.getElementById('inf-name').textContent     = u.name || '—';
    document.getElementById('inf-email').textContent    = u.email || '—';
    document.getElementById('inf-mobile').textContent   = u.mobile || '—';
    document.getElementById('inf-role').textContent     = u.role || '—';
    document.getElementById('inf-status').textContent   = u.status || '—';
    document.getElementById('inf-approval').textContent = u.approval_status || '—';
    document.getElementById('inf-pan').textContent      = u.pan_number || (u.pan_image_path ? '✓ Uploaded' : '—');
    document.getElementById('inf-gst').textContent      = u.gst_number  || (u.gst_certificate_path ? '✓ Uploaded' : '—');
    document.getElementById('inf-city').textContent     = u.city  || '—';
    document.getElementById('inf-state').textContent    = u.state || '—';
    document.getElementById('inf-created').textContent  = fmtDate(u.created_at);
    document.getElementById('inf-updated').textContent  = fmtDate(u.updated_at);

    // Recharges tab
    const recharges = d.recent_recharges || [];
    document.getElementById('recharge-tbody').innerHTML = recharges.length
        ? recharges.map(r => `<tr>
            <td style="font-size:12px;color:var(--text-muted)">#${r.id}</td>
            <td>${r.mobile||'—'}</td>
            <td><span style="background:var(--bg-page);padding:2px 8px;border-radius:12px;font-size:11px;font-weight:600">${r.operator_code||'—'}</span></td>
            <td style="font-weight:600">${fmtMoney(r.amount)}</td>
            <td>${txnStatus(r.status)}</td>
            <td style="font-size:12px;color:var(--text-muted)">${fmtDate(r.created_at)}</td>
          </tr>`).join('')
        : '<tr><td colspan="6" style="text-align:center;padding:24px;color:var(--text-muted)">No recharges found</td></tr>';

    // Wallet txns tab
    const wtxns = d.wallet_txns || [];
    document.getElementById('wallet-tbody').innerHTML = wtxns.length
        ? wtxns.map(t => {
            const isCredit = t.type === 'credit';
            return `<tr>
                <td style="font-size:12px;color:var(--text-muted)">#${t.id}</td>
                <td><span style="background:${isCredit?'#d1fae5':'#fee2e2'};color:${isCredit?'#065f46':'#991b1b'};padding:2px 8px;border-radius:12px;font-size:11px;font-weight:600">${t.type}</span></td>
                <td style="font-weight:600;color:${isCredit?'#10b981':'#ef4444'}">${isCredit?'+':'−'}${fmtMoney(t.amount)}</td>
                <td style="font-weight:600;color:var(--accent-blue)">${fmtMoney(t.balance_after)}</td>
                <td style="font-size:12px;color:var(--text-secondary)">${t.description||'—'}</td>
                <td style="font-size:12px;color:var(--text-muted)">${fmtDate(t.created_at)}</td>
              </tr>`;
          }).join('')
        : '<tr><td colspan="6" style="text-align:center;padding:24px;color:var(--text-muted)">No wallet transactions</td></tr>';

    // Payment requests tab
    const payments = d.payment_requests || [];
    document.getElementById('payments-tbody').innerHTML = payments.length
        ? payments.map(p => `<tr>
            <td style="font-size:12px;color:var(--text-muted)">#${p.id}</td>
            <td style="font-weight:600">${fmtMoney(p.amount)}</td>
            <td style="font-size:12px">${p.payment_method||p.method||'—'}</td>
            <td>${txnStatus(p.status)}</td>
            <td style="font-size:12px;color:var(--text-muted)">${fmtDate(p.created_at)}</td>
          </tr>`).join('')
        : '<tr><td colspan="5" style="text-align:center;padding:24px;color:var(--text-muted)">No payment requests</td></tr>';
}

document.addEventListener('DOMContentLoaded', loadUserDetail);
</script>
@endpush
