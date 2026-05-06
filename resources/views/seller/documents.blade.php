@extends('layouts.seller')
@section('title','My Documents')
@section('page-title','My Documents')
@section('content')

<style>
.doc-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:18px}
.doc-icon-wrap{width:52px;height:52px;border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.doc-icon-wrap svg{width:26px;height:26px}
.doc-filename{background:#f8fafc;border-radius:8px;padding:9px 13px;font-size:12.5px;color:#334155;display:flex;align-items:center;gap:8px;margin-bottom:14px;word-break:break-all}
.doc-filename svg{width:13px;height:13px;color:var(--green);flex-shrink:0}
.doc-actions{display:flex;gap:8px;flex-wrap:wrap}
.doc-note{font-size:12.5px;color:var(--muted);margin-bottom:14px;line-height:1.5}
</style>

<div class="page-header">
    <div>
        <h1 class="page-title">My Documents</h1>
        <p class="page-sub">Upload and manage your KYC verification documents</p>
    </div>
</div>

<div id="docStatusBar" style="margin-bottom:18px"></div>

<div id="docGrid" class="doc-grid">
    <div class="loading"><div class="spinner"></div> Loading documents…</div>
</div>

{{-- Upload Modal --}}
<div class="modal-overlay" id="uploadModal">
    <div class="modal" style="max-width:420px">
        <button class="modal-close" onclick="closeUpload()">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <div style="margin-bottom:16px">
            <h3 style="font-size:16px;font-weight:700;margin-bottom:4px">Upload Document</h3>
            <p id="uploadTypeLabel" style="font-size:13px;color:var(--muted)"></p>
        </div>
        <input type="hidden" id="uploadType">
        <div class="form-group">
            <label class="form-label">Select File</label>
            <input type="file" id="uploadFile" accept=".jpg,.jpeg,.png,.pdf" class="form-input">
            <div style="font-size:11.5px;color:var(--muted);margin-top:5px">Allowed: JPG, PNG, PDF · Max 2 MB</div>
        </div>
        <div style="display:flex;gap:10px;margin-top:8px">
            <button class="btn btn-primary" onclick="doUpload()" id="uploadBtn">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Upload
            </button>
            <button class="btn btn-outline" onclick="closeUpload()">Cancel</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
if (!requireAuth()) { /* blocked */ }

const DOC_META = {
    pan: {
        label: 'PAN Card',
        desc:  'Permanent Account Number card for identity verification',
        color: '#dbeafe',
        iconColor: '#2563eb',
        icon: `<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2"/></svg>`,
    },
    gst: {
        label: 'GST Certificate',
        desc:  'Goods and Services Tax registration certificate for business verification',
        color: '#d1fae5',
        iconColor: '#059669',
        icon: `<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>`,
    },
    doc: {
        label: 'Other Document',
        desc:  'Any additional supporting document for verification',
        color: '#fef3c7',
        iconColor: '#d97706',
        icon: `<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>`,
    },
};

const APPROVAL_BADGE = {
    approved: '<span class="badge badge-success">APPROVED</span>',
    pending:  '<span class="badge badge-pending">PENDING REVIEW</span>',
    rejected: '<span class="badge badge-failed">REJECTED</span>',
};

async function loadDocuments() {
    try {
        const res = await fetch('/api/v1/seller/documents', {
            headers: { 'Authorization': 'Bearer ' + getToken(), 'Accept': 'application/json' }
        });
        const d = await res.json();
        if (!res.ok) { showBar('danger', d.message || 'Failed to load documents.'); return; }
        renderStatus(d);
        renderCards(d.documents);
    } catch (e) {
        showBar('danger', 'Network error. Please refresh the page.');
    }
}

function renderStatus(d) {
    const approvalHtml = APPROVAL_BADGE[d.approval_status] || APPROVAL_BADGE.pending;
    const accountBadge = d.account_status === 'active'
        ? '<span class="badge badge-success" style="margin-left:6px">Active</span>'
        : '<span class="badge badge-pending" style="margin-left:6px">' + (d.account_status || 'Pending') + '</span>';
    document.getElementById('docStatusBar').innerHTML = `
        <div class="alert alert-info" style="margin-bottom:0">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Account Approval: ${approvalHtml} &nbsp;·&nbsp; Account Status: ${accountBadge}</span>
        </div>`;
}

function renderCards(docs) {
    const grid = document.getElementById('docGrid');
    grid.innerHTML = Object.entries(DOC_META).map(([type, meta]) => {
        const doc = docs[type] || {};
        const has = doc.has;
        return `
        <div class="card">
            <div class="card-header" style="gap:14px;align-items:flex-start">
                <div class="doc-icon-wrap" style="background:${meta.color};color:${meta.iconColor}">${meta.icon}</div>
                <div style="flex:1;min-width:0">
                    <div class="card-title">${meta.label}</div>
                    <div style="font-size:12px;color:var(--muted);margin-top:2px;line-height:1.4">${meta.desc}</div>
                </div>
                <span class="badge ${has ? 'badge-success' : 'badge-failed'}" style="flex-shrink:0">${has ? 'Uploaded' : 'Missing'}</span>
            </div>
            <div class="card-body">
                ${has ? `
                <div class="doc-filename">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    ${escHtml(doc.filename || 'document')}
                </div>
                <div class="doc-actions">
                    <button class="btn btn-outline btn-sm" onclick="viewDoc('${escAttr(doc.view_url || '')}')">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:13px;height:13px"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        View
                    </button>
                    <button class="btn btn-primary btn-sm" onclick="openUpload('${type}')">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:13px;height:13px"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        Re-Upload
                    </button>
                </div>
                ` : `
                <p class="doc-note">No document uploaded yet. Please upload your <strong>${meta.label}</strong> for verification.</p>
                <div class="doc-actions">
                    <button class="btn btn-primary btn-sm" onclick="openUpload('${type}')">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:13px;height:13px"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        Upload Document
                    </button>
                </div>
                `}
            </div>
        </div>`;
    }).join('');
}

function showBar(type, msg) {
    document.getElementById('docStatusBar').innerHTML = `<div class="alert alert-${type}">${msg}</div>`;
}

function viewDoc(url) {
    if (url) window.open(url, '_blank');
}

function openUpload(type) {
    document.getElementById('uploadType').value = type;
    document.getElementById('uploadTypeLabel').textContent = DOC_META[type]?.label || type;
    document.getElementById('uploadFile').value = '';
    document.getElementById('uploadModal').classList.add('open');
}

function closeUpload() {
    document.getElementById('uploadModal').classList.remove('open');
}

async function doUpload() {
    const type = document.getElementById('uploadType').value;
    const file = document.getElementById('uploadFile').files[0];
    if (!file) { alert('Please select a file first.'); return; }

    const btn = document.getElementById('uploadBtn');
    btn.disabled = true;
    btn.innerHTML = '<div class="spinner" style="width:14px;height:14px;border-width:2px"></div> Uploading…';

    const form = new FormData();
    form.append('type', type);
    form.append('file', file);

    try {
        const res = await fetch('/api/v1/seller/documents/upload', {
            method: 'POST',
            headers: { 'Authorization': 'Bearer ' + getToken(), 'Accept': 'application/json' },
            body: form,
        });
        const d = await res.json();
        if (res.ok) {
            closeUpload();
            showBar('success', d.message || 'Document uploaded successfully.');
            loadDocuments();
        } else {
            const errs = d.errors ? Object.values(d.errors).flat().join(' ') : (d.message || 'Upload failed.');
            alert(errs);
        }
    } catch (e) {
        alert('Network error. Please try again.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg> Upload';
    }
}

function escHtml(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function escAttr(s) { return String(s).replace(/"/g,'&quot;'); }

loadDocuments();
</script>
@endpush
@endsection
