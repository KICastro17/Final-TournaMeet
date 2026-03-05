/* ============================================================
   TOURNAMEET — script.js
   ============================================================ */

/* ===== THEME TOGGLE ===== */
const themeToggles = [
    document.getElementById("theme-toggle"),
    document.getElementById("theme-toggle-mobile"),
];

function setTheme(mode) {
    if (mode === "light") {
        document.body.classList.add("light");
    } else {
        document.body.classList.remove("light");
    }
    themeToggles.forEach(btn => {
        if (!btn) return;
        const icon  = btn.querySelector(".theme-icon");
        const label = btn.querySelector(".theme-label");
        if (icon)  icon.textContent  = mode === "light" ? "🌙" : "☀";
        if (label) label.textContent = mode === "light" ? "Dark" : "Light";
    });
    localStorage.setItem("tm-theme", mode);
}

// Apply saved preference on load
setTheme(localStorage.getItem("tm-theme") || "dark");

themeToggles.forEach(btn => {
    if (!btn) return;
    btn.addEventListener("click", () => {
        const isDark = !document.body.classList.contains("light");
        setTheme(isDark ? "light" : "dark");
    });
});


/* ===== NAVBAR SCROLL SHADOW ===== */
const navbar = document.getElementById("navbar");

window.addEventListener("scroll", () => {
    navbar.classList.toggle("scrolled", window.scrollY > 60);
}, { passive: true });


/* ===== HAMBURGER MENU ===== */
const hamburger = document.getElementById("hamburger");
const mobileNav = document.getElementById("mobile-nav");

hamburger.addEventListener("click", () => {
    const isOpen = hamburger.classList.toggle("open");
    mobileNav.classList.toggle("open", isOpen);
    hamburger.setAttribute("aria-expanded", isOpen);
    mobileNav.setAttribute("aria-hidden", !isOpen);
});

// Close when a mobile link is clicked
mobileNav.querySelectorAll(".mobile-link").forEach(link => {
    link.addEventListener("click", () => {
        hamburger.classList.remove("open");
        mobileNav.classList.remove("open");
        hamburger.setAttribute("aria-expanded", "false");
        mobileNav.setAttribute("aria-hidden", "true");
    });
});

// Close when clicking outside
document.addEventListener("click", (e) => {
    if (!navbar.contains(e.target) && mobileNav.classList.contains("open")) {
        hamburger.classList.remove("open");
        mobileNav.classList.remove("open");
        hamburger.setAttribute("aria-expanded", "false");
        mobileNav.setAttribute("aria-hidden", "true");
    }
});


/* ===== SCROLL REVEAL ===== */
const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add("active");
            revealObserver.unobserve(entry.target);
        }
    });
}, { threshold: 0.10, rootMargin: "0px 0px -50px 0px" });

document.querySelectorAll(".reveal").forEach(el => revealObserver.observe(el));


/* ===== ACTIVE NAV LINK ON SCROLL ===== */
const sections   = document.querySelectorAll("section[id], footer[id]");
const navAnchors = document.querySelectorAll(".nav-links a[href^='#']");

const sectionObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const id = entry.target.getAttribute("id");
            navAnchors.forEach(a => {
                a.classList.toggle("active-link", a.getAttribute("href") === `#${id}`);
            });
        }
    });
}, { rootMargin: "-40% 0px -50% 0px" });

sections.forEach(s => sectionObserver.observe(s));


/* ===== MAP (only runs if Leaflet + map element are present) ===== */
if (typeof L !== "undefined" && document.getElementById("map")) {
    const map = L.map("map").setView([16.4023, 120.5960], 13);

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        attribution: "© OpenStreetMap contributors"
    }).addTo(map);

    function iconForSport(sport) {
        const icons = {
            Basketball: ["🏀", "basketball"],
            Football:   ["⚽", "football"],
            Volleyball: ["🏐", "volleyball"],
            Tennis:     ["🎾", "tennis"],
            Badminton:  ["🏸", "badminton"],
            Baseball:   ["⚾", "baseball"],
        };
        const [emoji, cls] = icons[sport] || ["🏆", ""];
        return L.divIcon({
            html: `<div class="t-marker ${cls}">${emoji}</div>`,
            className: "",
            iconSize: [40, 40],
            iconAnchor: [20, 40],
        });
    }

    function renderList() {
        const list    = document.getElementById("tournamentList");
        const search  = document.getElementById("searchInput")?.value.toLowerCase() || "";
        const filter  = document.getElementById("sportFilter")?.value || "All";
        if (!list || typeof tournaments === "undefined") return;

        list.innerHTML = "";
        tournaments.forEach(t => {
            if ((filter === "All" || t.sport === filter) && t.name.toLowerCase().includes(search)) {
                const card = document.createElement("div");
                card.className = "tournament-card";
                card.innerHTML = `
                    <strong>${t.name}</strong>
                    <small>${t.sport}</small>
                    <small>${t.date_time}</small>
                    <button onclick="focusTournament(${t.id})">See More</button>
                `;
                card.addEventListener("click", () => focusTournament(t.id));
                list.appendChild(card);
            }
        });
    }

    if (typeof tournaments !== "undefined") {
        renderList();
        tournaments.forEach(t => {
            t.marker = L.marker([t.lat, t.lon], { icon: iconForSport(t.sport) })
                .addTo(map)
                .on("click", () => focusTournament(t.id));
        });
    }

    window.focusTournament = function (id) {
        const t = tournaments.find(x => x.id == id);
        if (!t) return;
        map.flyTo([t.lat, t.lon], 16);
        if (typeof openSidebar === "function") openSidebar();
    };

    document.getElementById("searchInput")?.addEventListener("input", renderList);
    document.getElementById("sportFilter")?.addEventListener("change", renderList);
}