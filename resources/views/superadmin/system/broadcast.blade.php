@extends('layouts.superadmin')
@section('title', 'Broadcast')
@section('page-title', 'Broadcast')

@push('head')
<style>
.broadcast-grid { display:grid; grid-template-columns:1.4fr 1fr; gap:16px; }
@media(max-width:900px){ .broadcast-grid{grid-template-columns:1fr;} }
.channel-btn { display:flex; align-items:center; gap:8px; padding:10px 14px; border-radius:var(--rh-radius-sm); border:2px solid var(--rh-border); cursor:pointer; transition:all var(--rh-transition); background:var(--rh-page); }
.channel-btn.selected { border-color:var(--rh-brand); background:var(--rh-brand-lt); }
.channel-btn svg { width:18px; height:18px; }
.channel-btn-lbl { font-size:12.5px; font-weight:600; color:var(--rh-text); }
.char-count { font-size:11px; color:var(--rh-muted); text-align:right; margin-top:4px; }
</style>
@endpush

@section('content')

<div class="rh-breadcrumb">
    <a href="{{ route('superadmin.dashboard') }}">Dashboard</a>
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <span>Broadcast</span>
</div>

<div class="broadcast-grid">

    {{-- Compose --}}
    <div>
        <div class="rh-card" style="margin-bottom:16px">
            <div class="rh-card-header">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;color:var(--rh-brand)"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                <span class="rh-card-title">Compose Broadcast</span>
            </div>
            <div class="rh-card-body" style="display:flex;flex-direction:column;gap:14px">

                {{-- Channels --}}
                <div>
                    <label class="rh-label">Channel</label>
                    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:6px">
                        <label class="channel-btn selected" id="ch-app">
                            <input type="checkbox" checked style="display:none">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:var(--rh-brand)"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            <span class="channel-btn-lbl">In-App</span>
                        </label>
                        <label class="channel-btn" id="ch-sms">
                            <input type="checkbox" style="display:none">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:var(--rh-muted)"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                            <span class="channel-btn-lbl">SMS</span>
                        </label>
                        <label class="channel-btn" id="ch-email">
                            <input type="checkbox" style="display:none">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:var(--rh-muted)"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <span class="channel-btn-lbl">Email</span>
                        </label>
                        <label class="channel-btn" id="ch-whatsapp">
                            <input type="checkbox" style="display:none">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:var(--rh-muted)"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            <span class="channel-btn-lbl">WhatsApp</span>
                        </label>
                    </div>
                </div>

                {{-- Recipients --}}
                <div>
                    <label class="rh-label">Recipients</label>
                    <select class="rh-input" id="recipientSelect">
                        <option value="all">All Sellers (247)</option>
                        <option value="active">Active Sellers Only (219)</option>
                        <option value="admins">Admins Only (14)</option>
                        <option value="low_balance">Low Balance Sellers (23)</option>
                        <option value="specific">Specific Admin's Sellers</option>
                    </select>
                </div>

                {{-- Type --}}
                <div>
                    <label class="rh-label">Message Type</label>
                    <select class="rh-input">
                        <option>General Announcement</option>
                        <option>System Maintenance</option>
                        <option>Offer / Promotion</option>
                        <option>Alert / Warning</option>
                        <option>API Update</option>
                    </select>
                </div>

                {{-- Title --}}
                <div>
                    <label class="rh-label">Title / Subject</label>
                    <input type="text" class="rh-input" placeholder="e.g. System Maintenance on 25 Mar 2026" id="broadcastTitle">
                </div>

                {{-- Message --}}
                <div>
                    <label class="rh-label">Message</label>
                    <textarea class="rh-input" rows="5" placeholder="Write your broadcast message here…" id="broadcastMsg" oninput="updateCharCount(this)" style="resize:vertical"></textarea>
                    <div class="char-count"><span id="charCount">0</span> / 500</div>
                </div>

                {{-- Schedule --}}
                <div style="display:flex;align-items:center;gap:10px">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                        <div class="rh-toggle-wrap">
                            <input type="checkbox" class="rh-toggle-input" id="scheduleToggle" onchange="toggleSchedule()">
                            <span class="rh-toggle"></span>
                        </div>
                        <span style="font-size:13px;font-weight:500">Schedule for later</span>
                    </label>
                </div>
                <div id="scheduleDate" style="display:none">
                    <label class="rh-label">Schedule Date & Time</label>
                    <input type="datetime-local" class="rh-input">
                </div>

                {{-- Actions --}}
                <div style="display:flex;gap:10px;padding-top:4px">
                    <button class="btn btn-md btn-outline" onclick="previewBroadcast()">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        Preview
                    </button>
                    <button class="btn btn-md btn-primary" style="flex:1" onclick="sendBroadcast()">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        Send Broadcast
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- History --}}
    <div>
        <div class="rh-card">
            <div class="rh-card-header">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;color:var(--rh-muted)"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="rh-card-title">Broadcast History</span>
            </div>
            <div style="padding:8px 16px 14px;display:flex;flex-direction:column;gap:0">
                @php
                $history = [
                    ['title'=>'System Maintenance Notice','type'=>'System','channel'=>'In-App, SMS','recipients'=>247,'sentAt'=>'19 Mar 2026, 11:30 AM','status'=>'sent'],
                    ['title'=>'New Offer: Extra 0.5% on Jio','type'=>'Offer','channel'=>'In-App, Email','recipients'=>219,'sentAt'=>'15 Mar 2026, 10:00 AM','status'=>'sent'],
                    ['title'=>'API Rate Update — BSNL','type'=>'API Update','channel'=>'In-App','recipients'=>247,'sentAt'=>'10 Mar 2026, 2:45 PM','status'=>'sent'],
                    ['title'=>'Wallet Top-up Reminder','type'=>'Alert','channel'=>'SMS, WhatsApp','recipients'=>23,'sentAt'=>'5 Mar 2026, 9:00 AM','status'=>'sent'],
                    ['title'=>'Scheduled: April Promo Campaign','type'=>'Offer','channel'=>'Email, WhatsApp','recipients'=>247,'sentAt'=>'25 Mar 2026, 8:00 AM','status'=>'scheduled'],
                ];
                @endphp
                @foreach($history as $h)
                <div style="padding:12px 0;border-bottom:1px solid var(--rh-border)">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px">
                        <div>
                            <div style="font-size:13px;font-weight:600;color:var(--rh-text);margin-bottom:3px">{{ $h['title'] }}</div>
                            <div style="font-size:11.5px;color:var(--rh-muted)">{{ $h['channel'] }} · {{ $h['recipients'] }} recipients</div>
                            <div style="font-size:11px;color:var(--rh-faint);margin-top:2px">{{ $h['sentAt'] }}</div>
                        </div>
                        <div style="flex-shrink:0">
                            @if($h['status']==='sent')
                                <span class="badge badge-green">Sent</span>
                            @else
                                <span class="badge badge-amber">Scheduled</span>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function updateCharCount(el){ document.getElementById('charCount').textContent=el.value.length; }
function toggleSchedule(){
    const show=document.getElementById('scheduleToggle').checked;
    document.getElementById('scheduleDate').style.display=show?'block':'none';
}
document.querySelectorAll('.channel-btn').forEach(btn=>{
    btn.addEventListener('click',()=>btn.classList.toggle('selected'));
});
function sendBroadcast(){
    const t=document.getElementById('broadcastTitle').value.trim();
    const m=document.getElementById('broadcastMsg').value.trim();
    if(!t||!m){ alert('Please enter title and message.'); return; }
    if(confirm('Send broadcast to selected recipients?')){
        alert('Broadcast sent successfully!');
    }
}
function previewBroadcast(){
    const t=document.getElementById('broadcastTitle').value||'(No title)';
    const m=document.getElementById('broadcastMsg').value||'(No message)';
    alert(`PREVIEW\n\nTitle: ${t}\n\nMessage:\n${m}`);
}
</script>
@endpush
