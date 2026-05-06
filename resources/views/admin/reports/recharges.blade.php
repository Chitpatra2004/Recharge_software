@extends('layouts.admin')

@section('title', 'Recharge Report')
@section('page-title', 'Recharge Report')

@section('content')
<style>
.rr-card{background:#fff;border:1px solid var(--border);border-radius:4px;margin-bottom:18px}
.rr-head{padding:13px 14px;border-bottom:1px solid var(--border);font-size:12px;font-weight:800;text-transform:uppercase;color:#111827}
.rr-filters{display:grid;grid-template-columns:100px 100px 90px 120px 120px 120px 185px 120px 120px auto auto;gap:12px;align-items:end;padding:20px 10px 22px}
.rr-field label{display:block;font-size:16px;font-weight:700;color:#858da8;margin-bottom:8px}.rr-field input,.rr-field select{width:100%;height:28px;border:1px solid #444;padding:3px 8px;font-size:12px;background:#fff}
.rr-blue{background:#0068ce;color:#fff;border:none;border-radius:4px;padding:7px 12px;font-weight:700;cursor:pointer}.rr-green{background:#10b900;color:#fff;border:none;border-radius:4px;padding:7px 12px;font-weight:700;cursor:pointer}
.rr-table-wrap{overflow:auto;padding:0 6px 14px}.rr-table{width:100%;border-collapse:collapse;min-width:1380px}.rr-table th{font-size:13px;font-weight:800;text-align:left;padding:14px 12px;border-top:1px solid #d8dde6;border-bottom:1px solid #d8dde6;color:#111827}.rr-table td{font-size:14px;vertical-align:top;padding:13px 12px;border-bottom:1px solid #d8dde6;color:#000}
.rr-id{color:#005fd1;font-weight:700;cursor:pointer;line-height:1.5}.rr-agent{color:#2563dc;font-size:16px;font-weight:800}.rr-status-badge{font-weight:800;padding:2px 6px;border-radius:3px;display:inline-block}
.rr-status-pending{background:#f59e0b;color:#fff}
.rr-status-queued{background:#0ea5e9;color:#fff}
.rr-status-processing{background:#8b5cf6;color:#fff}
.rr-status-success{background:#16a34a;color:#fff}
.rr-status-failed{background:#dc2626;color:#fff}
.rr-actions{display:flex;flex-direction:column;gap:2px;width:70px}.rr-actions button{border:none;color:#fff;font-size:12px;font-weight:800;padding:7px 6px;border-radius:4px;cursor:pointer}.rr-a-blue{background:#0068ce}.rr-a-orange{background:#f59e0b}.rr-a-cyan{background:#14a3b8}
.rr-modal{display:none;position:fixed;inset:0;background:rgba(0,0,0,.35);z-index:700;align-items:center;justify-content:center}.rr-box{background:#f4f4f4;min-width:590px;max-width:96vw}.rr-box-head{display:flex;align-items:center;justify-content:space-between;padding:12px 12px;background:#fff;color:#858da8;font-size:20px;font-weight:800}.rr-box-body{padding:20px 12px}.rr-box-foot{display:flex;justify-content:flex-end;gap:6px;padding:16px 12px;background:#fff}.rr-box textarea{width:400px;height:48px}.rr-box select{height:31px}.rr-box-foot button{border:none;color:#fff;border-radius:4px;padding:8px 13px;font-weight:800;cursor:pointer}
.rr-log .rr-box{width:1195px;max-width:98vw;max-height:88vh;overflow:auto;background:#fff}.rr-log .rr-box-head{background:#1a2035;color:#fff}.rr-log table{width:100%;border-collapse:collapse}.rr-log th,.rr-log td{border-top:1px solid #d8dde6;padding:14px 12px;vertical-align:top;text-align:left}.rr-log pre{white-space:pre-wrap;font-family:inherit;margin:0}
@media(max-width:1200px){.rr-filters{grid-template-columns:repeat(3,1fr)}}
</style>

<div class="rr-card">
    <div class="rr-head">Search Filters</div>
    <div class="rr-filters">
        <div class="rr-field"><label>From Date</label><input id="f-from" type="date"></div>
        <div class="rr-field"><label>To Date</label><input id="f-to" type="date"></div>
        <div class="rr-field"><label>Status</label><select id="f-status"><option value="">ALL</option><option>pending</option><option>queued</option><option>processing</option><option>success</option><option>failed</option><option>refunded</option></select></div>
        <div class="rr-field"><label>Service</label><select id="f-service"><option value="">ALL</option><option value="prepaid">Prepaid</option><option value="postpaid">Postpaid</option><option value="dth">DTH</option><option value="broadband">Broadband</option></select></div>
        <div class="rr-field"><label>Operator</label><select id="f-operator"><option value="">ALL</option></select></div>
        <div class="rr-field"><label>State</label><select id="f-state"><option value="">ALL</option></select></div>
        <div class="rr-field"><label>API</label><select id="f-api"><option value="">ALL</option></select></div>
        <div class="rr-field"><label>Number / Id</label><input id="f-search" type="text"></div>
        <div class="rr-field"><label>Data</label><select id="f-data"><option>LIVE</option><option>ALL</option></select></div>
        <button class="rr-blue" onclick="loadReport()">Submit</button>
        <button class="rr-green" onclick="exportReport()">Export</button>
    </div>
</div>

<div class="rr-card">
    <div class="rr-head">Recharge Report</div>
    <div style="padding:22px 6px 6px;color:#005fd1;font-size:13px"><span id="rr-count">0</span>&gt;Last ›</div>
    <div class="rr-table-wrap">
        <table class="rr-table">
            <thead><tr><th>Pdrs.Id</th><th>Api TxnId</th><th>Live Id</th><th>Rec.<br>Date</th><th>TAT</th><th>Agent Name</th><th>opcode</th><th>Mobile No</th><th>Amt</th><th>Status</th><th>API</th><th>Purchase<br>Comm.</th><th>Sale<br>Comm.</th><th>Profit</th><th></th></tr></thead>
            <tbody id="rr-body"><tr><td colspan="15" style="text-align:center;padding:30px;color:#777">Loading...</td></tr></tbody>
        </table>
    </div>
</div>

<div id="action-modal" class="rr-modal">
    <div class="rr-box">
        <div class="rr-box-head"><span>Action</span><button onclick="closeAction()" style="border:none;background:none;font-size:18px;color:#777;cursor:pointer">×</button></div>
        <div class="rr-box-body">
            <p>Username : <span id="am-user"></span></p>
            <p>Operator : <span id="am-op"></span></p>
            <p>Mobile Number : <span id="am-mobile"></span></p>
            <p>Amount : <span id="am-amount"></span></p>
            <p>Remarks or OperatorId or Transaction Id :</p>
            <textarea id="am-remarks"></textarea>
            <p>Recharge Ip:</p>
            <p>Send To Another API: <select id="am-api"></select> <button class="rr-blue" onclick="sendToApi()">Send</button></p>
        </div>
        <div class="rr-box-foot">
            <button style="background:#e83e58" onclick="doAction('refund')">Refund</button>
            <button style="background:#f59e0b" onclick="doAction('status')">Status</button>
            <button style="background:#0068ce" onclick="doAction('success')">Success</button>
            <button style="background:#f59e0b" onclick="doAction('resend')">Resend</button>
        </div>
    </div>
</div>

<div id="log-modal" class="rr-modal rr-log">
    <div class="rr-box">
        <div class="rr-box-head"><span id="log-title">Request/Response Log</span><button onclick="closeLog()" style="border:none;background:none;color:#fff;font-size:18px;cursor:pointer">×</button></div>
        <div style="padding:16px 14px"><table><thead><tr><th>LogId</th><th>Type</th><th>Label</th><th>api</th><th>RechargeId</th><th>DateTime</th><th>Request</th><th>Response</th></tr></thead><tbody id="log-body"></tbody></table></div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let rows=[], currentTxn=null, apiRoutes=[];
function todayLocalISO(){
    const d = new Date();
    const y = d.getFullYear();
    const m = String(d.getMonth()+1).padStart(2,'0');
    const day = String(d.getDate()).padStart(2,'0');
    return `${y}-${m}-${day}`;
}
const today = todayLocalISO();
document.getElementById('f-from').value=today; document.getElementById('f-to').value=today;

function esc(s){return String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));}
function jsonText(v){if(!v)return ''; try{return JSON.stringify(typeof v==='string'?JSON.parse(v):v,null,2)}catch(e){return String(v)}}
function fmtDur(ms){
    if(ms == null || !isFinite(ms) || ms < 0) return '—';
    const sec = Math.max(0, Math.round(ms/1000));
    return `${sec} sec`;
}
function params(){const p=new URLSearchParams(); const map={date_from:'f-from',date_to:'f-to',status:'f-status',recharge_type:'f-service',operator_code:'f-operator',api_provider:'f-api'}; Object.entries(map).forEach(([k,id])=>{const v=document.getElementById(id).value;if(v)p.set(k,v)}); const s=document.getElementById('f-search').value.trim(); if(/^\d{10,15}$/.test(s))p.set('mobile',s); p.set('per_page',100); return p;}

async function bootFilters(){
    // Start report load immediately (don't block UI on dropdown APIs)
    loadReport();

    const [opsRes, apiRes] = await Promise.all([
        apiFetch('/api/v1/employee/operator-settings'),
        apiFetch('/api/v1/employee/api-providers')
    ]);
    const ops  = opsRes ? await opsRes.json() : {};
    const apis = apiRes ? await apiRes.json() : {};
    document.getElementById('f-operator').innerHTML =
        '<option value="">ALL</option>' + (ops.operators||[]).map(o=>`<option value="${esc(o.code)}">${esc(o.name)}</option>`).join('');
    apiRoutes = apis.routes || [];
    const providers = [...new Set(apiRoutes.map(r=>r.api_provider).filter(Boolean))];
    document.getElementById('f-api').innerHTML =
        '<option value="">ALL</option>' + providers.map(p=>`<option value="${esc(p)}">${esc(p)}</option>`).join('');
    document.getElementById('am-api').innerHTML =
        '<option value="">select Api</option>' + apiRoutes.map(r=>`<option value="${r.id}">${esc(r.name)} - ${esc(r.api_provider)}</option>`).join('');
}

async function loadReport(){
    document.getElementById('rr-body').innerHTML='<tr><td colspan="15" style="text-align:center;padding:30px;color:#777">Loading...</td></tr>';
    const res=await apiFetch('/api/v1/employee/reports/recharges?'+params().toString()); if(!res)return;
    const data=await res.json(); rows=data.transactions?.data||[]; document.getElementById('rr-count').textContent=data.transactions?.total||rows.length; renderRows();
}

function renderRows(){
    const body=document.getElementById('rr-body');
    if(!rows.length){body.innerHTML='<tr><td colspan="15" style="text-align:center;padding:30px;color:#777">No records found.</td></tr>';return}
    body.innerHTML=rows.map(r=>{
        const d=r.created_at?new Date(r.created_at):null;
        const p=r.processed_at?new Date(r.processed_at):null;
        const tatMs=(d&&p)?(p-d):null;
        const salePct=r.amount?((Number(r.commission||0)/Number(r.amount))*100):0, purchase=Number(r.operator_margin||0), profit=(Number(r.operator_margin||0)-Number(r.commission||0));
        const statusCls = r.status==='success' ? 'rr-status-success'
                        : (r.status==='failed'||r.status==='refunded') ? 'rr-status-failed'
                        : r.status==='queued' ? 'rr-status-queued'
                        : r.status==='processing' ? 'rr-status-processing'
                        : 'rr-status-pending';
        return `<tr>
            <td><div class="rr-id" onclick="openLog(${r.id})">${r.id}<br>API<br>no</div></td>
            <td>${esc(r.api_ref||'')}</td><td>${esc(r.operator_ref||'')}</td>
            <td style="white-space:nowrap;font-size:12px">${d?d.toISOString().slice(0,10):''}<br><span style="color:#64748b;font-weight:700">${d?d.toLocaleTimeString('en-IN',{hour12:false}):''}</span></td>
            <td style="white-space:nowrap;font-size:12px;font-weight:800">${fmtDur(tatMs)}</td>
            <td><span class="rr-agent">${esc(r.user_name||'')}</span></td>
            <td>${esc((r.operator_code||'')+' '+(r.recharge_type||''))}</td>
            <td>${esc(r.mobile)}<br>${esc(r.circle||'')}</td><td>${Number(r.amount||0).toFixed(2)}</td>
            <td><span class="rr-status-badge ${statusCls}">${esc(r.status||'')}</span></td>
            <td>${esc(r.api_provider||r.route_name||'')}</td>
            <td>${purchase.toFixed(3)}<br>${r.amount?((purchase/Number(r.amount))*100).toFixed(2):'0.00'}%</td>
            <td>${salePct.toFixed(2)} %</td><td>${profit.toFixed(2)}</td>
            <td><div class="rr-actions"><button class="rr-a-blue" onclick="openAction(${r.id})">Action</button><button class="rr-a-orange" onclick="openResponse(${r.id})">Response</button><button class="rr-a-cyan" onclick="openResponse(${r.id})">OfferResp</button></div></td>
        </tr>`;
    }).join('');
}

function openAction(id){currentTxn=rows.find(r=>r.id===id); if(!currentTxn)return; document.getElementById('am-user').textContent=currentTxn.user_name||''; document.getElementById('am-op').textContent=(currentTxn.operator_code||'')+' '+(currentTxn.recharge_type||''); document.getElementById('am-mobile').textContent=currentTxn.mobile||''; document.getElementById('am-amount').textContent=Number(currentTxn.amount||0).toFixed(2); document.getElementById('am-remarks').value=''; document.getElementById('action-modal').style.display='flex';}
function closeAction(){document.getElementById('action-modal').style.display='none'}
function closeLog(){document.getElementById('log-modal').style.display='none'}

async function doAction(type){
    if(!currentTxn)return; const remarks=document.getElementById('am-remarks').value; let url=`/api/v1/employee/recharges/${currentTxn.id}/${type}`, body={remarks};
    if(type==='status'){const st=prompt('Enter status: pending, processing, success, failed, refunded', currentTxn.status||'pending'); if(!st)return; body.status=st;}
    const res=await apiFetch(url,{method:'POST',body:JSON.stringify(body)}); const j=await res.json().catch(()=>({})); alert(j.message||'Done'); closeAction(); loadReport();
}
async function sendToApi(){if(!currentTxn)return; const route_id=document.getElementById('am-api').value; if(!route_id){alert('Select API');return} const remarks=document.getElementById('am-remarks').value; const res=await apiFetch(`/api/v1/employee/recharges/${currentTxn.id}/send-api`,{method:'POST',body:JSON.stringify({route_id,remarks})}); const j=await res.json().catch(()=>({})); alert(j.message||'Sent'); closeAction(); loadReport();}

async function openLog(id){await loadLog(id,'Request/Response Log')}
async function openResponse(id){await loadLog(id,'Response')}
async function loadLog(id,title){
    document.getElementById('log-title').textContent = title;
    document.getElementById('log-body').innerHTML = '<tr><td colspan="8">Loading...</td></tr>';
    document.getElementById('log-modal').style.display = 'flex';

    const res = await apiFetch(`/api/v1/employee/recharges/${id}`);
    const j   = await res.json();
    const d   = j.data || {};
    const tx  = d.transaction || {};
    const attempts = d.attempts || [];
    const logs = attempts.length ? attempts.map(a => {
        const requestPayload = a.request_payload ? jsonText(a.request_payload) : '';
        const requestLines = [`[${(a.log_type || 'recharge').toUpperCase()}] ${a.log_label || ''}`.trim()];
        if (a.request_url) requestLines.push(a.request_url);
        if (requestPayload) requestLines.push(requestPayload);

        return {
            id: a.id,
            type: a.log_type || 'recharge',
            label: a.log_label || 'Recharge',
            api: `${a.api_provider || a.operator_code || ''}`.trim() || '—',
            recharge: a.created_at,
            request: requestLines.join('\\n'),
            response: a.response_payload ? jsonText(a.response_payload) : (a.error_message || ''),
        };
    }) : [{
        id: '—',
        type: 'none',
        label: 'No logs found',
        api: tx.api_provider || tx.operator_code || '—',
        recharge: tx.created_at,
        request: 'No request log found',
        response: jsonText(tx.operator_response) || tx.failure_reason || '',
    }];
    document.getElementById('log-body').innerHTML = logs.map(l =>
        `<tr><td>${esc(l.id)}</td><td>${esc(l.type)}</td><td>${esc(l.label)}</td><td>${esc(l.api)}</td><td>${esc(tx.id||'')}</td><td>${esc(l.recharge||'')}</td><td><pre>${esc(l.request)}</pre></td><td><pre>${esc(l.response)}</pre></td></tr>`
    ).join('');
}
function exportReport(){window.open('/api/v1/employee/reports/recharges?'+params().toString()+'&export=csv','_blank')}
document.addEventListener('DOMContentLoaded',bootFilters);
</script>
@endpush
