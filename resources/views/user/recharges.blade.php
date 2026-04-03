@extends('layouts.user')
@section('title','Recharge & Bill Pay')
@section('page-title','Recharge & Bills')

@section('content')
<div class="breadcrumb">
    <a href="/user/dashboard">Dashboard</a>
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <span>Recharge & Bills</span>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════════
     SERVICE PANEL
═══════════════════════════════════════════════════════════════════════════ --}}
<div class="card" style="margin-bottom:18px">

    {{-- Service Tabs --}}
    <div style="display:flex;border-bottom:1px solid var(--border2);padding:0 18px">
        <button class="svc-tab active" id="tab-btn-mobile"  onclick="switchSvc('mobile')">📱 Mobile</button>
        <button class="svc-tab"        id="tab-btn-dth"     onclick="switchSvc('dth')">📺 DTH</button>
        <button class="svc-tab"        id="tab-btn-bbps"    onclick="switchSvc('bbps')">🏦 Bill Pay</button>
    </div>

    {{-- ── MOBILE RECHARGE ─────────────────────────────────────────────── --}}
    <div id="pane-mobile" class="svc-pane" style="padding:18px">
        <div style="display:flex;gap:6px;margin-bottom:16px">
            <button class="type-btn active" id="mbtn-prepaid"  onclick="setMobileType('prepaid')">Prepaid</button>
            <button class="type-btn"        id="mbtn-postpaid" onclick="setMobileType('postpaid')">Postpaid</button>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
            <div>
                <label class="flbl">Mobile Number *</label>
                <input type="tel" id="m-number" maxlength="10" placeholder="10-digit number"
                    oninput="onMobileInput()" class="finp">
                <div id="m-detect-status" style="margin-top:5px;display:flex;align-items:center;gap:6px;flex-wrap:wrap;min-height:20px">
                    <span id="m-detect-text" style="font-size:11px;color:var(--muted)"></span>
                    <span id="m-circle-badge" style="display:none;font-size:10px;font-weight:600;padding:2px 8px;border-radius:10px;background:var(--card2);border:1px solid var(--border2);color:var(--muted)"></span>
                </div>
            </div>
            <div>
                <label class="flbl">Operator *</label>
                <select id="m-operator" class="finp" onchange="loadMobilePlans()">
                    <option value="">Select Operator</option>
                    <option value="AIRTEL">Airtel</option>
                    <option value="JIO">Jio</option>
                    <option value="VI">Vi (Vodafone Idea)</option>
                    <option value="BSNL">BSNL</option>
                    <option value="MTNL">MTNL</option>
                </select>
            </div>
        </div>

        {{-- Plans --}}
        <div id="mobile-plans-wrap" style="display:none;margin-bottom:16px">
            <div style="display:flex;gap:6px;margin-bottom:12px;flex-wrap:wrap">
                <button class="plan-filter-btn active" onclick="filterPlans('all',this)">All</button>
                <button class="plan-filter-btn" onclick="filterPlans('popular',this)">⭐ Popular</button>
                <button class="plan-filter-btn" onclick="filterPlans('data',this)">📶 Data</button>
                <button class="plan-filter-btn" onclick="filterPlans('unlimited',this)">♾️ Unlimited</button>
                <button class="plan-filter-btn" onclick="filterPlans('sms',this)">💬 SMS</button>
                <button class="plan-filter-btn" onclick="filterPlans('validity',this)">📅 Long Validity</button>
            </div>
            <div id="plan-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px;max-height:320px;overflow-y:auto"></div>
        </div>

        {{-- Amount + Pay --}}
        <div style="display:flex;gap:10px;align-items:flex-end">
            <div style="flex:1">
                <label class="flbl">Amount (₹) *</label>
                <input type="number" id="m-amount" placeholder="Enter amount" min="1" class="finp">
            </div>
            <button class="btn btn-primary" style="white-space:nowrap;padding:9px 20px" onclick="doRecharge('mobile')" id="mobile-pay-btn">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Recharge Now
            </button>
        </div>
        <div id="mobile-msg" style="display:none;margin-top:10px;font-size:13px;border-radius:8px;padding:10px 12px"></div>
    </div>

    {{-- ── DTH RECHARGE ────────────────────────────────────────────────── --}}
    <div id="pane-dth" class="svc-pane" style="padding:18px;display:none">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
            <div>
                <label class="flbl">Subscriber / Customer ID *</label>
                <input type="text" id="d-number" placeholder="Enter subscriber ID" class="finp">
            </div>
            <div>
                <label class="flbl">DTH Operator *</label>
                <select id="d-operator" class="finp" onchange="loadDthPlans()">
                    <option value="">Select Operator</option>
                    <option value="TATAPLAY">Tata Play</option>
                    <option value="DISHTV">Dish TV</option>
                    <option value="SUNDIRECT">Sun Direct</option>
                    <option value="VIDEOCOND2H">Videocon D2H</option>
                    <option value="AIRTELDIGITAL">Airtel Digital TV</option>
                </select>
            </div>
        </div>
        <div id="dth-plans-wrap" style="display:none;margin-bottom:16px">
            <div style="display:flex;gap:6px;margin-bottom:12px;flex-wrap:wrap">
                <button class="plan-filter-btn active" onclick="filterDthPlans('all',this)">All</button>
                <button class="plan-filter-btn" onclick="filterDthPlans('sd',this)">SD</button>
                <button class="plan-filter-btn" onclick="filterDthPlans('hd',this)">HD</button>
                <button class="plan-filter-btn" onclick="filterDthPlans('sports',this)">Sports</button>
                <button class="plan-filter-btn" onclick="filterDthPlans('family',this)">Family</button>
            </div>
            <div id="dth-plan-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px;max-height:280px;overflow-y:auto"></div>
        </div>
        <div style="display:flex;gap:10px;align-items:flex-end">
            <div style="flex:1">
                <label class="flbl">Amount (₹) *</label>
                <input type="number" id="d-amount" placeholder="Enter amount" min="1" class="finp">
            </div>
            <button class="btn btn-primary" style="white-space:nowrap;padding:9px 20px" onclick="doRecharge('dth')" id="dth-pay-btn">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Recharge Now
            </button>
        </div>
        <div id="dth-msg" style="display:none;margin-top:10px;font-size:13px;border-radius:8px;padding:10px 12px"></div>
    </div>

    {{-- ── BBPS BILL PAY ───────────────────────────────────────────────── --}}
    <div id="pane-bbps" class="svc-pane" style="padding:18px;display:none">
        {{-- Step 1 — Category --}}
        <div id="bbps-step1">
            <div style="font-size:13px;font-weight:600;color:var(--muted);margin-bottom:14px">Select Bill Category</div>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:10px">
                @foreach([
                    ['electricity','⚡','Electricity','#f59e0b'],
                    ['gas','🔥','Gas / PNG','#ef4444'],
                    ['water','💧','Water','#3b82f6'],
                    ['broadband','📡','Broadband','#8b5cf6'],
                    ['postpaid','📱','Postpaid','#10b981'],
                    ['insurance','🛡️','Insurance','#0891b2'],
                    ['emi','🏦','Loan EMI','#6366f1'],
                    ['creditcard','💳','Credit Card','#ec4899'],
                    ['fasttag','🛣️','FASTag','#14b8a6'],
                    ['education','🎓','Education','#f97316'],
                    ['municipal','🏛️','Municipality','#64748b'],
                    ['cable','📺','Cable TV','#a855f7'],
                ] as [$cat, $icon, $name, $color])
                <button onclick="selectBbpsCategory('{{ $cat }}')"
                    style="background:var(--card2);border:1px solid var(--border2);border-radius:10px;padding:14px 8px;cursor:pointer;text-align:center;transition:all .15s;color:var(--text)"
                    onmouseover="this.style.borderColor='{{ $color }}'" onmouseout="this.style.borderColor='var(--border2)'">
                    <div style="font-size:24px;margin-bottom:6px">{{ $icon }}</div>
                    <div style="font-size:12px;font-weight:600;color:var(--text)">{{ $name }}</div>
                </button>
                @endforeach
            </div>
        </div>

        {{-- Step 2 — Biller + consumer number --}}
        <div id="bbps-step2" style="display:none">
            <button onclick="resetBbps()" style="background:none;border:none;color:var(--muted);font-size:12px;cursor:pointer;margin-bottom:14px;padding:0;display:flex;align-items:center;gap:4px">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:13px;height:13px"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                Change Category
            </button>
            <div id="bbps-category-title" style="font-size:14px;font-weight:700;color:var(--text);margin-bottom:14px"></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
                <div>
                    <label class="flbl">Select Biller *</label>
                    <select id="b-biller" class="finp">
                        <option value="">Choose biller…</option>
                    </select>
                </div>
                <div>
                    <label class="flbl" id="b-consumer-label">Consumer / Account Number *</label>
                    <input type="text" id="b-consumer" placeholder="Enter number" class="finp">
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
                <div>
                    <label class="flbl">Amount (₹) *</label>
                    <input type="number" id="b-amount" placeholder="Enter amount" min="1" class="finp">
                </div>
                <div id="b-mobile-wrap">
                    <label class="flbl">Registered Mobile</label>
                    <input type="tel" id="b-mobile" maxlength="10" placeholder="10-digit" class="finp">
                </div>
            </div>
            <div style="display:flex;gap:10px">
                <button class="btn btn-primary" style="padding:9px 24px" onclick="doRecharge('bbps')" id="bbps-pay-btn">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Pay Bill
                </button>
            </div>
            <div id="bbps-msg" style="display:none;margin-top:10px;font-size:13px;border-radius:8px;padding:10px 12px"></div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════════
     TRANSACTION HISTORY
═══════════════════════════════════════════════════════════════════════════ --}}
<div class="card">
    <div class="card-header" style="justify-content:space-between">
        <span class="card-title">Transaction History</span>
        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
            <select id="f-status" onchange="loadTxns()" style="background:var(--card2);border:1px solid var(--border2);border-radius:6px;padding:5px 8px;font-size:12px;color:var(--text);outline:none">
                <option value="">All Status</option>
                <option value="success">Success</option>
                <option value="failed">Failed</option>
                <option value="processing">Processing</option>
                <option value="pending">Pending</option>
                <option value="refunded">Refunded</option>
            </select>
            <input type="date" id="f-from" onchange="loadTxns()" style="background:var(--card2);border:1px solid var(--border2);border-radius:6px;padding:5px 8px;font-size:12px;color:var(--text);outline:none;color-scheme:dark">
            <input type="date" id="f-to"   onchange="loadTxns()" style="background:var(--card2);border:1px solid var(--border2);border-radius:6px;padding:5px 8px;font-size:12px;color:var(--text);outline:none;color-scheme:dark">
            <span id="txn-count" style="font-size:11px;color:var(--muted)"></span>
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Txn ID</th>
                    <th>Mobile / Account</th>
                    <th>Operator / Biller</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Receipt</th>
                </tr>
            </thead>
            <tbody id="txn-tbody">
                <tr><td colspan="8"><div class="loading"><div class="spinner"></div> Loading…</div></td></tr>
            </tbody>
        </table>
    </div>
    <div class="card-footer" id="pagination" style="gap:8px;justify-content:flex-end"></div>
</div>

{{-- Receipt Modal --}}
<div id="receipt-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:999;align-items:center;justify-content:center;padding:20px">
    <div style="background:#fff;border-radius:16px;width:100%;max-width:420px;overflow:hidden;box-shadow:0 24px 60px rgba(0,0,0,.4)">
        <div style="background:linear-gradient(135deg,#2563eb,#6366f1);padding:24px;text-align:center;color:#fff">
            <div style="width:52px;height:52px;background:rgba(255,255,255,.2);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:26px;height:26px"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div style="font-size:13px;opacity:.8;margin-bottom:4px">RechargeHub</div>
            <div style="font-size:20px;font-weight:800">Transaction Receipt</div>
        </div>
        <div style="padding:24px" id="receipt-body"></div>
        <div style="padding:16px 24px;border-top:1px solid #f1f5f9;display:flex;gap:10px">
            <button onclick="printReceipt()" style="flex:1;background:linear-gradient(135deg,#2563eb,#6366f1);color:#fff;border:none;border-radius:8px;padding:10px;font-size:13px;font-weight:600;cursor:pointer">Print / Save PDF</button>
            <button onclick="closeReceipt()" style="flex:1;background:#f1f5f9;color:#374151;border:none;border-radius:8px;padding:10px;font-size:13px;font-weight:600;cursor:pointer">Close</button>
        </div>
    </div>
</div>

<style>
.flbl { font-size:12px;font-weight:600;color:var(--muted);display:block;margin-bottom:4px }
.finp { width:100%;background:var(--card2);border:1px solid var(--border2);border-radius:8px;padding:9px 12px;font-size:13px;color:var(--text);outline:none;font-family:inherit;box-sizing:border-box;color-scheme:dark }
.finp:focus { border-color:var(--primary) }
.finp option { background:var(--card2,#1e2538);color:var(--text,#e2e8f0) }
.svc-tab { background:none;border:none;padding:12px 20px;font-size:13px;font-weight:600;color:var(--muted);cursor:pointer;border-bottom:2px solid transparent;transition:all .15s;font-family:inherit }
.svc-tab.active { color:var(--primary);border-bottom-color:var(--primary) }
.type-btn { background:var(--card2);border:1px solid var(--border2);border-radius:6px;padding:6px 16px;font-size:12px;font-weight:600;color:var(--muted);cursor:pointer;transition:all .15s;font-family:inherit }
.type-btn.active { background:var(--primary);border-color:var(--primary);color:#fff }
.plan-filter-btn { background:var(--card2);border:1px solid var(--border2);border-radius:20px;padding:4px 12px;font-size:12px;font-weight:500;color:var(--muted);cursor:pointer;transition:all .15s;font-family:inherit;white-space:nowrap }
.plan-filter-btn.active { background:var(--primary);border-color:var(--primary);color:#fff }
.plan-card { background:var(--card2);border:1px solid var(--border2);border-radius:10px;padding:12px;cursor:pointer;transition:all .15s;position:relative }
.plan-card:hover,.plan-card.selected { border-color:var(--primary);background:rgba(99,102,241,.08) }
.plan-card .tag { position:absolute;top:8px;right:8px;font-size:9px;font-weight:700;padding:2px 6px;border-radius:10px;background:rgba(245,158,11,.2);color:#f59e0b }
</style>
@endsection

@push('scripts')
<script>
// ═══════════════════════════════════════════════════════════════════
//  PLAN DATABASE
// ═══════════════════════════════════════════════════════════════════
const PLANS = {
    AIRTEL: {
        prepaid: [
            { amt:19,  data:'200MB',    validity:'1 day',   desc:'200MB Data + Unlimited Calls',                                     tags:['data'] },
            { amt:99,  data:'1GB/day',  validity:'14 days', desc:'1GB/day + Unlimited Calls + 100 SMS/day',                          tags:['data','unlimited'] },
            { amt:155, data:'1GB/day',  validity:'20 days', desc:'1GB/day + Unlimited Calls + 100 SMS/day',                          tags:['data'] },
            { amt:179, data:'1.5GB/day',validity:'28 days', desc:'1.5GB/day + Unlimited Calls + 100 SMS/day',                        tags:['popular','data','unlimited'] },
            { amt:239, data:'1.5GB/day',validity:'28 days', desc:'1.5GB/day + Unlimited Calls + 100 SMS/day + Disney+ Hotstar',      tags:['data'] },
            { amt:299, data:'2GB/day',  validity:'28 days', desc:'2GB/day + Unlimited Calls + 100 SMS/day',                          tags:['popular','data','unlimited'] },
            { amt:359, data:'2.5GB/day',validity:'28 days', desc:'2.5GB/day + Unlimited Calls + 100 SMS/day',                        tags:['data'] },
            { amt:399, data:'2.5GB/day',validity:'28 days', desc:'2.5GB/day + Unlimited Calls + Disney+ Hotstar',                    tags:['popular','data'] },
            { amt:479, data:'2.5GB/day',validity:'56 days', desc:'2.5GB/day + Unlimited Calls + Amazon Prime',                       tags:['validity','data'] },
            { amt:599, data:'2.5GB/day',validity:'84 days', desc:'2.5GB/day + Unlimited Calls + 100 SMS/day',                        tags:['validity','data'] },
            { amt:699, data:'3GB/day',  validity:'84 days', desc:'3GB/day + Unlimited Calls + 100 SMS/day + Disney+ Hotstar',        tags:['popular','validity','data'] },
            { amt:999, data:'2GB/day',  validity:'180 days',desc:'2GB/day + Unlimited Calls + 100 SMS/day',                          tags:['validity'] },
            { amt:1699,data:'24GB',     validity:'365 days',desc:'24GB Total + Unlimited Calls + 100 SMS/day',                       tags:['validity'] },
        ],
        postpaid: [
            { amt:399, data:'75GB',     validity:'Monthly', desc:'75GB Data + Unlimited Calls + 100 SMS/day + Disney+ Hotstar',      tags:['popular'] },
            { amt:499, data:'Unlimited',validity:'Monthly', desc:'Unlimited Data + Unlimited Calls + Amazon Prime + Disney+ Hotstar', tags:['popular','unlimited'] },
            { amt:999, data:'Unlimited',validity:'Monthly', desc:'Unlimited Data for 3 connections + OTT Pack',                      tags:['unlimited'] },
        ]
    },
    JIO: {
        prepaid: [
            { amt:19,  data:'200MB',    validity:'1 day',   desc:'200MB Data + Unlimited Calls',                                     tags:['data'] },
            { amt:129, data:'2GB/day',  validity:'15 days', desc:'2GB/day + Unlimited Calls + 100 SMS/day + JioCinema',              tags:['popular','data'] },
            { amt:189, data:'1.5GB/day',validity:'28 days', desc:'1.5GB/day + Unlimited Calls + 100 SMS/day',                        tags:['data','unlimited'] },
            { amt:249, data:'1.5GB/day',validity:'28 days', desc:'1.5GB/day + Unlimited Calls + JioCinema Premium',                  tags:['data'] },
            { amt:299, data:'2GB/day',  validity:'28 days', desc:'2GB/day + Unlimited Calls + 100 SMS/day + JioCinema',              tags:['popular','data','unlimited'] },
            { amt:349, data:'3GB/day',  validity:'28 days', desc:'3GB/day + Unlimited Calls + 100 SMS/day + JioCinema Premium',      tags:['popular','data'] },
            { amt:479, data:'3GB/day',  validity:'56 days', desc:'3GB/day + Unlimited Calls + 100 SMS/day',                          tags:['validity','data'] },
            { amt:629, data:'3GB/day',  validity:'84 days', desc:'3GB/day + Unlimited Calls + 100 SMS/day + JioCinema',              tags:['popular','validity','data'] },
            { amt:799, data:'3GB/day',  validity:'84 days', desc:'3GB/day + Unlimited Calls + 100 SMS/day + JioCinema Premium',      tags:['validity','data'] },
            { amt:999, data:'6GB/day',  validity:'84 days', desc:'6GB/day + Unlimited Calls + 100 SMS/day + JioCinema Premium',      tags:['validity','data','popular'] },
            { amt:1559,data:'24GB',     validity:'365 days',desc:'24GB Total + Unlimited Calls + 100 SMS/day + JioCinema',           tags:['validity'] },
        ],
        postpaid: [
            { amt:399, data:'75GB',     validity:'Monthly', desc:'75GB + Unlimited Calls + JioCinema Premium',                       tags:['popular'] },
            { amt:499, data:'Unlimited',validity:'Monthly', desc:'Unlimited Data + Calls + JioCinema + Netflix',                     tags:['popular','unlimited'] },
            { amt:999, data:'Unlimited',validity:'Monthly', desc:'Unlimited Data (3 SIMs) + All OTT apps',                           tags:['unlimited'] },
        ]
    },
    VI: {
        prepaid: [
            { amt:19,  data:'200MB',    validity:'1 day',   desc:'200MB Data + Unlimited Calls',                                     tags:['data'] },
            { amt:119, data:'1GB/day',  validity:'14 days', desc:'1GB/day + Unlimited Calls + 100 SMS/day',                          tags:['data'] },
            { amt:179, data:'1.5GB/day',validity:'28 days', desc:'1.5GB/day + Unlimited Calls + 100 SMS/day',                        tags:['popular','data','unlimited'] },
            { amt:269, data:'2GB/day',  validity:'28 days', desc:'2GB/day + Unlimited Calls + 100 SMS/day + Vi Movies & TV',         tags:['popular','data'] },
            { amt:299, data:'2GB/day',  validity:'28 days', desc:'2GB/day + Unlimited Calls + Weekend Rollover Data',                tags:['popular','data','unlimited'] },
            { amt:359, data:'2.5GB/day',validity:'28 days', desc:'2.5GB/day + Unlimited Calls + Vi Heroes Unlimited',                tags:['data'] },
            { amt:449, data:'2GB/day',  validity:'56 days', desc:'2GB/day + Unlimited Calls + 100 SMS/day',                          tags:['validity','data'] },
            { amt:569, data:'1.5GB/day',validity:'84 days', desc:'1.5GB/day + Unlimited Calls + 100 SMS/day',                        tags:['validity','data'] },
            { amt:699, data:'2GB/day',  validity:'84 days', desc:'2GB/day + Unlimited Calls + 100 SMS/day + Vi Movies & TV',         tags:['popular','validity','data'] },
            { amt:995, data:'1.5GB/day',validity:'180 days',desc:'1.5GB/day + Unlimited Calls + 100 SMS/day',                        tags:['validity'] },
        ],
        postpaid: [
            { amt:399, data:'75GB',     validity:'Monthly', desc:'75GB + Unlimited Calls + Vi Movies & TV',                          tags:['popular'] },
            { amt:499, data:'Unlimited',validity:'Monthly', desc:'Unlimited Data + Calls + Netflix + Amazon Prime',                  tags:['popular','unlimited'] },
        ]
    },
    BSNL: {
        prepaid: [
            { amt:22,  data:'100MB',    validity:'1 day',   desc:'100MB Data + 100 Mins Calls',                                      tags:['data'] },
            { amt:97,  data:'2GB/day',  validity:'18 days', desc:'2GB/day + Unlimited Calls + 100 SMS/day',                          tags:['popular','data'] },
            { amt:187, data:'2GB/day',  validity:'28 days', desc:'2GB/day + Unlimited Calls + 100 SMS/day',                          tags:['popular','data','unlimited'] },
            { amt:247, data:'2GB/day',  validity:'28 days', desc:'2GB/day + Unlimited Calls + 100 SMS/day + EROS Now',               tags:['data'] },
            { amt:397, data:'2GB/day',  validity:'60 days', desc:'2GB/day + Unlimited Calls + 100 SMS/day',                          tags:['validity','data'] },
            { amt:797, data:'2GB/day',  validity:'180 days',desc:'2GB/day + Unlimited Calls + 100 SMS/day',                          tags:['validity','data'] },
        ],
        postpaid: [
            { amt:199, data:'5GB',      validity:'Monthly', desc:'5GB Data + 500 Mins Calls + 100 SMS/day',                         tags:[] },
            { amt:399, data:'Unlimited',validity:'Monthly', desc:'Unlimited Calls + 10GB Data + 100 SMS/day',                        tags:['popular','unlimited'] },
        ]
    },
    MTNL: {
        prepaid: [
            { amt:107, data:'1GB/day',  validity:'28 days', desc:'1GB/day + Unlimited Calls + 100 SMS/day',                          tags:['popular','data'] },
            { amt:197, data:'2GB/day',  validity:'28 days', desc:'2GB/day + Unlimited Calls + 100 SMS/day',                          tags:['popular','data','unlimited'] },
        ],
        postpaid: [
            { amt:299, data:'Unlimited',validity:'Monthly', desc:'Unlimited Calls + 5GB Data',                                       tags:['popular'] },
        ]
    },
    TATAPLAY: {
        plans: [
            { amt:153, channels:'220+', validity:'30 days', desc:'South Lite Pack — 220+ channels, 30 days',                         tags:['sd','family'] },
            { amt:175, channels:'300+', validity:'30 days', desc:'Super Value Pack — 300+ channels, SD, 30 days',                    tags:['sd','family','popular'] },
            { amt:239, channels:'350+', validity:'30 days', desc:'Grand Value Pack — 350+ channels, SD, 30 days',                    tags:['sd','family'] },
            { amt:299, channels:'280+', validity:'30 days', desc:'HD Sports Pack — 280+ channels + Sports HD, 30 days',              tags:['hd','sports','popular'] },
            { amt:399, channels:'350+', validity:'30 days', desc:'HD Premium — 350+ channels, HD, 30 days',                          tags:['hd','family','popular'] },
            { amt:599, channels:'400+', validity:'30 days', desc:'Maxi HD — 400+ channels, Full HD, 30 days',                        tags:['hd','sports'] },
        ]
    },
    DISHTV: {
        plans: [
            { amt:153, channels:'200+', validity:'30 days', desc:'South Basic — 200+ channels, SD',                                  tags:['sd'] },
            { amt:199, channels:'250+', validity:'30 days', desc:'Super Family Pack — 250+ channels, SD, 30 days',                   tags:['sd','family','popular'] },
            { amt:299, channels:'300+', validity:'30 days', desc:'HD Starter — 300+ channels with HD, 30 days',                      tags:['hd','popular'] },
            { amt:399, channels:'350+', validity:'30 days', desc:'Sports HD Pack — 350+ channels, Sports HD',                        tags:['hd','sports'] },
        ]
    },
    SUNDIRECT: {
        plans: [
            { amt:153, channels:'180+', validity:'30 days', desc:'Basic Pack — 180+ channels, SD',                                   tags:['sd'] },
            { amt:219, channels:'250+', validity:'30 days', desc:'Super Pack — 250+ channels, SD, 30 days',                          tags:['sd','family','popular'] },
            { amt:299, channels:'290+', validity:'30 days', desc:'HD Pack — 290+ channels, HD, 30 days',                             tags:['hd','popular'] },
        ]
    },
    VIDEOCOND2H: {
        plans: [
            { amt:153, channels:'200+', validity:'30 days', desc:'D2H Basic — 200+ channels, SD',                                    tags:['sd'] },
            { amt:249, channels:'280+', validity:'30 days', desc:'D2H Smart — 280+ channels, HD',                                    tags:['hd','popular'] },
        ]
    },
    AIRTELDIGITAL: {
        plans: [
            { amt:153, channels:'210+', validity:'30 days', desc:'Basic HD — 210+ channels, SD',                                     tags:['sd'] },
            { amt:249, channels:'300+', validity:'30 days', desc:'HD Sports — 300+ channels, HD + Sports',                           tags:['hd','sports','popular'] },
            { amt:399, channels:'380+', validity:'30 days', desc:'HD Supreme — 380+ channels, Full HD',                              tags:['hd','family','popular'] },
        ]
    },
};

// ═══════════════════════════════════════════════════════════════════
//  BBPS BILLERS
// ═══════════════════════════════════════════════════════════════════
const BILLERS = {
    electricity: {
        label:'Consumer Number', title:'⚡ Electricity Bill',
        list:['MSEB / MAHADISCOM (Maharashtra)','UPPCL (Uttar Pradesh)','BESCOM (Karnataka)','TNEB (Tamil Nadu)','CESC (West Bengal)','BSES Rajdhani (Delhi)','BSES Yamuna (Delhi)','Tata Power (Mumbai)','Adani Electricity (Mumbai)','PSPCL (Punjab)','JVVNL (Rajasthan)','DHBVN (Haryana)','WBSEDCL (West Bengal)','APEPDCL (Andhra Pradesh)','TSSPDCL (Telangana)','MSEDCL (Maharashtra)','KESCO (Kanpur)','DGVCL (Gujarat)','PGVCL (Gujarat)','MGVCL (Gujarat)']
    },
    gas: {
        label:'BP / Customer ID', title:'🔥 Gas / PNG Bill',
        list:['Mahanagar Gas (Mumbai)','IGL (Delhi)','Adani Gas','Gujarat Gas','Sabarmati Gas','GAIL Gas','MGL CNG','Torrent Gas','HPCL LPG (Rasoi)','BPCL LPG (Bharatgas)','Indian Oil LPG (Indane)']
    },
    water: {
        label:'Consumer Number', title:'💧 Water Bill',
        list:['BWSSB (Bangalore)','Delhi Jal Board','Chennai Metrowater (CMWSSB)','HMWSSB (Hyderabad)','Pune Municipal Corporation','MCGM (Mumbai)','NWMC (Navi Mumbai)','KDMC (Kalyan)','AMC (Ahmedabad)','GMCBL (Gandhinagar)']
    },
    broadband: {
        label:'Account / CA Number', title:'📡 Broadband Bill',
        list:['Airtel Broadband','Jio Fiber','BSNL Broadband','ACT Fibernet','Hathway','YOU Broadband','Excitel Broadband','Tata Play Fiber','Den Networks','Tikona Infinet','Spectra','Atria Convergence']
    },
    postpaid: {
        label:'Mobile Number', title:'📱 Mobile Postpaid',
        list:['Airtel Postpaid','Jio Postpaid','Vi (Vodafone Idea) Postpaid','BSNL Postpaid','MTNL Delhi','MTNL Mumbai']
    },
    insurance: {
        label:'Policy Number', title:'🛡️ Insurance Premium',
        list:['LIC of India','HDFC Life','ICICI Prudential Life','Max Life Insurance','SBI Life Insurance','Bajaj Allianz Life','Tata AIA Life','Kotak Life Insurance','Birla Sun Life','Reliance Nippon Life','Star Health Insurance','HDFC ERGO Health','Niva Bupa Health','Care Health Insurance','New India Assurance']
    },
    emi: {
        label:'Loan Account Number', title:'🏦 Loan EMI',
        list:['Bajaj Finance','HDFC Bank Loan','ICICI Bank Loan','SBI Personal Loan','Axis Bank Loan','Kotak Mahindra Bank','IDFC First Bank','Muthoot Finance','Manappuram Finance','Tata Capital','L&T Finance','Shriram Finance','Cholamandalam Finance','Hero FinCorp','Aditya Birla Finance']
    },
    creditcard: {
        label:'Card Number (last 4 digits)', title:'💳 Credit Card Bill',
        list:['HDFC Bank Credit Card','ICICI Bank Credit Card','SBI Card','Axis Bank Credit Card','Kotak Credit Card','Citibank Credit Card','American Express','Standard Chartered','RBL Bank Credit Card','YES Bank Credit Card','IndusInd Bank Credit Card','HSBC Credit Card','Bajaj Finserv Card','OneCard']
    },
    fasttag: {
        label:'Vehicle Number', title:'🛣️ FASTag Recharge',
        list:['HDFC Bank FASTag','ICICI Bank FASTag','Axis Bank FASTag','Kotak Bank FASTag','SBI FASTag','Paytm Payments Bank FASTag','IDFC First Bank FASTag','IndusInd Bank FASTag','Punjab National Bank FASTag','Bank of Baroda FASTag','NHAI FASTag (via NETC)']
    },
    education: {
        label:'Student / Application ID', title:'🎓 Education Fee',
        list:['DU (Delhi University)','Mumbai University','VTU (Visvesvaraya Tech)','Anna University','Osmania University','Savitribai Phule Pune Univ.','IGNOU','Amity University','Manipal University','LPU (Lovely Professional)','BITS Pilani','NIT Fee Portal','IIT Fee Portal']
    },
    municipal: {
        label:'Property / Assessment ID', title:'🏛️ Municipal / Property Tax',
        list:['MCGM (Mumbai)','BBMP (Bangalore)','MCD (Delhi)','Pune Municipal Corporation','GHMC (Hyderabad)','AMC (Ahmedabad)','Surat Municipal Corporation','Jaipur Nagar Nigam','Nagpur Municipal Corporation','Lucknow Municipal Corporation','Chandigarh MC','Indore Municipal Corporation']
    },
    cable: {
        label:'Subscriber ID', title:'📺 Cable TV',
        list:['Hathway Cable','Den Networks','Fastway Transmission','SITI Cable','In-Cable (GTPL)','Nxtdigital','GTPL Hathway','Asianet Cable','Suvarna Cable','Mega Cable Network']
    },
};

// ═══════════════════════════════════════════════════════════════════
//  STATE
// ═══════════════════════════════════════════════════════════════════
let currentSvc     = 'mobile';
let mobileType     = 'prepaid';
let allMobilePlans = [];
let allDthPlans    = [];
let currentReceipt = null;
let detectTimer    = null;

// ═══════════════════════════════════════════════════════════════════
//  SERVICE TABS
// ═══════════════════════════════════════════════════════════════════
function switchSvc(svc) {
    currentSvc = svc;
    ['mobile','dth','bbps'].forEach(s => {
        document.getElementById('tab-btn-' + s).classList.toggle('active', s === svc);
        document.getElementById('pane-' + s).style.display = s === svc ? 'block' : 'none';
    });
}

// ═══════════════════════════════════════════════════════════════════
//  MOBILE
// ═══════════════════════════════════════════════════════════════════
function setMobileType(t) {
    mobileType = t;
    document.getElementById('mbtn-prepaid').classList.toggle('active',  t === 'prepaid');
    document.getElementById('mbtn-postpaid').classList.toggle('active', t === 'postpaid');
    loadMobilePlans();
}

function onMobileInput() {
    const num = document.getElementById('m-number').value.replace(/\D/g, '');
    clearTimeout(detectTimer);
    // Reset badges when user edits
    _detectClear();
    if (num.length === 10) {
        document.getElementById('m-detect-text').textContent = 'Detecting…';
        detectTimer = setTimeout(() => detectOperator(num), 600);
    }
}

function _detectClear() {
    document.getElementById('m-detect-text').textContent = '';
    document.getElementById('m-circle-badge').style.display = 'none';
}

async function detectOperator(num) {
    const textEl   = document.getElementById('m-detect-text');
    const badgeEl  = document.getElementById('m-circle-badge');
    textEl.style.color = 'var(--muted)';
    textEl.textContent = 'Detecting operator & circle…';
    try {
        const res = await apiFetch('/api/v1/operators/detect?mobile=' + encodeURIComponent(num));
        if (res?.ok) {
            const d = await res.json();
            if (d.operator_code) {
                document.getElementById('m-operator').value = d.operator_code;

                textEl.textContent = '✓ ' + (d.operator_name || d.operator_code);
                textEl.style.color = '#34d399';

                if (d.circle) {
                    badgeEl.textContent  = '📍 ' + d.circle;
                    badgeEl.style.display = 'inline-flex';
                } else {
                    badgeEl.style.display = 'none';
                }

                loadMobilePlans();
                return;
            }
        }
    } catch {}
    textEl.textContent = 'Could not detect — please select manually';
    textEl.style.color = 'var(--muted)';
    badgeEl.style.display = 'none';
}

function loadMobilePlans() {
    const op = document.getElementById('m-operator').value;
    const wrap = document.getElementById('mobile-plans-wrap');
    if (!op || !PLANS[op]) { wrap.style.display = 'none'; return; }
    const opPlans = PLANS[op][mobileType] || [];
    allMobilePlans = opPlans;
    wrap.style.display = opPlans.length ? 'block' : 'none';
    renderMobilePlans(opPlans);
}

function renderMobilePlans(plans) {
    document.getElementById('plan-grid').innerHTML = plans.map(p => `
        <div class="plan-card" onclick="selectMobilePlan(${p.amt})">
            ${p.tags?.includes('popular') ? '<span class="tag">⭐ Popular</span>' : ''}
            <div style="font-size:20px;font-weight:800;color:var(--text);margin-bottom:4px">₹${p.amt}</div>
            <div style="font-size:11px;font-weight:600;color:var(--primary);margin-bottom:6px">${p.data || ''} · ${p.validity}</div>
            <div style="font-size:11px;color:var(--muted);line-height:1.4">${p.desc}</div>
        </div>`).join('') || '<div style="color:var(--muted);font-size:13px;padding:12px">No plans found for this filter.</div>';
}

function filterPlans(tag, btn) {
    document.querySelectorAll('.plan-filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const filtered = tag === 'all' ? allMobilePlans : allMobilePlans.filter(p => p.tags?.includes(tag));
    renderMobilePlans(filtered);
}

function selectMobilePlan(amt) {
    document.getElementById('m-amount').value = amt;
    document.querySelectorAll('#plan-grid .plan-card').forEach(c => {
        c.classList.toggle('selected', parseInt(c.querySelector('div').textContent.replace('₹','')) === amt);
    });
}

// ═══════════════════════════════════════════════════════════════════
//  DTH
// ═══════════════════════════════════════════════════════════════════
function loadDthPlans() {
    const op = document.getElementById('d-operator').value;
    const wrap = document.getElementById('dth-plans-wrap');
    if (!op || !PLANS[op]) { wrap.style.display = 'none'; return; }
    const opPlans = PLANS[op].plans || [];
    allDthPlans = opPlans;
    wrap.style.display = opPlans.length ? 'block' : 'none';
    renderDthPlans(opPlans);
}

function renderDthPlans(plans) {
    document.getElementById('dth-plan-grid').innerHTML = plans.map(p => `
        <div class="plan-card" onclick="selectDthPlan(${p.amt})">
            ${p.tags?.includes('popular') ? '<span class="tag">⭐ Popular</span>' : ''}
            <div style="font-size:20px;font-weight:800;color:var(--text);margin-bottom:4px">₹${p.amt}</div>
            <div style="font-size:11px;font-weight:600;color:var(--primary);margin-bottom:6px">${p.channels} channels · ${p.validity}</div>
            <div style="font-size:11px;color:var(--muted);line-height:1.4">${p.desc}</div>
        </div>`).join('');
}

function filterDthPlans(tag, btn) {
    document.querySelectorAll('#dth-plans-wrap .plan-filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const filtered = tag === 'all' ? allDthPlans : allDthPlans.filter(p => p.tags?.includes(tag));
    renderDthPlans(filtered);
}

function selectDthPlan(amt) {
    document.getElementById('d-amount').value = amt;
    document.querySelectorAll('#dth-plan-grid .plan-card').forEach(c => {
        c.classList.toggle('selected', parseInt(c.querySelector('div').textContent.replace('₹','')) === amt);
    });
}

// ═══════════════════════════════════════════════════════════════════
//  BBPS
// ═══════════════════════════════════════════════════════════════════
function selectBbpsCategory(cat) {
    const cfg = BILLERS[cat];
    if (!cfg) return;
    document.getElementById('bbps-step1').style.display = 'none';
    document.getElementById('bbps-step2').style.display = 'block';
    document.getElementById('bbps-category-title').textContent = cfg.title;
    document.getElementById('b-consumer-label').textContent = cfg.label + ' *';
    document.getElementById('b-biller').dataset.cat = cat;
    const sel = document.getElementById('b-biller');
    sel.innerHTML = '<option value="">Choose biller…</option>' +
        cfg.list.map(b => `<option value="${b}">${b}</option>`).join('');
}

function resetBbps() {
    document.getElementById('bbps-step1').style.display = 'block';
    document.getElementById('bbps-step2').style.display = 'none';
    document.getElementById('bbps-msg').style.display = 'none';
}

// ═══════════════════════════════════════════════════════════════════
//  RECHARGE / PAY — unified
// ═══════════════════════════════════════════════════════════════════
async function doRecharge(svc) {
    let body = {}, msgEl, btnEl;

    if (svc === 'mobile') {
        const mobile   = document.getElementById('m-number').value.trim();
        const operator = document.getElementById('m-operator').value;
        const amount   = parseFloat(document.getElementById('m-amount').value);
        msgEl = document.getElementById('mobile-msg');
        btnEl = document.getElementById('mobile-pay-btn');
        if (!mobile || mobile.length !== 10) { showSvcMsg(msgEl,'Enter a valid 10-digit mobile number.','error'); return; }
        if (!operator)                        { showSvcMsg(msgEl,'Please select an operator.','error'); return; }
        if (!amount || amount < 1)            { showSvcMsg(msgEl,'Enter a valid amount.','error'); return; }
        body = { mobile, operator_code: operator, recharge_type: mobileType, amount };

    } else if (svc === 'dth') {
        const number   = document.getElementById('d-number').value.trim();
        const operator = document.getElementById('d-operator').value;
        const amount   = parseFloat(document.getElementById('d-amount').value);
        msgEl = document.getElementById('dth-msg');
        btnEl = document.getElementById('dth-pay-btn');
        if (!number)           { showSvcMsg(msgEl,'Enter subscriber / customer ID.','error'); return; }
        if (!operator)         { showSvcMsg(msgEl,'Please select a DTH operator.','error'); return; }
        if (!amount || amount < 1) { showSvcMsg(msgEl,'Enter a valid amount.','error'); return; }
        body = { mobile: number, operator_code: operator, recharge_type: 'dth', amount };

    } else if (svc === 'bbps') {
        const biller   = document.getElementById('b-biller').value;
        const consumer = document.getElementById('b-consumer').value.trim();
        const amount   = parseFloat(document.getElementById('b-amount').value);
        const mobile   = document.getElementById('b-mobile').value.trim();
        const cat      = document.getElementById('b-biller').dataset.cat;
        msgEl = document.getElementById('bbps-msg');
        btnEl = document.getElementById('bbps-pay-btn');
        if (!biller)           { showSvcMsg(msgEl,'Please select a biller.','error'); return; }
        if (!consumer)         { showSvcMsg(msgEl,'Please enter consumer / account number.','error'); return; }
        if (!amount || amount < 1) { showSvcMsg(msgEl,'Enter a valid amount.','error'); return; }
        body = { mobile: consumer, operator_code: biller, recharge_type: 'bbps_' + cat, amount };
    }

    btnEl.disabled = true;
    const origText = btnEl.innerHTML;
    btnEl.innerHTML = '<div class="spinner" style="display:inline-block;width:14px;height:14px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite;vertical-align:middle"></div> Processing…';

    body.idempotency_key = 'rh_' + Date.now() + '_' + Math.random().toString(36).slice(2, 10);
    const res = await apiFetch('/api/v1/recharge', { method: 'POST', body: JSON.stringify(body) });
    btnEl.disabled = false;
    btnEl.innerHTML = origText;

    if (!res) return;
    const data = await res.json();
    if (res.ok) {
        showSvcMsg(msgEl, '✓ Recharge submitted! Opening receipt…', 'success');
        loadTxns();
        // Build receipt object from API response
        const txn = data.transaction || data.data || data;
        const receiptTxn = {
            id:            txn.id || data.id || '—',
            mobile:        txn.mobile || body.mobile || '—',
            operator_code: txn.operator_code || body.operator_code || '—',
            recharge_type: txn.recharge_type || body.recharge_type || svc,
            amount:        txn.amount || body.amount || 0,
            status:        txn.status || 'pending',
            operator_ref:  txn.operator_ref || txn.operator_txn_id || null,
            created_at:    txn.created_at || new Date().toISOString(),
        };
        setTimeout(() => showReceipt(receiptTxn), 300);
    } else {
        showSvcMsg(msgEl, data.message || 'Transaction failed. Please try again.', 'error');
    }
}

function showSvcMsg(el, txt, type) {
    el.textContent = txt;
    el.style.cssText = `display:block;font-size:13px;border-radius:8px;padding:10px 12px;background:${type==='success'?'rgba(16,185,129,.1)':'rgba(239,68,68,.1)'};border:1px solid ${type==='success'?'rgba(16,185,129,.25)':'rgba(239,68,68,.25)'};color:${type==='success'?'#6ee7b7':'#fca5a5'}`;
}

// ═══════════════════════════════════════════════════════════════════
//  TRANSACTION HISTORY
// ═══════════════════════════════════════════════════════════════════
async function loadTxns(page = 1) {
    document.getElementById('txn-tbody').innerHTML = '<tr><td colspan="8"><div class="loading"><div class="spinner"></div> Loading…</div></td></tr>';
    const p = new URLSearchParams({ page, per_page: 20 });
    const status = document.getElementById('f-status').value;
    const from   = document.getElementById('f-from').value;
    const to     = document.getElementById('f-to').value;
    if (status) p.set('status', status);
    if (from)   p.set('from', from);
    if (to)     p.set('to', to);

    const res = await apiFetch('/api/v1/transactions?' + p);
    if (!res) return;
    const data = await res.json();
    const txns  = data.data?.data || data.data || data.transactions || [];
    const total = data.data?.total ?? data.total ?? null;
    document.getElementById('txn-count').textContent = total != null ? fmtNum(total) + ' records' : '';

    if (!txns.length) {
        document.getElementById('txn-tbody').innerHTML = '<tr><td colspan="8" style="text-align:center;color:var(--muted);padding:30px">No transactions found</td></tr>';
        document.getElementById('pagination').innerHTML = '';
        return;
    }

    document.getElementById('txn-tbody').innerHTML = txns.map(t => {
        const sc  = t.status === 'success' ? 'success' : t.status === 'failed' ? 'failure' : 'pending';
        const dt  = t.created_at ? new Date(t.created_at).toLocaleString('en-IN',{day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:true}) : '—';
        const reason = t.failure_reason ? `<div style="font-size:10px;color:#ef4444;margin-top:2px">${t.failure_reason}</div>` : '';
        return `<tr>
            <td style="font-family:monospace;font-size:11px;color:var(--muted)">#${t.id||'—'}</td>
            <td style="font-weight:600">${t.mobile||'—'}</td>
            <td>${t.operator_code||'—'}</td>
            <td style="font-size:12px;text-transform:capitalize">${(t.recharge_type||'—').replace('bbps_','')}</td>
            <td style="font-weight:700">₹${parseFloat(t.amount||0).toFixed(2)}</td>
            <td><span class="badge ${sc}">${t.status||'—'}</span>${reason}</td>
            <td style="font-size:11px;color:var(--muted)">${dt}</td>
            <td>${t.status==='success'||t.status==='refunded'
                ? `<button onclick="showReceipt(${JSON.stringify(t).replace(/"/g,'&quot;')})" style="background:rgba(16,185,129,.15);border:none;color:#34d399;font-size:11px;font-weight:600;padding:4px 10px;border-radius:6px;cursor:pointer">Receipt</button>`
                : '<span style="font-size:11px;color:var(--muted2)">—</span>'}</td>
        </tr>`;
    }).join('');

    const meta     = data.data || data;
    const lastPage = meta.last_page || 1;
    const currPage = meta.current_page || page;
    const pag = document.getElementById('pagination');
    if (lastPage > 1) {
        let h = '';
        if (currPage > 1) h += `<button class="btn btn-outline btn-sm" onclick="loadTxns(${currPage-1})">← Prev</button>`;
        h += `<span style="font-size:12px;color:var(--muted)">Page ${currPage} of ${lastPage}</span>`;
        if (currPage < lastPage) h += `<button class="btn btn-outline btn-sm" onclick="loadTxns(${currPage+1})">Next →</button>`;
        pag.innerHTML = h;
    } else pag.innerHTML = '';
}

// ═══════════════════════════════════════════════════════════════════
//  RECEIPT
// ═══════════════════════════════════════════════════════════════════
function showReceipt(txn) {
    currentReceipt = txn;
    const user = getUserData();
    const date = txn.created_at ? new Date(txn.created_at).toLocaleString('en-IN',{day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:true}) : '—';
    const statusColor = txn.status === 'success' ? '#059669' : '#2563eb';
    document.getElementById('receipt-body').innerHTML = `
        <div style="text-align:center;margin-bottom:20px">
            <div style="display:inline-block;background:#f0fdf4;border-radius:50%;width:56px;height:56px;line-height:56px;font-size:26px;margin-bottom:8px">✅</div>
            <div style="font-size:22px;font-weight:800;color:#111">₹${parseFloat(txn.amount||0).toFixed(2)}</div>
            <div style="font-size:13px;color:${statusColor};font-weight:600;margin-top:4px;text-transform:capitalize">${txn.status}</div>
        </div>
        <div style="border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;font-size:13px">
            ${rrow('Transaction ID','#'+(txn.id||'—'))}
            ${rrow('Mobile / Account',txn.mobile||'—')}
            ${rrow('Operator / Biller',txn.operator_code||'—')}
            ${rrow('Type',(txn.recharge_type||'—').replace('bbps_','').toUpperCase())}
            ${rrow('Amount','₹'+parseFloat(txn.amount||0).toFixed(2))}
            ${txn.operator_ref ? rrow('Operator Ref',txn.operator_ref) : ''}
            ${rrow('Date & Time',date)}
            ${rrow('Account',user.name||'—')}
        </div>
        <div style="text-align:center;margin-top:16px;font-size:11px;color:#9ca3af">Thank you for using RechargeHub</div>`;
    const overlay = document.getElementById('receipt-overlay');
    overlay.style.display = 'flex';
    overlay.onclick = e => { if (e.target === overlay) closeReceipt(); };
}
function rrow(l,v){return `<div style="display:flex;justify-content:space-between;padding:10px 14px;border-bottom:1px solid #f1f5f9"><span style="color:#6b7280;font-weight:500">${l}</span><span style="color:#111;font-weight:600;text-align:right;max-width:60%">${v}</span></div>`;}
function closeReceipt(){ document.getElementById('receipt-overlay').style.display='none'; }
function printReceipt(){
    const u=getUserData(), t=currentReceipt, date=t.created_at?new Date(t.created_at).toLocaleString('en-IN',{day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:true}):'—';
    const w=window.open('','_blank','width=480,height=700');
    w.document.write(`<!DOCTYPE html><html><head><title>Receipt #${t.id}</title><style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:Arial,sans-serif;padding:30px;color:#111}.brand{font-size:22px;font-weight:800;color:#2563eb;text-align:center;margin-bottom:20px}.amount{font-size:36px;font-weight:900;text-align:center;margin:12px 0}.status{color:#059669;font-weight:600;font-size:14px;text-align:center}table{width:100%;border-collapse:collapse;margin-top:20px;font-size:13px}td{padding:10px 0;border-bottom:1px solid #f1f5f9}td:last-child{text-align:right;font-weight:600}.footer{text-align:center;margin-top:24px;font-size:11px;color:#9ca3af}@media print{body{padding:0}}</style></head><body><div class="brand">RechargeHub</div><div class="amount">₹${parseFloat(t.amount||0).toFixed(2)}</div><div class="status">${(t.status||'').toUpperCase()}</div><table><tr><td style="color:#6b7280">Transaction ID</td><td>#${t.id}</td></tr><tr><td style="color:#6b7280">Mobile / Account</td><td>${t.mobile||'—'}</td></tr><tr><td style="color:#6b7280">Operator</td><td>${t.operator_code||'—'}</td></tr><tr><td style="color:#6b7280">Type</td><td>${(t.recharge_type||'—').replace('bbps_','').toUpperCase()}</td></tr><tr><td style="color:#6b7280">Amount</td><td>₹${parseFloat(t.amount||0).toFixed(2)}</td></tr>${t.operator_ref?`<tr><td style="color:#6b7280">Ref</td><td>${t.operator_ref}</td></tr>`:''}<tr><td style="color:#6b7280">Date & Time</td><td>${date}</td></tr><tr><td style="color:#6b7280">Account</td><td>${u.name||'—'}</td></tr></table><div class="footer">Thank you for using RechargeHub<br>Keep this receipt for your records</div><script>window.onload=()=>{window.print()}<\/script></body></html>`);
    w.document.close();
}

document.addEventListener('DOMContentLoaded', () => loadTxns());
</script>
@endpush
