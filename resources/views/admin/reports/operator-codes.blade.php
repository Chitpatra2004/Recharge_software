@extends('layouts.admin')
@section('title','Operator Code List')

@push('head')
<style>
.oc-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:20px}
@media(max-width:900px){.oc-grid{grid-template-columns:1fr 1fr}}
@media(max-width:600px){.oc-grid{grid-template-columns:1fr}}
.op-card{background:var(--card-bg);border-radius:var(--radius);padding:16px;box-shadow:var(--shadow-sm);border-top:3px solid var(--border)}
.op-card.airtel{border-top-color:#e4131b}
.op-card.jio{border-top-color:#0066cc}
.op-card.vi{border-top-color:#e62366}
.op-card.bsnl{border-top-color:#1a5276}
.op-card.idea{border-top-color:#813c8d}
.op-card.other{border-top-color:#6b7280}
.op-badge{display:inline-flex;align-items:center;gap:6px;background:#f1f5f9;border-radius:6px;padding:5px 10px;font-size:12px;font-weight:600;margin:3px}
.circle-tag{background:#e2e8f0;color:#475569;padding:2px 7px;border-radius:4px;font-size:11px;font-family:monospace}
.search-bar{display:flex;gap:10px;margin-bottom:18px}
.search-bar input{flex:1;padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:13px}
.search-bar select{padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:13px;background:#fff}
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Operator Code List</h1>
        <p class="page-sub">Complete operator codes, circle codes, and API parameter mappings</p>
    </div>
    <button class="btn btn-outline" onclick="exportCodes()">Export CSV</button>
</div>

<div class="search-bar">
    <input type="text" id="searchOp" placeholder="Search operator or code..." oninput="filterOps()">
    <select id="filterType" onchange="filterOps()">
        <option value="">All Types</option>
        <option value="mobile">Mobile</option>
        <option value="dth">DTH</option>
        <option value="broadband">Broadband</option>
    </select>
</div>

{{-- Operator Cards --}}
<div class="oc-grid" id="opGrid">

    @php
    $operators = [
        ['name'=>'Airtel Mobile','code'=>'airtel','class'=>'airtel','type'=>'mobile','api_code'=>'AT','prefix_codes'=>['98','97','96','95','94','93','76','75','70'],'circles'=>['DL'=>'Delhi','MH'=>'Maharashtra','KA'=>'Karnataka','TN'=>'Tamil Nadu','AP'=>'Andhra Pradesh','UP'=>'UP East','UPW'=>'UP West','RJ'=>'Rajasthan','GJ'=>'Gujarat','WB'=>'West Bengal']],
        ['name'=>'Jio Mobile','code'=>'jio','class'=>'jio','type'=>'mobile','api_code'=>'JIO','prefix_codes'=>['89','88','87','86','85','70','62'],'circles'=>['DL'=>'Delhi','MH'=>'Maharashtra','KA'=>'Karnataka','TN'=>'Tamil Nadu','AP'=>'Andhra Pradesh','UP'=>'UP East','UPW'=>'UP West','RJ'=>'Rajasthan','GJ'=>'Gujarat','WB'=>'West Bengal']],
        ['name'=>'Vi (Vodafone Idea)','code'=>'vi','class'=>'vi','type'=>'mobile','api_code'=>'VI','prefix_codes'=>['99','98','97','96','95','72','91'],'circles'=>['DL'=>'Delhi','MH'=>'Maharashtra','KA'=>'Karnataka','TN'=>'Tamil Nadu','AP'=>'Andhra Pradesh','UP'=>'UP East','UPW'=>'UP West','RJ'=>'Rajasthan','GJ'=>'Gujarat']],
        ['name'=>'BSNL Mobile','code'=>'bsnl','class'=>'bsnl','type'=>'mobile','api_code'=>'BSN','prefix_codes'=>['94','93','70'],'circles'=>['DL'=>'Delhi','MH'=>'Maharashtra','KA'=>'Karnataka','TN'=>'Tamil Nadu','AP'=>'Andhra Pradesh','RJ'=>'Rajasthan']],
        ['name'=>'BSNL Special','code'=>'bsnlspecial','class'=>'bsnl','type'=>'mobile','api_code'=>'BSNS','prefix_codes'=>['94443','94444'],'circles'=>['TN'=>'Tamil Nadu','KA'=>'Karnataka']],
        ['name'=>'Airtel DTH','code'=>'airtel_dth','class'=>'airtel','type'=>'dth','api_code'=>'ADT','prefix_codes'=>[],'circles'=>[]],
        ['name'=>'Tata Play DTH','code'=>'tataplay','class'=>'other','type'=>'dth','api_code'=>'TPS','prefix_codes'=>[],'circles'=>[]],
        ['name'=>'Dish TV','code'=>'dishtv','class'=>'other','type'=>'dth','api_code'=>'DSH','prefix_codes'=>[],'circles'=>[]],
        ['name'=>'Sun Direct','code'=>'sundirect','class'=>'other','type'=>'dth','api_code'=>'SUN','prefix_codes'=>[],'circles'=>[]],
    ];
    @endphp

    @foreach($operators as $op)
    <div class="op-card {{ $op['class'] }}" data-type="{{ $op['type'] }}" data-name="{{ strtolower($op['name']) }}" data-code="{{ strtolower($op['api_code']) }}">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
            <div>
                <div style="font-size:14px;font-weight:700;color:var(--text-primary)">{{ $op['name'] }}</div>
                <div style="font-size:11.5px;color:var(--text-secondary)">API Code: <code style="background:#f1f5f9;padding:1px 6px;border-radius:4px;font-weight:700">{{ $op['api_code'] }}</code></div>
            </div>
            <span class="txn-status {{ $op['type']==='mobile'?'status-success':($op['type']==='dth'?'status-pending':'status-info') }}" style="font-size:10.5px">{{ strtoupper($op['type']) }}</span>
        </div>

        @if(count($op['prefix_codes']))
        <div style="margin-bottom:10px">
            <div style="font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;margin-bottom:5px">Number Prefixes</div>
            <div>
                @foreach($op['prefix_codes'] as $pfx)
                <span class="op-badge">{{ $pfx }}XXXXXXX</span>
                @endforeach
            </div>
        </div>
        @endif

        @if(count($op['circles']))
        <div>
            <div style="font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;margin-bottom:5px">Circle Codes</div>
            <div>
                @foreach($op['circles'] as $code=>$name)
                <span class="circle-tag" title="{{ $name }}">{{ $code }}</span>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endforeach
</div>

{{-- Full API Mapping Table --}}
<div class="card">
    <div class="card-header">
        <span style="font-weight:600;font-size:14px">Complete API Operator Mapping</span>
        <span style="margin-left:auto;font-size:12px;color:var(--text-secondary)">Use these codes in your API requests</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Operator Name</th>
                    <th>API Code</th>
                    <th>Category</th>
                    <th>Circle Required</th>
                    <th>Sample Prefixes</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($operators as $op)
                <tr>
                    <td style="font-weight:600">{{ $op['name'] }}</td>
                    <td><code style="background:#f1f5f9;padding:2px 8px;border-radius:5px;font-size:12px;font-weight:700">{{ $op['api_code'] }}</code></td>
                    <td><span class="txn-status {{ $op['type']==='mobile'?'status-success':($op['type']==='dth'?'status-pending':'status-info') }}" style="font-size:11px">{{ ucfirst($op['type']) }}</span></td>
                    <td>{{ count($op['circles'])>0?'Yes':'No' }}</td>
                    <td style="font-size:12px;color:var(--text-secondary)">{{ count($op['prefix_codes'])?implode(', ',array_slice($op['prefix_codes'],0,3)).'...':'N/A' }}</td>
                    <td><span class="txn-status status-success">Active</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
function filterOps(){
    const q=document.getElementById('searchOp').value.toLowerCase();
    const t=document.getElementById('filterType').value;
    document.querySelectorAll('#opGrid .op-card').forEach(card=>{
        const name=card.dataset.name||'';
        const code=card.dataset.code||'';
        const type=card.dataset.type||'';
        const matchQ=!q||name.includes(q)||code.includes(q);
        const matchT=!t||type===t;
        card.style.display=(matchQ&&matchT)?'':'none';
    });
}

function exportCodes(){
    const rows=['Operator,API Code,Type'];
    document.querySelectorAll('#opGrid .op-card').forEach(card=>{
        const name=card.querySelector('[style*="font-weight:700"]').textContent.trim();
        const code=card.querySelector('code').textContent.trim();
        const type=card.dataset.type;
        rows.push(`"${name}",${code},${type}`);
    });
    const a=document.createElement('a');
    a.href='data:text/csv;charset=utf-8,'+encodeURIComponent(rows.join('\n'));
    a.download='operator_codes.csv';a.click();
}
</script>
@endpush
@endsection
