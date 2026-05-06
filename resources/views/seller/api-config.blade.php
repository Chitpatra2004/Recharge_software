@extends('layouts.seller')
@section('title','API Configuration')
@section('page-title','API Configuration')
@section('content')

<style>
/* ── Base layout ── */
.apc-wrap{display:flex;flex-direction:column;gap:18px}

/* ── Add-API card ── */
.apc-card{background:#fff;border:1px solid var(--border);border-radius:14px;box-shadow:var(--shadow);overflow:hidden}
.apc-card-head{background:#1d4ed8;color:#fff;padding:14px 20px;display:flex;align-items:center;justify-content:space-between}
.apc-card-head h2{font-size:17px;font-weight:700;margin:0}
.apc-card-body{padding:22px 24px}

/* ── Form grid ── */
.apc-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px 20px}
.apc-col-1{grid-column:span 1}
.apc-col-2{grid-column:span 2}
.apc-col-3{grid-column:span 3}
.apc-field label{display:block;font-size:12px;font-weight:600;color:#334155;margin-bottom:6px}
.apc-field input,.apc-field select{width:100%;padding:10px 13px;border:1.5px solid #cbd5e1;border-radius:9px;font-size:13px;font-family:inherit;background:#fff;color:#0f172a;outline:none;transition:border-color .15s}
.apc-field input:focus,.apc-field select:focus{border-color:#2563eb}

/* ── Toggle ── */
.apc-toggle-row{display:flex;align-items:center;gap:12px;padding:10px 14px;background:#f8fafc;border-radius:10px;border:1.5px solid #e2e8f0}
.apc-toggle-label{font-size:13px;font-weight:600;color:#1e293b;flex:1}
.apc-toggle-desc{font-size:11px;color:#64748b;margin-top:2px}
.toggle-switch{position:relative;width:44px;height:24px;flex-shrink:0}
.toggle-switch input{opacity:0;width:0;height:0;position:absolute}
.toggle-track{position:absolute;inset:0;background:#cbd5e1;border-radius:99px;cursor:pointer;transition:background .2s}
.toggle-track::after{content:'';position:absolute;left:3px;top:3px;width:18px;height:18px;background:#fff;border-radius:50%;transition:transform .2s;box-shadow:0 1px 3px rgba(0,0,0,.2)}
.toggle-switch input:checked+.toggle-track{background:#2563eb}
.toggle-switch input:checked+.toggle-track::after{transform:translateX(20px)}

/* ── Notification types ── */
.notif-types{display:flex;flex-wrap:wrap;gap:10px;margin-top:4px}
.notif-chip{display:flex;align-items:center;gap:7px;padding:8px 14px;border:1.5px solid #e2e8f0;border-radius:99px;font-size:12.5px;font-weight:600;color:#475569;cursor:pointer;transition:all .15s;background:#fff;user-select:none}
.notif-chip:hover{border-color:#2563eb;color:#2563eb;background:#eff6ff}
.notif-chip input[type=checkbox]{width:14px;height:14px;accent-color:#2563eb;cursor:pointer}
.notif-chip.selected{border-color:#2563eb;background:#eff6ff;color:#1d4ed8}

/* ── Conditional fields ── */
.apc-cond{display:none}

/* ── Action buttons ── */
.apc-actions{margin-top:20px;display:flex;gap:12px;align-items:center}
.apc-btn{display:inline-flex;align-items:center;gap:7px;padding:10px 22px;border-radius:9px;font-size:13px;font-weight:700;cursor:pointer;border:none;font-family:inherit;transition:all .15s}
.apc-btn.primary{background:#2563eb;color:#fff;box-shadow:0 4px 14px rgba(37,99,235,.3)}
.apc-btn.primary:hover{background:#1d4ed8}
.apc-btn.primary:disabled{opacity:.6;cursor:not-allowed}
.apc-btn.outline{background:#fff;color:#374151;border:1.5px solid #e2e8f0}
.apc-btn.outline:hover{background:#f8fafc}
.apc-btn.sm{padding:7px 16px;font-size:12px}

/* ── API list table ── */
.api-table-wrap{overflow-x:auto}
.api-table{width:100%;border-collapse:collapse}
.api-table thead th{padding:10px 16px;text-align:left;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#64748b;background:#f8fafc;border-bottom:1.5px solid #e2e8f0}
.api-table tbody td{padding:13px 16px;font-size:13px;color:#1e293b;border-bottom:1px solid #f1f5f9}
.api-table tbody tr:last-child td{border-bottom:none}
.api-table tbody tr:hover td{background:#f8fbff}
.api-tbl-id{font-family:monospace;font-size:12px;color:#64748b;background:#f1f5f9;padding:3px 8px;border-radius:6px}
.api-tbl-name{font-weight:600;color:#1d4ed8}
.apill{display:inline-flex;align-items:center;padding:3px 10px;border-radius:99px;font-size:11px;font-weight:700}
.apill.green{background:#dcfce7;color:#166534}
.apill.red{background:#fee2e2;color:#991b1b}
.apill.yellow{background:#fef3c7;color:#92400e}
.settings-link{display:inline-flex;align-items:center;gap:5px;color:#2563eb;font-size:12.5px;font-weight:600;text-decoration:none;padding:6px 14px;border-radius:8px;background:#eff6ff;border:1px solid #bfdbfe;transition:all .15s}
.settings-link:hover{background:#dbeafe;color:#1d4ed8}
.settings-link svg{width:13px;height:13px}

/* ── Config panel ── */
.apc-config-panel{display:none;flex-direction:column;gap:16px}
.apc-config-panel.visible{display:flex}
.apc-section{background:#fff;border:1px solid var(--border);border-radius:14px;box-shadow:var(--shadow);overflow:hidden}
.apc-section-head{background:#c8dcf4;color:#0b4f9c;font-size:16px;font-weight:600;padding:10px 18px;display:flex;align-items:center;justify-content:space-between}
.apc-section-body{padding:18px}
.apc-form-grid{display:grid;grid-template-columns:repeat(6,1fr);gap:14px 16px}
.afc-1{grid-column:span 1}.afc-2{grid-column:span 2}.afc-3{grid-column:span 3}.afc-4{grid-column:span 4}.afc-6{grid-column:span 6}
.afc-field label{display:block;font-size:12px;font-weight:600;color:#334155;margin-bottom:5px}
.afc-field input,.afc-field select,.afc-field textarea{width:100%;padding:9px 12px;border:1px solid #cbd5e1;border-radius:8px;font-size:13px;font-family:inherit;background:#fff;color:#0f172a}
.afc-field textarea{min-height:70px;resize:vertical}
.afc-actions{display:flex;justify-content:flex-start;margin-top:16px}
.api-operator-table{width:100%;border-collapse:collapse}
.api-operator-table th,.api-operator-table td{border:1px solid #e5e7eb;padding:9px;font-size:12.5px;text-align:left}
.api-operator-table thead th{background:#f8fafc;font-size:11px;color:#0f172a;font-weight:700}
.api-operator-table tbody tr:nth-child(even) td{background:#f8fbff}
.api-operator-table input{width:100%;padding:6px 9px;border:1px solid #cbd5e1;border-radius:6px;font-size:12.5px;font-family:inherit}

.apc-info-note{border-radius:10px;padding:14px 18px;font-size:12.5px;line-height:1.7}
.apc-info-note.red{background:#ff4338;color:#fff}
.apc-info-note.yellow{background:#fffbd6;border:1px solid #b7d76d;color:#1f2937}

/* ── Alert ── */
#page-alert .alert{padding:12px 16px;border-radius:10px;font-size:13px;margin-bottom:12px}
.alert-success{background:#d1fae5;border:1px solid #a7f3d0;color:#065f46}
.alert-danger{background:#fee2e2;border:1px solid #fecaca;color:#991b1b}

/* ── API Status toggle ── */
.cfg-toggle-row{display:flex;align-items:center;justify-content:space-between;gap:18px;border:1px solid var(--border);border-radius:12px;padding:16px;background:#f8fafc}
.cfg-switch{position:relative;width:66px;height:34px;flex:0 0 auto}
.cfg-switch input{display:none}
.cfg-slider{position:absolute;inset:0;border-radius:999px;background:#ef4444;cursor:pointer;transition:.2s}
.cfg-slider:before{content:'';position:absolute;width:26px;height:26px;left:4px;top:4px;background:#fff;border-radius:50%;box-shadow:0 2px 6px rgba(0,0,0,.25);transition:.2s}
.cfg-switch input:checked+.cfg-slider{background:#10b981}
.cfg-switch input:checked+.cfg-slider:before{transform:translateX(32px)}
.cfg-switch input:disabled+.cfg-slider{opacity:.55;cursor:not-allowed}
.cfg-salert{border-radius:10px;padding:12px 14px;font-size:13px;line-height:1.55;margin-top:12px}
.cfg-salert.info{background:#dbeafe;border:1px solid #bfdbfe;color:#1e40af}
.cfg-salert.warn{background:#fef3c7;border:1px solid #fde68a;color:#92400e}
.cfg-salert.ok{background:#d1fae5;border:1px solid #a7f3d0;color:#065f46}
.cfg-salert.err{background:#fee2e2;border:1px solid #fecaca;color:#991b1b}
@media(max-width:560px){.cfg-toggle-row{flex-direction:column;align-items:flex-start}}

@media(max-width:900px){
    .apc-grid{grid-template-columns:repeat(2,1fr)}
    .apc-col-1,.apc-col-2,.apc-col-3{grid-column:span 2}
    .apc-form-grid{grid-template-columns:repeat(2,1fr)}
    .afc-1,.afc-2,.afc-3,.afc-4,.afc-6{grid-column:span 2}
}
@media(max-width:560px){
    .apc-grid{grid-template-columns:1fr}
    .apc-col-1,.apc-col-2,.apc-col-3{grid-column:span 1}
    .apc-form-grid{grid-template-columns:1fr}
    .afc-1,.afc-2,.afc-3,.afc-4,.afc-6{grid-column:span 1}
}
</style>

<div id="page-alert"></div>

<div class="apc-wrap">

    {{-- ══════════════════════════════════════════════════
         ADD API SECTION  (shown when no integration)
    ══════════════════════════════════════════════════ --}}
    <div id="add-api-section" class="apc-card" style="display:none">
        <div class="apc-card-head">
            <h2>
                <svg style="width:18px;height:18px;vertical-align:-3px;margin-right:6px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Add API
            </h2>
            <span style="font-size:12px;opacity:.8">Configure your API integration settings</span>
        </div>
        <div class="apc-card-body">
            <div class="apc-grid">
                <div class="apc-field apc-col-3">
                    <label for="add_api_name">API Name <span style="color:#ef4444">*</span></label>
                    <input id="add_api_name" type="text" placeholder="e.g. My Recharge API, Production API…" maxlength="150">
                </div>

                <div class="apc-col-3">
                    <div class="apc-toggle-row">
                        <div>
                            <div class="apc-toggle-label">Low Balance Notification</div>
                            <div class="apc-toggle-desc">Get notified when your balance drops below a threshold</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" id="add_low_notif_toggle" onchange="toggleNotifFields('add')">
                            <span class="toggle-track"></span>
                        </label>
                    </div>
                </div>

                <div class="apc-field apc-col-1 apc-cond" id="add_limit_field">
                    <label for="add_low_balance_limit">Low Balance Limit (₹) <span style="color:#ef4444">*</span></label>
                    <input id="add_low_balance_limit" type="number" min="0" step="0.01" placeholder="e.g. 500">
                </div>

                <div class="apc-col-2 apc-cond" id="add_notif_types_field" style="display:none">
                    <label style="display:block;font-size:12px;font-weight:600;color:#334155;margin-bottom:8px">Notification Type <span style="color:#ef4444">*</span></label>
                    <div class="notif-types">
                        <label class="notif-chip" id="chip_add_email">
                            <input type="checkbox" name="add_notif_type" value="email" onchange="updateChip(this)">
                            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            Email
                        </label>
                        <label class="notif-chip" id="chip_add_hangout">
                            <input type="checkbox" name="add_notif_type" value="hangout" onchange="updateChip(this)">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/></svg>
                            Hangout
                        </label>
                        <label class="notif-chip" id="chip_add_gmail">
                            <input type="checkbox" name="add_notif_type" value="gmail" onchange="updateChip(this)">
                            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            Gmail
                        </label>
                        <label class="notif-chip" id="chip_add_whatsapp">
                            <input type="checkbox" name="add_notif_type" value="whatsapp" onchange="updateChip(this)">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            WhatsApp
                        </label>
                        <label class="notif-chip" id="chip_add_outlook">
                            <input type="checkbox" name="add_notif_type" value="outlook" onchange="updateChip(this)">
                            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            Outlook
                        </label>
                    </div>
                </div>
            </div>

            <div class="apc-actions">
                <button class="apc-btn primary" id="add-api-btn" onclick="submitAddApi(this)">
                    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Add API
                </button>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════
         API LIST TABLE  (shown after integration saved)
    ══════════════════════════════════════════════════ --}}
    <div id="api-list-section" class="apc-card" style="display:none">
        <div class="apc-card-head" style="background:#0f172a">
            <h2>My APIs</h2>
            <span style="font-size:12px;opacity:.75">Click <strong>Settings</strong> to configure an API</span>
        </div>
        <div class="apc-card-body" style="padding:0">
            <div class="api-table-wrap">
                <table class="api-table">
                    <thead>
                        <tr>
                            <th>API ID</th>
                            <th>API Name</th>
                            <th>API Status</th>
                            <th>Admin Status</th>
                            <th>Settings</th>
                        </tr>
                    </thead>
                    <tbody id="api-list-body"></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════
         FULL CONFIGURATION PANEL (hidden until Settings clicked)
    ══════════════════════════════════════════════════ --}}
    <div id="config-panel" class="apc-config-panel">

        <div class="apc-info-note red">
            <strong>Parameters:</strong>
            `[number]` Recharge Number, `[amount]` Recharge Amount, `[opcode]` Operator Code, `[transid]` Your Unique ID, `[apirefid]` API Transaction ID, `[optional1]` Optional 1, `[optional2]` Optional 2, `[optional3]` Optional 3
        </div>

        <div class="apc-info-note yellow">
            <div><strong>Platform Callback URL:</strong> <span id="note-callback-url">—</span></div>
            <div><strong>Platform Server IP:</strong> <span id="note-server-ip">—</span></div>
            <div><strong>Base API URL:</strong> <span id="note-api-url">—</span></div>
            <div><strong>Auth Header:</strong> <code>X-API-Key: your_api_key</code></div>
            <div id="note-api-key" style="margin-top:6px"></div>
        </div>

        {{-- Notification Settings (edit existing) --}}
        <div class="apc-section">
            <div class="apc-section-head">
                <span>
                    <svg style="width:15px;height:15px;vertical-align:-2px;margin-right:6px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    Notification Settings
                </span>
            </div>
            <div class="apc-section-body">
                <div class="apc-grid">
                    <div class="apc-field apc-col-3">
                        <label for="edit_api_name">API Name <span style="color:#ef4444">*</span></label>
                        <input id="edit_api_name" type="text" placeholder="API Name" maxlength="150">
                    </div>
                    <div class="apc-col-3">
                        <div class="apc-toggle-row">
                            <div>
                                <div class="apc-toggle-label">Low Balance Notification</div>
                                <div class="apc-toggle-desc">Alert when balance drops below threshold</div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" id="edit_low_notif_toggle" onchange="toggleNotifFields('edit')">
                                <span class="toggle-track"></span>
                            </label>
                        </div>
                    </div>
                    <div class="apc-field apc-col-1 apc-cond" id="edit_limit_field" style="display:none">
                        <label for="edit_low_balance_limit">Low Balance Limit (₹)</label>
                        <input id="edit_low_balance_limit" type="number" min="0" step="0.01" placeholder="e.g. 500">
                    </div>
                    <div class="apc-col-2 apc-cond" id="edit_notif_types_field" style="display:none">
                        <label style="display:block;font-size:12px;font-weight:600;color:#334155;margin-bottom:8px">Notification Types</label>
                        <div class="notif-types">
                            <label class="notif-chip" id="chip_edit_email">
                                <input type="checkbox" name="edit_notif_type" value="email" onchange="updateChip(this)">
                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                Email
                            </label>
                            <label class="notif-chip" id="chip_edit_hangout">
                                <input type="checkbox" name="edit_notif_type" value="hangout" onchange="updateChip(this)">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/></svg>
                                Hangout
                            </label>
                            <label class="notif-chip" id="chip_edit_gmail">
                                <input type="checkbox" name="edit_notif_type" value="gmail" onchange="updateChip(this)">
                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                Gmail
                            </label>
                            <label class="notif-chip" id="chip_edit_whatsapp">
                                <input type="checkbox" name="edit_notif_type" value="whatsapp" onchange="updateChip(this)">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                WhatsApp
                            </label>
                            <label class="notif-chip" id="chip_edit_outlook">
                                <input type="checkbox" name="edit_notif_type" value="outlook" onchange="updateChip(this)">
                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                Outlook
                            </label>
                        </div>
                    </div>
                </div>
                <div class="apc-actions">
                    <button class="apc-btn primary sm" onclick="saveNotificationSettings(this)">Save Notification Settings</button>
                </div>
            </div>
        </div>

        {{-- Integration Details --}}
        <div class="apc-section">
            <div class="apc-section-head">Integration Details</div>
            <div class="apc-section-body">
                <div class="apc-form-grid">
                    <div class="afc-field afc-3"><label for="website_url">Website URL</label><input id="website_url" type="url" placeholder="https://yourdomain.com"></div>
                    <div class="afc-field afc-3"><label for="callback_url">Seller Callback URL</label><input id="callback_url" type="url" placeholder="https://yourdomain.com/callback"></div>
                    <div class="afc-field afc-6"><label for="allowed_ips">Allowed Seller IPs</label><textarea id="allowed_ips" placeholder="203.0.113.42&#10;198.51.100.0/24"></textarea></div>
                </div>
            </div>
        </div>

        {{-- Recharge API --}}
        <div class="apc-section">
            <div class="apc-section-head">Recharge API Settings</div>
            <div class="apc-section-body">
                <div class="apc-form-grid">
                    <div class="afc-field afc-1"><label>Method</label><select id="recharge_method"><option>GET</option><option>POST</option><option>PUT</option><option>PATCH</option></select></div>
                    <div class="afc-field afc-3"><label>URL</label><input id="recharge_url" type="url"></div>
                    <div class="afc-field afc-2"><label>Response Type</label><select id="recharge_response_type"><option>JSON</option><option>XML</option><option>OTHER</option></select></div>
                    <div class="afc-field afc-6"><label>Request Parameters</label><textarea id="recharge_params"></textarea></div>
                    <div class="afc-field afc-1"><label>Separator</label><input id="recharge_separator" type="text"></div>
                    <div class="afc-field afc-1"><label>Status Field</label><input id="recharge_status_field" type="text"></div>
                    <div class="afc-field afc-1"><label>API TxnId</label><input id="recharge_api_txnid_field" type="text"></div>
                    <div class="afc-field afc-1"><label>Live Id</label><input id="recharge_live_id_field" type="text"></div>
                    <div class="afc-field afc-1"><label>Balance</label><input id="recharge_balance_field" type="text"></div>
                    <div class="afc-field afc-2"><label>Success</label><input id="recharge_success_param" type="text"></div>
                    <div class="afc-field afc-2"><label>Pending</label><input id="recharge_pending_param" type="text"></div>
                    <div class="afc-field afc-2"><label>Failure</label><input id="recharge_failure_param" type="text"></div>
                </div>
                <div class="afc-actions"><button class="apc-btn primary sm" onclick="saveSection('recharge',this)">Update</button></div>
            </div>
        </div>

        {{-- Balance API --}}
        <div class="apc-section">
            <div class="apc-section-head">Balance API Settings</div>
            <div class="apc-section-body">
                <div class="apc-form-grid">
                    <div class="afc-field afc-1"><label>Method</label><select id="balance_method"><option>GET</option><option>POST</option><option>PUT</option><option>PATCH</option></select></div>
                    <div class="afc-field afc-3"><label>URL</label><input id="balance_url" type="url"></div>
                    <div class="afc-field afc-2"><label>Parameters</label><input id="balance_params" type="text"></div>
                    <div class="afc-field afc-1"><label>Response Type</label><select id="balance_response_type"><option>JSON</option><option>XML</option><option>OTHER</option></select></div>
                    <div class="afc-field afc-1"><label>Separator</label><input id="balance_separator" type="text"></div>
                    <div class="afc-field afc-1"><label>Balance Field</label><input id="balance_balance_field" type="text"></div>
                </div>
                <div class="afc-actions"><button class="apc-btn primary sm" onclick="saveSection('balance',this)">Update</button></div>
            </div>
        </div>

        {{-- Status API --}}
        <div class="apc-section">
            <div class="apc-section-head">Status API Settings</div>
            <div class="apc-section-body">
                <div class="apc-form-grid">
                    <div class="afc-field afc-1"><label>Method</label><select id="status_method"><option>GET</option><option>POST</option><option>PUT</option><option>PATCH</option></select></div>
                    <div class="afc-field afc-3"><label>URL</label><input id="status_url" type="url"></div>
                    <div class="afc-field afc-2"><label>Parameters</label><input id="status_params" type="text"></div>
                    <div class="afc-field afc-1"><label>Response Type</label><select id="status_response_type"><option>JSON</option><option>XML</option><option>OTHER</option></select></div>
                    <div class="afc-field afc-1"><label>Separator</label><input id="status_separator" type="text"></div>
                    <div class="afc-field afc-1"><label>Status Field</label><input id="status_status_field" type="text"></div>
                    <div class="afc-field afc-1"><label>API TxnId</label><input id="status_api_txnid_field" type="text"></div>
                    <div class="afc-field afc-1"><label>Live Id</label><input id="status_live_id_field" type="text"></div>
                    <div class="afc-field afc-1"><label>Balance</label><input id="status_balance_field" type="text"></div>
                    <div class="afc-field afc-2"><label>Success</label><input id="status_success_param" type="text"></div>
                    <div class="afc-field afc-2"><label>Pending</label><input id="status_pending_param" type="text"></div>
                    <div class="afc-field afc-2"><label>Failure</label><input id="status_failure_param" type="text"></div>
                </div>
                <div class="afc-actions"><button class="apc-btn primary sm" onclick="saveSection('status',this)">Update</button></div>
            </div>
        </div>

        {{-- Callback API --}}
        <div class="apc-section">
            <div class="apc-section-head">Callback API Settings</div>
            <div class="apc-section-body">
                <div class="apc-form-grid">
                    <div class="afc-field afc-1"><label>Response Type</label><select id="callback_response_type"><option>JSON</option><option>XML</option><option>OTHER</option></select></div>
                    <div class="afc-field afc-2"><label>IP Validation</label><input id="callback_ip_validation" type="text" placeholder="203.0.113.42"></div>
                    <div class="afc-field afc-2"><label>Status Field</label><input id="callback_status_field" type="text"></div>
                    <div class="afc-field afc-1"><label>OurTransId</label><input id="callback_ourtransid" type="text"></div>
                    <div class="afc-field afc-1"><label>API TxnId</label><input id="callback_api_txnid_field" type="text"></div>
                    <div class="afc-field afc-2"><label>Live Id</label><input id="callback_live_id_field" type="text"></div>
                    <div class="afc-field afc-1"><label>Balance</label><input id="callback_balance_field" type="text"></div>
                    <div class="afc-field afc-2"><label>Success</label><input id="callback_success_param" type="text"></div>
                    <div class="afc-field afc-2"><label>Pending</label><input id="callback_pending_param" type="text"></div>
                    <div class="afc-field afc-2"><label>Failure</label><input id="callback_failure_param" type="text"></div>
                </div>
                <div class="afc-actions"><button class="apc-btn primary sm" onclick="saveSection('callback',this)">Update</button></div>
            </div>
        </div>

        {{-- Complain API --}}
        <div class="apc-section">
            <div class="apc-section-head">Complain API</div>
            <div class="apc-section-body">
                <div class="apc-info-note red" style="margin-bottom:14px;font-size:12px">
                    <strong>Parameters:</strong> `[date]` recharge date yyyy-mm-dd, `[transid]` Your Unique ID, `[apirefid]` API Transaction ID
                </div>
                <div class="apc-form-grid">
                    <div class="afc-field afc-1"><label>Request Type</label><select id="dispute_method"><option>GET</option><option>POST</option><option>PUT</option><option>PATCH</option></select></div>
                    <div class="afc-field afc-3"><label>URL</label><input id="dispute_url" type="url"></div>
                    <div class="afc-field afc-2"><label>Parameters</label><input id="dispute_params" type="text"></div>
                    <div class="afc-field afc-1"><label>Response Type</label><select id="dispute_response_type"><option>JSON</option><option>XML</option><option>OTHER</option></select></div>
                    <div class="afc-field afc-1"><label>Status Field</label><input id="dispute_status_field" type="text"></div>
                    <div class="afc-field afc-1"><label>Success Key</label><input id="dispute_success_param" type="text"></div>
                    <div class="afc-field afc-1"><label>Failure Key</label><input id="dispute_failure_param" type="text"></div>
                    <div class="afc-field afc-1"><label>Pending Key</label><input id="dispute_pending_param" type="text"></div>
                    <div class="afc-field afc-1"><label>Separator</label><input id="dispute_separator" type="text"></div>
                </div>
                <div class="afc-actions"><button class="apc-btn primary sm" onclick="saveSection('dispute',this)">Update</button></div>
            </div>
        </div>

        {{-- Operator Codes --}}
        <div class="apc-section">
            <div class="apc-section-head">Operator Code Mapping</div>
            <div class="apc-section-body">
                <div style="overflow:auto">
                    <table class="api-operator-table">
                        <thead>
                            <tr>
                                <th>Sr</th><th>Company Name</th><th>OPParam1</th><th>OPParam2</th><th>OPParam3</th><th>OPParam4</th><th>OPParam5</th><th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="opcode-table-body"></tbody>
                    </table>
                </div>
                <div class="afc-actions" style="margin-top:14px"><button class="apc-btn primary sm" onclick="saveSection('integration',this)">Update All Settings</button></div>
            </div>
        </div>

    </div>{{-- /config-panel --}}
</div>

<script>
let currentConfig = null;
let operatorRows  = [];

/* ── Helpers ── */
function showAlert(type, msg) {
    const colors = { success:'alert-success', error:'alert-danger' };
    document.getElementById('page-alert').innerHTML =
        `<div class="alert ${colors[type]||'alert-info'}">${msg}</div>`;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
function fv(id, val) { const e = document.getElementById(id); if (e) e.value = val || ''; }
function fv_check(id, val) { const e = document.getElementById(id); if (e) e.checked = !!val; }

/* ── Toggle notification conditional fields ── */
function toggleNotifFields(prefix) {
    const enabled = document.getElementById(`${prefix}_low_notif_toggle`).checked;
    const limitEl = document.getElementById(`${prefix}_limit_field`);
    const typeEl  = document.getElementById(`${prefix}_notif_types_field`);
    if (limitEl) limitEl.style.display = enabled ? 'block' : 'none';
    if (typeEl)  typeEl.style.display  = enabled ? 'block' : 'none';
}

/* ── Chip visual feedback ── */
function updateChip(cb) {
    const chip = cb.closest('.notif-chip');
    if (chip) chip.classList.toggle('selected', cb.checked);
}

/* ── Collect checked notification types ── */
function collectNotifTypes(prefix) {
    return Array.from(document.querySelectorAll(`input[name="${prefix}_notif_type"]:checked`))
        .map(c => c.value);
}

/* ── Populate notification chips ── */
function fillNotifTypes(prefix, types) {
    const all = ['email','hangout','gmail','whatsapp','outlook'];
    const set = new Set(types || []);
    all.forEach(t => {
        const inp = document.querySelector(`input[name="${prefix}_notif_type"][value="${t}"]`);
        if (!inp) return;
        inp.checked = set.has(t);
        updateChip(inp);
    });
}

/* ── Render API list table row ── */
function renderApiList(integration, saleAccess) {
    const statusClass = { enabled: 'green', disabled: 'red', pending: 'yellow' };
    const apiStatus   = saleAccess?.api_status   || integration?.api_status   || 'disabled';
    const adminStatus = saleAccess?.admin_status  || integration?.admin_status  || 'disabled';
    const intgStatus  = integration?.status || 'none';
    const canToggle   = intgStatus === 'approved';
    const isOn        = apiStatus === 'enabled';

    document.getElementById('api-list-body').innerHTML = `
        <tr>
            <td><span class="api-tbl-id">#${integration.id || '—'}</span></td>
            <td><span class="api-tbl-name">${integration.api_name || '<em style="color:#94a3b8">Unnamed</em>'}</span></td>
            <td>
                <div style="display:flex;align-items:center;gap:10px">
                    <label class="cfg-switch" title="${canToggle ? 'Click to toggle API' : 'Requires approved integration'}">
                        <input type="checkbox" id="tbl-api-toggle" ${isOn ? 'checked' : ''} ${!canToggle ? 'disabled' : ''} onchange="toggleApiStatus(this)">
                        <span class="cfg-slider"></span>
                    </label>
                    <span class="apill ${isOn ? 'green' : 'red'}" id="tbl-api-badge">${apiStatus.toUpperCase()}</span>
                </div>
            </td>
            <td><span class="apill ${statusClass[adminStatus]||'red'}">${adminStatus.toUpperCase()}</span></td>
            <td>
                <a href="#config-panel" class="settings-link" onclick="showConfigPanel(event)">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/></svg>
                    Settings
                </a>
            </td>
        </tr>
    `;
}

/* ── Show/hide config panel ── */
function showConfigPanel(e) {
    if (e) e.preventDefault();
    const panel = document.getElementById('config-panel');
    panel.classList.add('visible');
    setTimeout(() => panel.scrollIntoView({ behavior: 'smooth', block: 'start' }), 50);
}

/* ── Fill endpoint fields ── */
function fillEndpoint(prefix, cfg) {
    fv(`${prefix}_method`, cfg?.method);
    fv(`${prefix}_url`, cfg?.url);
    fv(`${prefix}_params`, cfg?.params);
    fv(`${prefix}_response_type`, cfg?.response_type);
    fv(`${prefix}_separator`, cfg?.separator);
    fv(`${prefix}_status_field`, cfg?.status_field);
    fv(`${prefix}_api_txnid_field`, cfg?.api_txnid_field);
    fv(`${prefix}_live_id_field`, cfg?.live_id_field);
    fv(`${prefix}_balance_field`, cfg?.balance_field);
    fv(`${prefix}_success_param`, cfg?.success_param);
    fv(`${prefix}_pending_param`, cfg?.pending_param);
    fv(`${prefix}_failure_param`, cfg?.failure_param);
}

function collectEndpoint(prefix) {
    const rt = document.getElementById(`${prefix}_response_type`)?.value || 'JSON';
    return {
        method:           document.getElementById(`${prefix}_method`)?.value || 'GET',
        url:              document.getElementById(`${prefix}_url`)?.value.trim() || '',
        params:           document.getElementById(`${prefix}_params`)?.value.trim() || '',
        response_type:    rt,
        separator:        ['JSON','XML'].includes(rt) ? '' : (document.getElementById(`${prefix}_separator`)?.value.trim() || ''),
        status_field:     document.getElementById(`${prefix}_status_field`)?.value.trim() || '',
        api_txnid_field:  document.getElementById(`${prefix}_api_txnid_field`)?.value.trim() || '',
        live_id_field:    document.getElementById(`${prefix}_live_id_field`)?.value.trim() || '',
        balance_field:    document.getElementById(`${prefix}_balance_field`)?.value.trim() || '',
        success_param:    document.getElementById(`${prefix}_success_param`)?.value.trim() || '',
        pending_param:    document.getElementById(`${prefix}_pending_param`)?.value.trim() || '',
        failure_param:    document.getElementById(`${prefix}_failure_param`)?.value.trim() || '',
    };
}

/* ── Operator codes ── */
function renderOperatorRows(operators, saved) {
    operatorRows = (operators || []).map(op => ({ name: op.name, code: op.code }));
    const body    = document.getElementById('opcode-table-body');
    const savedMap = new Map((saved || []).map(r => [r.company_name || '', r]));
    body.innerHTML = operatorRows.map((row, i) => {
        const s = savedMap.get(row.name) || {};
        return `<tr>
            <td>${i+1}</td>
            <td>${row.name}</td>
            <td><input id="opcode_param1_${i}" type="text" value="${s.opparam1||''}"></td>
            <td><input id="opcode_param2_${i}" type="text" value="${s.opparam2||''}"></td>
            <td><input id="opcode_param3_${i}" type="text" value="${s.opparam3||''}"></td>
            <td><input id="opcode_param4_${i}" type="text" value="${s.opparam4||''}"></td>
            <td><input id="opcode_param5_${i}" type="text" value="${s.opparam5||''}"></td>
            <td><button class="apc-btn primary sm" onclick="saveOperatorRow(${i},this)">Update</button></td>
        </tr>`;
    }).join('');
}

function collectOperatorCodes() {
    return operatorRows.map((r, i) => ({
        company_name: r.name,
        opparam1: document.getElementById(`opcode_param1_${i}`)?.value.trim() || '',
        opparam2: document.getElementById(`opcode_param2_${i}`)?.value.trim() || '',
        opparam3: document.getElementById(`opcode_param3_${i}`)?.value.trim() || '',
        opparam4: document.getElementById(`opcode_param4_${i}`)?.value.trim() || '',
        opparam5: document.getElementById(`opcode_param5_${i}`)?.value.trim() || '',
    })).filter(r => r.opparam1 || r.opparam2 || r.opparam3 || r.opparam4 || r.opparam5);
}

/* ── Fill full page ── */
function fillPage(data) {
    currentConfig = data;
    const intg = data.integration || {};
    const ns   = data.notification_settings || {};
    const ak   = data.api_key || null;

    /* Info notes */
    fv('note-callback-url', data.callback_url || '—');
    fv_check && (document.getElementById('note-callback-url').textContent = data.callback_url || '—');
    document.getElementById('note-callback-url').textContent = data.callback_url || '—';
    document.getElementById('note-server-ip').textContent   = data.server_ip || '—';
    document.getElementById('note-api-url').textContent     = window.location.origin + '/api/v1/buyer/recharge';
    document.getElementById('note-api-key').innerHTML       = ak
        ? `<strong>API Key:</strong> ${ak.prefix}****************`
        : `<strong>API Key:</strong> Not generated yet`;

    /* Edit notification fields */
    fv('edit_api_name', ns.api_name || intg.api_name || '');
    const editToggle = document.getElementById('edit_low_notif_toggle');
    if (editToggle) { editToggle.checked = !!ns.low_balance_notification; toggleNotifFields('edit'); }
    fv('edit_low_balance_limit', ns.low_balance_limit || '');
    fillNotifTypes('edit', ns.notification_types || []);

    /* Integration fields */
    fv('website_url', intg.website_url);
    fv('callback_url', intg.callback_url);
    fv('allowed_ips', intg.allowed_ips);

    fillEndpoint('recharge', intg.recharge_api || {});
    fillEndpoint('status',   intg.status_api   || {});
    fillEndpoint('balance',  intg.balance_api  || {});
    fillEndpoint('dispute',  intg.dispute_api  || {});

    fv('callback_response_type', intg.callback_config?.response_type || 'JSON');
    fv('callback_ip_validation', intg.callback_config?.ip_validation);
    fv('callback_status_field',  intg.callback_config?.status_field);
    fv('callback_ourtransid',    intg.callback_config?.ourtransid);
    fv('callback_api_txnid_field', intg.callback_config?.api_txnid_field);
    fv('callback_live_id_field', intg.callback_config?.live_id_field);
    fv('callback_balance_field', intg.callback_config?.balance_field);
    fv('callback_success_param', intg.callback_config?.success_param);
    fv('callback_pending_param', intg.callback_config?.pending_param);
    fv('callback_failure_param', intg.callback_config?.failure_param);

    renderOperatorRows(data.operators || [], intg.op_code_map || []);
}

/* ── Decide which section to show ── */
function updateView(data) {
    const hasIntegration = !!data.integration;
    document.getElementById('add-api-section').style.display  = hasIntegration ? 'none' : '';
    document.getElementById('api-list-section').style.display = hasIntegration ? '' : 'none';

    if (hasIntegration) {
        renderApiList(data.integration, data.sale_access || {});
    }
}

/* ── Submit Add API (first-time) ── */
async function submitAddApi(btn) {
    const apiName = document.getElementById('add_api_name').value.trim();
    if (!apiName) { showAlert('error', 'API Name is required.'); return; }

    const notifEnabled = document.getElementById('add_low_notif_toggle').checked;
    const limit        = document.getElementById('add_low_balance_limit').value.trim();
    if (notifEnabled && !limit) { showAlert('error', 'Please enter the low balance limit amount.'); return; }

    const types = collectNotifTypes('add');
    if (notifEnabled && types.length === 0) { showAlert('error', 'Please select at least one notification type.'); return; }

    const orig = btn.textContent;
    btn.disabled = true; btn.textContent = 'Adding…';

    try {
        await apiFetch('/api/v1/seller/api-config/notification-settings', {
            method: 'PATCH',
            body: JSON.stringify({
                api_name:                 apiName,
                low_balance_notification: notifEnabled,
                low_balance_limit:        notifEnabled ? parseFloat(limit) : null,
                notification_types:       notifEnabled ? types : [],
            }),
        });
        showAlert('success', 'API added successfully! Configure your settings below.');
        await loadConfiguration();
        // Auto-show config panel after adding
        showConfigPanel(null);
    } catch (err) {
        showAlert('error', err.message || 'Failed to add API.');
    } finally {
        btn.disabled = false; btn.textContent = orig;
    }
}

/* ── Save notification settings (edit) ── */
async function saveNotificationSettings(btn) {
    const apiName = document.getElementById('edit_api_name').value.trim();
    if (!apiName) { showAlert('error', 'API Name is required.'); return; }

    const notifEnabled = document.getElementById('edit_low_notif_toggle').checked;
    const limit        = document.getElementById('edit_low_balance_limit').value.trim();
    const types        = collectNotifTypes('edit');

    if (notifEnabled && !limit) { showAlert('error', 'Please enter the low balance limit amount.'); return; }
    if (notifEnabled && types.length === 0) { showAlert('error', 'Please select at least one notification type.'); return; }

    const orig = btn.textContent;
    btn.disabled = true; btn.textContent = 'Saving…';

    try {
        await apiFetch('/api/v1/seller/api-config/notification-settings', {
            method: 'PATCH',
            body: JSON.stringify({
                api_name:                 apiName,
                low_balance_notification: notifEnabled,
                low_balance_limit:        notifEnabled ? parseFloat(limit) : null,
                notification_types:       notifEnabled ? types : [],
            }),
        });
        showAlert('success', 'Notification settings saved.');
        await loadConfiguration();
    } catch (err) {
        showAlert('error', err.message || 'Failed to save notification settings.');
    } finally {
        btn.disabled = false; btn.textContent = orig;
    }
}

/* ── Build full payload ── */
function buildPayload() {
    return {
        website_url:      document.getElementById('website_url').value.trim(),
        callback_url:     document.getElementById('callback_url').value.trim(),
        status_check_url: document.getElementById('status_url')?.value.trim() || '',
        dispute_url:      document.getElementById('dispute_url')?.value.trim() || '',
        allowed_ips:      document.getElementById('allowed_ips').value.trim(),
        recharge_api:     collectEndpoint('recharge'),
        status_api:       collectEndpoint('status'),
        balance_api:      collectEndpoint('balance'),
        dispute_api:      collectEndpoint('dispute'),
        callback_config: {
            response_type:   document.getElementById('callback_response_type').value,
            ip_validation:   document.getElementById('callback_ip_validation').value.trim(),
            status_field:    document.getElementById('callback_status_field').value.trim(),
            ourtransid:      document.getElementById('callback_ourtransid').value.trim(),
            api_txnid_field: document.getElementById('callback_api_txnid_field').value.trim(),
            live_id_field:   document.getElementById('callback_live_id_field').value.trim(),
            balance_field:   document.getElementById('callback_balance_field').value.trim(),
            success_param:   document.getElementById('callback_success_param').value.trim(),
            pending_param:   document.getElementById('callback_pending_param').value.trim(),
            failure_param:   document.getElementById('callback_failure_param').value.trim(),
        },
        op_code_map: collectOperatorCodes(),
    };
}

function buildSectionPayload(section) {
    const body = buildPayload();
    const intg = currentConfig?.integration || {};
    const keep = k => intg[k] || body[k];
    if (section === 'recharge')  return { ...body, status_api: keep('status_api'), balance_api: keep('balance_api'), dispute_api: keep('dispute_api'), callback_config: keep('callback_config'), op_code_map: keep('op_code_map') };
    if (section === 'balance')   return { ...body, recharge_api: keep('recharge_api'), status_api: keep('status_api'), dispute_api: keep('dispute_api'), callback_config: keep('callback_config'), op_code_map: keep('op_code_map') };
    if (section === 'status')    return { ...body, recharge_api: keep('recharge_api'), balance_api: keep('balance_api'), dispute_api: keep('dispute_api'), callback_config: keep('callback_config'), op_code_map: keep('op_code_map') };
    if (section === 'callback')  return { ...body, recharge_api: keep('recharge_api'), status_api: keep('status_api'), balance_api: keep('balance_api'), dispute_api: keep('dispute_api'), op_code_map: keep('op_code_map') };
    if (section === 'dispute')   return { ...body, recharge_api: keep('recharge_api'), status_api: keep('status_api'), balance_api: keep('balance_api'), callback_config: keep('callback_config'), op_code_map: keep('op_code_map') };
    return body;
}

async function persistPayload(payload, btn, msg) {
    if (!payload.website_url || !payload.callback_url || !payload.allowed_ips) {
        showAlert('error', 'Website URL, Callback URL, and Allowed IPs are required.');
        return;
    }
    if (!confirm('Apply this update now?')) return;

    const hasExisting = !!currentConfig?.integration;
    const isRejected  = currentConfig?.integration?.status === 'rejected';
    const method      = hasExisting && !isRejected ? 'PATCH' : 'POST';

    const orig = btn.textContent;
    btn.disabled = true; btn.textContent = 'Updating…';

    try {
        await apiFetch('/api/v1/seller/api-config/integration', { method, body: JSON.stringify(payload) });
        showAlert('success', msg);
        await loadConfiguration();
    } catch (err) {
        showAlert('error', err.message || 'Failed to save.');
    } finally {
        btn.disabled = false; btn.textContent = orig;
    }
}

async function saveSection(section, btn) {
    await persistPayload(buildSectionPayload(section), btn, 'Section updated successfully.');
}

async function saveOperatorRow(index, btn) {
    const payload = buildPayload();
    const row     = operatorRows[index];
    const single  = [{
        company_name: row.name,
        opparam1: document.getElementById(`opcode_param1_${index}`)?.value.trim() || '',
        opparam2: document.getElementById(`opcode_param2_${index}`)?.value.trim() || '',
        opparam3: document.getElementById(`opcode_param3_${index}`)?.value.trim() || '',
        opparam4: document.getElementById(`opcode_param4_${index}`)?.value.trim() || '',
        opparam5: document.getElementById(`opcode_param5_${index}`)?.value.trim() || '',
    }];
    const rest = (currentConfig?.integration?.op_code_map || []).filter(r => (r.company_name||'') !== row.name);
    payload.op_code_map = rest.concat(single);
    await persistPayload(payload, btn, `Operator ${row.name} updated.`);
}

/* ── API Status toggle ── */
async function toggleApiStatus(input) {
    const previous = !input.checked;
    input.disabled = true;
    try {
        const res = await apiFetch('/api/v1/seller/api-config/toggle-api', { method: 'PATCH' });
        showAlert('success', res.message || 'API status updated.');
        await loadConfiguration();
    } catch (e) {
        input.checked = previous;
        input.disabled = false;
        showAlert('error', e.message || 'Unable to update API status.');
    }
}

/* ── Load ── */
async function loadConfiguration() {
    try {
        const res = await apiFetch('/api/v1/seller/api-config');
        const data = res.data || res;
        fillPage(data);
        updateView(data);
    } catch (err) {
        showAlert('error', err.message || 'Failed to load configuration.');
    }
}

document.addEventListener('DOMContentLoaded', loadConfiguration);
</script>
@endsection
