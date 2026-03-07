<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>TournaMeet — Where Athletes Meet Opportunities</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="LP.css">
</head>
<body>

<!-- ===== NAVBAR ===== -->
<header class="navbar" id="navbar">
    <div class="nav-inner">
        <a href="#" class="logo">
            <img src="DARK ICON-Tournameet.png" alt="TournaMeet Logo" class="logo-img logo-light">
            <img src="LIGHT ICON-Tournameet.png" alt="TournaMeet Logo" class="logo-img logo-dark">
            <span class="logo-text">TournaMeet</span>
        </a>

        <nav class="nav-links" id="nav-links">
            <a href="#features">Features</a>
            <a href="#showcase">Showcase</a>
            <a href="#about">About</a>
            <a href="#contact">Contact</a>
            <a href="http://localhost/registration/index.php" class="nav-search">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                Search
            </a>
            <button id="theme-toggle" class="theme-btn">
                <span class="theme-icon">☀</span>
                <span class="theme-label">Light</span>
            </button>
        </nav>

        <div class="nav-right">
            <button id="theme-toggle-mobile" class="theme-btn">
                <span class="theme-icon">☀</span>
            </button>
            <button class="hamburger" id="hamburger" aria-label="Toggle menu" aria-expanded="false">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </div>

    <!-- Mobile Drawer -->
    <div class="mobile-nav" id="mobile-nav" aria-hidden="true">
        <a href="#features" class="mobile-link">Features</a>
        <a href="#showcase" class="mobile-link">Showcase</a>
        <a href="#about" class="mobile-link">About</a>
        <a href="#contact" class="mobile-link">Contact</a>
        <a href="http://localhost/registration/index.php" class="mobile-link">Search Tournaments</a>
        <div class="mobile-nav-footer">
            <a href="#" class="btn-primary">Get Started</a>
        </div>
    </div>
</header>

<!-- ===== HERO ===== -->
<section class="hero">
    <div class="hero-bg">
        <div class="hero-grid"></div>
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    <div class="hero-content">
        <div class="hero-badge">
            <span class="badge-dot"></span>
            Now in Beta &mdash; The platform for athletes, organizers, and communities to connect and compete.
        </div>
        <h1 class="hero-title">
         Where Athletes<br>
         <span class="meet-wrapper">
        <em>Meet</em>
        <img src="meet.png" alt="icon" class="meet-img">
        </span>
        Opportunities
        </h1>
        <p class="hero-sub">
            A professional platform built to streamline tournament organization,
            empower competitors, and elevate competitive experiences worldwide.
        </p>
        <div class="hero-buttons">
            <a href="#" class="btn-primary">
                Get Started
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
            <a href="#features" class="btn-ghost">See Features</a>
        </div>

        <div class="hero-stats">
            <div class="stat">
                <span class="stat-num">0</span><span class="stat-unit">+</span>
                <span class="stat-label">Tournaments</span>
            </div>
            <div class="stat-divider"></div>
            <div class="stat">
                <span class="stat-num">5</span><span class="stat-unit">+</span>
                <span class="stat-label">Athletes</span>
            </div>
            <div class="stat-divider"></div>
            <div class="stat">
                <span class="stat-num">1</span><span class="stat-unit">+</span>
                <span class="stat-label">Communities</span>
            </div>
        </div>
    </div>

    <div class="hero-visual">
        <div class="float-card card-tournament">
            <div class="fc-icon">🏆</div>
            <div class="fc-info">
                <strong>Grand Slam Open 2026</strong>
                <span>Starts in 3 days &bull; 128 slots</span>
            </div>
            <span class="live-badge">Live</span>
        </div>
        <div class="float-card card-players">
            <div class="avatar-row">
                <div class="av av1">A</div>
                <div class="av av2">B</div>
                <div class="av av3">C</div>
                <div class="av av4">+</div>
            </div>
            <span>Athletes registered today</span>
        </div>
        <div class="float-card card-bracket">
            <div class="mini-bracket">
                <div class="mb-col">
                    <div class="mb-match"></div>
                    <div class="mb-match"></div>
                    <div class="mb-match"></div>
                    <div class="mb-match"></div>
                </div>
                <div class="mb-col">
                    <div class="mb-match winner"></div>
                    <div class="mb-match"></div>
                </div>
                <div class="mb-col">
                    <div class="mb-match champion">🏆</div>
                </div>
            </div>
            <span>Auto-Bracket™</span>
        </div>
    </div>
</section>

<!-- ===== FEATURES ===== -->
<section class="features reveal" id="features">
    <div class="section-header">
        <div class="section-label">Platform Features</div>
        <h2 class="section-title">Everything You Need<br>to Run <em>Elite</em> Events</h2>
    </div>

    <div class="feature-grid">
        <div class="feature-card">
            <div class="feature-icon">🏠</div>
            <h3>Dashboard & Main Menu</h3>
            <p>Your central hub for all activity. Monitor upcoming matches, announcements, and quick-access tools from one clean, organized view.</p>
            <span class="card-tag">Overview</span>
        </div>
        <div class="feature-card">
            <div class="feature-icon">💬</div>
            <h3>Chats & Notifications</h3>
            <p>Stay connected with teammates and organizers through real-time messaging and smart alerts for bracket updates and schedule changes.</p>
            <span class="card-tag">Communication</span>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🛒</div>
            <h3>Shop</h3>
            <p>Browse and purchase sports gear, tournament entry fees, and exclusive merchandise directly within the platform.</p>
            <span class="card-tag">Marketplace</span>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🗺️</div>
            <h3>Tournament Finder</h3>
            <p>Discover tournaments near you with an interactive, sport-tagged map. Filter by sport, date, and distance to find your next competition.</p>
            <span class="card-tag">Discovery</span>
        </div>
        <div class="feature-card">
            <div class="feature-icon">📣</div>
            <h3>Community Posts</h3>
            <p>Share updates, highlights, and achievements with the broader TournaMeet community. React, comment, and grow your athlete presence.</p>
            <span class="card-tag">Community</span>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🤝</div>
            <h3>Teams & Friends</h3>
            <p>Build and manage your team, connect with fellow athletes, and coordinate registrations together for a stronger competitive experience.</p>
            <span class="card-tag">Social</span>
        </div>
    </div>
</section>

<!-- ===== FEATURE SHOWCASE ===== -->
<section class="showcase reveal" id="showcase">
    <div class="section-header">
        <div class="section-label">Feature Showcase</div>
        <h2 class="section-title">See It in <em>Action</em></h2>
    </div>

    <!-- Showcase 1: Dashboard -->
    <div class="showcase-block reveal" id="showcase-dashboard">
        <div class="showcase-text">
            <span class="showcase-num">01</span>
            <div class="section-label">Dashboard & Main Menu</div>
            <h3>Your Command Center</h3>
            <p>Everything at a glance. The TournaMeet dashboard brings together your upcoming tournaments, recent activity, team updates, and announcements in one clean, distraction-free view.</p>
            <ul class="showcase-list">
                <li>At-a-glance tournament schedule</li>
                <li>Quick-access to all platform sections</li>
                <li>Personalized activity feed</li>
            </ul>
        </div>
        <div class="showcase-screen">
            <div class="screen-placeholder">
                <span class="screen-icon">🏠</span>
                <span>Dashboard Preview</span>
                <span class="screen-sub">Screenshot or demo coming soon</span>
            </div>
        </div>
    </div>

    <!-- Showcase 2: Chats -->
    <div class="showcase-block reverse reveal" id="showcase-chats">
        <div class="showcase-text">
            <span class="showcase-num">02</span>
            <div class="section-label">Chats & Notifications</div>
            <h3>Stay in the Loop</h3>
            <p>Real-time messaging keeps you connected to your team and organizers. Smart notifications ensure you never miss a bracket update, schedule change, or important announcement.</p>
            <ul class="showcase-list">
                <li>Direct and group messaging</li>
                <li>Smart push notifications</li>
                <li>Bracket and match alerts</li>
            </ul>
        </div>
        <div class="showcase-screen">
            <div class="screen-placeholder">
                <span class="screen-icon">💬</span>
                <span>Chat Interface Preview</span>
                <span class="screen-sub">Screenshot or demo coming soon</span>
            </div>
        </div>
    </div>

    <!-- Showcase 3: Shop -->
    <div class="showcase-block reveal" id="showcase-shop">
        <div class="showcase-text">
            <span class="showcase-num">03</span>
            <div class="section-label">Shop</div>
            <h3>Gear Up for Competition</h3>
            <p>Browse sports equipment, official merchandise, and tournament entry passes — all without leaving the platform. A seamless marketplace built for competitors.</p>
            <ul class="showcase-list">
                <li>Sports gear and accessories</li>
                <li>Tournament entry fee payments</li>
                <li>Exclusive TournaMeet merchandise</li>
            </ul>
        </div>
        <div class="showcase-screen">
            <div class="screen-placeholder">
                <span class="screen-icon">🛒</span>
                <span>Shop Preview</span>
                <span class="screen-sub">Screenshot or demo coming soon</span>
            </div>
        </div>
    </div>

    <!-- Showcase 4: Tournament Finder -->
    <div class="showcase-block reverse reveal" id="showcase-finder">
        <div class="showcase-text">
            <span class="showcase-num">04</span>
            <div class="section-label">Tournament Finder</div>
            <h3>Find Your Next Match</h3>
            <p>An interactive map packed with sport-tagged tournaments in your area. Filter by sport type, date range, and distance to discover exactly the right event for you.</p>
            <ul class="showcase-list">
                <li>Live interactive map</li>
                <li>Sport, date, and distance filters</li>
                <li>One-tap tournament registration</li>
            </ul>
        </div>
        <div class="showcase-screen">
            <div class="screen-placeholder">
                <span class="screen-icon">🗺️</span>
                <span>Tournament Finder Preview</span>
                <span class="screen-sub">Screenshot or demo coming soon</span>
            </div>
        </div>
    </div>

    <!-- Showcase 5: Community Posts -->
    <div class="showcase-block reveal" id="showcase-community">
        <div class="showcase-text">
            <span class="showcase-num">05</span>
            <div class="section-label">Community Posts</div>
            <h3>Share Your Journey</h3>
            <p>Post match highlights, training updates, and achievements with the TournaMeet community. Build your athlete presence and engage with competitors across all sports.</p>
            <ul class="showcase-list">
                <li>Public and team-specific posts</li>
                <li>Reactions, comments, and shares</li>
                <li>Athlete profile integration</li>
            </ul>
        </div>
        <div class="showcase-screen">
            <div class="screen-placeholder">
                <span class="screen-icon">📣</span>
                <span>Community Feed Preview</span>
                <span class="screen-sub">Screenshot or demo coming soon</span>
            </div>
        </div>
    </div>

    <!-- Showcase 6: Teams & Friends -->
    <div class="showcase-block reverse reveal" id="showcase-teams">
        <div class="showcase-text">
            <span class="showcase-num">06</span>
            <div class="section-label">Teams & Friends</div>
            <h3>Compete Together</h3>
            <p>Create or join a team, connect with fellow athletes, and register for tournaments as a group. Build lasting relationships through sport and shared competitive ambition.</p>
            <ul class="showcase-list">
                <li>Team creation and management</li>
                <li>Friend requests and athlete profiles</li>
                <li>Group tournament registration</li>
            </ul>
        </div>
        <div class="showcase-screen">
            <div class="screen-placeholder">
                <span class="screen-icon">🤝</span>
                <span>Teams &amp; Friends Preview</span>
                <span class="screen-sub">Screenshot or demo coming soon</span>
            </div>
        </div>
    </div>
</section>

<!-- ===== ABOUT ===== -->
<section class="about reveal" id="about">
    <div class="about-inner">
        <div class="about-text">
            <div class="section-label">About TournaMeet</div>
            <h2 class="section-title">Built for the<br><em>Competitive Spirit</em></h2>
            <p>TournaMeet is dedicated to connecting athletes, organizers, and communities through a reliable and structured digital platform built for competitive excellence.</p>
            <p>Whether you're running a local community tournament or a national championship, our tools scale with your ambition — no technical expertise required.</p>
            <a href="#" class="btn-outline">Learn Our Story &rarr;</a>
        </div>
        <div class="about-cards">
            <div class="about-card sports-card">
                <div class="sports-icons">
                    <span>🏀</span><span>🏐</span><span>⚽</span>
                    <span>🎾</span><span>🏸</span><span>⚾</span>
                </div>
                <span class="about-card-label">6+ Sports Supported</span>
            </div>
            <div class="about-card metrics-card">
                <div class="metric-item">
                    <span class="mn">100%</span>
                    <span class="ml">Cloud-Based</span>
                </div>
                <div class="metric-item">
                    <span class="mn">24/7</span>
                    <span class="ml">Uptime SLA</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== CTA ===== -->
<section class="cta reveal">
    <div class="cta-pattern"></div>
    <div class="cta-content">
        <div class="section-label light">Get Started Free</div>
        <h2>Start Building<br>Opportunities <em>Today</em></h2>
        <p>No account fees. Set up your first tournament in under 5 minutes.</p>
        <div class="cta-actions">
            <a href="http://localhost/Tourna/register.php" class="btn-primary large">
                Create Free Account
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
            <a href="http://localhost/registration/index.php" class="btn-ghost-white">Browse Tournaments</a>
        </div>
    </div>
</section>

<!-- ===== FOOTER (unified) ===== -->
<footer id="contact">
    <div class="footer-inner">
        <div class="footer-brand">
            <a href="Landingpage.html" class="logo">
                <img src="DARK ICON-Tournameet.png" alt="TournaMeet" class="logo-img logo-light">
                <img src="LIGHT ICON-Tournameet.png" alt="TournaMeet" class="logo-img logo-dark">
                <span class="logo-text">TournaMeet</span>
            </a>
            <p>The platform where athletes<br>meet their opportunities.</p>
        </div>
        <div class="footer-links">
            <div class="footer-col">
                <strong>Platform</strong>
                <a href="#features">Features</a>
                <a href="#showcase">Showcase</a>
                <a href="#">Changelog</a>
            </div>
            <div class="footer-col">
                <strong>Company</strong>
                <a href="#about">About</a>
                <a href="story.html">Our Story</a>
                <a href="#">Careers</a>
            </div>
            <div class="footer-col">
                <strong>Connect</strong>
                <a href="#">Twitter / X</a>
                <a href="#">Instagram</a>
                <a href="#">Discord</a>
            </div>
            <div class="footer-col">
                <strong>Legal</strong>
                <a href="legal.html">Terms &amp; Conditions</a>
                <a href="legal.html?doc=privacy">Privacy Policy</a>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; 2026 TournaMeet. All rights reserved.</p>
        <div class="footer-legal">
            <a href="story.html">Our Story</a>
            <a href="legal.html?doc=privacy">Privacy Policy</a>
            <a href="legal.html">Terms &amp; Conditions</a>
        </div>
    </div>
</footer>

<link rel="stylesheet" href="LP.css">
<script src="LP.js"></script>
</body>
</html>