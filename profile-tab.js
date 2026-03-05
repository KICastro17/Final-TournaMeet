// profile-tab.js

const API_URL = 'profile_api.php';

// Tab switching
document.querySelectorAll('.tab').forEach(tab => {
  tab.addEventListener('click', () => {
    tab.closest('.tabs').querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    tab.classList.add('active');
  });
});

// Animate progress bars
function animateBars() {
  document.querySelectorAll('.bar-fill').forEach(bar => {
    const targetWidth = bar.getAttribute('data-width') + '%';
    bar.style.width = '0';
    setTimeout(() => { bar.style.width = targetWidth; }, 300);
  });
}

// Load profile from database
async function loadProfile(profileId) {
  try {
    const res = await fetch(`${API_URL}?id=${profileId}`);
    const json = await res.json();
    if (!json.success) { console.error('Failed to load profile:', json.error); return; }

    const d = json.data;

    document.querySelector('.profile-name').textContent = d.name || '';
    document.querySelector('.profile-role').textContent = d.role || '';

    if (d.stats) {
      const nums = document.querySelectorAll('.stat-num');
      if (nums[0]) nums[0].textContent = d.stats.tournaments_joined;
      if (nums[1]) nums[1].textContent = d.stats.tournaments_won;
      if (nums[2]) nums[2].textContent = d.stats.organized;
      if (nums[3]) nums[3].textContent = '₱' + Number(d.stats.prize_earnings).toLocaleString();

      document.querySelectorAll('.bar-fill').forEach((bar, i) => {
        if (i === 0) bar.setAttribute('data-width', d.stats.win_rate);
        if (i === 1) bar.setAttribute('data-width', d.stats.attendance_rate);
      });
      const rateLabels = document.querySelectorAll('.win-rate-label span:last-child');
      if (rateLabels[0]) rateLabels[0].textContent = d.stats.win_rate + '%';
      if (rateLabels[1]) rateLabels[1].textContent = d.stats.attendance_rate + '%';
    }

    const infoVals = document.querySelectorAll('.info-val');
    if (infoVals[0]) infoVals[0].textContent = d.sport    || '';
    if (infoVals[1]) infoVals[1].textContent = d.position || '';
    if (infoVals[2]) infoVals[2].textContent = d.team     || '';
    if (infoVals[3]) infoVals[3].textContent = d.region   || '';
    if (infoVals[4]) infoVals[4].textContent = d.level    || '';

    const initials = d.name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
    document.querySelector('.avatar').textContent = initials;

    if (d.sports && d.sports.length) {
      document.querySelector('.tags').innerHTML = d.sports.map(s => `<span class="tag">${s}</span>`).join('');
    }
    if (d.tournaments && d.tournaments.length) renderTournaments(d.tournaments);
    if (d.achievements && d.achievements.length) renderAchievements(d.achievements);

    animateBars();
  } catch (err) {
    console.error('Error loading profile:', err);
  }
}

// Render tournaments
function renderTournaments(tournaments) {
  const badgeMap = {
    won:       '<span class="t-badge badge-won">🏆 Won</span>',
    runner_up: '<span class="t-badge badge-runner">Runner-up</span>',
    active:    '<span class="t-badge badge-active">Active</span>',
    upcoming:  '<span class="t-badge badge-upcoming">Upcoming</span>',
  };
  const container = document.querySelector('.card-body .tabs')?.closest('.card-body');
  if (!container) return;
  container.querySelectorAll('.tournament-item').forEach(el => el.remove());
  tournaments.forEach(t => {
    const dateStr = t.date ? new Date(t.date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : '';
    const item = document.createElement('div');
    item.className = 'tournament-item';
    item.innerHTML = `
      <div class="t-icon">
        <svg width="20" height="20" fill="var(--orange)" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
      </div>
      <div class="t-info">
        <div class="t-name">${t.name}</div>
        <div class="t-date">${dateStr}${t.venue ? ' · ' + t.venue : ''}${t.type === 'organized' ? ' · Organizer' : ''}</div>
      </div>
      ${badgeMap[t.result] || ''}`;
    container.appendChild(item);
  });
}

// Render achievements
function renderAchievements(achievements) {
  const grid = document.querySelector('.achieve-grid');
  if (!grid) return;
  grid.innerHTML = achievements.map(a => `
    <div class="achieve-item ${a.earned ? 'earned' : ''}">
      <div class="achieve-icon ${a.earned ? '' : 'locked'}">${a.icon}</div>
      <div class="achieve-name ${a.earned ? '' : 'locked'}">${a.title}</div>
      <div class="achieve-desc">${a.description}</div>
    </div>`).join('');
}

// Save profile to database
async function saveProfile(profileData) {
  try {
    const res = await fetch(API_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(profileData)
    });
    const json = await res.json();
    if (json.success) {
      showToast('Profile saved successfully!');
      return json.profile_id;
    } else {
      showToast('Save failed: ' + json.error, true);
    }
  } catch (err) {
    showToast('Error saving profile.', true);
  }
}

// Toast notification
function showToast(message, isError = false) {
  const toast = document.createElement('div');
  toast.textContent = message;
  toast.style.cssText = `
    position:fixed;bottom:24px;right:24px;
    background:${isError ? '#e53e3e' : '#27AE72'};
    color:white;padding:12px 20px;border-radius:8px;
    font-family:'Barlow Condensed',sans-serif;font-weight:700;font-size:14px;
    letter-spacing:0.5px;box-shadow:0 4px 16px rgba(0,0,0,0.2);
    z-index:9999;opacity:0;transition:opacity 0.3s;`;
  document.body.appendChild(toast);
  setTimeout(() => toast.style.opacity = '1', 10);
  setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 300); }, 3000);
}

// On page load
window.addEventListener('load', () => {
  animateBars();
  // Uncomment to auto-load a profile:
  // loadProfile(1);
});
