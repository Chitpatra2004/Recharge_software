@extends('layouts.admin')
@section('title', 'Reminders')
@section('page-title', 'Reminders')

@section('content')
<div class="breadcrumb">
    <a href="/admin/dashboard">Dashboard</a>
    <svg class="breadcrumb-sep" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <span>Reminders</span>
</div>

{{-- Due Reminders Alert --}}
<div id="due-alert" style="display:none;background:#fef3c7;border:1px solid #fbbf24;border-radius:var(--radius-sm);padding:12px 16px;margin-bottom:20px;font-size:13px;color:#92400e">
    <strong>Reminders Due:</strong> <span id="due-list"></span>
</div>

<div style="display:grid;grid-template-columns:340px 1fr;gap:20px;align-items:start">
    {{-- Add Form --}}
    <div class="card">
        <div class="card-header"><span class="card-title">Add Reminder</span></div>
        <div class="card-body" style="display:flex;flex-direction:column;gap:14px">
            <div>
                <label style="font-size:12px;font-weight:600;color:var(--text-secondary);display:block;margin-bottom:4px">Title</label>
                <input type="text" id="r-title" placeholder="Reminder title"
                    style="width:100%;border:1px solid var(--border);border-radius:6px;padding:8px 12px;font-size:13px">
            </div>
            <div>
                <label style="font-size:12px;font-weight:600;color:var(--text-secondary);display:block;margin-bottom:4px">Remind At</label>
                <input type="datetime-local" id="r-at"
                    style="width:100%;border:1px solid var(--border);border-radius:6px;padding:8px 12px;font-size:13px">
            </div>
            <div>
                <label style="font-size:12px;font-weight:600;color:var(--text-secondary);display:block;margin-bottom:4px">Note (optional)</label>
                <textarea id="r-note" rows="3" placeholder="Additional note…"
                    style="width:100%;border:1px solid var(--border);border-radius:6px;padding:8px 12px;font-size:13px;resize:vertical"></textarea>
            </div>
            <button class="btn btn-primary" onclick="addReminder()">Add Reminder</button>
        </div>
    </div>

    {{-- Reminder List --}}
    <div>
        <div style="display:flex;gap:8px;margin-bottom:12px">
            <button class="btn btn-sm" id="f-upcoming" onclick="setFilter('upcoming')" style="background:var(--accent-blue);color:#fff">Upcoming</button>
            <button class="btn btn-outline btn-sm" id="f-past" onclick="setFilter('past')">Past / Done</button>
            <button class="btn btn-outline btn-sm" id="f-all2" onclick="setFilter('all2')">All</button>
        </div>
        <div id="rem-list" style="display:flex;flex-direction:column;gap:10px"></div>
        <div id="rem-empty" style="display:none;text-align:center;color:var(--text-muted);padding:40px;font-size:13px">No reminders yet.</div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let reminders = [];
let currentFilter = 'upcoming';

function saveReminders() { localStorage.setItem('admin_reminders', JSON.stringify(reminders)); }
function loadFromStorage() { reminders = JSON.parse(localStorage.getItem('admin_reminders') || '[]'); }

function checkDue() {
    const now  = Date.now();
    const due  = reminders.filter(r => !r.dismissed && new Date(r.remind_at).getTime() <= now);
    if (!due.length) return;
    document.getElementById('due-alert').style.display = 'block';
    document.getElementById('due-list').textContent = due.map(r => r.title).join(', ');
}

function renderReminders() {
    const now = Date.now();
    const list  = document.getElementById('rem-list');
    const empty = document.getElementById('rem-empty');
    let filtered = reminders;
    if (currentFilter === 'upcoming') filtered = reminders.filter(r => new Date(r.remind_at).getTime() > now && !r.dismissed);
    if (currentFilter === 'past')     filtered = reminders.filter(r => new Date(r.remind_at).getTime() <= now || r.dismissed);
    if (!filtered.length) { list.innerHTML = ''; empty.style.display = 'block'; return; }
    empty.style.display = 'none';
    const sorted = [...filtered].sort((a,b) => new Date(a.remind_at) - new Date(b.remind_at));
    list.innerHTML = sorted.map(r => {
        const isPast = new Date(r.remind_at).getTime() <= now;
        return `<div class="card" style="padding:16px;display:flex;gap:12px;align-items:flex-start;${r.dismissed ? 'opacity:.55' : ''}">
            <div style="width:38px;height:38px;border-radius:10px;background:${isPast ? '#fee2e2' : '#dbeafe'};color:${isPast ? 'var(--accent-red)' : 'var(--accent-blue)'};display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:18px;height:18px"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            </div>
            <div style="flex:1;min-width:0">
                <div style="font-size:13px;font-weight:600;${r.dismissed?'text-decoration:line-through;color:var(--text-muted)':''}">${r.title}</div>
                <div style="font-size:11px;color:${isPast ? 'var(--accent-red)' : 'var(--text-muted)'};margin-top:2px">${new Date(r.remind_at).toLocaleString('en-IN')}</div>
                ${r.note ? `<div style="font-size:12px;color:var(--text-secondary);margin-top:4px">${r.note}</div>` : ''}
            </div>
            <div style="display:flex;gap:6px;flex-shrink:0">
                ${!r.dismissed ? `<button class="btn btn-outline btn-sm" onclick="dismissReminder('${r.id}')">Dismiss</button>` : ''}
                <button onclick="deleteReminder('${r.id}')" class="btn btn-outline btn-sm" style="color:var(--accent-red)">Del</button>
            </div>
        </div>`;
    }).join('');
}

function addReminder() {
    const title    = document.getElementById('r-title').value.trim();
    const remind_at = document.getElementById('r-at').value;
    if (!title || !remind_at) { alert('Title and remind-at date are required.'); return; }
    reminders.push({
        id:         Date.now().toString(),
        title,
        remind_at,
        note:       document.getElementById('r-note').value.trim(),
        dismissed:  false,
        created:    new Date().toISOString(),
    });
    saveReminders();
    document.getElementById('r-title').value = '';
    document.getElementById('r-at').value    = '';
    document.getElementById('r-note').value  = '';
    renderReminders();
}

function dismissReminder(id) {
    const r = reminders.find(x => x.id === id);
    if (r) { r.dismissed = true; saveReminders(); renderReminders(); }
}

function deleteReminder(id) {
    reminders = reminders.filter(x => x.id !== id);
    saveReminders(); renderReminders();
}

function setFilter(f) {
    currentFilter = f;
    ['upcoming','past','all2'].forEach(k => {
        const btn = document.getElementById('f-' + k);
        if (!btn) return;
        btn.className = k === f ? 'btn btn-sm' : 'btn btn-outline btn-sm';
        if (k === f) { btn.style.background = 'var(--accent-blue)'; btn.style.color = '#fff'; }
        else { btn.style.background = ''; btn.style.color = ''; }
    });
    renderReminders();
}

document.addEventListener('DOMContentLoaded', () => {
    loadFromStorage();
    checkDue();
    renderReminders();
    setInterval(checkDue, 60000); // re-check every minute
});
</script>
@endpush
