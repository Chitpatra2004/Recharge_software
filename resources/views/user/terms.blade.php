@extends('layouts.user')
@section('title','Terms & Conditions — RechargeHub')
@section('page-title','Terms & Conditions')

@push('head')
<style>
.toc{display:flex;flex-direction:column;gap:4px;font-size:13px}
.toc a{color:var(--muted);text-decoration:none;padding:5px 8px;border-radius:6px;transition:all .15s;display:flex;align-items:center;gap:6px}
.toc a:hover{background:var(--card2);color:var(--text)}
.toc a::before{content:'';width:5px;height:5px;border-radius:50%;background:var(--blue);flex-shrink:0;opacity:.5}
.section-card{margin-bottom:20px}
.section-card h2{font-size:15px;font-weight:700;color:var(--text);margin-bottom:10px;display:flex;align-items:center;gap:8px;padding-top:4px}
.section-card h2 svg{width:18px;height:18px;color:var(--blue);flex-shrink:0}
.section-card p,.section-card li{font-size:13.5px;color:var(--muted);line-height:1.75;margin-bottom:6px}
.section-card ul{list-style:none;padding:0;margin:0}
.section-card ul li{padding:4px 0 4px 18px;position:relative}
.section-card ul li::before{content:'→';position:absolute;left:0;color:var(--blue);font-size:11px;top:5px}
.section-card strong{color:var(--text)}
.divider{border:none;border-top:1px solid var(--border);margin:20px 0}
.last-updated{display:inline-flex;align-items:center;gap:6px;font-size:11px;color:var(--muted2);background:var(--card);border:1px solid var(--border);border-radius:6px;padding:4px 10px;margin-bottom:20px}
</style>
@endpush

@section('content')
<div class="breadcrumb">
    <a href="/user/dashboard">Dashboard</a>
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <span>Terms & Conditions</span>
</div>

<div style="display:grid;grid-template-columns:220px 1fr;gap:20px;align-items:start">

    {{-- Sticky TOC --}}
    <div class="card" style="position:sticky;top:72px">
        <div class="card-header">
            <div class="card-title" style="font-size:12px">TABLE OF CONTENTS</div>
        </div>
        <nav class="toc">
            <a href="#acceptance">Acceptance</a>
            <a href="#account">Account & Security</a>
            <a href="#wallet">Wallet & Payments</a>
            <a href="#recharges">Recharge Services</a>
            <a href="#bbps">Bill Payments (BBPS)</a>
            <a href="#refunds">Refund Policy</a>
            <a href="#prohibited">Prohibited Uses</a>
            <a href="#liability">Limitation of Liability</a>
            <a href="#privacy">Privacy</a>
            <a href="#changes">Changes to Terms</a>
            <a href="#contact">Contact</a>
        </nav>
    </div>

    {{-- Content --}}
    <div>
        <div class="last-updated">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:13px;height:13px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            Last updated: January 1, 2026
        </div>

        <div class="card section-card" id="acceptance">
            <div class="card-header" style="padding-bottom:0">
                <h2>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    1. Acceptance of Terms
                </h2>
            </div>
            <p>By accessing or using RechargeHub's services, you agree to be bound by these Terms and Conditions. If you do not agree, please do not use our platform.</p>
            <p>These terms govern your use of our website, mobile application, and related services including recharges, bill payments, and wallet management.</p>
        </div>

        <div class="card section-card" id="account">
            <div class="card-header" style="padding-bottom:0">
                <h2>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    2. Account & Security
                </h2>
            </div>
            <ul>
                <li>You must be at least <strong>18 years of age</strong> to register an account.</li>
                <li>One person may hold <strong>only one active account</strong>. Duplicate accounts will be suspended.</li>
                <li>You are responsible for maintaining the <strong>confidentiality of your login credentials</strong>.</li>
                <li>Immediately notify us of any <strong>unauthorized access</strong> to your account.</li>
                <li>We may suspend accounts showing signs of <strong>fraud, abuse, or unusual activity</strong>.</li>
                <li>Enabling <strong>Two-Factor Authentication (2FA)</strong> is strongly recommended.</li>
            </ul>
        </div>

        <div class="card section-card" id="wallet">
            <div class="card-header" style="padding-bottom:0">
                <h2>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    3. Wallet & Payments
                </h2>
            </div>
            <ul>
                <li>Wallet funds are <strong>non-refundable</strong> except in cases covered under our Refund Policy.</li>
                <li>Minimum add-money request: <strong>₹10</strong> · Maximum: <strong>₹5,00,000</strong> per request.</li>
                <li>Payment requests are reviewed by admin and credited within <strong>24 business hours</strong>.</li>
                <li>Always transfer from your <strong>registered bank account</strong>. Third-party transfers may be rejected.</li>
                <li>Wallet balances do <strong>not earn interest</strong> and have no expiry.</li>
                <li>In case of account closure, remaining wallet balance will be refunded after verification.</li>
            </ul>
        </div>

        <div class="card section-card" id="recharges">
            <div class="card-header" style="padding-bottom:0">
                <h2>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    4. Recharge Services
                </h2>
            </div>
            <ul>
                <li>Recharges are processed in <strong>real-time</strong> via our operator API gateway.</li>
                <li>Wallet is debited <strong>immediately</strong> when a recharge is initiated.</li>
                <li>For <strong>failed recharges</strong>, the deducted amount is automatically reversed to your wallet.</li>
                <li>RechargeHub is not responsible for <strong>operator-side delays</strong> or service downtimes.</li>
                <li>Entering the wrong mobile number is the <strong>user's responsibility</strong>. Such transactions cannot be reversed.</li>
                <li>DTH and data card recharges require the correct <strong>subscriber ID or account number</strong>.</li>
            </ul>
        </div>

        <div class="card section-card" id="bbps">
            <div class="card-header" style="padding-bottom:0">
                <h2>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    5. Bill Payments (BBPS)
                </h2>
            </div>
            <ul>
                <li>Bill payments are processed through the <strong>Bharat Bill Payment System (BBPS)</strong> network.</li>
                <li>Always verify your <strong>consumer number and biller</strong> before making a payment.</li>
                <li>Payments are typically reflected in the biller's system within <strong>2–4 hours</strong>; in rare cases up to 2 business days.</li>
                <li>Late payment charges, disconnections, or penalties imposed by billers are <strong>not our responsibility</strong>.</li>
                <li>Successful BBPS payments are <strong>non-reversible</strong> once processed.</li>
            </ul>
        </div>

        <div class="card section-card" id="refunds">
            <div class="card-header" style="padding-bottom:0">
                <h2>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 2 2 2-2 2 2 2-2 4 2z"/></svg>
                    6. Refund Policy
                </h2>
            </div>
            <p>Refunds are processed as <strong>wallet credits</strong>, not back to the original payment method, unless the account is being closed.</p>
            <ul>
                <li><strong>Auto-refund:</strong> Failed recharges are automatically refunded within 24 hours.</li>
                <li><strong>Manual refund:</strong> Submit a complaint within 7 days of the failed transaction.</li>
                <li><strong>No refund</strong> for successful recharges made to wrong numbers provided by the user.</li>
                <li><strong>No refund</strong> for successful BBPS payments.</li>
                <li>Disputes must be raised within <strong>30 days</strong> of the transaction date.</li>
            </ul>
        </div>

        <div class="card section-card" id="prohibited">
            <div class="card-header" style="padding-bottom:0">
                <h2>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                    7. Prohibited Uses
                </h2>
            </div>
            <ul>
                <li>Using our platform for <strong>money laundering</strong> or fraudulent activity.</li>
                <li>Creating <strong>fake or multiple accounts</strong> to exploit promotions.</li>
                <li>Using <strong>bots or automation</strong> to make recharges or bill payments at scale.</li>
                <li><strong>Reselling services</strong> without a valid seller/API agreement.</li>
                <li>Attempting to <strong>hack, reverse-engineer</strong>, or disrupt our systems.</li>
                <li>Providing <strong>false payment proofs</strong> for wallet top-up requests.</li>
            </ul>
        </div>

        <div class="card section-card" id="liability">
            <div class="card-header" style="padding-bottom:0">
                <h2>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    8. Limitation of Liability
                </h2>
            </div>
            <p>RechargeHub shall not be liable for any indirect, incidental, or consequential damages arising from:</p>
            <ul>
                <li>Operator API downtime or delayed service delivery.</li>
                <li>User errors such as wrong mobile numbers or consumer IDs.</li>
                <li>Internet connectivity issues on the user's end.</li>
                <li>Unauthorized access due to compromised user credentials.</li>
            </ul>
            <p>Our maximum liability in any case shall not exceed the <strong>amount of the disputed transaction</strong>.</p>
        </div>

        <div class="card section-card" id="privacy">
            <div class="card-header" style="padding-bottom:0">
                <h2>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    9. Privacy
                </h2>
            </div>
            <ul>
                <li>We collect only the data necessary to provide our services.</li>
                <li>Your data is <strong>never sold</strong> to third parties.</li>
                <li>Payment proofs are stored securely and accessible only to authorized admins.</li>
                <li>You may request deletion of your account and data by contacting support.</li>
            </ul>
        </div>

        <div class="card section-card" id="changes">
            <div class="card-header" style="padding-bottom:0">
                <h2>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    10. Changes to Terms
                </h2>
            </div>
            <p>We reserve the right to update these Terms at any time. Continued use of the platform after changes are posted constitutes your acceptance of the revised Terms.</p>
        </div>

        <div class="card section-card" id="contact">
            <div class="card-header" style="padding-bottom:0">
                <h2>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    11. Contact Us
                </h2>
            </div>
            <p>For questions about these Terms, please contact:</p>
            <ul>
                <li><strong>Email:</strong> legal@rechargepay.in</li>
                <li><strong>Support Portal:</strong> <a href="/user/support" style="color:var(--blue)">Help & Support</a></li>
                <li><strong>Response Time:</strong> Within 2 business days</li>
            </ul>
        </div>

    </div>
</div>

@endsection
