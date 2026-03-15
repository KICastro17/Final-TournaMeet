<?php

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Checkout — Ball Sports PH</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:ital,wght@0,400;0,600;0,700;0,800;0,900&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <style>
    body { background: #f4f6fb; }
    .co-wrap { max-width: 1100px; margin: 0 auto; padding: 36px 20px 80px; display: grid; grid-template-columns: 1fr 360px; gap: 28px; }
    @media(max-width:820px){ .co-wrap { grid-template-columns: 1fr; } .co-summary { order: -1; } }

    /* Steps */
    .co-steps { display: flex; align-items: center; justify-content: center; gap: 0; margin-bottom: 28px; flex-wrap: wrap; }
    .co-step { display: flex; align-items: center; gap: 8px; font-family: 'Barlow Condensed', sans-serif; font-size: 12px; font-weight: 800; letter-spacing: 1px; color: #b0bac9; transition: color .3s; }
    .co-step.active { color: #F26522; }
    .co-step.done { color: #27AE60; }
    .co-step-num { width: 28px; height: 28px; border-radius: 50%; border: 2px solid #d0d8e8; display: flex; align-items: center; justify-content: center; font-size: 11px; transition: all .3s; background: #fff; }
    .co-step.active .co-step-num { border-color: #F26522; color: #F26522; }
    .co-step.done .co-step-num { background: #27AE60; border-color: #27AE60; color: #fff; }
    .co-step-div { width: 36px; height: 2px; background: #e0e8f0; margin: 0 4px; }
    .co-step-div.done { background: #27AE60; }
    @media(max-width:480px){ .co-step span:last-child { display:none; } }

    /* Form card */
    .co-card { background: #fff; border: 1.5px solid #e0e8f0; border-radius: 16px; padding: 28px; box-shadow: 0 1px 6px rgba(0,0,0,.04); }
    .co-section-title { font-family: 'Barlow Condensed', sans-serif; font-size: 22px; font-weight: 800; color: #1a1a2e; margin-bottom: 6px; }
    .co-section-sub { font-size: 13px; color: #9aa5bf; margin-bottom: 24px; }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    @media(max-width:520px){ .form-grid { grid-template-columns: 1fr; } }
    .form-field { display: flex; flex-direction: column; gap: 5px; }
    .form-field.full { grid-column: 1 / -1; }
    .form-field label { font-family: 'Barlow Condensed', sans-serif; font-size: 11px; font-weight: 800; letter-spacing: 1.5px; color: #9aa5bf; }
    .form-field input, .form-field select, .form-field textarea { background: #f8f9fb; border: 1.5px solid #e0e8f0; color: #1a1a2e; font-family: 'Barlow', sans-serif; font-size: 14px; padding: 12px 14px; border-radius: 8px; outline: none; width: 100%; transition: border-color .2s, box-shadow .2s; }
    .form-field input:focus, .form-field select:focus, .form-field textarea:focus { border-color: #F26522; box-shadow: 0 0 0 3px rgba(242,101,34,.1); }
    .form-field.err input, .form-field.err select, .form-field.err textarea { border-color: #E74C3C; }
    .form-err-msg { font-size: 11px; color: #E74C3C; margin-top: 3px; display: none; }
    .form-field.err .form-err-msg { display: block; }

    /* Payment options */
    .pay-opts { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 16px; }
    @media(max-width:480px){ .pay-opts { grid-template-columns: 1fr; } }
    .pay-opt { border: 2px solid #e0e8f0; border-radius: 10px; padding: 14px; cursor: pointer; transition: all .2s; background: #fff; }
    .pay-opt:hover { border-color: #F26522; background: #fff9f6; }
    .pay-opt.selected { border-color: #F26522; background: #fff5f0; }
    .pay-opt-icon { width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-bottom: 8px; }
    .pay-opt-icon.cod { background: #e8f5e9; }
    .pay-opt-icon.gcash { background: #e3f2fd; }
    .pay-opt-icon.card { background: #fce4ec; }
    .pay-opt-icon.bank { background: #f3e5f5; }
    .pay-opt-nm { font-family: 'Barlow Condensed', sans-serif; font-size: 15px; font-weight: 800; color: #1a1a2e; }
    .pay-opt-ds { font-size: 11px; color: #9aa5bf; margin-top: 2px; }
    .pay-extra { display: none; margin-top: 14px; padding-top: 14px; border-top: 1px solid #f0f4f8; }
    .pay-extra.show { display: block; }

    /* Cart review items */
    .ri-row { display: flex; align-items: center; gap: 14px; padding: 12px 0; border-bottom: 1px solid #f4f6fb; }
    .ri-row:last-child { border-bottom: none; }
    .ri-icon { width: 48px; height: 48px; background: #f4f6fb; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid #e0e8f0; }
    .ri-name { font-family: 'Barlow Condensed', sans-serif; font-size: 16px; font-weight: 700; color: #1a1a2e; }
    .ri-meta { font-size: 12px; color: #9aa5bf; }
    .ri-price { font-family: 'Barlow Condensed', sans-serif; font-size: 16px; font-weight: 800; color: #F26522; margin-left: auto; white-space: nowrap; }

    /* Navigation buttons */
    .co-navs { display: flex; gap: 12px; margin-top: 24px; flex-wrap: wrap; }
    .btn-back { background: #fff; border: 2px solid #e0e8f0; color: #9aa5bf; font-family: 'Barlow Condensed', sans-serif; font-size: 13px; font-weight: 800; letter-spacing: 1px; padding: 13px 22px; border-radius: 8px; cursor: pointer; transition: all .2s; }
    .btn-back:hover { border-color: #1a1a2e; color: #1a1a2e; }
    .btn-next { background: #F26522; border: none; color: #fff; font-family: 'Barlow Condensed', sans-serif; font-size: 13px; font-weight: 800; letter-spacing: 1.5px; padding: 14px 28px; border-radius: 8px; cursor: pointer; transition: all .2s; flex: 1; }
    .btn-next:hover { background: #D4551A; transform: translateY(-1px); box-shadow: 0 4px 14px rgba(242,101,34,.3); }
    .btn-place-order { background: linear-gradient(135deg, #27AE60, #1e8449); border: none; color: #fff; font-family: 'Barlow Condensed', sans-serif; font-size: 15px; font-weight: 800; letter-spacing: 1.5px; padding: 16px 28px; border-radius: 10px; cursor: pointer; transition: all .2s; width: 100%; margin-top: 8px; }
    .btn-place-order:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(39,174,96,.35); }

    /* Summary panel */
    .co-summary { position: sticky; top: 100px; }
    .sum-panel { background: #fff; border: 1.5px solid #e0e8f0; border-radius: 16px; padding: 24px; box-shadow: 0 1px 6px rgba(0,0,0,.04); }
    .sum-panel-title { font-family: 'Barlow Condensed', sans-serif; font-size: 15px; font-weight: 800; letter-spacing: 1px; color: #1a1a2e; margin-bottom: 18px; padding-bottom: 14px; border-bottom: 1.5px solid #f0f4f8; }
    .sum-items { margin-bottom: 18px; }
    .sum-item { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid #f8f9fb; }
    .sum-item:last-child { border-bottom: none; }
    .sum-item-icon { width: 36px; height: 36px; background: #f4f6fb; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid #e0e8f0; }
    .sum-item-name { font-family: 'Barlow Condensed', sans-serif; font-size: 13px; font-weight: 700; color: #1a1a2e; line-height: 1.2; }
    .sum-item-qty { font-size: 11px; color: #9aa5bf; }
    .sum-item-price { font-family: 'Barlow Condensed', sans-serif; font-size: 14px; font-weight: 800; color: #1a1a2e; margin-left: auto; }
    .sum-line { display: flex; justify-content: space-between; font-size: 13px; color: #7a8fa6; margin-bottom: 8px; }
    .sum-line.total { font-family: 'Barlow Condensed', sans-serif; font-size: 19px; font-weight: 800; color: #1a1a2e; margin-top: 8px; padding-top: 10px; border-top: 2px solid #f0f4f8; margin-bottom: 0; }
    .sum-line.total span:last-child { color: #F26522; }
    .sum-line.disc span:last-child { color: #27AE60; }

    /* Coupon in sidebar */
    .co-cpn-wrap { margin-top: 18px; padding-top: 16px; border-top: 1px solid #f0f4f8; }
    .co-cpn-row { display: flex; gap: 8px; }
    .co-cpn-inp { flex: 1; background: #f8f9fb; border: 1.5px solid #e0e8f0; color: #1a1a2e; font-family: 'Barlow', sans-serif; font-size: 13px; padding: 10px 13px; border-radius: 8px; outline: none; text-transform: uppercase; letter-spacing: 1px; transition: border-color .2s; }
    .co-cpn-inp:focus { border-color: #F26522; }
    .co-cpn-btn { background: #F26522; border: none; color: #fff; font-family: 'Barlow Condensed', sans-serif; font-size: 12px; font-weight: 800; letter-spacing: 1px; padding: 10px 14px; border-radius: 8px; cursor: pointer; transition: background .2s; white-space: nowrap; }
    .co-cpn-btn:hover { background: #D4551A; }
    .co-cpn-res { font-size: 12px; margin-top: 6px; }
    .co-cpn-res.ok { color: #27AE60; }
    .co-cpn-res.fail { color: #E74C3C; }

    /* Success page */
    #success-page { display: none; max-width: 600px; margin: 60px auto; padding: 0 20px 80px; text-align: center; }
    .suc-icon { width: 80px; height: 80px; background: linear-gradient(135deg, #27AE60, #2ecc71); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; box-shadow: 0 8px 24px rgba(39,174,96,.3); }
    .suc-title { font-family: 'Barlow Condensed', sans-serif; font-size: 38px; font-weight: 900; color: #1a1a2e; margin-bottom: 10px; }
    .suc-sub { font-size: 15px; color: #7a8fa6; margin-bottom: 28px; }
    .order-ref-box { background: #f4f6fb; border: 2px dashed #e0e8f0; border-radius: 12px; padding: 18px 24px; margin-bottom: 28px; }
    .order-ref-lbl { font-size: 11px; font-weight: 800; letter-spacing: 2px; color: #9aa5bf; font-family: 'Barlow Condensed', sans-serif; margin-bottom: 6px; }
    .order-ref-num { font-family: 'Barlow Condensed', sans-serif; font-size: 28px; font-weight: 900; color: #F26522; letter-spacing: 2px; }
    .od-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 28px; text-align: left; }
    .od-item { background: #f8f9fb; border-radius: 10px; padding: 14px 16px; }
    .od-lbl { font-family: 'Barlow Condensed', sans-serif; font-size: 10px; font-weight: 800; letter-spacing: 2px; color: #9aa5bf; margin-bottom: 4px; }
    .od-val { font-family: 'Barlow Condensed', sans-serif; font-size: 14px; font-weight: 700; color: #1a1a2e; }
    .suc-btns { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
    .btn-back-shop { background: #F26522; color: #fff; border: none; font-family: 'Barlow Condensed', sans-serif; font-size: 13px; font-weight: 800; letter-spacing: 1.5px; padding: 13px 28px; border-radius: 8px; cursor: pointer; transition: all .2s; }
    .btn-back-shop:hover { background: #D4551A; transform: translateY(-2px); }
    .btn-track { background: #27AE60; color: #fff; border: none; font-family: 'Barlow Condensed', sans-serif; font-size: 13px; font-weight: 800; letter-spacing: 1.5px; padding: 13px 28px; border-radius: 8px; cursor: pointer; transition: all .2s; }
    .btn-track:hover { background: #1e8449; transform: translateY(-2px); }
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

<div class="toast-wrap" id="toast-wrap"></div>

<!-- ═══ CHECKOUT PAGE ═══ -->
<div id="checkout-page">
  <!-- Step indicators -->
  <div style="padding:28px 20px 0;max-width:1100px;margin:0 auto">
    <div class="co-steps" id="co-steps">
      <div class="co-step active" data-s="1"><div class="co-step-num">1</div><span>CART</span></div>
      <div class="co-step-div"></div>
      <div class="co-step" data-s="2"><div class="co-step-num">2</div><span>CONTACT</span></div>
      <div class="co-step-div"></div>
      <div class="co-step" data-s="3"><div class="co-step-num">3</div><span>DELIVERY</span></div>
      <div class="co-step-div"></div>
      <div class="co-step" data-s="4"><div class="co-step-num">4</div><span>PAYMENT</span></div>
    </div>
  </div>

  <div class="co-wrap">
    <!-- LEFT: Steps content -->
    <div class="co-main">

      <!-- Step 1: Cart Review -->
      <div class="co-card" id="step-1">
        <div class="co-section-title">Review Your Cart</div>
        <div class="co-section-sub">Check your items before proceeding.</div>
        <div id="co-cart-items"><p style="text-align:center;color:#9aa5bf;padding:32px 0">Your cart is empty. <a href="index.php" style="color:#F26522">Shop now →</a></p></div>
        <div class="co-navs">
          <button class="btn-next" id="step1-next" style="display:none">CONTINUE TO CONTACT INFO →</button>
        </div>
      </div>

      <!-- Step 2: Contact -->
      <div class="co-card hidden" id="step-2">
        <div class="co-section-title">Contact Information</div>
        <div class="co-section-sub">We'll use this to confirm your order.</div>
        <div class="form-grid">
          <div class="form-field"><label>FIRST NAME *</label><input type="text" id="f-fn" placeholder="Juan"><div class="form-err-msg">First name required</div></div>
          <div class="form-field"><label>LAST NAME *</label><input type="text" id="f-ln" placeholder="Dela Cruz"><div class="form-err-msg">Last name required</div></div>
          <div class="form-field full"><label>EMAIL ADDRESS *</label><input type="email" id="f-em" placeholder="juan@email.com"><div class="form-err-msg">Valid email required</div></div>
          <div class="form-field full"><label>PHONE NUMBER *</label><input type="tel" id="f-ph" placeholder="09XX XXX XXXX"><div class="form-err-msg">Phone number required</div></div>
        </div>
        <div class="co-navs">
          <button class="btn-back" onclick="goStep(1)">← BACK</button>
          <button class="btn-next" onclick="goStep(3)">CONTINUE TO DELIVERY →</button>
        </div>
      </div>

      <!-- Step 3: Delivery -->
      <div class="co-card hidden" id="step-3">
        <div class="co-section-title">Delivery Address</div>
        <div class="co-section-sub">Where should we ship your order?</div>
        <div class="form-grid">
          <div class="form-field full"><label>STREET ADDRESS *</label><input type="text" id="f-ad" placeholder="123 Rizal Street, Brgy. San Antonio"><div class="form-err-msg">Address required</div></div>
          <div class="form-field"><label>CITY / MUNICIPALITY *</label><input type="text" id="f-ci" placeholder="Makati City"><div class="form-err-msg">City required</div></div>
          <div class="form-field"><label>PROVINCE *</label><input type="text" id="f-pv" placeholder="Metro Manila"><div class="form-err-msg">Province required</div></div>
          <div class="form-field"><label>ZIP CODE *</label><input type="text" id="f-zp" placeholder="1200"><div class="form-err-msg">Zip code required</div></div>
          <div class="form-field">
            <label>REGION</label>
            <select id="f-rg">
              <option value="">Select Region</option>
              <option>NCR – Metro Manila</option><option>CAR – Cordillera</option>
              <option>Region I – Ilocos</option><option>Region II – Cagayan Valley</option>
              <option>Region III – Central Luzon</option><option>Region IV-A – CALABARZON</option>
              <option>MIMAROPA</option><option>Region V – Bicol</option>
              <option>Region VI – Western Visayas</option><option>Region VII – Central Visayas</option>
              <option>Region VIII – Eastern Visayas</option><option>Region IX – Zamboanga</option>
              <option>Region X – Northern Mindanao</option><option>Region XI – Davao</option>
              <option>Region XII – SOCCSKSARGEN</option><option>Region XIII – Caraga</option>
              <option>BARMM</option>
            </select>
          </div>
          <div class="form-field full"><label>DELIVERY NOTES (optional)</label><textarea id="f-nt" rows="2" placeholder="e.g. Leave at gate / Call before delivery"></textarea></div>
        </div>
        <div class="co-navs">
          <button class="btn-back" onclick="goStep(2)">← BACK</button>
          <button class="btn-next" onclick="goStep(4)">CONTINUE TO PAYMENT →</button>
        </div>
      </div>

      <!-- Step 4: Payment -->
      <div class="co-card hidden" id="step-4">
        <div class="co-section-title">Payment Method</div>
        <div class="co-section-sub">Choose how you'd like to pay.</div>
        <div class="pay-opts">
          <div class="pay-opt" data-pay="cod" onclick="selectPay('cod')">
            <div class="pay-opt-icon cod"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#27AE60" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg></div>
            <div class="pay-opt-nm">Cash on Delivery</div>
            <div class="pay-opt-ds">Pay when you receive</div>
          </div>
          <div class="pay-opt" data-pay="gcash" onclick="selectPay('gcash')">
            <div class="pay-opt-icon gcash"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1565C0" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg></div>
            <div class="pay-opt-nm">GCash</div>
            <div class="pay-opt-ds">Digital wallet payment</div>
          </div>
          <div class="pay-opt" data-pay="card" onclick="selectPay('card')">
            <div class="pay-opt-icon card"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#C2185B" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg></div>
            <div class="pay-opt-nm">Credit / Debit</div>
            <div class="pay-opt-ds">Visa, Mastercard, JCB</div>
          </div>
          <div class="pay-opt" data-pay="bank" onclick="selectPay('bank')">
            <div class="pay-opt-icon bank"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#7B1FA2" stroke-width="2"><line x1="3" y1="22" x2="21" y2="22"/><line x1="6" y1="18" x2="6" y2="11"/><line x1="10" y1="18" x2="10" y2="11"/><line x1="14" y1="18" x2="14" y2="11"/><line x1="18" y1="18" x2="18" y2="11"/><polygon points="12 2 20 7 4 7"/></svg></div>
            <div class="pay-opt-nm">Bank Transfer</div>
            <div class="pay-opt-ds">Direct bank deposit</div>
          </div>
        </div>

        <!-- GCash extra -->
        <div class="pay-extra" id="extra-gcash">
          <div class="form-grid">
            <div class="form-field full"><label>GCASH NUMBER *</label><input type="tel" id="f-gc" placeholder="09XX XXX XXXX"><div class="form-err-msg">GCash number required</div></div>
          </div>
        </div>

        <!-- Card extra -->
        <div class="pay-extra" id="extra-card">
          <div class="form-grid">
            <div class="form-field full"><label>CARDHOLDER NAME *</label><input type="text" id="f-ch" placeholder="JUAN DELA CRUZ"><div class="form-err-msg">Cardholder name required</div></div>
            <div class="form-field full"><label>CARD NUMBER *</label><input type="text" id="f-cn" placeholder="0000 0000 0000 0000" maxlength="19" oninput="fmtCard(this)"><div class="form-err-msg">Card number required</div></div>
            <div class="form-field"><label>EXPIRY (MM/YY) *</label><input type="text" id="f-ce" placeholder="MM/YY" maxlength="5" oninput="fmtExp(this)"><div class="form-err-msg">Expiry required</div></div>
            <div class="form-field"><label>CVV *</label><input type="text" id="f-cv" placeholder="123" maxlength="4"><div class="form-err-msg">CVV required</div></div>
          </div>
        </div>

        <!-- Bank extra -->
        <div class="pay-extra" id="extra-bank">
          <div style="background:#f8f9fb;border:1px solid #e0e8f0;border-radius:10px;padding:16px">
            <div style="font-family:'Barlow Condensed',sans-serif;font-size:13px;font-weight:800;color:#7a8fa6;margin-bottom:10px">BANK ACCOUNT DETAILS</div>
            <div style="font-size:13px;line-height:2;color:#4a5568"><strong>Bank:</strong> BDO Unibank<br><strong>Account Name:</strong> Ball Sports PH<br><strong>Account Number:</strong> 1234-5678-9012<br><strong>Branch:</strong> Makati City</div>
          </div>
        </div>

        <div class="co-navs">
          <button class="btn-back" onclick="goStep(3)">← BACK</button>
        </div>
        <button class="btn-place-order" onclick="placeOrder()" id="btn-place">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display:inline;vertical-align:middle;margin-right:6px"><path d="M20 6L9 17l-5-5"/></svg>
          PLACE ORDER NOW
        </button>
      </div>

    </div><!-- .co-main -->

    <!-- RIGHT: Order Summary -->
    <div class="co-summary">
      <div class="sum-panel">
        <div class="sum-panel-title">ORDER SUMMARY</div>
        <div class="sum-items" id="sum-items"></div>
        <div class="sum-line"><span>Subtotal</span><span id="sum-sub">₱0</span></div>
        <div class="sum-line disc" id="sum-disc-row" style="display:none"><span>Discount</span><span id="sum-disc">−₱0</span></div>
        <div class="sum-line"><span>Shipping</span><span id="sum-ship">₱150</span></div>
        <div class="sum-line total"><span>TOTAL</span><span id="sum-total">₱150</span></div>
        <!-- Coupon -->
        <div class="co-cpn-wrap">
          <div style="font-family:'Barlow Condensed',sans-serif;font-size:11px;font-weight:800;letter-spacing:1.5px;color:#9aa5bf;margin-bottom:8px">APPLY COUPON</div>
          <div class="co-cpn-row">
            <input type="text" class="co-cpn-inp" id="co-cpn" placeholder="Enter code…">
            <button class="co-cpn-btn" onclick="applyCouponCo()">APPLY</button>
          </div>
          <div class="co-cpn-res" id="co-cpn-res"></div>
        </div>
      </div>
    </div>

  </div><!-- .co-wrap -->
</div><!-- #checkout-page -->

<!-- ═══ SUCCESS PAGE ═══ -->
<div id="success-page">
  <div class="suc-icon">
    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3"><path d="M20 6L9 17l-5-5"/></svg>
  </div>
  <div class="suc-title">ORDER PLACED!</div>
  <div class="suc-sub">Thank you! Your order has been received and is being processed.</div>
  <div class="order-ref-box">
    <div class="order-ref-lbl">YOUR ORDER REFERENCE</div>
    <div class="order-ref-num" id="suc-ref"></div>
  </div>
  <div class="od-grid" id="suc-details"></div>
  <div class="suc-btns">
    <button class="btn-track" id="suc-track-btn">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display:inline;vertical-align:middle;margin-right:4px"><rect x="1" y="3" width="15" height="13" rx="1"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
      TRACK MY ORDER
    </button>
    <button class="btn-back-shop" onclick="window.location.href='index.php'">
      CONTINUE SHOPPING
    </button>
  </div>
</div>

<script>
// ════════════════════════════════════════════
//  CHECKOUT — script (uses API)
// ════════════════════════════════════════════

const API = 'api.php';
const COUPONS = {
  BSPORTS10:{type:'percent',value:10,label:'10% off!'},
  WELCOME20:{type:'percent',value:20,label:'20% off!'},
  FREESHIP: {type:'ship',  value:0, label:'Free shipping!'},
  CHAMP15:  {type:'percent',value:15,label:'15% off!'},
};
const ICONS={
  Basketball:`<svg viewBox="0 0 60 60" fill="none" stroke="#F26522" stroke-width="2.2"><circle cx="30" cy="30" r="22"/><path d="M30 8 Q38 16 38 30 Q38 44 30 52" fill="none"/><path d="M30 8 Q22 16 22 30 Q22 44 30 52" fill="none"/><line x1="8" y1="30" x2="52" y2="30"/></svg>`,
  Football:`<svg viewBox="0 0 60 60" fill="none" stroke="#F26522" stroke-width="2.2"><circle cx="30" cy="30" r="22"/><polygon points="30,20 37,26 34,35 26,35 23,26" fill="rgba(242,101,34,.12)" stroke="#F26522" stroke-width="1.8"/></svg>`,
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

// State
const isBuyNow = new URLSearchParams(window.location.search).get('mode') === 'buynow';
let cart = isBuyNow
  ? JSON.parse(localStorage.getItem('bs_buynow') || '[]')
  : JSON.parse(localStorage.getItem('bs_cart') || '[]');
let coupon = null;
let currentStep = 1;
let selectedPay = '';
let orderData = {};
let placedRef = '';

// ── INIT ──
document.addEventListener('DOMContentLoaded', () => {
  renderCartReview();
  renderSummary();
});

function renderCartReview(){
  const el = document.getElementById('co-cart-items');
  const nextBtn = document.getElementById('step1-next');
  if(!cart.length){ el.innerHTML='<p style="text-align:center;color:#9aa5bf;padding:32px 0">Cart is empty. <a href="index.php" style="color:#F26522">Shop now →</a></p>'; nextBtn.style.display='none'; return; }
  el.innerHTML = cart.map(item=>`
    <div class="ri-row">
      <div class="ri-icon">${getIcon(item.cat||'Accessories')}</div>
      <div style="flex:1">
        <div class="ri-name">${item.name}</div>
        <div class="ri-meta">Qty: ${item.qty} × ₱${item.price.toLocaleString()}</div>
      </div>
      <div class="ri-price">₱${(item.price*item.qty).toLocaleString()}</div>
    </div>`).join('');
  nextBtn.style.display = 'flex';
}

function calcTotals(){
  const sub = cart.reduce((s,i)=>s+i.price*i.qty, 0);
  let disc = 0;
  if(coupon){ if(coupon.type==='percent') disc=Math.round(sub*coupon.value/100); if(coupon.type==='ship') disc=150; }
  const ship = (coupon&&coupon.type==='ship') ? 0 : (sub>2000 ? 0 : 150);
  return { sub, disc, ship, total: sub-disc+ship };
}

function renderSummary(){
  const { sub, disc, ship, total } = calcTotals();
  document.getElementById('sum-items').innerHTML = cart.map(item=>`
    <div class="sum-item">
      <div class="sum-item-icon">${getIcon(item.cat||'Accessories')}</div>
      <div style="flex:1"><div class="sum-item-name">${item.name}</div><div class="sum-item-qty">×${item.qty}</div></div>
      <div class="sum-item-price">₱${(item.price*item.qty).toLocaleString()}</div>
    </div>`).join('');
  document.getElementById('sum-sub').textContent = '₱'+sub.toLocaleString();
  document.getElementById('sum-disc').textContent = '−₱'+disc.toLocaleString();
  document.getElementById('sum-disc-row').style.display = disc>0 ? 'flex' : 'none';
  document.getElementById('sum-ship').textContent = ship===0 ? '🎉 FREE' : '₱'+ship;
  document.getElementById('sum-total').textContent = '₱'+total.toLocaleString();
}

// ── STEPS ──
window.goStep = function(n){
  if(n > currentStep){ if(!validateStep(currentStep)) return; }
  document.getElementById('step-'+currentStep).classList.add('hidden');
  document.getElementById('step-'+n).classList.remove('hidden');
  currentStep = n;
  document.querySelectorAll('.co-step').forEach(s=>{
    const sn = parseInt(s.dataset.s);
    s.classList.toggle('active', sn===n);
    s.classList.toggle('done', sn<n);
  });
  document.querySelectorAll('.co-step-div').forEach((d,i)=>{
    d.classList.toggle('done', i+1 < n);
  });
  window.scrollTo({top:0,behavior:'smooth'});
};
document.getElementById('step1-next').addEventListener('click', ()=>goStep(2));

function validateStep(n){
  if(n===1) return cart.length > 0;
  if(n===2){
    let ok=true;
    [['f-fn','First name'],['f-ln','Last name'],['f-em','Email'],['f-ph','Phone']].forEach(([id,lbl])=>{
      const el=document.getElementById(id); const field=el.closest('.form-field');
      const valid = id==='f-em' ? /\S+@\S+\.\S+/.test(el.value.trim()) : el.value.trim().length>0;
      field.classList.toggle('err',!valid); if(!valid)ok=false;
      if(valid){orderData[id.replace('f-','')]=el.value.trim();}
    });
    return ok;
  }
  if(n===3){
    let ok=true;
    [['f-ad','Address'],['f-ci','City'],['f-pv','Province'],['f-zp','Zip']].forEach(([id])=>{
      const el=document.getElementById(id); const field=el.closest('.form-field');
      const valid=el.value.trim().length>0; field.classList.toggle('err',!valid); if(!valid)ok=false;
      if(valid){orderData[id.replace('f-','')]=el.value.trim();}
    });
    if(ok){ orderData.rg=document.getElementById('f-rg').value; orderData.nt=document.getElementById('f-nt').value.trim(); }
    return ok;
  }
  return true;
}

// ── PAYMENT ──
window.selectPay = function(type){
  selectedPay = type;
  document.querySelectorAll('.pay-opt').forEach(o=>o.classList.toggle('selected', o.dataset.pay===type));
  ['gcash','card','bank'].forEach(t=>{ document.getElementById('extra-'+t).classList.toggle('show', t===type); });
};

window.fmtCard = function(inp){
  let v = inp.value.replace(/\D/g,'').slice(0,16);
  inp.value = v.replace(/(.{4})/g,'$1 ').trim();
};
window.fmtExp = function(inp){
  let v = inp.value.replace(/\D/g,'').slice(0,4);
  if(v.length>2) v = v.slice(0,2)+'/'+v.slice(2);
  inp.value = v;
};

// ── COUPON ──
window.applyCouponCo = function(){
  const code = document.getElementById('co-cpn').value.trim().toUpperCase();
  const res = document.getElementById('co-cpn-res');
  if(COUPONS[code]){ coupon={...COUPONS[code],code}; res.textContent='✓ '+COUPONS[code].label; res.className='co-cpn-res ok'; renderSummary(); }
  else { coupon=null; res.textContent='✗ Invalid code.'; res.className='co-cpn-res fail'; renderSummary(); }
};

// ── PLACE ORDER ──
window.placeOrder = async function(){
  if(!selectedPay){ showToast('Please select a payment method.','err'); return; }
  if(selectedPay==='card'){
    const required=[['f-cn','Card number'],['f-ch','Cardholder'],['f-ce','Expiry'],['f-cv','CVV']];
    let ok=true;
    required.forEach(([id])=>{ const el=document.getElementById(id); const field=el?.closest('.form-field'); if(el&&!el.value.trim()){ field?.classList.add('err'); ok=false; } else field?.classList.remove('err'); });
    if(!ok){ showToast('Fill in your card details.','err'); return; }
  }
  if(selectedPay==='gcash'){
    const gc=document.getElementById('f-gc');
    if(!gc||!gc.value.trim()){ gc?.closest('.form-field')?.classList.add('err'); showToast('Enter your GCash number.','err'); return; }
  }

  const {sub,disc,ship,total}=calcTotals();
  const btn=document.getElementById('btn-place');
  btn.disabled=true; btn.textContent='PLACING ORDER…';

  try {
    const res = await fetch(`${API}?action=place_order`, {
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify({
        customer:{ fn:orderData.fn||'', ln:orderData.ln||'', em:orderData.em||'', ph:orderData.ph||'' },
        address:{ ad:orderData.ad||'', ci:orderData.ci||'', pv:orderData.pv||'', zp:orderData.zp||'', rg:orderData.rg||'', nt:orderData.nt||'' },
        items: cart,
        payment: selectedPay,
        subtotal: sub, discount: disc, shipping: ship, total,
        coupon: coupon ? coupon.code : ''
      })
    });
    const data = await res.json();
    if(data.success){
      placedRef = data.ref;
      localStorage.removeItem('bs_cart');
      localStorage.removeItem('bs_buynow');
      localStorage.removeItem('bs_coupon');
      showSuccess(data.ref, sub, disc, ship, total);
    } else {
      showToast(data.error || 'Failed to place order. Try again.', 'err');
      btn.disabled=false; btn.textContent='PLACE ORDER NOW';
    }
  } catch(e){
    showToast('Network error. Please try again.','err');
    btn.disabled=false; btn.textContent='PLACE ORDER NOW';
  }
};

function showSuccess(ref, sub, disc, ship, total){
  document.getElementById('checkout-page').style.display='none';
  document.getElementById('success-page').style.display='block';
  document.getElementById('suc-ref').textContent = ref;
  document.getElementById('suc-track-btn').onclick = ()=>window.location.href='orders.php?ref='+ref;
  document.getElementById('suc-details').innerHTML=`
    <div class="od-item"><div class="od-lbl">CUSTOMER</div><div class="od-val">${orderData.fn||''} ${orderData.ln||''}</div></div>
    <div class="od-item"><div class="od-lbl">PHONE</div><div class="od-val">${orderData.ph||''}</div></div>
    <div class="od-item"><div class="od-lbl">ADDRESS</div><div class="od-val">${orderData.ad||''}, ${orderData.ci||''}</div></div>
    <div class="od-item"><div class="od-lbl">PAYMENT</div><div class="od-val">${{cod:'Cash on Delivery',gcash:'GCash',card:'Credit/Debit Card',bank:'Bank Transfer'}[selectedPay]}</div></div>
    <div class="od-item"><div class="od-lbl">SUBTOTAL</div><div class="od-val">₱${sub.toLocaleString()}</div></div>
    <div class="od-item"><div class="od-lbl">TOTAL PAID</div><div class="od-val" style="color:#F26522">₱${total.toLocaleString()}</div></div>`;
  window.scrollTo({top:0,behavior:'smooth'});
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