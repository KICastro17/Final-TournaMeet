<?php
session_start();

if (!isset($_SESSION['username']) || !in_array($_SESSION['role'] ?? '', ['organizer', 'admin'])) {
    header('Location: login.php');
    exit;
}

$selectedSport = isset($_GET['sport']) ? trim(urldecode($_GET['sport'])) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Create Tournament – TOURNAMEET</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
<style>
:root {
    --orange:      #e97817;
    --orange-dark: #c85c00;
    --cream:       #fffaf4;
    --ink:         #2b1c11;
    --line:        #e0cfc2;
}
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: "Manrope", "Segoe UI", sans-serif;
    background: var(--cream);
    color: var(--ink);
}

/* ── Navbar ── */
.navbar {
    background: linear-gradient(180deg, #ec8a2d 0%, #de7316 100%);
    padding: 12px 24px;
    color: #fff;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 200;
}
.navbar-brand {
    font-family: "Bebas Neue", sans-serif;
    font-size: 26px;
    letter-spacing: 1.5px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.navbar-links a {
    color: #fff;
    text-decoration: none;
    font-weight: 700;
    font-size: 13px;
    margin-left: 16px;
}
.navbar-links a:hover { text-decoration: underline; }

/* ── Page layout ── */
.container {
    max-width: 1360px;
    margin: 0 auto;
    padding: 32px 24px 60px;
}
.page-title {
    font-family: "Bebas Neue", sans-serif;
    font-size: 42px;
    letter-spacing: 1.5px;
    color: #be5b0a;
    margin-bottom: 4px;
}
.page-sub {
    color: #9a7060;
    font-size: 14px;
    margin-bottom: 28px;
}
.sport-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #fff4e6;
    border: 1.5px solid #f5c88a;
    color: #a65300;
    padding: 5px 14px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 1px;
    text-transform: uppercase;
    margin-bottom: 24px;
}

/* ── Two-column layout ── */
.layout {
    display: grid;
    grid-template-columns: minmax(540px, 1.1fr) minmax(360px, 0.9fr);
    gap: 22px;
    align-items: start;
}

/* ── Panel card ── */
.panel {
    background: #fff;
    border: 1px solid #f0d9bf;
    border-radius: 16px;
    box-shadow: 0 14px 30px rgba(200,120,0,0.09);
    padding: 26px;
}
.panel-title {
    font-family: "Bebas Neue", sans-serif;
    font-size: 24px;
    letter-spacing: 1px;
    color: #9b4500;
    margin-bottom: 6px;
}
.panel-sub {
    font-size: 13px;
    color: #a07860;
    margin-bottom: 22px;
}

/* ── Inputs ── */
.input-group { margin-bottom: 14px; }
.input-group label {
    display: block;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: #6b4f38;
    margin-bottom: 6px;
}
.input-group input,
.input-group textarea,
.input-group select {
    width: 100%;
    padding: 10px 12px;
    border: 1.5px solid var(--line);
    border-radius: 10px;
    font: inherit;
    font-size: 14px;
    color: var(--ink);
    background: #fffaf6;
    transition: border-color 0.2s, box-shadow 0.2s;
    outline: none;
}
.input-group input:focus,
.input-group textarea:focus,
.input-group select:focus {
    border-color: var(--orange);
    box-shadow: 0 0 0 3px rgba(233,120,23,0.12);
    background: #fff;
}
.input-group textarea { resize: vertical; }
.input-group .hint { font-size: 11px; color: #b09280; margin-top: 4px; }

.row2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.row3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; }

/* ── Map ── */
.map-section { margin-bottom: 14px; }
.map-section label {
    display: block;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: #6b4f38;
    margin-bottom: 6px;
}
#location_map {
    height: 240px;
    border: 1.5px solid var(--line);
    border-radius: 12px;
    margin-top: 8px;
}
.loc-btn {
    margin-top: 8px;
    border: 1.5px solid var(--line);
    background: #fff;
    border-radius: 9px;
    padding: 8px 12px;
    font: inherit;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    color: var(--ink);
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: border-color 0.2s;
}
.loc-btn:hover { border-color: var(--orange); }

/* ── Submit ── */
.submit-btn {
    width: 100%;
    padding: 14px;
    background: linear-gradient(120deg, #ff8c00, #e97817);
    color: #fff;
    border: none;
    border-radius: 12px;
    font: inherit;
    font-size: 15px;
    font-weight: 800;
    letter-spacing: 1px;
    text-transform: uppercase;
    cursor: pointer;
    transition: opacity 0.2s, transform 0.15s;
    margin-top: 6px;
    box-shadow: 0 6px 18px rgba(233,120,23,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}
.submit-btn:hover  { opacity: 0.92; transform: translateY(-1px); }
.submit-btn:active { opacity: 1; transform: none; }

/* ── Preview panel ── */
.preview-panel { position: sticky; top: 80px; }
.preview-map-wrap {
    height: 220px;
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid var(--line);
    margin-bottom: 16px;
}
.preview-map-wrap iframe { width: 100%; height: 100%; border: 0; }

.preview-sport-tag {
    display: inline-block;
    background: #fff4e6;
    border: 1px solid #f5c88a;
    color: #a65300;
    padding: 4px 12px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 1px;
    text-transform: uppercase;
    margin-bottom: 8px;
}
.preview-title {
    font-family: "Bebas Neue", sans-serif;
    font-size: 30px;
    letter-spacing: 1px;
    line-height: 1;
    color: var(--ink);
    margin-bottom: 10px;
}
.preview-row {
    font-size: 13px;
    color: #5d4a3a;
    margin-bottom: 5px;
    display: flex;
    align-items: flex-start;
    gap: 7px;
}
.preview-row i { color: var(--orange); margin-top: 2px; flex-shrink: 0; }
.preview-note {
    margin-top: 10px;
    background: #fffaf3;
    border: 1px dashed #d8c4b0;
    border-radius: 9px;
    padding: 10px;
    font-size: 13px;
    color: #5d4a3a;
    white-space: pre-wrap;
}
.divider-line {
    height: 1px;
    background: var(--line);
    margin: 14px 0;
}

@media (max-width: 1080px) { .layout { grid-template-columns: 1fr; } .preview-panel { position: static; } }
@media (max-width: 680px)  { .row2, .row3 { grid-template-columns: 1fr; } }
</style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <div class="navbar-brand"><i class="fas fa-trophy"></i> TOURNAMEET</div>
    <div class="navbar-links">
        <a href="organizer_dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a>
        <a href="my_tournaments.php"><i class="fas fa-list-alt"></i> My Tournaments</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<div class="container">
    <div class="page-title">CREATE TOURNAMENT</div>
    <div class="page-sub">Fill in all details below. The preview updates live before you publish.</div>

    <?php if ($selectedSport !== ''): ?>
    <div class="sport-badge">
        <i class="fas fa-tag"></i>
        Category: <?php echo htmlspecialchars($selectedSport); ?>
    </div>
    <?php endif; ?>

    <div class="layout">

        <!-- ── Left: Form ── -->
        <section class="panel">
            <div class="panel-title">Tournament Details</div>
            <div class="panel-sub">Use complete, accurate information. Athletes will see exactly what you enter.</div>

            <form action="save_tournament.php" method="POST" id="tournamentForm">
                
                <input type="hidden" name="sport"    value="<?php echo htmlspecialchars($selectedSport); ?>">

                <!-- Title -->
                <div class="input-group">
                    <label><i class="fas fa-heading"></i> Tournament Title *</label>
                    <input type="text" id="f_title" name="title" maxlength="150" placeholder="e.g. Baguio City Open 2025" required>
                </div>

                <!-- Description -->
                <div class="input-group">
                    <label><i class="fas fa-align-left"></i> Description *</label>
                    <textarea id="f_description" name="description" rows="3" placeholder="Describe your tournament, goals, and what to expect..." required></textarea>
                </div>

                <!-- Sport -->
                <div class="input-group">
                    <label><i class="fas fa-running"></i> Sport *</label>
                    <select id="f_sport" name="sport_manual" required>
                        <option value="">-- Select a Sport --</option>
                        <?php
                        $sportCategories = [
                            'Ball Sports'   => ['Basketball','Football','Volleyball','Rugby','Baseball','Handball','Sepak Takraw'],
                            'Racket Sports' => ['Badminton','Tennis','Squash','Pickleball','Table Tennis'],
                            'Combatives'    => ['Boxing','MMA','Karate','Judo','Taekwondo','Wrestling','Arnis'],
                            'Endurance'     => ['Running','Cycling','Triathlon','Marathon','Swimming','Rowing'],
                            'Precision'     => ['Archery','Shooting','Darts','Golf','Bowling'],
                            'E-Sports'      => ['FPS','MOBA','Fighting','RTS','Battle Royale','Sports Games'],
                        ];
                        foreach ($sportCategories as $category => $sports):
                        ?>
                        <optgroup label="── <?php echo htmlspecialchars($category); ?> ──">
                            <?php foreach ($sports as $sport): ?>
                            <option value="<?php echo htmlspecialchars($sport); ?>"
                                <?php echo (
                                    $sport === $selectedSport ||
                                    $category === $selectedSport
                                ) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($sport); ?>
                            </option>
                            <?php endforeach; ?>
                        </optgroup>
                        <?php endforeach; ?>
                    </select>
                    <div class="hint">Sport is grouped by category. Select the specific sport for your tournament.</div>
                </div>

                <!-- Date / Time -->
                <div class="row2">
                    <div class="input-group">
                        <label><i class="fas fa-calendar-alt"></i> Event Date *</label>
                        <input type="date" id="f_date" name="date" required>
                    </div>
                    <div class="input-group">
                        <label><i class="fas fa-clock"></i> Event Time *</label>
                        <input type="time" id="f_time" name="time" required>
                    </div>
                </div>

                <!-- Registration Deadline -->
                <div class="input-group">
                    <label><i class="fas fa-hourglass-end"></i> Registration Deadline *</label>
                    <input type="datetime-local" id="f_deadline" name="registration_deadline" required>
                </div>

                <!-- Location + Leaflet map -->
                <div class="map-section">
                    <label><i class="fas fa-map-marker-alt"></i> Location / Venue *</label>
                    <input type="text" id="f_location" name="location" maxlength="255"
                           placeholder="e.g. Baguio Athletic Bowl, Baguio City" required
                           style="width:100%;padding:10px 12px;border:1.5px solid #e0cfc2;border-radius:10px;font:inherit;font-size:14px;background:#fffaf6;outline:none;">

                    <div style="margin-top:8px;display:flex;gap:8px;flex-wrap:wrap;">
                        <input type="text" id="location_search"
                               placeholder="Search a place and press Enter…"
                               style="flex:1;min-width:200px;padding:9px 12px;border:1.5px solid #e0cfc2;border-radius:9px;font:inherit;font-size:13px;outline:none;">
                        <button type="button" class="loc-btn" id="use_current_location">
                            <i class="fas fa-location-arrow"></i> My Location
                        </button>
                    </div>
                    <div id="location_map"></div>
                    <div class="hint" style="margin-top:6px;"><i class="fas fa-info-circle"></i> Click the map or search to auto-fill venue name.</div>
                </div>

                <!-- Fee / Prize / Slots -->
                <div class="row3">
                    <div class="input-group">
                        <label><i class="fas fa-peso-sign"></i> Entry Fee (PHP)</label>
                        <input type="number" id="f_fee" name="registration_fee" step="0.01" min="0" value="0" required>
                    </div>
                    <div class="input-group">
                        <label><i class="fas fa-gift"></i> Prize Pool (PHP)</label>
                        <input type="number" id="f_prize" name="prize_pool" step="0.01" min="0" value="0" required>
                    </div>
                    <div class="input-group">
                        <label><i class="fas fa-users"></i> Slots *</label>
                        <input type="number" id="f_slots" name="slots" min="1" placeholder="e.g. 16" required>
                    </div>
                </div>

                <!-- Requirements -->
                <div class="input-group">
                    <label><i class="fas fa-clipboard-list"></i> Requirements</label>
                    <textarea id="f_requirements" name="requirements" rows="3"
                              placeholder="One requirement per line&#10;e.g. Must be 18+&#10;Bring valid ID"></textarea>
                </div>

                <!-- Organizer Note -->
                <div class="input-group">
                    <label><i class="fas fa-bullhorn"></i> Organizer Note / Announcement</label>
                    <textarea id="f_note" name="organizer_note" rows="2"
                              placeholder="Visible to all registered athletes…"></textarea>
                </div>

                <div class="divider-line"></div>

                <button class="submit-btn" type="submit">
                    <i class="fas fa-paper-plane"></i> Publish Tournament
                </button>
            </form>
        </section><!-- /form panel -->

        <!-- ── Right: Preview ── -->
        <aside class="panel preview-panel">
            <div class="panel-title">Live Preview</div>
            <div class="panel-sub">Updates as you type. This is how athletes will see your tournament.</div>

            <div class="preview-map-wrap">
                <iframe id="preview_map"
                        src="https://www.google.com/maps?q=Philippines&output=embed"
                        allowfullscreen loading="lazy"></iframe>
            </div>

            <span class="preview-sport-tag" id="p_sport">SPORT</span>
            <div class="preview-title" id="p_title">Tournament Title</div>

            <div class="preview-row"><i class="fas fa-align-left"></i><span id="p_desc">Description preview…</span></div>
            <div class="preview-row"><i class="fas fa-calendar-alt"></i><span id="p_date">Date &amp; Time</span></div>
            <div class="preview-row"><i class="fas fa-hourglass-end"></i>Deadline: <span id="p_deadline">—</span></div>
            <div class="preview-row"><i class="fas fa-map-marker-alt"></i><span id="p_location">Venue</span></div>

            <div class="divider-line"></div>

            <div class="preview-row"><i class="fas fa-peso-sign"></i>Entry: <strong id="p_fee">0.00</strong> PHP &nbsp;|&nbsp; Prize: <strong id="p_prize">0.00</strong> PHP</div>
            <div class="preview-row"><i class="fas fa-users"></i>Slots: <strong id="p_slots">—</strong></div>

            <div class="preview-note" id="p_note">No organizer note yet.</div>
        </aside>

    </div><!-- /layout -->
</div><!-- /container -->

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
// ── Live preview sync ─────────────────────────────────────────────────
const form = document.getElementById('tournamentForm');
function g(id) { return document.getElementById(id); }

function syncPreview() {
    const sport = g('f_sport').value.trim()   || 'General';
    g('p_sport').textContent    = sport.toUpperCase();
    g('p_title').textContent    = g('f_title').value.trim()       || 'Tournament Title';
    g('p_desc').textContent     = g('f_description').value.trim() || 'Description preview…';
    g('p_date').textContent     = (g('f_date').value || '—')
                                 + (g('f_time').value ? '  ' + g('f_time').value : '');
    g('p_deadline').textContent = g('f_deadline').value  || '—';
    g('p_location').textContent = g('f_location').value  || 'Venue';
    g('p_fee').textContent      = parseFloat(g('f_fee').value   || 0).toFixed(2);
    g('p_prize').textContent    = parseFloat(g('f_prize').value || 0).toFixed(2);
    g('p_slots').textContent    = g('f_slots').value || '—';
    g('p_note').textContent     = g('f_note').value.trim() || 'No organizer note yet.';

    const q = encodeURIComponent(g('f_location').value || 'Philippines');
    g('preview_map').src = 'https://www.google.com/maps?q=' + q + '&output=embed';
}

form.addEventListener('input',  function(e) { if (e.target.id && e.target.id.startsWith('f_')) syncPreview(); });
form.addEventListener('change', function(e) { if (e.target.id && e.target.id.startsWith('f_')) syncPreview(); });
syncPreview();

// ── Leaflet picker map ────────────────────────────────────────────────
const locInput   = g('f_location');
const locSearch  = g('location_search');
const locBtn     = g('use_current_location');

const pickerMap = L.map('location_map').setView([14.5995, 120.9842], 6);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors',
    maxZoom: 19,
}).addTo(pickerMap);

let marker = null;

function setMarker(lat, lng) {
    if (!marker) {
        marker = L.marker([lat, lng], { draggable: true }).addTo(pickerMap);
        marker.on('dragend', function(e) {
            const p = e.target.getLatLng();
            reverseGeocode(p.lat, p.lng);
        });
    } else {
        marker.setLatLng([lat, lng]);
    }
    pickerMap.setView([lat, lng], 15);
}

function reverseGeocode(lat, lon) {
    fetch('https://nominatim.openstreetmap.org/reverse?format=json&lat=' + lat + '&lon=' + lon)
        .then(r => r.json())
        .then(data => {
            if (data && data.display_name) {
                locInput.value = data.display_name;
                syncPreview();
            }
        }).catch(() => {});
}

function searchGeocode(query) {
    if (!query) return;
    fetch('https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(query))
        .then(r => r.json())
        .then(results => {
            if (results && results.length > 0) {
                const r = results[0];
                setMarker(parseFloat(r.lat), parseFloat(r.lon));
                locInput.value = r.display_name;
                syncPreview();
            }
        }).catch(() => {});
}

pickerMap.on('click', function(e) {
    setMarker(e.latlng.lat, e.latlng.lng);
    reverseGeocode(e.latlng.lat, e.latlng.lng);
});

locSearch.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); searchGeocode(this.value.trim()); }
});

locInput.addEventListener('blur', function() {
    if (this.value.trim()) searchGeocode(this.value.trim());
});

locBtn.addEventListener('click', function() {
    if (!navigator.geolocation) { alert('Geolocation not supported.'); return; }
    navigator.geolocation.getCurrentPosition(
        function(pos) {
            setMarker(pos.coords.latitude, pos.coords.longitude);
            reverseGeocode(pos.coords.latitude, pos.coords.longitude);
        },
        function() { alert('Could not get your location. Please allow location access.'); },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
});
</script>
</body>
</html>