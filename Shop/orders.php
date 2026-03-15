<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Orders — Ball Sports PH</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:ital,wght@0,400;0,600;0,700;0,800;0,900&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <style>
    body { background: #f4f6fb; }
    .ord-page { max-width: 960px; margin: 0 auto; padding: 40px 24px 80px; }
    .ord-page-title { font-family: 'Barlow Condensed', sans-serif; font-size: 36px; font-weight: 900; color: #1a1a2e; margin-bottom: 6px; }
    .ord-page-sub { font-size: 14px; color: #7a8fa6; margin-bottom: 32px; }

    /* Lookup form */
    .ord-lookup { background: #fff; border: 1.5px solid #e0e8f0; border-radius: 14px; padding: 24px; margin-bottom: 28px; display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end; }
    .ord-lookup-field { display: flex; flex-direction: column; gap: 5px; flex: 1; min-width: 200px; }
    .ord-lookup-field label { font-family: 'Barlow Condensed', sans-serif; font-size: 11px; font-weight: 800; letter-spacing: 1.5px; color: #9aa5bf; }
    .ord-lookup-inp { background: #f8f9fb; border: 1.5px solid #e0e8f0; color: #1a1a2e; font-family: 'Barlow', sans-serif; font-size: 14px; padding: 11px 14px; border-radius: 8px; outline: none; transition: border-color .2s; }
    .ord-lookup-inp:focus { border-color: #F26522; box-shadow: 0 0 0 3px rgba(242,101,34,.1); }
    .btn-lookup { background: #F26522; border: none; color: #fff; font-family: 'Barlow Condensed', sans-serif; font-size: 13px; font-weight: 800; letter-spacing: 1.5px; padding: 13px 24px; border-radius: 8px; cursor: pointer; transition: all .2s; white-space: nowrap; }
    .btn-lookup:hover { background: #D4551A; transform: translateY(-1px); }

    /* Filter */
    .ord-toolbar { display: flex; gap: 12px; margin-bottom: 20px; flex-wrap: wrap; align-items: center; }
    .ord-search { flex: 1; min-width: 200px; background: #fff; border: 1.5px solid #e0e8f0; color: #1a1a2e; font-family: 'Barlow', sans-serif; font-size: 14px; padding: 11px 16px; border-radius: 10px; outline: none; transition: border-color .2s; }
    .ord-search:focus { border-color: #F26522; }
    .ord-filter-sel { background: #fff; border: 1.5px solid #e0e8f0; color: #1a1a2e; font-family: 'Barlow Condensed', sans-serif; font-size: 13px; font-weight: 700; padding: 11px 14px; border-radius: 10px; outline: none; cursor: pointer; }
    .ord-filter-sel:focus { border-color: #F26522; }

    /* Empty */
    .ord-empty { text-align: center; padding: 80px 24px; background: #fff; border-radius: 16px; border: 1.5px solid #e0e8f0; }
    .ord-empty-icon { width: 80px; height: 80px; background: #f4f6fb; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; }
    .ord-empty h3 { font-family: 'Barlow Condensed', sans-serif; font-size: 24px; font-weight: 800; color: #1a1a2e; margin-bottom: 8px; }
    .ord-empty p { font-size: 14px; color: #7a8fa6; margin-bottom: 24px; }

    /* Order card */
    .ord-card { background: #fff; border: 1.5px solid #e0e8f0; border-radius: 16px; margin-bottom: 20px; overflow: hidden; box-shadow: 0 1px 6px rgba(0,0,0,.06); transition: box-shadow .2s; }
    .ord-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,.1); }
    .ord-card-hd { display: flex; align-items: center; justify-content: space-between; padding: 18px 24px; background: #f8f9fb; border-bottom: 1px solid #f0f4f8; flex-wrap: wrap; gap: 12px; cursor: pointer; user-select: none; }
    .ord-card-hd:hover { background: #f0f4f8; }
    .ord-ref { font-family: 'Barlow Condensed', sans-serif; font-size: 18px; font-weight: 900; letter-spacing: 1px; color: #F26522; }
    .ord-date { font-size: 12px; color: #9aa5bf; margin-top: 2px; }
    .ord-hd-right { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }
    .ord-total { font-family: 'Barlow Condensed', sans-serif; font-size: 20px; font-weight: 900; color: #1a1a2e; }
    .ord-chevron { color: #9aa5bf; transition: transform .3s; }
    .ord-card.open .ord-chevron { transform: rotate(180deg); }

    /* Status badge */
    .ord-status { display: inline-flex; align-items: center; gap: 6px; font-family: 'Barlow Condensed', sans-serif; font-size: 12px; font-weight: 800; letter-spacing: 1px; padding: 5px 12px; border-radius: 20px; }
    .s-placed    { background: #e8f0fe; color: #1a73e8; }
    .s-confirmed { background: #fef3e8; color: #F26522; }
    .s-packing   { background: #fff3e0; color: #e65100; }
    .s-shipped   { background: #e8f5e9; color: #2e7d32; }
    .s-delivered { background: #e8f5e9; color: #1b5e20; }
    .s-cancelled { background: #ffebee; color: #c62828; }

    /* Card body */
    .ord-card-body { display: none; }
    .ord-card.open .ord-card-body { display: block; }

    /* Items */
    .ord-items-section { padding: 20px 24px; border-bottom: 1px solid #f0f4f8; }
    .ord-items-title { font-family: 'Barlow Condensed', sans-serif; font-size: 12px; font-weight: 800; letter-spacing: 2px; color: #9aa5bf; margin-bottom: 14px; }
    .ord-item-row { display: flex; align-items: center; gap: 14px; padding: 10px 0; border-bottom: 1px solid #f7f8fb; }
    .ord-item-row:last-child { border-bottom: none; }
    .ord-item-icon { width: 50px; height: 50px; background: #f4f6fb; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid #e0e8f0; }
    .ord-item-icon svg { width: 28px; height: 28px; }
    .ord-item-name { font-family: 'Barlow Condensed', sans-serif; font-size: 16px; font-weight: 800; color: #1a1a2e; }
    .ord-item-meta { font-size: 12px; color: #9aa5bf; }
    .ord-item-price { font-family: 'Barlow Condensed', sans-serif; font-size: 17px; font-weight: 800; color: #F26522; margin-left: auto; }

    /* Info grid */
    .ord-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0; }
    .ord-info-col { padding: 20px 24px; }
    .ord-info-col:first-child { border-right: 1px solid #f0f4f8; }
    .ord-info-label { font-family: 'Barlow Condensed', sans-serif; font-size: 10px; font-weight: 800; letter-spacing: 2px; color: #9aa5bf; margin-bottom: 12px; }
    .ord-info-row { display: flex; justify-content: space-between; font-size: 13px; color: #7a8fa6; margin-bottom: 7px; }
    .ord-info-row span:last-child { color: #1a1a2e; font-weight: 600; }
    .ord-info-row.total-row { font-family: 'Barlow Condensed', sans-serif; font-size: 17px; font-weight: 800; color: #1a1a2e; border-top: 1.5px solid #f0f4f8; padding-top: 10px; margin-top: 4px; }
    .ord-info-row.total-row span:last-child { color: #F26522; }
    .addr-block { font-size: 13px; color: #4a5568; line-height: 1.8; }
    .addr-name { font-family: 'Barlow Condensed', sans-serif; font-size: 16px; font-weight: 800; color: #1a1a2e; margin-bottom: 4px; }

    /* Cancel button */
    .btn-cancel-order { background: #fff; border: 2px solid #E74C3C; color: #E74C3C; font-family: 'Barlow Condensed', sans-serif; font-size: 12px; font-weight: 800; letter-spacing: 1px; padding: 8px 18px; border-radius: 8px; cursor: pointer; transition: all .2s; margin-top: 16px; }
    .btn-cancel-order:hover { background: #E74C3C; color: #fff; }

    /* Tracking timeline */
    .ord-track-section { padding: 24px; background: #f8f9fb; border-top: 1px solid #f0f4f8; }
    .ord-track-title { font-family: 'Barlow Condensed', sans-serif; font-size: 13px; font-weight: 800; letter-spacing: 2px; color: #9aa5bf; margin-bottom: 20px; }
    .track-steps { display: flex; align-items: flex-start; overflow-x: auto; padding-bottom: 8px; }
    .track-step { display: flex; flex-direction: column; align-items: center; flex: 1; min-width: 90px; position: relative; }
    .track-step::before { content: ''; position: absolute; top: 18px; right: -50%; width: 100%; height: 2px; background: #e0e8f0; z-index: 0; }
    .track-step:last-child::before { display: none; }
    .track-step.done::before { background: #27AE60; }
    .track-step.active::before { background: linear-gradient(90deg, #27AE60, #e0e8f0); }
    .track-dot { width: 36px; height: 36px; border-radius: 50%; border: 2.5px solid #e0e8f0; background: #fff; display: flex; align-items: center; justify-content: center; z-index: 1; position: relative; flex-shrink: 0; }
    .track-step.done .track-dot { background: #27AE60; border-color: #27AE60; }
    .track-step.active .track-dot { background: #F26522; border-color: #F26522; box-shadow: 0 0 0 4px rgba(242,101,34,.2); }
    .track-step.cancelled .track-dot { background: #E74C3C; border-color: #E74C3C; }
    .track-step-label { font-family: 'Barlow Condensed', sans-serif; font-size: 11px; font-weight: 700; color: #9aa5bf; margin-top: 8px; text-align: center; line-height: 1.3; }
    .track-step.done .track-step-label, .track-step.active .track-step-label { color: #1a1a2e; }
    .track-step-date { font-size: 10px; color: #b0bac9; margin-top: 3px; text-align: center; }

    /* History log */
    .track-history { margin-top: 20px; }
    .th-title { font-family: 'Barlow Condensed', sans-serif; font-size: 11px; font-weight: 800; letter-spacing: 1.5px; color: #9aa5bf; margin-bottom: 12px; }
    .th-row { display: flex; align-items: flex-start; gap: 12px; padding: 10px 0; border-bottom: 1px solid #f0f4f8; }
    .th-row:last-child { border-bottom: none; }
    .th-dot { width: 10px; height: 10px; border-radius: 50%; background: #F26522; margin-top: 4px; flex-shrink: 0; }
    .th-dot.done { background: #27AE60; }
    .th-status { font-family: 'Barlow Condensed', sans-serif; font-size: 14px; font-weight: 800; color: #1a1a2e; }
    .th-note { font-size: 12px; color: #7a8fa6; margin-top: 2px; }
    .th-date { font-size: 11px; color: #b0bac9; margin-left: auto; white-space: nowrap; }

    /* Loading */
    .ord-loading { text-align: center; padding: 60px 0; color: #9aa5bf; }

    @media(max-width:640px){ .ord-info-grid { grid-template-columns: 1fr; } .ord-info-col:first-child { border-right: none; border-bottom: 1px solid #f0f4f8; } .ord-card-hd { flex-direction: column; align-items: flex-start; } }
  </style>
</head>
<body>

<!-- HEADER -->
<header class="site-header">
  <div class="nav-inner">
    <a href="index.php" class="nav-logo">
      <svg width="30" height="30" viewBox="0 0 30 30" fill="none">
        <circle cx="15" cy="15" r="13" stroke="#F26522" stroke-width="2"/>
        <path d="M15 2 Q22 8 22 15 Q22 22 15 28" stroke="#F26522" stroke-width="1.8" fill="none"/>
        <path d="M15 2 Q8 8 8 15 Q8 22 15 28" stroke="#F26522" stroke-width="1.8" fill="none"/>
        <line x1="2" y1="15" x2="28" y2="15" stroke="#F26522" stroke-width="1.8"/>
      </svg>
      <span class="logo-txt">BALL<span class="logo-acc">SPORTS</span></span>
    </a>
    <a href="index.php" class="nav-back-link">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
      Back to Shop
    </a>
  </div>
</header>

<div class="ord-page">
  <div style="display:flex;align-items:center;gap:14px;margin-bottom:8px">
    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#F26522" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
    <h1 class="ord-page-title">MY ORDERS</h1>
  </div>
  <p class="ord-page-sub">Track your orders and view delivery status in real time.</p>

  <!-- Email lookup OR reference lookup -->
  <div class="ord-lookup">
    <div class="ord-lookup-field">
      <label>LOOK UP BY EMAIL</label>
      <input type="email" class="ord-lookup-inp" id="lookup-email" placeholder="your@email.com">
    </div>
    <div style="font-family:'Barlow Condensed',sans-serif;font-size:12px;color:#9aa5bf;padding:8px 0;align-self:center">OR</div>
    <div class="ord-lookup-field">
      <label>LOOK UP BY ORDER REF</label>
      <input type="text" class="ord-lookup-inp" id="lookup-ref" placeholder="e.g. BS-ABCD1234">
    </div>
    <button class="btn-lookup" onclick="lookupOrders()">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display:inline;vertical-align:middle;margin-right:5px"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
      FIND ORDERS
    </button>
  </div>

  <!-- Filters (shown after lookup) -->
  <div class="ord-toolbar" id="ord-toolbar" style="display:none">
    <input type="text" class="ord-search" id="ord-search" placeholder="Filter by reference or name…">
    <select class="ord-filter-sel" id="ord-filter">
      <option value="all">All Orders</option>
      <option value="Order Placed">Order Placed</option>
      <option value="Confirmed">Confirmed</option>
      <option value="Packing">Packing</option>
      <option value="Shipped">Shipped</option>
      <option value="Delivered">Delivered</option>
      <option value="Cancelled">Cancelled</option>
    </select>
  </div>

  <div id="ord-list">
    <div class="ord-empty">
      <div class="ord-empty-icon"><svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#9aa5bf" stroke-width="1.8"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg></div>
      <h3>Find Your Orders</h3>
      <p>Enter your email address or order reference number above to view your orders.</p>
    </div>
  </div>
</div>

<div class="toast-wrap" id="toast-wrap"></div>

<script>
const API = 'api.php';
const ICONS={
  Basketball:`<svg viewBox="0 0 60 60" fill="none" stroke="#F26522" stroke-width="2.2"><circle cx="30" cy="30" r="22"/><path d="M30 8 Q38 16 38 30 Q38 44 30 52" fill="none"/><path d="M30 8 Q22 16 22 30 Q22 44 30 52" fill="none"/><line x1="8" y1="30" x2="52" y2="30"/></svg>`,
  Football:`<svg viewBox="0 0 60 60" fill="none" stroke="#F26522" stroke-width="2.2"><circle cx="30" cy="30" r="22"/><polygon points="30,20 37,26 34,35 26,35 23,26" fill="rgba(242,101,34,.12)"/></svg>`,
  Volleyball:`<svg viewBox="0 0 60 60" fill="none" stroke="#F26522" stroke-width="2.2"><circle cx="30" cy="30" r="22"/><path d="M8 30 Q15 12 30 12 Q45 12 52 30" fill="none"/></svg>`,
  Tennis:`<svg viewBox="0 0 60 60" fill="none" stroke="#F26522" stroke-width="2.2"><circle cx="26" cy="34" r="18"/></svg>`,
  Badminton:`<svg viewBox="0 0 60 60" fill="none" stroke="#F26522" stroke-width="2.2"><ellipse cx="30" cy="20" rx="13" ry="15"/><line x1="30" y1="35" x2="30" y2="54"/></svg>`,
  Swimming:`<svg viewBox="0 0 60 60" fill="none" stroke="#F26522" stroke-width="2.2"><path d="M8 44 Q16 38 24 44 Q32 50 40 44 Q48 38 52 44"/><circle cx="40" cy="14" r="5"/></svg>`,
  Baseball:`<svg viewBox="0 0 60 60" fill="none" stroke="#F26522" stroke-width="2.2"><circle cx="30" cy="30" r="21"/></svg>`,
  Running:`<svg viewBox="0 0 60 60" fill="none" stroke="#F26522" stroke-width="2.2"><circle cx="38" cy="11" r="5"/><path d="M34 17 L28 28 L16 30M28 28 L26 40 L18 52"/></svg>`,
  Accessories:`<svg viewBox="0 0 60 60" fill="none" stroke="#F26522" stroke-width="2.2"><rect x="12" y="22" width="36" height="26" rx="4"/><path d="M21 22V17Q21 12 27 12H33Q39 12 39 17V22"/></svg>`,
  Default:`<svg viewBox="0 0 60 60" fill="none" stroke="#F26522" stroke-width="2.2"><circle cx="30" cy="30" r="20"/></svg>`
};
function getIcon(cat){ return ICONS[cat]||ICONS.Default; }

const STATUS_STEPS = ['Order Placed','Confirmed','Packing','Shipped','Delivered'];
const STATUS_ICONS = {
  'Order Placed': `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>`,
  'Confirmed':    `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>`,
  'Packing':      `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><rect x="1" y="3" width="15" height="13" rx="1"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>`,
  'Shipped':      `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><rect x="1" y="3" width="15" height="13" rx="1"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>`,
  'Delivered':    `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>`,
  'Cancelled':    `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><path d="M18 6L6 18M6 6l12 12"/></svg>`,
};

let allOrders = [];

function getStatusClass(s){
  return {
    'Order Placed':'s-placed','Confirmed':'s-confirmed','Packing':'s-packing',
    'Shipped':'s-shipped','Delivered':'s-delivered','Cancelled':'s-cancelled'
  }[s]||'s-placed';
}

// ── LOOKUP ──
window.lookupOrders = async function(){
  const email = document.getElementById('lookup-email').value.trim();
  const ref   = document.getElementById('lookup-ref').value.trim().toUpperCase();
  if(!email && !ref){ showToast('Enter your email or order reference.','err'); return; }

  document.getElementById('ord-list').innerHTML = '<div class="ord-loading"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#F26522" stroke-width="2" style="animation:spin 1s linear infinite;display:block;margin:0 auto 12px"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>Looking up orders…</div>';

  try {
    let url = '';
    if(ref) url = `${API}?action=order&ref=${encodeURIComponent(ref)}`;
    else    url = `${API}?action=orders&email=${encodeURIComponent(email)}`;
    const res = await fetch(url);
    const data = await res.json();
    if(data.error){ showEmpty(data.error); return; }
    allOrders = Array.isArray(data) ? data : [data];
    if(!allOrders.length){ showEmpty('No orders found for that email.'); return; }
    document.getElementById('ord-toolbar').style.display = 'flex';
    renderOrders();
    // Auto-open if ref was searched
    const urlRef = new URLSearchParams(window.location.search).get('ref');
    if(urlRef){ setTimeout(()=>{ const el=document.querySelector(`[data-ref="${urlRef}"]`); if(el) el.classList.add('open'); },200); }
  } catch(e){
    showEmpty('Network error. Please try again.');
  }
};

// Auto-lookup on page load if URL has ref
document.addEventListener('DOMContentLoaded', async () => {
  const params = new URLSearchParams(window.location.search);
  const ref = params.get('ref');
  if(ref){
    document.getElementById('lookup-ref').value = ref;
    await lookupOrders();
    setTimeout(()=>{ const el=document.querySelector('.ord-card'); if(el) el.classList.add('open'); },300);
  }
  // Filter events
  document.getElementById('ord-search').addEventListener('input', renderOrders);
  document.getElementById('ord-filter').addEventListener('change', renderOrders);
  // CSS animation for spinner
  const st=document.createElement('style'); st.textContent='@keyframes spin{to{transform:rotate(360deg)}}'; document.head.appendChild(st);
});

function renderOrders(){
  const q    = document.getElementById('ord-search')?.value.trim().toLowerCase() || '';
  const filt = document.getElementById('ord-filter')?.value || 'all';
  const list = allOrders.filter(o=>{
    const matchQ = !q || o.ref.toLowerCase().includes(q) || (o.customer?.fn+' '+o.customer?.ln).toLowerCase().includes(q);
    const matchF = filt==='all' || o.status===filt;
    return matchQ && matchF;
  });
  const container = document.getElementById('ord-list');
  if(!list.length){ showEmpty('No orders match your filter.'); return; }
  container.innerHTML = list.map((o,idx)=>renderOrderCard(o,idx)).join('');
}

function renderOrderCard(o, idx){
  const canCancel = ['Order Placed','Confirmed'].includes(o.status);
  const items = Array.isArray(o.items) ? o.items : [];
  return `
  <div class="ord-card" data-ref="${o.ref}" id="ocard-${idx}">
    <div class="ord-card-hd" onclick="toggleCard(${idx})">
      <div>
        <div class="ord-ref">${o.ref}</div>
        <div class="ord-date">${o.date||''}</div>
      </div>
      <div class="ord-hd-right">
        <span class="ord-status ${getStatusClass(o.status)}">${o.status}</span>
        <span class="ord-total">₱${Number(o.total).toLocaleString()}</span>
        <svg class="ord-chevron" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
      </div>
    </div>
    <div class="ord-card-body">

      <!-- Items -->
      <div class="ord-items-section">
        <div class="ord-items-title">ORDERED ITEMS</div>
        ${items.map(item=>`
          <div class="ord-item-row">
            <div class="ord-item-icon">${getIcon(item.cat||'Accessories')}</div>
            <div style="flex:1">
              <div class="ord-item-name">${item.name}</div>
              <div class="ord-item-meta">Qty: ${item.qty} × ₱${Number(item.price).toLocaleString()}</div>
            </div>
            <div class="ord-item-price">₱${(Number(item.price)*Number(item.qty)).toLocaleString()}</div>
          </div>`).join('')}
      </div>

      <!-- Info -->
      <div class="ord-info-grid">
        <div class="ord-info-col">
          <div class="ord-info-label">ORDER SUMMARY</div>
          <div class="ord-info-row"><span>Subtotal</span><span>₱${Number(o.subtotal).toLocaleString()}</span></div>
          ${Number(o.discount)>0?`<div class="ord-info-row"><span>Discount${o.coupon?' ('+o.coupon+')':''}</span><span style="color:#27AE60">−₱${Number(o.discount).toLocaleString()}</span></div>`:''}
          <div class="ord-info-row"><span>Shipping</span><span>${Number(o.shipping)===0?'FREE':'₱'+Number(o.shipping).toLocaleString()}</span></div>
          <div class="ord-info-row total-row"><span>Total</span><span>₱${Number(o.total).toLocaleString()}</span></div>
          <div style="margin-top:14px">
            <div class="ord-info-label" style="margin-bottom:6px">PAYMENT</div>
            <div style="font-size:13px;color:#4a5568;font-weight:600">${{cod:'Cash on Delivery',gcash:'GCash',card:'Credit/Debit Card',bank:'Bank Transfer'}[o.payment]||o.payment}</div>
          </div>
          ${canCancel?`<button class="btn-cancel-order" onclick="cancelOrder('${o.ref}',${idx})">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display:inline;vertical-align:middle;margin-right:4px"><path d="M18 6L6 18M6 6l12 12"/></svg>
            CANCEL ORDER
          </button>`:''}
        </div>
        <div class="ord-info-col">
          <div class="ord-info-label">DELIVERY ADDRESS</div>
          <div class="addr-block">
            <div class="addr-name">${o.customer?.fn||''} ${o.customer?.ln||''}</div>
            ${o.customer?.ph||''}<br>
            ${o.address?.ad||''}<br>
            ${o.address?.ci||''}, ${o.address?.pv||''} ${o.address?.zp||''}<br>
            ${o.address?.rg||''}
            ${o.address?.nt?`<div style="margin-top:6px;font-size:12px;color:#9aa5bf;font-style:italic">Note: ${o.address.nt}</div>`:''}
          </div>
        </div>
      </div>

      <!-- Tracking -->
      <div class="ord-track-section">
        <div class="ord-track-title">SHIPMENT TRACKING</div>
        ${renderTimeline(o)}
        ${renderHistory(o)}
      </div>
    </div>
  </div>`;
}

function renderTimeline(o){
  const hist = Array.isArray(o.statusHistory) ? o.statusHistory : [];
  if(o.status==='Cancelled'){
    return `<div class="track-steps">
      ${STATUS_STEPS.map((s,i)=>`<div class="track-step ${i===0?'done':'cancelled'}" style="${i>0?'opacity:.35':''}">
        <div class="track-dot">${STATUS_ICONS['Cancelled']||''}</div>
        <div class="track-step-label">${s}</div>
      </div>`).join('')}
    </div>
    <div style="margin-top:14px;padding:12px 16px;background:#ffebee;border-radius:8px;font-size:13px;color:#c62828;font-weight:600;display:flex;align-items:center;gap:8px">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#c62828" stroke-width="2.5"><path d="M18 6L6 18M6 6l12 12"/></svg>
      This order has been cancelled.
    </div>`;
  }
  const curIdx = STATUS_STEPS.indexOf(o.status);
  return `<div class="track-steps">
    ${STATUS_STEPS.map((s,i)=>{
      const state = i < curIdx ? 'done' : i === curIdx ? 'active' : '';
      const h = hist.find(x=>x.status===s);
      return `<div class="track-step ${state}">
        <div class="track-dot">${state ? (STATUS_ICONS[s]||'') : '<span style="width:8px;height:8px;border-radius:50%;background:#d0d8e8;display:block"></span>'}</div>
        <div class="track-step-label">${s}</div>
        <div class="track-step-date">${h?h.date:''}</div>
      </div>`;
    }).join('')}
  </div>`;
}

function renderHistory(o){
  const hist = Array.isArray(o.statusHistory) ? o.statusHistory : [];
  if(!hist.length) return '';
  return `<div class="track-history">
    <div class="th-title">STATUS HISTORY</div>
    ${[...hist].reverse().map((h,i)=>`
      <div class="th-row">
        <div class="th-dot ${i===hist.length-1?'':'done'}"></div>
        <div>
          <div class="th-status">${h.status}</div>
          <div class="th-note">${h.note||''}</div>
        </div>
        <div class="th-date">${h.date||''}</div>
      </div>`).join('')}
  </div>`;
}

function toggleCard(idx){
  document.getElementById('ocard-'+idx)?.classList.toggle('open');
}

// ── CANCEL ORDER ──
window.cancelOrder = async function(ref, idx){
  if(!confirm(`Are you sure you want to cancel order ${ref}?\nThis cannot be undone once the order is being packed.`)) return;
  try {
    const res = await fetch(`${API}?action=cancel_order`, {
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ ref })
    });
    const data = await res.json();
    if(data.success){
      showToast('Order cancelled successfully.','ok');
      // Update the order in local state and re-render
      const o = allOrders.find(x=>x.ref===ref);
      if(o){
        o.status='Cancelled';
        if(!o.statusHistory) o.statusHistory=[];
        const today = new Date().toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'});
        o.statusHistory.push({status:'Cancelled',note:'Order was cancelled by customer.',date:today});
      }
      renderOrders();
      setTimeout(()=>{ document.getElementById('ocard-'+idx)?.classList.add('open'); },100);
    } else {
      showToast(data.error || 'Cannot cancel this order.','err');
    }
  } catch(e){
    showToast('Network error. Please try again.','err');
  }
};

function showEmpty(msg){
  document.getElementById('ord-list').innerHTML=`
    <div class="ord-empty">
      <div class="ord-empty-icon"><svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#9aa5bf" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div>
      <h3>No Orders Found</h3>
      <p>${msg}</p>
      <a href="index.php" class="btn-primary" style="display:inline-flex">SHOP NOW</a>
    </div>`;
}

function showToast(msg,type=''){
  const c=document.getElementById('toast-wrap');
  const t=document.createElement('div'); t.className='toast'+(type?' '+type:''); t.textContent=msg;
  c.appendChild(t); requestAnimationFrame(()=>requestAnimationFrame(()=>t.classList.add('show')));
  setTimeout(()=>{ t.classList.remove('show'); setTimeout(()=>t.remove(),350); },2800);
}
</script>
</body>
</html>