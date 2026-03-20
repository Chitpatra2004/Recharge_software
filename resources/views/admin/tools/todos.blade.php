@extends('layouts.admin')
@section('title', 'Todos')
@section('page-title', 'Todos')

@section('content')
<div class="breadcrumb">
    <a href="/admin/dashboard">Dashboard</a>
    <svg class="breadcrumb-sep" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <span>Todos</span>
</div>

<div style="display:grid;grid-template-columns:340px 1fr;gap:20px;align-items:start">
    {{-- Add Todo Form --}}
    <div class="card">
        <div class="card-header"><span class="card-title">Add Todo</span></div>
        <div class="card-body" style="display:flex;flex-direction:column;gap:14px">
            <div>
                <label style="font-size:12px;font-weight:600;color:var(--text-secondary);display:block;margin-bottom:4px">Title</label>
                <input type="text" id="t-title" placeholder="What needs to be done?"
                    style="width:100%;border:1px solid var(--border);border-radius:6px;padding:8px 12px;font-size:13px">
            </div>
            <div>
                <label style="font-size:12px;font-weight:600;color:var(--text-secondary);display:block;margin-bottom:4px">Note (optional)</label>
                <textarea id="t-note" rows="3" placeholder="Additional details…"
                    style="width:100%;border:1px solid var(--border);border-radius:6px;padding:8px 12px;font-size:13px;resize:vertical"></textarea>
            </div>
            <div>
                <label style="font-size:12px;font-weight:600;color:var(--text-secondary);display:block;margin-bottom:4px">Priority</label>
                <select id="t-priority" style="width:100%;border:1px solid var(--border);border-radius:6px;padding:8px 12px;font-size:13px">
                    <option value="low">Low</option>
                    <option value="medium" selected>Medium</option>
                    <option value="high">High</option>
                    <option value="critical">Critical</option>
                </select>
            </div>
            <div>
                <label style="font-size:12px;font-weight:600;color:var(--text-secondary);display:block;margin-bottom:4px">Due Date</label>
                <input type="date" id="t-due" style="width:100%;border:1px solid var(--border);border-radius:6px;padding:8px 12px;font-size:13px">
            </div>
            <button class="btn btn-primary" onclick="addTodo()">Add Todo</button>
        </div>
    </div>

    {{-- Todo List --}}
    <div>
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
            <div style="display:flex;gap:8px">
                <button class="btn btn-sm" id="f-all"      onclick="setFilter('all')"      style="background:var(--accent-blue);color:#fff">All</button>
                <button class="btn btn-outline btn-sm" id="f-pending"  onclick="setFilter('pending')">Pending</button>
                <button class="btn btn-outline btn-sm" id="f-done"     onclick="setFilter('done')">Done</button>
            </div>
            <button class="btn btn-outline btn-sm" style="color:var(--accent-red)" onclick="clearDone()">Clear Done</button>
        </div>
        <div id="todo-list" style="display:flex;flex-direction:column;gap:10px"></div>
        <div id="todo-empty" style="display:none;text-align:center;color:var(--text-muted);padding:40px;font-size:13px">No todos yet. Add one!</div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let todos = [];
let currentFilter = 'all';

const PRIORITY_COLOR = { low:'var(--text-muted)', medium:'var(--accent-orange)', high:'var(--accent-red)', critical:'#7c3aed' };
const PRIORITY_BG    = { low:'#f1f5f9', medium:'#fef3c7', high:'#fee2e2', critical:'#ede9fe' };

function saveTodos() { localStorage.setItem('admin_todos', JSON.stringify(todos)); }
function loadTodosFromStorage() { todos = JSON.parse(localStorage.getItem('admin_todos') || '[]'); }

function renderTodos() {
    const list  = document.getElementById('todo-list');
    const empty = document.getElementById('todo-empty');
    const filtered = todos.filter(t =>
        currentFilter === 'all' ? true :
        currentFilter === 'done' ? t.done :
        !t.done
    );
    if (!filtered.length) {
        list.innerHTML = ''; empty.style.display = 'block'; return;
    }
    empty.style.display = 'none';
    list.innerHTML = filtered.map(t => `
        <div class="card" style="padding:16px;display:flex;gap:12px;align-items:flex-start;${t.done ? 'opacity:.6' : ''}">
            <input type="checkbox" ${t.done ? 'checked' : ''} onchange="toggleDone('${t.id}')"
                style="margin-top:2px;width:16px;height:16px;cursor:pointer;flex-shrink:0">
            <div style="flex:1;min-width:0">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
                    <span style="font-size:13px;font-weight:600;${t.done ? 'text-decoration:line-through;color:var(--text-muted)' : ''}">${t.title}</span>
                    <span style="font-size:10px;font-weight:600;padding:2px 7px;border-radius:20px;background:${PRIORITY_BG[t.priority]};color:${PRIORITY_COLOR[t.priority]}">${t.priority}</span>
                </div>
                ${t.note ? `<p style="font-size:12px;color:var(--text-secondary);margin-bottom:4px">${t.note}</p>` : ''}
                ${t.due ? `<span style="font-size:11px;color:var(--text-muted)">Due: ${t.due}</span>` : ''}
            </div>
            <button onclick="deleteTodo('${t.id}')" style="background:none;border:none;cursor:pointer;color:var(--text-muted);flex-shrink:0" title="Delete">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:15px;height:15px"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    `).join('');
}

function addTodo() {
    const title = document.getElementById('t-title').value.trim();
    if (!title) { alert('Please enter a title.'); return; }
    todos.unshift({
        id:       Date.now().toString(),
        title,
        note:     document.getElementById('t-note').value.trim(),
        priority: document.getElementById('t-priority').value,
        due:      document.getElementById('t-due').value,
        done:     false,
        created:  new Date().toISOString(),
    });
    saveTodos();
    document.getElementById('t-title').value = '';
    document.getElementById('t-note').value  = '';
    document.getElementById('t-due').value   = '';
    renderTodos();
}

function toggleDone(id) {
    const t = todos.find(x => x.id === id);
    if (t) { t.done = !t.done; saveTodos(); renderTodos(); }
}

function deleteTodo(id) {
    todos = todos.filter(x => x.id !== id);
    saveTodos(); renderTodos();
}

function clearDone() {
    if (!confirm('Remove all completed todos?')) return;
    todos = todos.filter(x => !x.done);
    saveTodos(); renderTodos();
}

function setFilter(f) {
    currentFilter = f;
    ['all','pending','done'].forEach(k => {
        const btn = document.getElementById('f-' + k);
        btn.className = k === f ? 'btn btn-sm' : 'btn btn-outline btn-sm';
        if (k === f) btn.style.background = 'var(--accent-blue)', btn.style.color = '#fff';
        else btn.style.background = '', btn.style.color = '';
    });
    renderTodos();
}

document.addEventListener('DOMContentLoaded', () => { loadTodosFromStorage(); renderTodos(); });
</script>
@endpush
