@extends('layouts.user')
@section('title','Help & Support — RechargeHub')
@section('page-title','Help & Support')

@push('head')
<style>
.faq-item{border:1px solid var(--border);border-radius:10px;overflow:hidden;margin-bottom:10px}
.faq-q{width:100%;padding:14px 16px;background:var(--card);border:none;text-align:left;cursor:pointer;font-family:inherit;font-size:13.5px;font-weight:500;color:var(--text);display:flex;justify-content:space-between;align-items:center;gap:12px;transition:background .15s}
.faq-q:hover{background:var(--card2)}
.faq-q svg{width:16px;height:16px;color:var(--muted2);flex-shrink:0;transition:transform .2s}
.faq-q.open svg{transform:rotate(180deg)}
.faq-a{display:none;padding:14px 16px;font-size:13px;color:var(--muted);line-height:1.75;border-top:1px solid var(--border);background:rgba(255,255,255,.02)}
.faq-a.open{display:block}
.contact-card{display:flex;flex-direction:column;align-items:center;gap:8px;padding:22px 16px;border-radius:12px;background:var(--card);border:1px solid var(--border);text-align:center;text-decoration:none;transition:all .15s}
.contact-card:hover{border-color:var(--border2);background:var(--card2);transform:translateY(-2px)}
.contact-card svg{width:28px;height:28px;margin:0 auto}
.contact-card .c-title{font-size:14px;font-weight:600;color:var(--text)}
.contact-card .c-sub{font-size:12px;color:var(--muted)}
.contact-card .c-val{font-size:13px;font-weight:600;color:var(--blue);margin-top:4px}
.cat-tabs{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:18px}
.cat-tab{padding:6px 14px;border-radius:20px;border:1px solid var(--border2);background:var(--card);font-size:12px;font-weight:600;color:var(--muted);cursor:pointer;font-family:inherit;transition:all .15s}
.cat-tab:hover{color:var(--text);border-color:var(--border2)}
.cat-tab.active{background:rgba(59,130,246,.12);border-color:rgba(59,130,246,.4);color:#60a5fa}
.search-wrap{position:relative;margin-bottom:20px}
.search-wrap input{width:100%;background:var(--card2);border:1px solid var(--border2);border-radius:10px;padding:11px 16px 11px 42px;font-size:13.5px;color:var(--text);outline:none;font-family:inherit;transition:border-color .15s}
.search-wrap input:focus{border-color:var(--blue)}
.search-wrap svg{position:absolute;left:14px;top:50%;transform:translateY(-50%);width:16px;height:16px;color:var(--muted2)}
</style>
@endpush

@section('content')
<div class="breadcrumb">
    <a href="/user/dashboard">Dashboard</a>
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <span>Help & Support</span>
</div>

<div style="display:grid;grid-template-columns:1fr 300px;gap:20px;align-items:start">

    {{-- LEFT: FAQ --}}
    <div>
        {{-- Search --}}
        <div class="search-wrap">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" id="faq-search" placeholder="Search help articles…" oninput="filterFaqs()">
        </div>

        {{-- Category Filter --}}
        <div class="cat-tabs">
            <button class="cat-tab active" onclick="filterCat('all',this)">All Topics</button>
            <button class="cat-tab" onclick="filterCat('account',this)">Account</button>
            <button class="cat-tab" onclick="filterCat('wallet',this)">Wallet</button>
            <button class="cat-tab" onclick="filterCat('recharge',this)">Recharges</button>
            <button class="cat-tab" onclick="filterCat('bbps',this)">Bill Payments</button>
            <button class="cat-tab" onclick="filterCat('refund',this)">Refunds</button>
        </div>

        <div id="faq-container">
            @php
            $faqs = [
                ['cat'=>'account','q'=>'How do I register an account?','a'=>'Click Register on the login page. Enter your mobile number, email, and set a password. You will receive an OTP on your mobile number for verification. Once verified, your account is ready to use.'],
                ['cat'=>'account','q'=>'I forgot my password. How do I reset it?','a'=>'On the login page, click "Forgot Password". Enter your registered email address and you will receive an OTP. Enter the OTP and set your new password.'],
                ['cat'=>'account','q'=>'How do I enable Two-Factor Authentication (2FA)?','a'=>'Go to My Profile → Security settings. You can enable 2FA via OTP (SMS) or TOTP (Google Authenticator). We recommend TOTP for stronger security.'],
                ['cat'=>'account','q'=>'How do I update my profile information?','a'=>'Navigate to My Profile from the sidebar. You can update your name, email, and phone number. Changes take effect immediately.'],
                ['cat'=>'wallet','q'=>'How do I add money to my wallet?','a'=>'Go to Add Money from the sidebar. Choose a payment mode (UPI, Bank Transfer, NEFT, RTGS, or Cheque), transfer the amount to our account details shown, fill in the reference number, upload the payment proof, and submit. Admin will verify and credit within 24 hours.'],
                ['cat'=>'wallet','q'=>'What is the minimum and maximum add-money amount?','a'=>'Minimum: ₹10 per request. Maximum: ₹5,00,000 per request. For higher amounts, please contact support.'],
                ['cat'=>'wallet','q'=>'How long does wallet credit take after payment?','a'=>'Typically within 2–4 hours during business hours (10 AM – 7 PM). In exceptional cases, up to 24 business hours.'],
                ['cat'=>'wallet','q'=>'Can I withdraw money from my wallet to my bank account?','a'=>'Currently, wallet funds cannot be withdrawn directly. They are meant for platform use only (recharges and bill payments). For exceptional cases, contact support.'],
                ['cat'=>'recharge','q'=>'How do I do a mobile recharge?','a'=>'Go to Recharges from the sidebar. Enter the mobile number, select the operator, choose a plan or enter a custom amount, and click Recharge. The amount will be deducted from your wallet.'],
                ['cat'=>'recharge','q'=>'What operators are supported?','a'=>'We support all major Indian operators: Airtel, Jio, Vi (Vodafone Idea), BSNL, MTNL, and others. For DTH: Tata Play, Dish TV, Sun Direct, Airtel Digital TV, D2H, and BSNL DTH.'],
                ['cat'=>'recharge','q'=>'My recharge failed but money was deducted. What should I do?','a'=>'Failed recharges are automatically refunded to your wallet within 24 hours. If not received, go to Complaints and raise a dispute with the transaction ID.'],
                ['cat'=>'recharge','q'=>'I entered the wrong mobile number. Can I get a refund?','a'=>'Unfortunately, successful recharges to wrong numbers cannot be reversed as the amount is delivered to the number entered. Please double-check the number before confirming.'],
                ['cat'=>'bbps','q'=>'What bills can I pay through BBPS?','a'=>'You can pay Electricity, Water, Gas (PNG/piped), DTH, Broadband, Landline, Insurance premiums, Loan EMIs, and FASTag through our BBPS service.'],
                ['cat'=>'bbps','q'=>'How do I fetch my bill before paying?','a'=>'Select the category and biller, enter your consumer number (found on your previous bill), and click "Fetch Bill". The current outstanding amount will be displayed.'],
                ['cat'=>'bbps','q'=>'How long does a bill payment take to reflect?','a'=>'Most payments reflect within 2–4 hours. In rare cases (ISP holidays, biller delays), it may take up to 2 business days.'],
                ['cat'=>'refund','q'=>'What is the refund policy?','a'=>'Failed recharges are auto-refunded to your wallet within 24 hours. For manual refund disputes, raise a complaint within 30 days of the transaction. Refunds are credited to your wallet, not the original payment method.'],
                ['cat'=>'refund','q'=>'How do I raise a complaint?','a'=>'Go to Complaints from the sidebar. Click "New Complaint", enter the transaction ID and describe the issue. Our support team will review and respond within 48 hours.'],
                ['cat'=>'refund','q'=>'My complaint was resolved but I did not receive the refund?','a'=>'Once a complaint is marked resolved, wallet credit may take 2–4 hours. If not credited within 24 hours, reply to the complaint thread or contact support directly.'],
            ];
            @endphp

            @foreach($faqs as $i => $faq)
            <div class="faq-item" data-cat="{{ $faq['cat'] }}" data-q="{{ strtolower($faq['q']) }} {{ strtolower($faq['a']) }}">
                <button class="faq-q" onclick="toggleFaq({{ $i }})">
                    {{ $faq['q'] }}
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div class="faq-a" id="faq-a-{{ $i }}">{{ $faq['a'] }}</div>
            </div>
            @endforeach

            <div id="faq-empty" style="display:none;text-align:center;padding:40px;color:var(--muted)">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:36px;height:36px;margin:0 auto 10px;display:block;opacity:.4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                No results for your search. Try different keywords or <a href="#" onclick="clearSearch()" style="color:var(--blue)">clear the filter</a>.
            </div>
        </div>
    </div>

    {{-- RIGHT: Contact & Status --}}
    <div style="display:flex;flex-direction:column;gap:16px">

        {{-- Contact Cards --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title">Contact Support</div>
                <div class="card-sub">We typically reply within 24 hours</div>
            </div>
            <div style="display:flex;flex-direction:column;gap:10px">
                <a href="mailto:support@rechargepay.in" class="contact-card">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color:var(--blue)"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <div class="c-title">Email Support</div>
                    <div class="c-sub">For account & billing queries</div>
                    <div class="c-val">support@rechargepay.in</div>
                </a>
                <a href="https://wa.me/919999999999" class="contact-card" target="_blank" rel="noopener">
                    <svg viewBox="0 0 24 24" fill="currentColor" style="color:#25D366"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    <div class="c-title">WhatsApp</div>
                    <div class="c-sub">Quick response, 10 AM – 7 PM</div>
                    <div class="c-val">+91 99999 99999</div>
                </a>
                <a href="/user/complaints" class="contact-card">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color:var(--orange)"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                    <div class="c-title">Raise a Complaint</div>
                    <div class="c-sub">For transaction disputes</div>
                    <div class="c-val">Go to Complaints →</div>
                </a>
            </div>
        </div>

        {{-- Business Hours --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title">Business Hours</div>
            </div>
            <div style="font-size:13px;display:flex;flex-direction:column;gap:8px">
                <div style="display:flex;justify-content:space-between;align-items:center">
                    <span style="color:var(--muted)">Mon – Fri</span>
                    <span style="font-weight:500">10:00 AM – 7:00 PM</span>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center">
                    <span style="color:var(--muted)">Saturday</span>
                    <span style="font-weight:500">10:00 AM – 4:00 PM</span>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center">
                    <span style="color:var(--muted)">Sunday</span>
                    <span style="color:var(--red);font-weight:500">Closed</span>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;padding-top:8px;border-top:1px solid var(--border)">
                    <span style="color:var(--muted)">API Support</span>
                    <span style="font-weight:500">24×7</span>
                </div>
            </div>
        </div>

        {{-- Quick Links --}}
        <div class="card">
            <div class="card-header"><div class="card-title">Quick Links</div></div>
            <div style="display:flex;flex-direction:column;gap:4px">
                @foreach([
                    ['/user/transactions','Transaction History'],
                    ['/user/wallet','Wallet Balance'],
                    ['/user/complaints','My Complaints'],
                    ['/user/terms','Terms & Conditions'],
                ] as [$url,$label])
                <a href="{{ $url }}" style="display:flex;align-items:center;justify-content:space-between;padding:8px 6px;color:var(--muted);text-decoration:none;font-size:13px;border-radius:6px;transition:all .15s" onmouseover="this.style.background='var(--card)'" onmouseout="this.style.background='none'">
                    {{ $label }}
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:14px;height:14px;opacity:.5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                @endforeach
            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
let activeCat = 'all';

function toggleFaq(i) {
    const q = document.querySelectorAll('.faq-q')[i];
    const a = document.getElementById('faq-a-' + i);
    q.classList.toggle('open');
    a.classList.toggle('open');
}

function filterCat(cat, btn) {
    activeCat = cat;
    document.querySelectorAll('.cat-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    applyFilters();
}

function filterFaqs() { applyFilters(); }
function clearSearch() { document.getElementById('faq-search').value = ''; applyFilters(); }

function applyFilters() {
    const q = (document.getElementById('faq-search').value || '').toLowerCase();
    let visible = 0;
    document.querySelectorAll('.faq-item').forEach(item => {
        const matchCat = activeCat === 'all' || item.dataset.cat === activeCat;
        const matchQ   = !q || item.dataset.q.includes(q);
        const show = matchCat && matchQ;
        item.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    document.getElementById('faq-empty').style.display = visible === 0 ? '' : 'none';
}
</script>
@endpush
