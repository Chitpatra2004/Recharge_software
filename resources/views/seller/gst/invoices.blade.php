@extends('layouts.seller')
@section('title','GST Invoices')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">GST Invoices</h1>
        <p class="page-sub">Monthly GST invoices and tax certificates</p>
    </div>
    <div style="display:flex;gap:8px">
        <select id="fYear" onchange="loadData()" style="padding:7px 12px;border:1px solid var(--border);border-radius:8px;font-size:13px;background:#fff">
            <option value="2026">FY 2025-26</option>
            <option value="2025">FY 2024-25</option>
        </select>
        <button class="btn btn-primary" onclick="loadData()">Refresh</button>
    </div>
</div>

<div style="background:#fef3c7;border:1px solid #fcd34d;border-radius:10px;padding:14px 18px;margin-bottom:18px;display:flex;align-items:center;gap:12px;font-size:13px;color:#92400e">
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    GST invoices are auto-generated on the 5th of every month for the previous month's transactions. GSTIN: <strong>07ABCDE1234F1Z5</strong>
</div>

<div class="card">
    <div class="card-header"><span style="font-weight:600">Invoice List</span></div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Invoice No.</th>
                    <th>Period</th>
                    <th>Taxable Amount</th>
                    <th>CGST (9%)</th>
                    <th>SGST (9%)</th>
                    <th>Total GST</th>
                    <th>Invoice Total</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="gstBody"></tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
const DEMO_GST=[
    {inv:'GST/2026/03',period:'March 2026',taxable:14820,status:'pending'},
    {inv:'GST/2026/02',period:'February 2026',taxable:13450,status:'issued'},
    {inv:'GST/2026/01',period:'January 2026',taxable:12800,status:'issued'},
    {inv:'GST/2025/12',period:'December 2025',taxable:15600,status:'issued'},
    {inv:'GST/2025/11',period:'November 2025',taxable:11900,status:'issued'},
    {inv:'GST/2025/10',period:'October 2025',taxable:10500,status:'issued'},
];

function loadData(){
    document.getElementById('gstBody').innerHTML=DEMO_GST.map(r=>{
        const cgst=(r.taxable*0.09).toFixed(2);
        const sgst=(r.taxable*0.09).toFixed(2);
        const totalGst=(r.taxable*0.18).toFixed(2);
        const invTotal=(r.taxable*1.18).toFixed(2);
        const badge=r.status==='issued'
            ?'<span style="background:#d1fae5;color:#065f46;padding:3px 9px;border-radius:20px;font-size:11.5px;font-weight:600">Issued</span>'
            :'<span style="background:#fef3c7;color:#92400e;padding:3px 9px;border-radius:20px;font-size:11.5px;font-weight:600">Pending</span>';
        const actions=r.status==='issued'
            ?`<button onclick="downloadInv('${r.inv}')" style="padding:4px 10px;border-radius:6px;font-size:11.5px;cursor:pointer;border:1px solid var(--accent-blue);color:var(--accent-blue);background:#fff">Download PDF</button>`
            :'<span style="font-size:12px;color:var(--text-muted)">Not yet generated</span>';
        return `<tr>
            <td style="font-family:monospace;font-size:12px;font-weight:600">${r.inv}</td>
            <td>${r.period}</td>
            <td>₹${r.taxable.toLocaleString('en-IN')}</td>
            <td>₹${Number(cgst).toLocaleString('en-IN')}</td>
            <td>₹${Number(sgst).toLocaleString('en-IN')}</td>
            <td style="font-weight:600">₹${Number(totalGst).toLocaleString('en-IN')}</td>
            <td style="font-weight:700;color:var(--accent-blue)">₹${Number(invTotal).toLocaleString('en-IN')}</td>
            <td>${badge}</td>
            <td>${actions}</td>
        </tr>`;
    }).join('');
}

function downloadInv(inv){alert('Downloading invoice: '+inv);}
document.addEventListener('DOMContentLoaded',loadData);
</script>
@endpush
@endsection
