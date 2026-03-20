@extends('layouts.admin')
@section('title', 'API Keys')
@section('page-title', 'API Keys')

@section('content')
<div class="breadcrumb">
    <a href="/admin/dashboard">Dashboard</a>
    <svg class="breadcrumb-sep" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <span>API Keys</span>
</div>

<div class="note-bar" style="margin-bottom:20px">
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    API keys grant machine-to-machine access for buyer/partner users. Keys are shown <strong>once</strong> on generation. Store them securely.
</div>

<div style="display:flex;justify-content:flex-end;margin-bottom:16px">
    <button class="btn btn-primary btn-sm" onclick="openGenModal()">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:15px;height:15px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Generate New Key
    </button>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Key ID</th>
                    <th>Name / Label</th>
                    <th>User</th>
                    <th>Prefix</th>
                    <th>Scopes</th>
                    <th>Created</th>
                    <th>Last Used</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="keys-tbody">
                <tr><td colspan="9"><div class="loading-overlay"><div class="spinner"></div> Loading…</div></td></tr>
            </tbody>
        </table>
    </div>
    <div id="keys-pagination" style="padding:12px 16px;border-top:1px solid var(--border);display:flex;gap:8px;justify-content:flex-end"></div>
</div>

{{-- Generate Modal --}}
<div id="gen-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:200;align-items:center;justify-content:center">
    <div style="background:#fff;border-radius:var(--radius);padding:28px;width:460px;max-width:95vw;box-shadow:var(--shadow-lg);color:#1e293b">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
            <h3 style="font-size:15px;font-weight:700;color:#1e293b">Generate API Key</h3>
            <button onclick="closeGenModal()" style="background:none;border:none;cursor:pointer;color:#64748b">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:18px;height:18px"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div id="gen-form">
            <div style="display:flex;flex-direction:column;gap:14px">
                {{-- User search --}}
                <div>
                    <label style="font-size:12px;font-weight:600;color:#475569;display:block;margin-bottom:4px">User (Retailer / Partner) <span style="color:#ef4444">*</span></label>
                    <input type="text" id="m-user-search" placeholder="Search by name or email…"
                        oninput="searchUsers(this.value)"
                        style="width:100%;border:1px solid #cbd5e1;border-radius:6px;padding:8px 12px;font-size:13px;background:#fff;color:#1e293b;color-scheme:light;outline:none">
                    <div id="m-user-results" style="border:1px solid #e2e8f0;border-radius:6px;margin-top:4px;max-height:140px;overflow-y:auto;display:none;background:#fff"></div>
                    <input type="hidden" id="m-user-id">
                    <div id="m-user-selected" style="display:none;margin-top:6px;font-size:12px;padding:6px 10px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:6px;color:#166534"></div>
                </div>
                {{-- Key Name --}}
                <div>
                    <label style="font-size:12px;font-weight:600;color:#475569;display:block;margin-bottom:4px">Key Name / Label <span style="color:#ef4444">*</span></label>
                    <input type="text" id="m-keyname" placeholder="e.g. Partner Integration Key"
                        style="width:100%;border:1px solid #cbd5e1;border-radius:6px;padding:8px 12px;font-size:13px;background:#fff;color:#1e293b;color-scheme:light;outline:none">
                </div>
                {{-- Scopes --}}
                <div>
                    <label style="font-size:12px;font-weight:600;color:#475569;display:block;margin-bottom:6px">Scopes (Permissions)</label>
                    <div style="display:flex;flex-direction:column;gap:6px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:10px 12px">
                        <label style="display:flex;align-items:center;gap:8px;font-size:13px;color:#1e293b;cursor:pointer"><input type="checkbox" value="recharge:read"  checked> recharge:read — view transactions</label>
                        <label style="display:flex;align-items:center;gap:8px;font-size:13px;color:#1e293b;cursor:pointer"><input type="checkbox" value="recharge:write" checked> recharge:write — initiate recharges</label>
                        <label style="display:flex;align-items:center;gap:8px;font-size:13px;color:#1e293b;cursor:pointer"><input type="checkbox" value="wallet:read"> wallet:read — view balance</label>
                    </div>
                </div>
                <div style="display:flex;gap:10px;margin-top:6px">
                    <button class="btn btn-primary" style="flex:1" onclick="generateKey()">Generate Key</button>
                    <button class="btn btn-outline" onclick="closeGenModal()">Cancel</button>
                </div>
            </div>
        </div>
        <div id="gen-result" style="display:none">
            <div style="background:#fef9c3;border:1px solid #fde047;border-radius:8px;padding:10px 12px;font-size:12.5px;color:#713f12;margin-bottom:14px;display:flex;gap:8px;align-items:flex-start">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;flex-shrink:0;margin-top:1px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                Copy this key now — it will <strong>not</strong> be shown again after you close this dialog.
            </div>
            <div style="background:#f1f5f9;border:1px solid #cbd5e1;border-radius:6px;padding:12px;font-family:monospace;font-size:12px;word-break:break-all;user-select:all;color:#1e293b" id="new-key-display"></div>
            <button class="btn btn-outline btn-sm" style="margin-top:10px;width:100%" onclick="copyKey()">📋 Copy to Clipboard</button>
            <button class="btn btn-primary btn-sm" style="margin-top:8px;width:100%" onclick="closeGenModal();loadKeys()">✓ Done — Close</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let _userSearchTimer = null;
let _currentPage = 1;

// ── Load and render keys ──────────────────────────────────────────
async function loadKeys(page = 1) {
    _currentPage = page;
    const res = await apiFetch(`/api/v1/employee/api-keys?per_page=20&page=${page}`);
    if (!res) return;
    const json = await res.json();
    const keys = json.data?.data || json.data || [];
    const tbody = document.getElementById('keys-tbody');

    if (!keys.length) {
        tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;color:var(--text-muted);padding:24px">No API keys found</td></tr>';
        document.getElementById('keys-pagination').innerHTML = '';
        return;
    }

    tbody.innerHTML = keys.map(k => `<tr>
        <td style="font-family:monospace;font-size:11px">${k.id}</td>
        <td><strong>${k.name||'—'}</strong></td>
        <td>
            <div style="font-size:12.5px;font-weight:600">${k.user?.name||'—'}</div>
            <div style="font-size:11px;color:var(--text-muted)">${k.user?.email||''}</div>
        </td>
        <td style="font-family:monospace;font-size:11px">${k.key_prefix||'—'}…</td>
        <td style="font-size:11px">${(k.scopes||[]).join(', ')||'—'}</td>
        <td>${fmtAgo(k.created_at)}</td>
        <td>${k.last_used_at ? fmtAgo(k.last_used_at) : '<span style="color:var(--text-muted)">Never</span>'}</td>
        <td>${k.is_active ? '<span class="txn-status success">active</span>' : '<span class="txn-status failure">revoked</span>'}</td>
        <td>
            <button class="btn btn-outline btn-sm" style="color:var(--accent-red);border-color:var(--accent-red)" onclick="revokeKey(${k.id})">Revoke</button>
        </td>
    </tr>`).join('');

    // Pagination
    const meta = json.data?.meta || json.meta || {};
    const pagEl = document.getElementById('keys-pagination');
    if (meta.last_page > 1) {
        let btns = '';
        for (let p = 1; p <= meta.last_page; p++) {
            btns += `<button class="btn btn-sm ${p === meta.current_page ? 'btn-primary' : 'btn-outline'}" onclick="loadKeys(${p})">${p}</button>`;
        }
        pagEl.innerHTML = btns;
    } else {
        pagEl.innerHTML = '';
    }
}

// ── Modal open/close ──────────────────────────────────────────────
function openGenModal() {
    document.getElementById('gen-form').style.display   = 'block';
    document.getElementById('gen-result').style.display = 'none';
    document.getElementById('m-keyname').value          = '';
    document.getElementById('m-user-search').value      = '';
    document.getElementById('m-user-id').value          = '';
    document.getElementById('m-user-selected').style.display = 'none';
    document.getElementById('m-user-results').style.display  = 'none';
    document.getElementById('gen-modal').style.display  = 'flex';
}

function closeGenModal() {
    document.getElementById('gen-modal').style.display = 'none';
}

// ── User search ───────────────────────────────────────────────────
function searchUsers(q) {
    clearTimeout(_userSearchTimer);
    const resultsEl = document.getElementById('m-user-results');
    if (q.length < 2) { resultsEl.style.display = 'none'; return; }
    _userSearchTimer = setTimeout(async () => {
        const res = await apiFetch(`/api/v1/employee/users/search?q=${encodeURIComponent(q)}`);
        if (!res) return;
        const json = await res.json();
        const users = json.data || [];
        if (!users.length) {
            resultsEl.innerHTML = '<div style="padding:10px 12px;font-size:12px;color:#64748b">No users found</div>';
        } else {
            resultsEl.innerHTML = users.map(u => {
                const safeName  = (u.name  || '').replace(/\\/g,'\\\\').replace(/'/g,"\\'");
                const safeEmail = (u.email || '').replace(/\\/g,'\\\\').replace(/'/g,"\\'");
                return `<div onclick="selectUser(${u.id},'${safeName}','${safeEmail}')"
                    style="padding:8px 12px;cursor:pointer;font-size:13px;border-bottom:1px solid #f1f5f9;color:#1e293b;background:#fff"
                    onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
                    <strong>${u.name||'—'}</strong>
                    <span style="color:#64748b;font-size:11px;margin-left:4px">${u.email||''}</span>
                    <span style="font-size:10px;background:#e0f2fe;color:#0369a1;padding:1px 6px;border-radius:10px;margin-left:4px">${u.role||''}</span>
                </div>`;
            }).join('');
        }
        resultsEl.style.display = 'block';
    }, 300);
}

function selectUser(id, name, email) {
    document.getElementById('m-user-id').value = id;
    document.getElementById('m-user-search').value = name;
    document.getElementById('m-user-results').style.display = 'none';
    const sel = document.getElementById('m-user-selected');
    sel.textContent = `✓ Selected: ${name} (${email})`;
    sel.style.display = 'block';
}

// ── Generate key ──────────────────────────────────────────────────
async function generateKey() {
    const userId = document.getElementById('m-user-id').value;
    const name   = document.getElementById('m-keyname').value.trim();
    const scopes = [...document.querySelectorAll('#gen-form input[type=checkbox]:checked')].map(c => c.value);

    if (!userId) { alert('Please select a user first.'); return; }
    if (!name)   { alert('Please provide a key name.'); return; }
    if (!scopes.length) { alert('Please select at least one scope.'); return; }

    const res = await apiFetch('/api/v1/employee/api-keys', {
        method: 'POST',
        body: JSON.stringify({ user_id: parseInt(userId), name, scopes })
    });

    if (!res?.ok) {
        const e = await res?.json();
        alert(e?.message || 'Failed to generate key.');
        return;
    }

    const data = await res.json();
    const rawKey = data.key || data.api_key || 'key_generated';

    document.getElementById('new-key-display').textContent = rawKey;
    document.getElementById('gen-form').style.display   = 'none';
    document.getElementById('gen-result').style.display = 'block';
}

function copyKey() {
    const txt = document.getElementById('new-key-display').textContent;
    navigator.clipboard.writeText(txt).then(() => {
        const btn = document.querySelector('#gen-result .btn-outline');
        btn.textContent = '✓ Copied!';
        setTimeout(() => { btn.textContent = '📋 Copy to Clipboard'; }, 2000);
    }).catch(() => alert('Copy failed — please select and copy manually.'));
}

// ── Revoke key ────────────────────────────────────────────────────
async function revokeKey(id) {
    if (!confirm('Revoke this API key? The partner will immediately lose access. This cannot be undone.')) return;
    const res = await apiFetch(`/api/v1/employee/api-keys/${id}`, { method: 'DELETE' });
    if (res?.ok || res?.status === 204) {
        loadKeys(_currentPage);
    } else {
        const e = await res?.json();
        alert(e?.message || 'Failed to revoke key.');
    }
}

document.addEventListener('DOMContentLoaded', loadKeys);

// Close dropdown when clicking outside
document.addEventListener('click', (e) => {
    if (!e.target.closest('#m-user-search') && !e.target.closest('#m-user-results')) {
        const el = document.getElementById('m-user-results');
        if (el) el.style.display = 'none';
    }
});
</script>
@endpush
