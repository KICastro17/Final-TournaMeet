<?php

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TournaMeet — Official Shop</title>
    <link rel="icon" type="image/png" href="/Tourna/favicon.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:ital,wght@0,400;0,600;0,700;0,800;0,900;1,800&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <style>
    /* ═══ HOME BUTTON ═══ */
    .home-btn {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      background: #F26522;
      color: #fff;
      border: none;
      border-radius: 6px;
      padding: 8px 14px;
      font-family: 'Barlow Condensed', sans-serif;
      font-size: 14px;
      font-weight: 700;
      letter-spacing: 0.06em;
      cursor: pointer;
      text-transform: uppercase;
      transition: background 0.18s, transform 0.12s;
      white-space: nowrap;
    }
    .home-btn:hover {
      background: #d9541a;
      transform: translateY(-1px);
    }
    .home-btn:active {
      transform: translateY(0);
      background: #c04a14;
    }
    .home-btn svg {
      flex-shrink: 0;
    }
    .nav-logo-group {
      display: flex;
      align-items: center;
      gap: 12px;
    }
  </style>
</head>
<body>

<!-- ═══ HEADER ═══ -->
<header class="site-header" id="site-header">
  <div class="nav-inner">
    <div class="nav-logo-group">
      <button class="home-btn" onclick="window.location.href='/Tourna/NewsFeed/newsfeed.php'">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9.5L12 3l9 6.5V20a1 1 0 01-1 1H4a1 1 0 01-1-1V9.5z"/><path d="M9 21V12h6v9"/></svg>
        <span>HOME</span>
      </button>
      
    </div>
    <div class="search-wrap">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#9aa5bf" stroke-width="2.2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
      <input type="text" id="search-input" class="search-input" placeholder="Search products, sports, gear…">
      <div class="search-sug" id="search-sug"></div>
    </div>
    <div class="nav-acts">
      <a href="orders.php" class="nav-btn" title="My Orders" id="orders-btn">
        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
        <span class="nbadge" id="orders-count" style="display:none">0</span>
      </a>
      <button class="nav-btn" id="wishlist-btn" title="Wishlist">
        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        <span class="nbadge" id="wish-count">0</span>
      </button>
      <button class="nav-btn" id="cart-btn" title="Cart">
        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
        <span class="nbadge" id="cart-count">0</span>
      <a href="seller.php">
  <button class="nav-btn" id="sell-btn" title="Sell">
    <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M20.59 13.41 11 3H4v7l9.59 9.59a2 2 0 0 0 2.82 0l4.18-4.18a2 2 0 0 0 0-2.82z"/>
      <circle cx="7.5" cy="7.5" r="1.5"/>
    </svg>
    <span class="nbadge" id="sell-count">0</span>
  </button>
</a>
      </button>
    </div>
  </div>
</header>

<!-- ═══ HERO ═══ -->
<section class="hero" id="hero">
  <div class="hero-bg-shape" aria-hidden="true"></div>
  <div class="hero-content">
    <p class="hero-eye">TournaMeet - OFFICIAL GEAR STORE</p>
    <h1 class="hero-h1">Where Athletes meet.<br><em>Opportunity.</em></h1>
    <p class="hero-sub">Premium equipment for every sport, every athlete, every level.</p>
    <div class="hero-ctas">
      <a href="#products" class="btn-hero-primary">SHOP NOW</a>
      <a href="#categories" class="btn-hero-outline">BROWSE SPORTS</a>
    </div>
  </div>
  <div class="hero-stats">
    <div class="h-stat"><span class="sv" data-t="25">0</span><span class="ss">+</span><span class="sl">Products</span></div>
    <div class="h-stat"><span class="sv" data-t="8">0</span><span class="ss"></span><span class="sl">Sports</span></div>
    <div class="h-stat"><span class="sv" data-t="50">0</span><span class="ss">K+</span><span class="sl">Athletes</span></div>
    <div class="h-stat"><span class="sv" data-t="24">0</span><span class="ss">/7</span><span class="sl">Support</span></div>
  </div>
</section>

<!-- ═══ CATEGORIES ═══ -->
<section class="cat-section" id="categories">
  <div class="inner">
    <div class="sec-label">BROWSE BY SPORT</div>
    <div class="cat-tabs" id="cat-tabs">
      <button class="ctab active" data-cat="All">All</button>
      <button class="ctab" data-cat="Basketball">Basketball</button>
      <button class="ctab" data-cat="Football">Football</button>
      <button class="ctab" data-cat="Volleyball">Volleyball</button>
      <button class="ctab" data-cat="Tennis">Tennis</button>
      <button class="ctab" data-cat="Badminton">Badminton</button>
      <button class="ctab" data-cat="Swimming">Swimming</button>
      <button class="ctab" data-cat="Baseball">Baseball</button>
      <button class="ctab" data-cat="Running">Running</button>
      <button class="ctab" data-cat="Accessories">Accessories</button>
    </div>
  </div>
</section>

<!-- ═══ FILTER BAR ═══ -->
<section class="filter-section">
  <div class="inner filter-bar">
    <div class="price-wrap">
      <div class="price-lbl">PRICE RANGE: <span id="price-disp">₱0 – ₱10,000</span></div>
      <div class="dual-range">
        <div class="rt-bg"></div>
        <div class="rt-fill" id="rt-fill"></div>
        <input type="range" id="rng-min" class="rng" min="0" max="10000" value="0" step="100">
        <input type="range" id="rng-max" class="rng" min="0" max="10000" value="10000" step="100">
      </div>
    </div>
    <div class="fchips" id="fchips"></div>
    <button class="reset-btn" id="reset-btn" style="display:none">✕ Clear All</button>
  </div>
</section>

<!-- ═══ PRODUCTS ═══ -->
<section class="products-section" id="products">
  <div class="inner">
    <div class="ptoolbar">
      <span class="pcount" id="pcount">Loading products…</span>
      <select id="sort-sel" class="sort-sel">
        <option value="featured">Featured</option>
        <option value="price-asc">Price: Low → High</option>
        <option value="price-desc">Price: High → Low</option>
        <option value="rating">Top Rated</option>
        <option value="name">A → Z</option>
        <option value="newest">Newest First</option>
      </select>
    </div>
    <div class="pgrid" id="pgrid">
      <div style="grid-column:1/-1;text-align:center;padding:60px 0;color:#9aa5bf">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#dde4ef" stroke-width="1.5" style="display:block;margin:0 auto 12px"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        Loading products…
      </div>
    </div>
    <div class="no-res hidden" id="no-res">
      <svg width="52" height="52" viewBox="0 0 24 24" fill="none" stroke="#bcc8d8" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
      <p>No products match your filters.</p>
      <button class="btn-sm-outline" onclick="resetAllFilters()">Clear Filters</button>
    </div>
  </div>
</section>

<!-- ═══ PROMO ═══ -->
<section class="promo-section">
  <div class="inner promo-inner">
    <div class="promo-left">
      <div class="promo-tag">LIMITED OFFER</div>
      <h2 class="promo-h">FREE SHIPPING<br>OVER <span class="promo-acc">₱2,000</span></h2>
      <p>Orders above ₱2,000 ship free — or use a coupon at checkout.</p>
    </div>
    <div class="coupon-card">
      <div class="coupon-card-title">AVAILABLE COUPONS</div>
      <div class="coupon-list">
        <div class="cpn-row"><code>BSPORTS10</code><span>10% off</span></div>
        <div class="cpn-row"><code>WELCOME20</code><span>20% off</span></div>
        <div class="cpn-row"><code>FREESHIP</code><span>Free shipping</span></div>
        <div class="cpn-row"><code>CHAMP15</code><span>15% off</span></div>
      </div>
    </div>
  </div>
</section>

<!-- ═══ PRODUCT MODAL ═══ -->
<div class="overlay" id="modal-ov"></div>
<div class="pmodal" id="pmodal" role="dialog" aria-modal="true">
  <button class="mclose" id="mclose" aria-label="Close">
    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M1 1l12 12M13 1L1 13"/></svg>
  </button>
  <div class="modal-tabs" id="modal-tabs">
    <button class="mtab active" data-tab="details">Details</button>
    <button class="mtab" data-tab="reviews">Reviews <span id="rev-badge"></span></button>
    <button class="mtab" data-tab="buy">Where to Buy</button>
  </div>
  <div class="mtab-body" id="tab-details"></div>
  <div class="mtab-body hidden" id="tab-reviews"></div>
  <div class="mtab-body hidden" id="tab-buy"></div>
</div>

<!-- ═══ ADMIN MODAL ═══ -->
<div class="overlay" id="adm-ov"></div>
<div class="adm-modal" id="adm-modal" role="dialog" aria-modal="true">
  <div class="adm-topbar">
    <div class="adm-topbar-left">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#F26522" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 1.69 2.32M21 12h-1M19.07 19.07a10 10 0 0 1-2.32 1.69M12 21v-1M4.93 19.07a10 10 0 0 1-1.69-2.32M3 12H4M4.93 4.93a10 10 0 0 1 2.32-1.69M12 3v1"/></svg>
      <span class="adm-logo">ADMIN PANEL</span>
      <span class="adm-badge">Ball Sports PH</span>
    </div>
    <button class="mclose static" id="adm-close" aria-label="Close">
      <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M1 1l12 12M13 1L1 13"/></svg>
    </button>
  </div>
  <!-- Login -->
  <div id="adm-login">
    <div class="adm-login-card">
      <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="#F26522" stroke-width="1.6" style="margin-bottom:16px"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
      <h3>Admin Access</h3>
      <p>Enter your password to manage the store.</p>
      <label class="adm-field-lbl">PASSWORD</label>
      <input type="password" id="adm-pass" class="adm-inp" placeholder="Enter password">
      <button class="btn-primary full-w" id="adm-login-btn">UNLOCK PANEL</button>
      <div class="adm-hint">Hint: <code>admin123</code></div>
    </div>
  </div>
  <!-- Panel -->
  <div id="adm-panel" class="hidden">
    <div class="adm-sidebar">
      <button class="adm-nav-btn active" data-view="products">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
        Products
      </button>
      <button class="adm-nav-btn" data-view="orders">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        Orders
      </button>
      <button class="adm-nav-btn" data-view="stats">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
        Stats
      </button>
    </div>
    <div class="adm-main">
      <!-- Products -->
      <div id="adm-view-products">
        <div class="adm-view-header">
          <div><h2 class="adm-view-title">Products</h2><p class="adm-view-sub">Manage all shop products.</p></div>
          <button class="btn-primary" id="adm-add-btn">+ Add Product</button>
        </div>
        <input type="text" id="adm-search" class="adm-inp" placeholder="Search products…" style="max-width:280px;margin-bottom:16px">
        <div class="adm-table-wrap">
          <table class="adm-table"><thead><tr><th>Product</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody id="adm-tbody"></tbody></table>
        </div>
      </div>
      <!-- Orders -->
      <div id="adm-view-orders" class="hidden">
        <div class="adm-view-header">
          <div><h2 class="adm-view-title">Orders</h2><p class="adm-view-sub">Manage customer orders and update shipping status.</p></div>
        </div>
        <input type="text" id="adm-order-search" class="adm-inp" placeholder="Search by name or reference…" style="max-width:280px;margin-bottom:16px">
        <div class="adm-table-wrap">
          <table class="adm-table"><thead><tr><th>Reference</th><th>Customer</th><th>Items</th><th>Total</th><th>Payment</th><th>Date</th><th>Status</th><th>Update</th></tr></thead>
          <tbody id="adm-orders-tbody"></tbody></table>
        </div>
      </div>
      <!-- Stats -->
      <div id="adm-view-stats" class="hidden">
        <div class="adm-view-header"><div><h2 class="adm-view-title">Store Stats</h2><p class="adm-view-sub">Overview of your shop.</p></div></div>
        <div class="adm-stats-grid" id="adm-stats-grid"></div>
      </div>
    </div>
  </div>
  <!-- Form -->
  <div id="adm-form" class="hidden">
    <div class="adm-form-topbar">
      <button class="btn-ghost" id="adm-back">← Back</button>
      <h3 id="adm-form-title">Add Product</h3>
    </div>
    <div class="adm-form-body">
      <div class="adm-form-grid">
        <div class="adm-fg"><label>PRODUCT NAME *</label><input type="text" id="af-name" class="adm-inp" placeholder="e.g. Pro Basketball"></div>
        <div class="adm-fg"><label>CATEGORY *</label>
          <select id="af-cat" class="adm-inp">
            <option>Basketball</option><option>Football</option><option>Volleyball</option>
            <option>Tennis</option><option>Badminton</option><option>Swimming</option>
            <option>Baseball</option><option>Running</option><option>Accessories</option>
          </select>
        </div>
        <div class="adm-fg"><label>PRICE (₱) *</label><input type="number" id="af-price" class="adm-inp" placeholder="1500" min="1"></div>
        <div class="adm-fg"><label>BADGE</label>
          <select id="af-badge" class="adm-inp">
            <option value="">None</option><option value="new">New Arrival</option>
            <option value="sale">Sale</option><option value="top">Top Rated</option><option value="best">Best Seller</option>
          </select>
        </div>
        <div class="adm-fg"><label>STOCK AVAILABLE</label><input type="number" id="af-stock" class="adm-inp" placeholder="10" min="0"></div>
        <div class="adm-fg"><label>TOTAL STOCK</label><input type="number" id="af-total" class="adm-inp" placeholder="20" min="1"></div>
        <div class="adm-fg" style="grid-column:1/-1"><label>DESCRIPTION *</label><textarea id="af-desc" class="adm-inp" rows="3" placeholder="Describe the product…"></textarea></div>
        <div class="adm-fg"><label>SHOPEE LINK</label><input type="url" id="af-shopee" class="adm-inp" placeholder="https://shopee.ph/…"></div>
        <div class="adm-fg"><label>LAZADA LINK</label><input type="url" id="af-lazada" class="adm-inp" placeholder="https://lazada.com.ph/…"></div>
        <div class="adm-fg"><label>OTHER STORE NAME</label><input type="text" id="af-other-name" class="adm-inp" placeholder="e.g. Nike Official"></div>
        <div class="adm-fg"><label>OTHER STORE LINK</label><input type="url" id="af-other" class="adm-inp" placeholder="https://…"></div>
      </div>
      <div class="adm-form-actions">
        <button class="btn-primary" id="adm-save">SAVE PRODUCT</button>
        <button class="btn-ghost" id="adm-cancel">CANCEL</button>
      </div>
    </div>
  </div>
</div>

<!-- ═══ CART DRAWER ═══ -->
<div class="drw-ov" id="drw-ov"></div>
<aside class="cart-drw" id="cart-drw">
  <div class="drw-hd">
    <h3 class="drw-title">
      <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
      YOUR CART
    </h3>
    <button class="drw-close" id="drw-close">
      <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M1 1l12 12M13 1L1 13"/></svg>
    </button>
  </div>
  <div class="drw-items" id="drw-items"><p class="empty-msg">Your cart is empty.</p></div>
  <div class="drw-foot">
    <div class="cpn-row-inp">
      <input type="text" id="cpn-inp" class="cpn-inp" placeholder="Coupon code…">
      <button class="btn-apply" id="apply-cpn">APPLY</button>
    </div>
    <div class="cpn-res" id="cpn-res"></div>
    <div class="sum-row"><span>Subtotal</span><span id="cart-sub">₱0</span></div>
    <div class="sum-row"><span>Discount</span><span id="cart-disc" class="green">−₱0</span></div>
    <div class="sum-row"><span>Shipping</span><span id="cart-ship">₱150</span></div>
    <div class="sum-total"><span>TOTAL</span><span id="cart-total">₱150</span></div>
    <button class="btn-checkout" id="checkout-btn">PROCEED TO CHECKOUT</button>
    <button class="btn-continue" id="cont-btn">Continue Shopping</button>
  </div>
</aside>

<!-- ═══ WISHLIST DRAWER ═══ -->
<aside class="wish-drw" id="wish-drw">
  <div class="drw-hd">
    <h3 class="drw-title">
      <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
      WISHLIST
    </h3>
    <button class="drw-close" id="wish-close">
      <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M1 1l12 12M13 1L1 13"/></svg>
    </button>
  </div>
  <div class="drw-items" id="wish-items"><p class="empty-msg">No saved items yet.</p></div>
</aside>

<!-- ═══ TOAST ═══ -->
<div class="toast-wrap" id="toast-wrap"></div>

<!-- ═══ FOOTER ═══ -->
<footer class="site-footer">
  <div class="inner foot-grid">
    <div class="foot-brand">
      <div class="foot-logo">TournaMeet</div>
      <p>Premium gear for every athlete. From courts to fields, we've got you covered.</p>
      <div class="foot-soc">
        <a href="https://www.facebook.com/ballsportsph" target="_blank" rel="noopener" class="soc-btn">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
          Facebook
        </a>
        <a href="https://www.instagram.com/ballsportsph" target="_blank" rel="noopener" class="soc-btn">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
          Instagram
        </a>
        <a href="https://twitter.com/ballsportsph" target="_blank" rel="noopener" class="soc-btn">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"/></svg>
          Twitter
        </a>
      </div>
    </div>
    <div class="foot-col">
      <h4>SPORTS</h4>
      <a href="index.php?cat=Basketball">Basketball</a>
      <a href="index.php?cat=Football">Football</a>
      <a href="index.php?cat=Volleyball">Volleyball</a>
      <a href="index.php?cat=Tennis">Tennis</a>
      <a href="index.php?cat=Badminton">Badminton</a>
      <a href="index.php?cat=Swimming">Swimming</a>
    </div>
    <div class="foot-col">
      <h4>SHOP</h4>
      <a href="index.php?sort=newest">New Arrivals</a>
      <a href="index.php?sort=rating">Best Sellers</a>
      <a href="index.php?cat=Accessories">Accessories</a>
      <a href="orders.php">My Orders</a>
      <a href="index.php">All Products</a>
    </div>
    <div class="foot-col">
      <h4>HELP</h4>
      <a href="mailto:support@ballsportsph.com">Contact Us</a>
      <a href="mailto:returns@ballsportsph.com">Returns</a>
      <a href="orders.php">Track My Order</a>
      <a href="#" id="adm-trigger">Admin Panel</a>
    </div>
  </div>
  <div class="foot-btm">
    <span>© <?= date('Y') ?> TournaMeet. All rights reserved.</span>
  </div>
</footer>

<script src="script.js"></script>
</body>
</html>