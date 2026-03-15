

const API = 'api.php';

const ICONS = {
  Basketball:`<svg viewBox="0 0 60 60" fill="none" stroke="#F26522" stroke-width="2.2"><circle cx="30" cy="30" r="22"/><path d="M30 8 Q38 16 38 30 Q38 44 30 52" fill="none"/><path d="M30 8 Q22 16 22 30 Q22 44 30 52" fill="none"/><line x1="8" y1="30" x2="52" y2="30"/></svg>`,
  Football:  `<svg viewBox="0 0 60 60" fill="none" stroke="#F26522" stroke-width="2.2"><circle cx="30" cy="30" r="22"/><polygon points="30,20 37,26 34,35 26,35 23,26" fill="rgba(242,101,34,.12)" stroke="#F26522" stroke-width="1.8"/><line x1="30" y1="8" x2="30" y2="20"/><line x1="44.5" y1="14.5" x2="37" y2="26"/><line x1="30" y1="52" x2="30" y2="40"/></svg>`,
  Volleyball:`<svg viewBox="0 0 60 60" fill="none" stroke="#F26522" stroke-width="2.2"><circle cx="30" cy="30" r="22"/><path d="M8 30 Q15 12 30 12 Q45 12 52 30" fill="none"/><path d="M30 8 Q44 16 47 30 Q50 44 30 52" fill="none"/><path d="M30 8 Q16 16 13 30 Q10 44 30 52" fill="none"/></svg>`,
  Tennis:    `<svg viewBox="0 0 60 60" fill="none" stroke="#F26522" stroke-width="2.2"><circle cx="26" cy="34" r="18"/><path d="M14 28 Q18 20 26 20 Q34 20 38 28 Q34 36 26 36 Q18 36 14 28" fill="none"/><line x1="38" y1="22" x2="52" y2="10" stroke-width="3"/><rect x="46" y="4" width="10" height="10" rx="2" fill="rgba(242,101,34,.15)" stroke="#F26522" stroke-width="1.8"/></svg>`,
  Badminton: `<svg viewBox="0 0 60 60" fill="none" stroke="#F26522" stroke-width="2.2"><ellipse cx="30" cy="20" rx="13" ry="15"/><line x1="22" y1="14" x2="38" y2="14"/><line x1="20" y1="20" x2="40" y2="20"/><line x1="22" y1="26" x2="38" y2="26"/><line x1="30" y1="35" x2="30" y2="54"/><circle cx="30" cy="10" r="4" fill="rgba(242,101,34,.15)"/></svg>`,
  Swimming:  `<svg viewBox="0 0 60 60" fill="none" stroke="#F26522" stroke-width="2.2"><circle cx="40" cy="14" r="5"/><path d="M35 14 L20 22 L28 28"/><path d="M28 28 L24 40"/><path d="M8 44 Q16 38 24 44 Q32 50 40 44 Q48 38 52 44"/><path d="M8 52 Q16 46 24 52 Q32 58 40 52 Q48 46 52 52"/></svg>`,
  Baseball:  `<svg viewBox="0 0 60 60" fill="none" stroke="#F26522" stroke-width="2.2"><circle cx="30" cy="30" r="21"/><path d="M20 10 Q24 20 24 30 Q24 40 20 50" fill="none"/><path d="M40 10 Q36 20 36 30 Q36 40 40 50" fill="none"/><line x1="20" y1="20" x2="24" y2="22"/><line x1="20" y1="30" x2="24" y2="30"/><line x1="20" y1="40" x2="24" y2="38"/><line x1="40" y1="20" x2="36" y2="22"/><line x1="40" y1="30" x2="36" y2="30"/><line x1="40" y1="40" x2="36" y2="38"/></svg>`,
  Running:   `<svg viewBox="0 0 60 60" fill="none" stroke="#F26522" stroke-width="2.2"><circle cx="38" cy="11" r="5"/><path d="M34 17 L28 28 L16 30"/><path d="M28 28 L26 40"/><path d="M26 40 L18 52"/><path d="M26 40 L36 50"/><path d="M34 17 L46 22 L50 15"/></svg>`,
  Accessories:`<svg viewBox="0 0 60 60" fill="none" stroke="#F26522" stroke-width="2.2"><rect x="12" y="22" width="36" height="26" rx="4"/><path d="M21 22 V17 Q21 12 27 12 H33 Q39 12 39 17 V22"/><line x1="12" y1="33" x2="48" y2="33"/><circle cx="30" cy="40" r="3.5"/></svg>`,
  Default:   `<svg viewBox="0 0 60 60" fill="none" stroke="#F26522" stroke-width="2.2"><circle cx="30" cy="30" r="20"/><line x1="30" y1="20" x2="30" y2="40"/><line x1="20" y1="30" x2="40" y2="30"/></svg>`
};
function getIcon(cat){ return ICONS[cat] || ICONS.Default; }

const COUPONS = {
  BSPORTS10:{type:'percent',value:10,label:'10% off your order!'},
  WELCOME20:{type:'percent',value:20,label:'20% off your order!'},
  FREESHIP: {type:'ship',  value:0, label:'Free shipping!'},
  CHAMP15:  {type:'percent',value:15,label:'15% off your order!'},
};

const ORDER_STATUSES = ['Order Placed','Confirmed','Packing','Shipped','Delivered','Cancelled'];

// ── STATE ──
let PRODUCTS  = [];
let cart      = JSON.parse(localStorage.getItem('bs_cart')  || '[]');
let wishlist  = JSON.parse(localStorage.getItem('bs_wish')  || '[]');
let coupon    = null;
let priceMin  = 0, priceMax = 10000;
let activeCat = 'All', searchQ = '', sortBy = 'featured';
let modalPid  = null, modalQty = 1;
let adminIn   = false, adminEditId = null;
let adminToken = '';

// ════════════════════════════════════════════
//  INIT
// ════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', () => {
  // Handle URL filters from footer links
  const urlParams = new URLSearchParams(window.location.search);
  const urlCat  = urlParams.get('cat');
  const urlSort = urlParams.get('sort');
  if (urlSort) { sortBy = urlSort; const sel = document.getElementById('sort-sel'); if (sel) sel.value = urlSort; }
  if (urlCat)  { activeCat = urlCat; }

  updateCartUI(); updateWishUI(); updateOrdersBadge();
  setupSearch(); setupCats(); setupSort(); setupPrice();
  setupCartDrw(); setupWishDrw();
  setupModal(); setupAdmin();
  animStats(); setupScrollHeader();

  document.getElementById('reset-btn')?.addEventListener('click', resetAllFilters);
  document.getElementById('apply-cpn')?.addEventListener('click', applyCoupon);
  document.getElementById('cpn-inp')?.addEventListener('keydown', e => { if (e.key === 'Enter') applyCoupon(); });

  // Load products from API
  loadProducts();

  // Apply URL cat tab highlight after DOM ready
  if (urlCat) {
    document.querySelectorAll('.ctab').forEach(t => t.classList.toggle('active', t.dataset.cat === urlCat));
    setTimeout(() => document.getElementById('products')?.scrollIntoView({ behavior: 'smooth', block: 'start' }), 300);
  }
});

// ── LOAD PRODUCTS FROM DB ──
async function loadProducts() {
  try {
    document.getElementById('pgrid').innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:60px 0;color:#9aa5bf"><svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#F26522" stroke-width="2" style="animation:spin 1s linear infinite;display:block;margin:0 auto 10px"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>Loading products…</div>';
    const params = new URLSearchParams();
    if (activeCat !== 'All') params.set('cat', activeCat);
    if (searchQ) params.set('q', searchQ);
    const res  = await fetch(`${API}?action=products&${params}`);
    PRODUCTS   = await res.json();
    renderProducts();
  } catch (e) {
    document.getElementById('pgrid').innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:40px;color:#E74C3C">Failed to load products. Is the API running?</div>';
  }
}

// ── ORDER BADGE ──
function updateOrdersBadge() {
  const badge = document.getElementById('orders-count');
  if (!badge) return;
  // Just keep hidden — badge count comes from DB not localStorage in PHP version
  badge.style.display = 'none';
}

// ══════════════════════════════════════════
//  RENDER PRODUCTS
// ══════════════════════════════════════════
function renderProducts() {
  let list = [...PRODUCTS];
  if (activeCat !== 'All') list = list.filter(p => p.cat === activeCat);
  if (searchQ) {
    const q = searchQ.toLowerCase();
    list = list.filter(p => p.name.toLowerCase().includes(q) || p.cat.toLowerCase().includes(q) || (p.description||'').toLowerCase().includes(q));
  }
  list = list.filter(p => p.price >= priceMin && p.price <= priceMax);
  if (sortBy === 'price-asc')  list.sort((a,b) => a.price - b.price);
  if (sortBy === 'price-desc') list.sort((a,b) => b.price - a.price);
  if (sortBy === 'rating')     list.sort((a,b) => (b.avg_rating||b.rating) - (a.avg_rating||a.rating));
  if (sortBy === 'name')       list.sort((a,b) => a.name.localeCompare(b.name));
  if (sortBy === 'newest')     list.sort((a,b) => (b.is_new ? 1 : 0) - (a.is_new ? 1 : 0));

  const grid  = document.getElementById('pgrid');
  const noRes = document.getElementById('no-res');

  document.getElementById('pcount').textContent = list.length
    ? `Showing ${list.length} product${list.length !== 1 ? 's' : ''}${activeCat !== 'All' ? ' in ' + activeCat : ''}`
    : 'No products found';

  if (!list.length) { grid.innerHTML = ''; noRes.classList.remove('hidden'); return; }
  noRes.classList.add('hidden');
  grid.innerHTML = list.map(cardHTML).join('');
  document.querySelectorAll('.pcard').forEach((el,i) => {
    el.style.transitionDelay = (i % 6) * 55 + 'ms';
    cardObs.observe(el);
  });
  animStockBars();
  updateChips();
}

function cardHTML(p) {
  const inW = wishlist.some(w => w.id === p.id);
  const bc  = { best:'b-best', new:'b-new', sale:'b-sale', top:'b-top' }[p.badge] || '';
  const avg = parseFloat(p.avg_rating || p.rating || 5);
  const cnt = parseInt(p.total_reviews || p.review_count || 0);
  const stockPct = Math.round((p.stock / p.total_stock) * 100);
  let stockLabel = '';
  if      (p.stock === 0)  stockLabel = '<span style="color:#E74C3C;font-size:10px;font-weight:700">Out of stock</span>';
  else if (p.stock <= 3)   stockLabel = `<span style="color:#E74C3C;font-size:10px;font-weight:700">⚠ Only ${p.stock} left!</span>`;
  else if (p.stock <= 8)   stockLabel = '<span style="color:#F39C12;font-size:10px;font-weight:600">Low stock</span>';
  else                     stockLabel = '<span style="color:#27AE60;font-size:10px;font-weight:600">In stock</span>';
  const soldOut = p.stock === 0;

  return `<div class="pcard" data-id="${p.id}">
    ${p.badge ? `<span class="pc-badge ${bc}">${p.badge_text}</span>` : ''}
    <button class="pc-wish ${inW ? 'on' : ''}" onclick="toggleWish(event,${p.id})">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="${inW ? '#E74C3C' : 'none'}" stroke="${inW ? '#E74C3C' : '#9aa5bf'}" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
    </button>
    <div class="pc-img">${getIcon(p.cat)}</div>
    <div class="pc-body">
      <div class="pc-cat">${p.cat}</div>
      <div class="pc-name">${p.name}</div>
      <div class="pc-desc">${p.description || ''}</div>
      <div class="pc-rating"><span class="pc-stars">${renderStars(avg)}</span><span class="pc-rcount">${avg.toFixed(1)} (${cnt})</span></div>
      <div class="pc-stock"><div class="pc-stock-lbl">${stockLabel}</div><div class="pc-stock-track"><div class="pc-stock-fill" data-w="${stockPct}"></div></div></div>
      <div class="pc-price-row"><span class="pc-price">₱${Number(p.price).toLocaleString()}</span>${p.badge ? `<span class="pc-badge-inline">${p.badge_text}</span>` : ''}</div>
      <div class="pc-btns">
        <div class="pc-btns-top">
          <button class="pc-add" onclick="quickAdd(event,${p.id})" ${soldOut ? 'disabled style="opacity:.5;cursor:not-allowed"' : ''}>
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
            ${soldOut ? 'SOLD OUT' : 'ADD TO CART'}
          </button>
          <button class="pc-view" onclick="openModal(${p.id})">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            DETAILS
          </button>
        </div>
        <button class="pc-buynow" onclick="buyNow(event,${p.id})" ${soldOut ? 'disabled style="opacity:.5;cursor:not-allowed"' : ''}>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          ${soldOut ? 'OUT OF STOCK' : 'BUY NOW — QUICK CHECKOUT'}
        </button>
      </div>
    </div>
  </div>`;
}

function renderStars(avg) {
  const f = Math.floor(avg), h = avg - f >= 0.4;
  return Array.from({ length: 5 }, (_, i) => i < f ? '★' : (i === f && h ? '⯨' : '☆')).join('');
}

const cardObs = new IntersectionObserver(entries => {
  entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('reveal'); cardObs.unobserve(e.target); } });
}, { threshold: 0.07 });

function animStockBars() {
  const o = new IntersectionObserver(entries => {
    entries.forEach(e => { if (e.isIntersecting) { e.target.style.width = e.target.dataset.w + '%'; o.unobserve(e.target); } });
  }, { threshold: 0.3 });
  document.querySelectorAll('.pc-stock-fill').forEach(b => o.observe(b));
}

// ── SEARCH ──
function setupSearch() {
  const inp = document.getElementById('search-input');
  const sug = document.getElementById('search-sug');
  if (!inp) return;
  inp.addEventListener('input', () => {
    searchQ = inp.value.trim().toLowerCase();
    renderProducts();
    const q = inp.value.trim().toLowerCase();
    if (q.length < 2) { sug.classList.remove('show'); return; }
    const m = PRODUCTS.filter(p => p.name.toLowerCase().includes(q) || p.cat.toLowerCase().includes(q)).slice(0, 7);
    if (!m.length) { sug.classList.remove('show'); return; }
    sug.innerHTML = m.map(p => `<div class="sug-it" onclick="pickSug('${p.name.replace(/'/g, "\\'")}')"><div class="sug-icon">${getIcon(p.cat)}</div><span>${p.name}</span><span class="sug-cat">${p.cat}</span></div>`).join('');
    sug.classList.add('show');
  });
  document.addEventListener('click', e => { if (!e.target.closest('.search-wrap')) sug.classList.remove('show'); });
}
window.pickSug = function(name) { document.getElementById('search-input').value = name; document.getElementById('search-sug').classList.remove('show'); searchQ = name.toLowerCase(); renderProducts(); };

function setupCats() {
  document.getElementById('cat-tabs')?.addEventListener('click', e => {
    const t = e.target.closest('.ctab'); if (!t) return;
    activeCat = t.dataset.cat;
    document.querySelectorAll('.ctab').forEach(x => x.classList.toggle('active', x.dataset.cat === activeCat));
    renderProducts();
    document.getElementById('products')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
  });
}
function setupSort() { document.getElementById('sort-sel')?.addEventListener('change', e => { sortBy = e.target.value; renderProducts(); }); }
function setupPrice() {
  const lo = document.getElementById('rng-min'), hi = document.getElementById('rng-max');
  if (!lo) return;
  function upd() {
    let a = parseInt(lo.value), b = parseInt(hi.value);
    if (a > b - 100) { a = b - 100; lo.value = a; }
    priceMin = a; priceMax = b;
    document.getElementById('price-disp').textContent = `₱${a.toLocaleString()} – ₱${b.toLocaleString()}`;
    const pct = v => (v / 10000) * 100;
    const fill = document.getElementById('rt-fill');
    fill.style.left = pct(a) + '%'; fill.style.width = (pct(b) - pct(a)) + '%';
    renderProducts();
  }
  lo.addEventListener('input', upd); hi.addEventListener('input', upd); upd();
}
function updateChips() {
  const el = document.getElementById('fchips'), rb = document.getElementById('reset-btn'), chips = [];
  if (activeCat !== 'All') chips.push(`<div class="chip">${activeCat}<button onclick="clrCat()">✕</button></div>`);
  if (searchQ) chips.push(`<div class="chip">Search: "${searchQ}"<button onclick="clrSrch()">✕</button></div>`);
  if (priceMin > 0 || priceMax < 10000) chips.push(`<div class="chip">₱${priceMin.toLocaleString()}–₱${priceMax.toLocaleString()}<button onclick="clrPrice()">✕</button></div>`);
  el.innerHTML = chips.join(''); rb.style.display = chips.length ? 'block' : 'none';
}
window.clrCat   = () => { activeCat = 'All'; document.querySelectorAll('.ctab').forEach(t => t.classList.toggle('active', t.dataset.cat === 'All')); renderProducts(); };
window.clrSrch  = () => { searchQ = ''; document.getElementById('search-input').value = ''; renderProducts(); };
window.clrPrice = () => { priceMin = 0; priceMax = 10000; document.getElementById('rng-min').value = 0; document.getElementById('rng-max').value = 10000; document.getElementById('price-disp').textContent = '₱0 – ₱10,000'; document.getElementById('rt-fill').style.cssText = 'left:0%;width:100%'; renderProducts(); };
window.resetAllFilters = () => { clrCat(); clrSrch(); clrPrice(); };

// ── CART ──
window.quickAdd = function(e, id) { e.stopPropagation(); addToCart(id, 1); };

window.buyNow = function(e, id) {
  if (e) e.stopPropagation();
  const p = PRODUCTS.find(x => x.id === id);
  if (!p || p.stock === 0) { showToast('Sorry, this item is out of stock.', 'err'); return; }
  localStorage.setItem('bs_buynow', JSON.stringify([{ id: p.id, name: p.name, price: p.price, cat: p.cat, qty: 1 }]));
  window.location.href = 'checkout.php?mode=buynow';
};

function addToCart(id, qty = 1) {
  const p = PRODUCTS.find(x => x.id === id);
  if (!p || p.stock === 0) { showToast('Sorry, this item is out of stock.', 'err'); return; }
  const ex = cart.find(i => i.id === id);
  if (ex && ex.qty + qty > p.stock) { showToast(`Only ${p.stock} in stock!`, 'err'); return; }
  if (ex) ex.qty += qty;
  else cart.push({ id, name: p.name, price: p.price, cat: p.cat, qty });
  saveCart(); updateCartUI(); showToast(`Added ${p.name} to cart`); animBadge('cart-count');
}

function calcTotals() {
  const sub = cart.reduce((s, i) => s + i.price * i.qty, 0);
  let disc = 0;
  if (coupon) { if (coupon.type === 'percent') disc = Math.round(sub * coupon.value / 100); if (coupon.type === 'ship') disc = 150; }
  const ship = (coupon && coupon.type === 'ship') ? 0 : (sub > 2000 ? 0 : 150);
  return { sub, disc, ship, total: sub - disc + ship };
}

function updateCartUI() {
  const count = cart.reduce((s, i) => s + i.qty, 0);
  const { sub, disc, ship, total } = calcTotals();
  document.getElementById('cart-count').textContent = count;
  document.getElementById('cart-sub').textContent   = '₱' + sub.toLocaleString();
  document.getElementById('cart-disc').textContent  = '−₱' + disc.toLocaleString();
  document.getElementById('cart-ship').textContent  = ship === 0 ? '🎉 FREE' : '₱' + ship;
  document.getElementById('cart-total').textContent = '₱' + total.toLocaleString();
  const el = document.getElementById('drw-items');
  if (!cart.length) { el.innerHTML = '<p class="empty-msg">Your cart is empty.</p>'; return; }
  el.innerHTML = cart.map(item => `<div class="ci">
    <div class="ci-img">${getIcon(item.cat || 'Accessories')}</div>
    <div class="ci-body">
      <div class="ci-nm">${item.name}</div>
      <div class="ci-pr">₱${(item.price * item.qty).toLocaleString()}</div>
      <div class="ci-ctrl">
        <button class="cibtn" onclick="cQty(${item.id},-1)">−</button>
        <span class="ciqty">${item.qty}</span>
        <button class="cibtn" onclick="cQty(${item.id},1)">+</button>
        <button class="cirm" onclick="cRm(${item.id})"><svg width="13" height="13" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 1l12 12M13 1L1 13"/></svg></button>
      </div>
    </div>
  </div>`).join('');
}

window.cQty = (id, d) => {
  const i = cart.find(x => x.id === id); if (!i) return;
  const p = PRODUCTS.find(x => x.id === id);
  if (d > 0 && p && i.qty >= p.stock) { showToast(`Only ${p.stock} in stock!`, 'err'); return; }
  i.qty += d; if (i.qty <= 0) cart = cart.filter(x => x.id !== id);
  saveCart(); updateCartUI();
};
window.cRm = (id) => { const p = cart.find(x => x.id === id); cart = cart.filter(x => x.id !== id); saveCart(); updateCartUI(); if (p) showToast(`Removed ${p.name}`, 'err'); };

function applyCoupon() {
  const code = document.getElementById('cpn-inp').value.trim().toUpperCase();
  const res  = document.getElementById('cpn-res');
  if (COUPONS[code]) { coupon = { ...COUPONS[code], code }; res.textContent = '✓ ' + COUPONS[code].label; res.className = 'cpn-res ok'; showToast('Coupon applied!', 'ok'); localStorage.setItem('bs_coupon', JSON.stringify(coupon)); updateCartUI(); }
  else { coupon = null; res.textContent = '✗ Invalid coupon code.'; res.className = 'cpn-res fail'; localStorage.removeItem('bs_coupon'); updateCartUI(); }
}

// ── WISHLIST ──
function updateWishUI() {
  document.getElementById('wish-count').textContent = wishlist.length;
  const el = document.getElementById('wish-items');
  if (!wishlist.length) { el.innerHTML = '<p class="empty-msg">No saved items yet.</p>'; return; }
  el.innerHTML = wishlist.map(p => `<div class="wi">
    <div class="wi-img">${getIcon(p.cat || 'Accessories')}</div>
    <div style="flex:1"><div class="wi-nm">${p.name}</div><div class="wi-pr">₱${p.price.toLocaleString()}</div></div>
    <button class="wi-add" onclick="addToCart(${p.id},1)">ADD</button>
    <button class="wi-rm" onclick="wRm(${p.id})"><svg width="13" height="13" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 1l12 12M13 1L1 13"/></svg></button>
  </div>`).join('');
}

window.toggleWish = function(e, id) {
  if (e) e.stopPropagation();
  const p = PRODUCTS.find(x => x.id === id); if (!p) return;
  const inW = wishlist.some(w => w.id === id);
  if (inW) { wishlist = wishlist.filter(w => w.id !== id); showToast('Removed from wishlist', 'wsh'); }
  else { wishlist.push({ id, name: p.name, price: p.price, cat: p.cat }); showToast(`Saved: ${p.name}`, 'wsh'); }
  saveWish(); updateWishUI(); animBadge('wish-count');
  document.querySelectorAll(`.pcard[data-id="${id}"] .pc-wish`).forEach(btn => {
    const nw = wishlist.some(w => w.id === id); btn.classList.toggle('on', nw);
    btn.innerHTML = `<svg width="15" height="15" viewBox="0 0 24 24" fill="${nw ? '#E74C3C' : 'none'}" stroke="${nw ? '#E74C3C' : '#9aa5bf'}" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>`;
  });
  const mb = document.getElementById('m-wish'); if (mb && modalPid === id) { const nw = wishlist.some(w => w.id === id); mb.classList.toggle('on', nw); }
};
window.wRm = (id) => { wishlist = wishlist.filter(w => w.id !== id); saveWish(); updateWishUI(); };

// ── DRAWERS ──
function setupCartDrw() {
  document.getElementById('cart-btn')?.addEventListener('click', () => openDrw('cart'));
  document.getElementById('drw-close')?.addEventListener('click', () => closeDrw('cart'));
  document.getElementById('drw-ov')?.addEventListener('click', () => { closeDrw('cart'); closeDrw('wish'); });
  document.getElementById('cont-btn')?.addEventListener('click', () => closeDrw('cart'));
  document.getElementById('checkout-btn')?.addEventListener('click', goToCheckout);
}
function setupWishDrw() {
  document.getElementById('wishlist-btn')?.addEventListener('click', () => openDrw('wish'));
  document.getElementById('wish-close')?.addEventListener('click', () => closeDrw('wish'));
}
function openDrw(t) {
  if (t === 'cart') document.getElementById('cart-drw').classList.add('open');
  else document.getElementById('wish-drw').classList.add('open');
  document.getElementById('drw-ov').classList.add('on'); document.body.style.overflow = 'hidden';
}
function closeDrw(t) {
  if (t === 'cart') document.getElementById('cart-drw').classList.remove('open');
  else document.getElementById('wish-drw').classList.remove('open');
  if (!document.getElementById('cart-drw').classList.contains('open') && !document.getElementById('wish-drw').classList.contains('open')) {
    document.getElementById('drw-ov').classList.remove('on'); document.body.style.overflow = '';
  }
}
function goToCheckout() {
  if (!cart.length) { showToast('Your cart is empty!', 'err'); return; }
  if (coupon) localStorage.setItem('bs_coupon', JSON.stringify(coupon));
  window.location.href = 'checkout.php';
}

// ── MODAL ──
function setupModal() {
  document.getElementById('mclose')?.addEventListener('click', closeModal);
  document.getElementById('modal-ov')?.addEventListener('click', closeModal);
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
  document.getElementById('modal-tabs')?.addEventListener('click', e => {
    const t = e.target.closest('.mtab'); if (!t) return;
    document.querySelectorAll('.mtab').forEach(x => x.classList.remove('active')); t.classList.add('active');
    document.querySelectorAll('.mtab-body').forEach(c => c.classList.add('hidden'));
    document.getElementById('tab-' + t.dataset.tab).classList.remove('hidden');
  });
}

window.openModal = async function(id) {
  const p = PRODUCTS.find(x => x.id === id); if (!p) return;
  modalPid = id; modalQty = 1;
  const inW = wishlist.some(w => w.id === id);
  const avg = parseFloat(p.avg_rating || p.rating || 5);
  const cnt = parseInt(p.total_reviews || p.review_count || 0);
  const specs = typeof p.specs === 'object' ? p.specs : (JSON.parse(p.specs || '{}'));
  const links = Array.isArray(p.links) ? p.links : (JSON.parse(p.links || '[]'));

  // Details tab
  document.getElementById('tab-details').innerHTML = `
    <div class="md-grid">
      <div class="md-img">${getIcon(p.cat)}</div>
      <div>
        <div class="md-cat">${p.cat}</div>
        <h2 class="md-name">${p.name}</h2>
        <p class="md-desc">${p.description || ''}</p>
        <div class="md-specs">
          <div class="sbox"><span class="slbl">PRICE</span><span class="sval or">₱${Number(p.price).toLocaleString()}</span></div>
          <div class="sbox"><span class="slbl">RATING</span><span class="sval">⭐ ${avg.toFixed(1)}</span></div>
          <div class="sbox"><span class="slbl">STOCK</span><span class="sval">${p.stock}/${p.total_stock}</span></div>
          <div class="sbox"><span class="slbl">CATEGORY</span><span class="sval">${p.cat}</span></div>
          ${Object.entries(specs).map(([k,v]) => `<div class="sbox"><span class="slbl">${k.toUpperCase()}</span><span class="sval">${v}</span></div>`).join('')}
        </div>
        <div class="md-qty"><span class="qty-lbl">QTY</span>
          <div class="qty-ctrl">
            <button class="qb" id="qb-min">−</button>
            <span class="qn" id="qb-num">1</span>
            <button class="qb" id="qb-pls">+</button>
          </div>
        </div>
        <div class="md-acts">
          <button class="btn-primary" style="flex:1" id="m-add">Add to Cart</button>
          <button class="md-wish ${inW ? 'on' : ''}" id="m-wish">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="${inW ? '#E74C3C' : 'none'}" stroke="${inW ? '#E74C3C' : '#9aa5bf'}" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
          </button>
        </div>
      </div>
    </div>`;

  document.getElementById('qb-min').onclick = () => { if (modalQty > 1) { modalQty--; document.getElementById('qb-num').textContent = modalQty; } };
  document.getElementById('qb-pls').onclick = () => { if (modalQty < p.stock) { modalQty++; document.getElementById('qb-num').textContent = modalQty; } };
  document.getElementById('m-add').onclick   = () => { addToCart(id, modalQty); closeModal(); };
  document.getElementById('m-wish').onclick  = () => toggleWish(null, id);

  // Where to buy tab
  document.getElementById('tab-buy').innerHTML = `
    <p class="buy-intro">Find <strong>${p.name}</strong> on your preferred platform:</p>
    <div class="shop-links">
      ${links.map(l => `
        <a href="${l.url}" target="_blank" rel="noopener noreferrer" class="shop-card">
          <div class="shop-ico ${l.type || 'other'}">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/></svg>
          </div>
          <div>
            <div class="shop-nm">${l.name}</div>
            <div class="shop-ds">${l.type === 'shopee' ? 'Shopee Philippines' : l.type === 'lazada' ? 'Lazada Philippines' : 'Official Store'}</div>
          </div>
          <span class="shop-ar">→</span>
        </a>`).join('')}
      ${links.length === 0 ? '<p style="color:#9aa5bf;text-align:center;padding:24px">No store links available for this product.</p>' : ''}
    </div>`;

  // Load reviews from API
  document.getElementById('tab-reviews').innerHTML = '<div style="text-align:center;padding:32px;color:#9aa5bf">Loading reviews…</div>';
  document.getElementById('rev-badge').textContent = '';

  document.querySelectorAll('.mtab').forEach(x => x.classList.toggle('active', x.dataset.tab === 'details'));
  document.querySelectorAll('.mtab-body').forEach(c => c.classList.add('hidden'));
  document.getElementById('tab-details').classList.remove('hidden');
  document.getElementById('modal-ov').classList.add('on');
  document.getElementById('pmodal').classList.add('on');
  document.body.style.overflow = 'hidden';

  // Fetch reviews async
  loadReviews(id, avg, cnt);
};

async function loadReviews(pid, baseAvg, baseCount) {
  try {
    const res  = await fetch(`${API}?action=reviews&product_id=${pid}`);
    const revs = await res.json();
    renderRevTab(pid, revs, baseAvg, baseCount);
    document.getElementById('rev-badge').textContent = revs.length ? `(${revs.length})` : '';
  } catch(e) {
    document.getElementById('tab-reviews').innerHTML = '<p style="color:#E74C3C;text-align:center;padding:24px">Failed to load reviews.</p>';
  }
}

function renderRevTab(pid, revs, baseAvg, baseCount) {
  const total  = baseCount + revs.length;
  const sumNew = revs.reduce((s, r) => s + r.rating, 0);
  const avg    = total > 0 ? (baseAvg * baseCount + sumNew) / total : baseAvg;
  const stH    = n => Array.from({ length: 5 }, (_, i) => `<span style="color:${i < n ? '#F39C12' : '#d0d8e8'}">${i < n ? '★' : '☆'}</span>`).join('');
  const brk    = [5,4,3,2,1].map(s => { const c = revs.filter(r => r.rating === s).length; const pct = revs.length ? Math.round(c / revs.length * 100) : 0; return `<div class="rv-bar-row"><span>${s}★</span><div class="rv-track"><div class="rv-fill" style="width:${pct}%"></div></div><span>${c}</span></div>`; }).join('');

  document.getElementById('tab-reviews').innerHTML = `
    <div class="rv-sum">
      <div><div class="rv-big">${avg.toFixed(1)}</div><div class="rv-stars">${stH(Math.round(avg))}</div><div class="rv-info">${total} reviews</div></div>
      <div class="rv-bars">${brk}</div>
    </div>
    <div class="rv-list">${revs.length
      ? revs.map(r => `<div class="rv-item"><div class="rv-hd"><span class="rv-name">${r.reviewer}</span><span class="rv-date">${r.date}</span></div><div class="rv-star">${stH(r.rating)}</div><p class="rv-txt">${r.comment}</p></div>`).join('')
      : '<p style="color:#9aa5bf;text-align:center;padding:24px 0">No reviews yet — be the first!</p>'
    }</div>
    <div class="rv-form"><h4>Write a Review</h4>
      <div class="star-inp" id="sri-${pid}">${[5,4,3,2,1].map(n => `<label for="sr${n}x${pid}">★</label><input type="radio" name="srn-${pid}" id="sr${n}x${pid}" value="${n}">`).join('')}</div>
      <input type="text" class="rf-inp" id="rn-${pid}" placeholder="Your name">
      <textarea class="rf-inp" id="rc-${pid}" rows="3" placeholder="Share your experience…"></textarea>
      <button class="btn-primary" onclick="submitRev(${pid})">SUBMIT REVIEW</button>
    </div>`;
}

window.submitRev = async function(pid) {
  const nm = document.getElementById(`rn-${pid}`)?.value.trim();
  const cm = document.getElementById(`rc-${pid}`)?.value.trim();
  const st = document.querySelector(`input[name="srn-${pid}"]:checked`);
  if (!st) { showToast('Please select a star rating.', 'err'); return; }
  if (!nm) { showToast('Enter your name.', 'err'); return; }
  if (!cm) { showToast('Write a comment.', 'err'); return; }
  try {
    const res  = await fetch(`${API}?action=add_review`, {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ product_id: pid, reviewer: nm, rating: parseInt(st.value), comment: cm })
    });
    const data = await res.json();
    if (data.success) {
      showToast('Review submitted! Thank you.', 'ok');
      const p = PRODUCTS.find(x => x.id === pid);
      const avg = parseFloat(p?.avg_rating || p?.rating || 5);
      const cnt = parseInt(p?.total_reviews || p?.review_count || 0);
      await loadReviews(pid, avg, cnt);
      // Refresh product list to update ratings
      loadProducts();
    } else { showToast(data.error || 'Could not submit review.', 'err'); }
  } catch(e) { showToast('Network error.', 'err'); }
};

function closeModal() { document.getElementById('modal-ov').classList.remove('on'); document.getElementById('pmodal').classList.remove('on'); document.body.style.overflow = ''; }

// ── ADMIN ──
function setupAdmin() {
  document.getElementById('adm-trigger')?.addEventListener('click', e => { e.preventDefault(); openAdmin(); });
  document.getElementById('adm-ov')?.addEventListener('click', closeAdmin);
  document.getElementById('adm-close')?.addEventListener('click', closeAdmin);
  document.getElementById('adm-login-btn')?.addEventListener('click', doLogin);
  document.getElementById('adm-pass')?.addEventListener('keydown', e => { if (e.key === 'Enter') doLogin(); });
  document.getElementById('adm-add-btn')?.addEventListener('click', () => showForm(null));
  document.getElementById('adm-back')?.addEventListener('click', showTable);
  document.getElementById('adm-cancel')?.addEventListener('click', showTable);
  document.getElementById('adm-save')?.addEventListener('click', saveProduct);
  document.getElementById('adm-search')?.addEventListener('input', e => renderTable(e.target.value));
  document.getElementById('adm-order-search')?.addEventListener('input', e => renderOrdersTable(e.target.value));
  document.getElementById('adm-panel')?.addEventListener('click', e => {
    const btn = e.target.closest('.adm-nav-btn'); if (!btn) return;
    document.querySelectorAll('.adm-nav-btn').forEach(b => b.classList.remove('active')); btn.classList.add('active');
    const v = btn.dataset.view;
    document.querySelectorAll('#adm-panel .adm-main > div').forEach(d => d.classList.add('hidden'));
    document.getElementById('adm-view-' + v).classList.remove('hidden');
    if (v === 'stats') renderStats(); if (v === 'orders') renderOrdersTable();
  });
}
function openAdmin() { document.getElementById('adm-ov').classList.add('on'); document.getElementById('adm-modal').classList.add('on'); document.body.style.overflow = 'hidden'; if (adminIn) showTable(); else { document.getElementById('adm-login').style.display = 'flex'; document.getElementById('adm-panel').classList.add('hidden'); document.getElementById('adm-form').classList.add('hidden'); } }
function closeAdmin() { document.getElementById('adm-ov').classList.remove('on'); document.getElementById('adm-modal').classList.remove('on'); document.body.style.overflow = ''; }

async function doLogin() {
  const pass = document.getElementById('adm-pass').value;
  try {
    const res  = await fetch(`${API}?action=admin_login`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ password: pass }) });
    const data = await res.json();
    if (data.success) { adminIn = true; adminToken = data.token; showTable(); showToast('Admin panel unlocked!', 'ok'); }
    else { showToast('Incorrect password.', 'err'); document.getElementById('adm-pass').value = ''; }
  } catch(e) { showToast('Login error.', 'err'); }
}

function showTable() {
  document.getElementById('adm-login').style.display = 'none'; document.getElementById('adm-form').classList.add('hidden'); document.getElementById('adm-panel').classList.remove('hidden');
  document.getElementById('adm-view-products').classList.remove('hidden'); document.getElementById('adm-view-stats').classList.add('hidden'); document.getElementById('adm-view-orders').classList.add('hidden');
  document.querySelectorAll('.adm-nav-btn').forEach(b => b.classList.toggle('active', b.dataset.view === 'products'));
  renderTable();
}

function renderTable(q = '') {
  const list = PRODUCTS.filter(p => !q || p.name.toLowerCase().includes(q.toLowerCase()) || p.cat.toLowerCase().includes(q.toLowerCase()));
  const bc = { best:'b-best', new:'b-new', sale:'b-sale', top:'b-top' };
  document.getElementById('adm-tbody').innerHTML = list.map(p => `<tr>
    <td><div class="td-prod"><div class="td-em">${getIcon(p.cat)}</div><div><div class="td-nm">${p.name}</div><div class="td-cat">${p.cat}</div></div></div></td>
    <td>${p.cat}</td><td class="td-price">₱${Number(p.price).toLocaleString()}</td>
    <td><span style="font-weight:700">${p.stock}</span><span style="color:#9aa5bf">/${p.total_stock}</span></td>
    <td>${p.badge ? `<span class="td-badge ${bc[p.badge]||''}">${p.badge_text}</span>` : '<span class="td-badge none">None</span>'}</td>
    <td><button class="atbtn" onclick="showForm(${p.id})">Edit</button><button class="atbtn del" onclick="delProduct(${p.id})">Delete</button></td>
  </tr>`).join('');
}

async function renderOrdersTable(q = '') {
  const tbody = document.getElementById('adm-orders-tbody');
  tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:#9aa5bf;padding:24px">Loading…</td></tr>';
  try {
    const url = `${API}?action=orders${q ? '&q='+encodeURIComponent(q) : ''}`;
    const res = await fetch(url, { headers: { 'X-Admin-Token': adminToken } });
    const orders = await res.json();
    if (!orders.length) { tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:#9aa5bf;padding:32px">No orders yet.</td></tr>'; return; }
    const sClass = { 'Order Placed':'s-placed','Confirmed':'s-confirmed','Packing':'s-packing','Shipped':'s-shipped','Delivered':'s-delivered','Cancelled':'s-cancelled' };
    tbody.innerHTML = orders.map(o => `<tr>
      <td><span style="font-family:'Barlow Condensed',sans-serif;font-weight:800;color:#F26522;font-size:13px">${o.ref}</span></td>
      <td><div style="font-weight:700;font-size:13px">${o.customer?.fn||''} ${o.customer?.ln||''}</div><div style="font-size:11px;color:#9aa5bf">${o.customer?.ph||''}</div></td>
      <td>${(o.items||[]).length} item${(o.items||[]).length !== 1 ? 's' : ''}</td>
      <td class="td-price">₱${Number(o.total).toLocaleString()}</td>
      <td style="font-size:12px">${{cod:'COD',gcash:'GCash',card:'Card',bank:'Bank'}[o.payment]||o.payment}</td>
      <td style="font-size:12px;color:#9aa5bf">${(o.date||'').split(',')[0]}</td>
      <td><span class="ord-status-badge ${sClass[o.status]||''}">${o.status}</span></td>
      <td>
        <select class="status-sel" onchange="updateOrderStatus('${o.ref}',this.value)">
          ${ORDER_STATUSES.map(s => `<option value="${s}" ${o.status === s ? 'selected' : ''}>${s}</option>`).join('')}
        </select>
      </td>
    </tr>`).join('');
  } catch(e) { tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:#E74C3C;padding:24px">Failed to load orders.</td></tr>'; }
}

window.updateOrderStatus = async function(ref, newStatus) {
  try {
    const res  = await fetch(`${API}?action=update_order_status`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-Admin-Token': adminToken }, body: JSON.stringify({ ref, status: newStatus }) });
    const data = await res.json();
    if (data.success) showToast(`Order ${ref} → ${newStatus}`, 'ok');
    else showToast(data.error || 'Update failed.', 'err');
  } catch(e) { showToast('Network error.', 'err'); }
};

async function renderStats() {
  try {
    const res  = await fetch(`${API}?action=stats`, { headers: { 'X-Admin-Token': adminToken } });
    const s    = await res.json();
    document.getElementById('adm-stats-grid').innerHTML = `
      <div class="adm-stat-card"><div class="adm-stat-val">${s.total_products}</div><div class="adm-stat-lbl">TOTAL PRODUCTS</div></div>
      <div class="adm-stat-card"><div class="adm-stat-val">${s.in_stock}</div><div class="adm-stat-lbl">IN STOCK</div></div>
      <div class="adm-stat-card"><div class="adm-stat-val">${s.categories}</div><div class="adm-stat-lbl">CATEGORIES</div></div>
      <div class="adm-stat-card"><div class="adm-stat-val">${s.avg_rating}</div><div class="adm-stat-lbl">AVG RATING</div></div>
      <div class="adm-stat-card"><div class="adm-stat-val">${s.total_orders}</div><div class="adm-stat-lbl">TOTAL ORDERS</div></div>
      <div class="adm-stat-card"><div class="adm-stat-val">${s.delivered}</div><div class="adm-stat-lbl">DELIVERED</div></div>
      <div class="adm-stat-card"><div class="adm-stat-val">${s.shipped}</div><div class="adm-stat-lbl">SHIPPED</div></div>
      <div class="adm-stat-card"><div class="adm-stat-val">₱${Number(s.revenue).toLocaleString()}</div><div class="adm-stat-lbl">TOTAL REVENUE</div></div>
      <div class="adm-stat-card"><div class="adm-stat-val">${s.total_reviews}</div><div class="adm-stat-lbl">REVIEWS</div></div>`;
  } catch(e) { document.getElementById('adm-stats-grid').innerHTML = '<p style="color:#E74C3C;padding:24px">Failed to load stats.</p>'; }
}

window.showForm = function(id) {
  adminEditId = id;
  document.getElementById('adm-panel').classList.add('hidden'); document.getElementById('adm-form').classList.remove('hidden');
  document.getElementById('adm-form-title').textContent = id ? 'Edit Product' : 'Add New Product';
  if (id) {
    const p = PRODUCTS.find(x => x.id === id); if (!p) return;
    document.getElementById('af-name').value  = p.name;  document.getElementById('af-cat').value   = p.cat;
    document.getElementById('af-price').value = p.price; document.getElementById('af-badge').value = p.badge || '';
    document.getElementById('af-stock').value = p.stock; document.getElementById('af-total').value  = p.total_stock;
    document.getElementById('af-desc').value  = p.description || '';
    const links = Array.isArray(p.links) ? p.links : JSON.parse(p.links || '[]');
    document.getElementById('af-shopee').value = links.find(l => l.type === 'shopee')?.url || '';
    document.getElementById('af-lazada').value = links.find(l => l.type === 'lazada')?.url || '';
    const oth = links.find(l => l.type === 'other');
    document.getElementById('af-other-name').value = oth?.name || ''; document.getElementById('af-other').value = oth?.url || '';
  } else {
    ['af-name','af-price','af-stock','af-total','af-desc','af-shopee','af-lazada','af-other','af-other-name'].forEach(i => document.getElementById(i).value = '');
    document.getElementById('af-badge').value = ''; document.getElementById('af-cat').value = 'Basketball';
  }
};

async function saveProduct() {
  const name  = document.getElementById('af-name').value.trim();
  const price = parseFloat(document.getElementById('af-price').value);
  const desc  = document.getElementById('af-desc').value.trim();
  if (!name) { showToast('Product name required.', 'err'); return; }
  if (!price || price < 1) { showToast('Valid price required.', 'err'); return; }
  if (!desc) { showToast('Description required.', 'err'); return; }
  const links = [];
  const sh = document.getElementById('af-shopee').value.trim(), lz = document.getElementById('af-lazada').value.trim(), ot = document.getElementById('af-other').value.trim(), otnm = document.getElementById('af-other-name').value.trim();
  if (sh) links.push({ name:'Shopee PH', type:'shopee', url:sh });
  if (lz) links.push({ name:'Lazada PH', type:'lazada', url:lz });
  if (ot) links.push({ name:otnm||'Official Store', type:'other', url:ot });
  const badge = document.getElementById('af-badge').value;
  const bt = { new:'NEW ARRIVAL', sale:'SALE', top:'TOP RATED', best:'BEST SELLER', '':'' }[badge];
  try {
    const res  = await fetch(`${API}?action=save_product`, {
      method: 'POST', headers: { 'Content-Type': 'application/json', 'X-Admin-Token': adminToken },
      body: JSON.stringify({ id: adminEditId, name, cat: document.getElementById('af-cat').value, price, stock: parseInt(document.getElementById('af-stock').value)||10, total_stock: parseInt(document.getElementById('af-total').value)||20, badge, badge_text: bt, description: desc, links })
    });
    const data = await res.json();
    if (data.success) { showToast(adminEditId ? 'Product updated!' : 'Product added!', 'ok'); await loadProducts(); showTable(); }
    else showToast(data.error || 'Save failed.', 'err');
  } catch(e) { showToast('Network error.', 'err'); }
}

window.delProduct = async function(id) {
  if (!confirm('Delete this product?')) return;
  try {
    const res  = await fetch(`${API}?action=delete_product`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-Admin-Token': adminToken }, body: JSON.stringify({ id }) });
    const data = await res.json();
    if (data.success) { showToast('Product deleted.', 'err'); await loadProducts(); renderTable(document.getElementById('adm-search').value); }
    else showToast(data.error || 'Delete failed.', 'err');
  } catch(e) { showToast('Network error.', 'err'); }
};

// ── MISC ──
function animStats() {
  const o = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (!e.isIntersecting) return;
      const el = e.target, target = parseInt(el.dataset.t), t0 = performance.now();
      const step = now => { const p = Math.min((now - t0) / 1400, 1); el.textContent = Math.floor((1 - Math.pow(1 - p, 3)) * target); if (p < 1) requestAnimationFrame(step); else el.textContent = target; };
      requestAnimationFrame(step); o.unobserve(el);
    });
  }, { threshold: 0.5 });
  document.querySelectorAll('.sv[data-t]').forEach(s => o.observe(s));
}
function setupScrollHeader() { window.addEventListener('scroll', () => { document.getElementById('site-header')?.classList.toggle('scrolled', window.scrollY > 60); }, { passive: true }); }
function animBadge(id) { const el = document.getElementById(id); if (!el) return; el.classList.remove('pulse'); void el.offsetWidth; el.classList.add('pulse'); }
function showToast(msg, type = '') { const c = document.getElementById('toast-wrap'); const t = document.createElement('div'); t.className = 'toast' + (type ? ' ' + type : ''); t.textContent = msg; c.appendChild(t); requestAnimationFrame(() => requestAnimationFrame(() => t.classList.add('show'))); setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 350); }, 2800); }
function saveCart() { try { localStorage.setItem('bs_cart', JSON.stringify(cart)); } catch(e) {} }
function saveWish() { try { localStorage.setItem('bs_wish', JSON.stringify(wishlist)); } catch(e) {} }

// Status badge styles for admin panel
const _st = document.createElement('style');
_st.textContent = `@keyframes spin{to{transform:rotate(360deg)}} .ord-status-badge{display:inline-flex;align-items:center;font-family:'Barlow Condensed',sans-serif;font-size:11px;font-weight:800;letter-spacing:.5px;padding:4px 10px;border-radius:16px}.s-placed{background:#e8f0fe;color:#1a73e8}.s-confirmed{background:#fef3e8;color:#F26522}.s-packing{background:#fff3e0;color:#e65100}.s-shipped{background:#e8f5e9;color:#2e7d32}.s-delivered{background:#e8f5e9;color:#1b5e20}.s-cancelled{background:#ffebee;color:#c62828}`;
document.head.appendChild(_st);