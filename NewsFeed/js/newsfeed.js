/**
 * Tournameet — Newsfeed JS (with post modal fullview)
 */

// ── Load posts ────────────────────────────────────────────────────────────
async function loadPosts() {
  const feed = document.getElementById('postsFeed');
  feed.innerHTML = '<div class="loading-wrap"><div class="spinner"></div></div>';
  let data;
  try {
    const res = await fetch('get_posts.php');
    data = await res.json();
  } catch (e) {
    feed.innerHTML = `<div class="empty-state" style="color:#dc2626">Network error: ${e.message}</div>`;
    return;
  }
  if (!data.success) {
    feed.innerHTML = `<div class="empty-state" style="color:#dc2626">Error: ${data.error}</div>`;
    return;
  }
  feed.innerHTML = '';
  if (!data.posts || !data.posts.length) {
    feed.innerHTML = '<div class="empty-state">No posts yet — be the first to share!</div>';
    return;
  }
  data.posts.forEach(p => feed.appendChild(buildPost(p)));
}

// ── Build post card ───────────────────────────────────────────────────────
function buildPost(p) {
  const tpl = document.getElementById('postTemplate');
  const el  = tpl.content.cloneNode(true).querySelector('.post-card');
  el.dataset.postId = p.id;

  el.querySelector('.post-avatar-slot').replaceWith(makeAvatar(p.pic, p.username, 'avatar-sm'));
  el.querySelector('.author-name').textContent    = p.username;
  el.querySelector('.post-time').textContent      = p.ago;
  el.querySelector('.category-badge').textContent = 'General';

  if (p.is_own) {
    el.querySelector('.own-badge').style.display = '';
    const del = el.querySelector('.delete-btn');
    del.style.display = '';
    del.addEventListener('click', e => { e.stopPropagation(); deletePost(p.id, el); });
  }

  el.querySelector('.post-content').textContent = p.caption || '';

  // Media — clicking opens full view
  if (p.media && p.media.length) {
    const grid = el.querySelector('.post-media-grid');
    grid.style.display = 'grid';
    grid.classList.add('media-' + Math.min(p.media.length, 4));
    p.media.slice(0, 4).forEach(m => {
      const type = (m.type || 'image').toLowerCase();
      if (type === 'video') {
        const vid = document.createElement('video');
        vid.src = m.url; vid.controls = true; vid.muted = true;
        vid.style.cssText = 'width:100%;border-radius:8px;';
        grid.appendChild(vid);
      } else {
        const img = document.createElement('img');
        img.src = m.url; img.alt = ''; img.loading = 'lazy';
        img.style.cssText = 'width:100%;border-radius:8px;object-fit:cover;max-height:400px;cursor:pointer;';
        img.addEventListener('click', e => { e.stopPropagation(); openPostModal(p); });
        grid.appendChild(img);
      }
    });
  }

  // Click caption/header to open full view
  el.querySelector('.post-content').style.cursor = 'pointer';
  el.querySelector('.post-content').addEventListener('click', () => openPostModal(p));
  el.querySelector('.author-name').style.cursor = 'pointer';
  el.querySelector('.author-name').addEventListener('click', () => openPostModal(p));

  // Like
  const likeBtn = el.querySelector('.btn-like');
  const likeCnt = el.querySelector('.like-count');
  likeCnt.textContent = p.like_count || 0;
  if (p.i_liked) likeBtn.classList.add('liked');
  likeBtn.addEventListener('click', e => { e.stopPropagation(); toggleLike(p.id, likeBtn, likeCnt); });

  // Comment toggle — opens modal instead
  const cntSpan = el.querySelector('.comment-count');
  cntSpan.textContent = p.comment_count || 0;
  el.querySelector('.btn-comment-toggle').addEventListener('click', e => {
    e.stopPropagation();
    openPostModal(p);
  });

  // Comment input row — also opens modal
  el.querySelector('.comment-user-av-slot').replaceWith(makeAvatar(CURRENT_USER.photo, CURRENT_USER.name, 'avatar-xs'));
  el.querySelector('.comment-input').addEventListener('focus', e => {
    e.preventDefault();
    el.querySelector('.comment-input').blur();
    openPostModal(p, true); // true = focus comment box in modal
  });

  // Reactions
  el.querySelector('.btn-react').addEventListener('click', e => {
    e.stopPropagation();
    openPicker(el, e.currentTarget, p.id, likeBtn, likeCnt);
  });

  return el;
}

// ══════════════════════════════════════════════════════════════════════════
//  POST MODAL (full view like Facebook)
// ══════════════════════════════════════════════════════════════════════════
let modalPostData = null;

function openPostModal(p, focusComment = false) {
  modalPostData = p;
  const modal = document.getElementById('postModal');

  // Media side
  const mediaWrap = document.getElementById('pmMediaWrap');
  mediaWrap.innerHTML = '';
  if (p.media && p.media.length) {
    mediaWrap.style.display = '';
    // Slideshow if multiple
    let idx = 0;
    const renderSlide = () => {
      mediaWrap.innerHTML = '';
      const m    = p.media[idx];
      const type = (m.type||'image').toLowerCase();
      if (type === 'video') {
        const vid = document.createElement('video');
        vid.src = m.url; vid.controls = true; vid.autoplay = true; vid.muted = true;
        vid.style.cssText = 'max-width:100%;max-height:100%;border-radius:0;';
        mediaWrap.appendChild(vid);
      } else {
        const img = document.createElement('img');
        img.src = m.url; img.alt = '';
        img.style.cssText = 'max-width:100%;max-height:100%;object-fit:contain;';
        mediaWrap.appendChild(img);
      }
      if (p.media.length > 1) {
        const prev = document.createElement('button');
        prev.className = 'pm-nav pm-prev';
        prev.innerHTML = '&#8249;';
        prev.onclick = () => { idx = (idx - 1 + p.media.length) % p.media.length; renderSlide(); };
        const next = document.createElement('button');
        next.className = 'pm-nav pm-next';
        next.innerHTML = '&#8250;';
        next.onclick = () => { idx = (idx + 1) % p.media.length; renderSlide(); };
        const counter = document.createElement('div');
        counter.className = 'pm-counter';
        counter.textContent = `${idx+1} / ${p.media.length}`;
        mediaWrap.appendChild(prev);
        mediaWrap.appendChild(next);
        mediaWrap.appendChild(counter);
      }
    };
    renderSlide();
  } else {
    mediaWrap.style.display = 'none';
  }

  // Author
  const avatarSlot = document.getElementById('pmAvatar');
  avatarSlot.innerHTML = '';
  avatarSlot.appendChild(makeAvatar(p.pic, p.username, 'avatar-sm'));
  document.getElementById('pmUsername').textContent = p.username;
  document.getElementById('pmTime').textContent     = p.ago;
  document.getElementById('pmCaption').textContent  = p.caption || '';

  // Like button state
  const pmLikeBtn = document.getElementById('pmLikeBtn');
  const pmLikeCnt = document.getElementById('pmLikeCnt');
  pmLikeCnt.textContent = p.like_count || 0;
  pmLikeBtn.classList.toggle('liked', !!p.i_liked);
  pmLikeBtn.onclick = () => toggleLike(p.id, pmLikeBtn, pmLikeCnt);

  // Comment count
  document.getElementById('pmCommentCnt').textContent = p.comment_count || 0;

  // Load comments
  loadModalComments(p.id);

  // Show modal
  modal.style.display = 'flex';
  document.body.style.overflow = 'hidden';

  if (focusComment) setTimeout(() => document.getElementById('pmCommentInput').focus(), 300);
}

function closePostModal() {
  document.getElementById('postModal').style.display = 'none';
  document.body.style.overflow = '';
  modalPostData = null;
}

async function loadModalComments(postId) {
  const list = document.getElementById('pmCommentList');
  list.innerHTML = '<div style="text-align:center;padding:20px;color:#999;font-size:13px;">Loading...</div>';
  try {
    const res  = await fetch(`api.php?action=get_comments&post_id=${postId}`);
    const data = await res.json();
    list.innerHTML = '';
    if (data.success && data.comments.length) {
      data.comments.forEach(c => list.appendChild(buildModalComment(c)));
      document.getElementById('pmCommentCnt').textContent = data.count;
    } else {
      list.innerHTML = '<div style="text-align:center;padding:20px;color:#999;font-size:13px;">No comments yet. Be the first!</div>';
    }
    list.scrollTop = list.scrollHeight;
  } catch(e) {
    list.innerHTML = '<div style="color:#dc2626;font-size:12px;padding:10px;">Failed to load comments.</div>';
  }
}

function buildModalComment(c) {
  const div = document.createElement('div');
  div.style.cssText = 'display:flex;gap:10px;margin-bottom:14px;align-items:flex-start;';
  div.innerHTML = `
    <div class="pm-comment-av"></div>
    <div style="flex:1;">
      <div style="background:#f0f2f5;border-radius:12px;padding:10px 14px;">
        <div style="font-weight:700;font-size:13px;color:#1a2540;margin-bottom:2px;">${escHtml(c.username)}</div>
        <div style="font-size:14px;color:#333;line-height:1.5;">${escHtml(c.content)}</div>
      </div>
      <div style="font-size:11px;color:#999;margin-top:4px;padding-left:4px;">${escHtml(c.ago||'Just now')}</div>
    </div>`;
  div.querySelector('.pm-comment-av').replaceWith(makeAvatar(c.pic, c.username, 'avatar-xs'));
  return div;
}

// Submit comment from modal
async function submitModalComment() {
  if (!modalPostData) return;
  const input = document.getElementById('pmCommentInput');
  const text  = input.value.trim();
  if (!text) return;

  input.value = '';
  const list = document.getElementById('pmCommentList');
  const noMsg = list.querySelector('div');
  if (noMsg && noMsg.textContent.includes('No comments')) noMsg.remove();

  // Optimistic
  const tempC = { username: CURRENT_USER.name, pic: CURRENT_USER.photo, content: text, ago: 'Just now' };
  list.appendChild(buildModalComment(tempC));
  list.scrollTop = list.scrollHeight;

  const cnt = document.getElementById('pmCommentCnt');
  cnt.textContent = parseInt(cnt.textContent||0) + 1;

  // Also update the card in feed
  const feedCard = document.querySelector(`.post-card[data-post-id="${modalPostData.id}"]`);
  if (feedCard) {
    const feedCnt = feedCard.querySelector('.comment-count');
    if (feedCnt) feedCnt.textContent = cnt.textContent;
  }

  try {
    const fd = new FormData();
    fd.append('action', 'add_comment');
    fd.append('post_id', modalPostData.id);
    fd.append('content', text);
    await fetch('api.php', { method: 'POST', body: fd });
  } catch(e) {}
}

// ── Like helper ───────────────────────────────────────────────────────────
async function toggleLike(postId, btn, cntEl) {
  const wasLiked = btn.classList.contains('liked');
  btn.classList.toggle('liked');
  cntEl.textContent = parseInt(cntEl.textContent) + (wasLiked ? -1 : 1);

  // Sync feed card and modal
  document.querySelectorAll(`.post-card[data-post-id="${postId}"] .btn-like`).forEach(b => {
    b.classList.toggle('liked', !wasLiked);
  });
  document.querySelectorAll(`.post-card[data-post-id="${postId}"] .like-count`).forEach(c => {
    c.textContent = cntEl.textContent;
  });

  try {
    const fd = new FormData();
    fd.append('action', 'toggle_like');
    fd.append('post_id', postId);
    const res  = await fetch('api.php', { method: 'POST', body: fd });
    const data = await res.json();
    if (data.success) {
      btn.classList.toggle('liked', data.liked);
      cntEl.textContent = data.count;
    }
  } catch(e) {
    btn.classList.toggle('liked', wasLiked);
    cntEl.textContent = parseInt(cntEl.textContent) + (wasLiked ? 1 : -1);
  }
}

// ── Reaction picker ───────────────────────────────────────────────────────
const picker      = document.getElementById('reactionPicker');
let   pickerEl    = null;
let   pickerPostId = null;
let   pickerLikeBtn = null;
let   pickerLikeCnt = null;

function openPicker(postEl, anchor, postId, likeBtn, likeCnt) {
  pickerEl      = postEl;
  pickerPostId  = postId;
  pickerLikeBtn = likeBtn;
  pickerLikeCnt = likeCnt;
  picker.classList.add('visible');
  const r = anchor.getBoundingClientRect();
  picker.style.top  = (r.top - 64 + window.scrollY) + 'px';
  picker.style.left = r.left + 'px';
}

picker.querySelectorAll('button').forEach(btn => {
  btn.addEventListener('click', async () => {
    if (!pickerEl) return;
    pickerEl.querySelector('.btn-react .my-emoji').textContent = btn.dataset.emoji;
    pickerEl.querySelector('.btn-react').classList.add('reacted');
    picker.classList.remove('visible');
    try {
      const fd = new FormData();
      fd.append('action', 'react');
      fd.append('post_id', pickerPostId);
      fd.append('emoji', btn.dataset.emoji);
      await fetch('api.php', { method: 'POST', body: fd });
    } catch(e) {}
    pickerEl = pickerPostId = pickerLikeBtn = pickerLikeCnt = null;
  });
});
document.addEventListener('click', e => {
  if (!picker.contains(e.target) && !e.target.closest('.btn-react'))
    picker.classList.remove('visible');
});

// ── Delete ────────────────────────────────────────────────────────────────
async function deletePost(postId, el) {
  if (!confirm('Delete this post?')) return;
  try {
    const fd = new FormData();
    fd.append('post_id', postId);
    const res  = await fetch('../delete_post.php', { method: 'POST', body: fd });
    const data = await res.json();
    if (data.success) {
      el.style.transition = 'opacity 0.2s';
      el.style.opacity = '0';
      setTimeout(() => el.remove(), 220);
    } else alert(data.error || 'Could not delete');
  } catch(e) { alert('Network error'); }
}

// ── Compose ───────────────────────────────────────────────────────────────
const textarea       = document.getElementById('postContent');
const composeActions = document.getElementById('composeActions');
const submitBtn      = document.getElementById('submitPost');
const cancelBtn      = document.getElementById('cancelPost');

textarea.addEventListener('focus', () => { composeActions.style.display='flex'; textarea.rows=3; });
textarea.addEventListener('input', () => { submitBtn.disabled = !textarea.value.trim(); });
cancelBtn.addEventListener('click', () => {
  textarea.value=''; textarea.rows=1; submitBtn.disabled=true; composeActions.style.display='none';
});
submitBtn.addEventListener('click', async () => {
  const caption = textarea.value.trim();
  if (!caption) return;
  const orig = submitBtn.innerHTML;
  submitBtn.disabled = true; submitBtn.textContent = '…';
  try {
    const fd = new FormData();
    fd.append('caption', caption);
    const res  = await fetch('newsfeed_post.php', { method:'POST', body:fd });
    const text = await res.text();
    let data;
    try { data = JSON.parse(text); } catch(e) { alert('Server error: '+text.slice(0,200)); return; }
    if (data.success) {
      textarea.value=''; textarea.rows=1; composeActions.style.display='none';
      const feed = document.getElementById('postsFeed');
      const emptyMsg = feed.querySelector('.empty-state');
      if (emptyMsg) emptyMsg.remove();
      feed.prepend(buildPost({
        id: data.post_id||0, user_id: CURRENT_USER.id, is_own: true,
        caption, username: CURRENT_USER.name, pic: CURRENT_USER.photo,
        ago: 'Just now', media: data.media||[], like_count:0, i_liked:false, comment_count:0,
      }));
    } else alert(data.error||'Failed to post');
  } catch(e) { alert('Network error: '+e.message); }
  finally { submitBtn.innerHTML=orig; submitBtn.disabled=false; }
});

// ── Filter tabs ───────────────────────────────────────────────────────────
const filterTabsEl = document.getElementById('filterTabs');
if (filterTabsEl) {
  filterTabsEl.addEventListener('click', e => {
    const tab = e.target.closest('.filter-tab');
    if (!tab) return;
    document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
    tab.classList.add('active');
  });
}

// ── Helpers ───────────────────────────────────────────────────────────────
function makeAvatar(photoSrc, name, cls) {
  const el = document.createElement('div');
  el.className = 'avatar ' + cls;
  if (photoSrc) {
    el.classList.add('has-photo');
    const img = document.createElement('img');
    img.src = photoSrc; img.alt = name||'';
    el.appendChild(img);
  } else {
    const colors = ['#e8630a','#2563eb','#059669','#0891b2','#7c3aed','#db2777'];
    let h = 0;
    for (let i=0;i<(name||'').length;i++) h=(name.charCodeAt(i)+((h<<5)-h));
    el.style.background = colors[Math.abs(h)%colors.length];
    el.textContent = (name||'?').trim().slice(0,2).toUpperCase();
  }
  return el;
}
function escHtml(str) {
  return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

loadPosts();