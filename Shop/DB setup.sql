
CREATE DATABASE IF NOT EXISTS ballsports CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ballsports;

-- ── PRODUCTS ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS products (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(200)   NOT NULL,
    cat         VARCHAR(100)   NOT NULL,
    price       DECIMAL(10,2)  NOT NULL,
    stock       INT            NOT NULL DEFAULT 10,
    total_stock INT            NOT NULL DEFAULT 20,
    rating      DECIMAL(3,1)   NOT NULL DEFAULT 5.0,
    review_count INT           NOT NULL DEFAULT 0,
    badge       VARCHAR(20)    DEFAULT '',
    badge_text  VARCHAR(50)    DEFAULT '',
    description TEXT,
    specs_json  JSON,
    links_json  JSON,
    is_new      TINYINT(1)     DEFAULT 0,
    created_at  TIMESTAMP      DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── REVIEWS ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS reviews (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    product_id   INT            NOT NULL,
    reviewer     VARCHAR(150)   NOT NULL,
    rating       TINYINT        NOT NULL,
    comment      TEXT           NOT NULL,
    created_at   TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── ORDERS ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS orders (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    ref           VARCHAR(30)    NOT NULL UNIQUE,
    customer_json JSON           NOT NULL,
    address_json  JSON           NOT NULL,
    items_json    JSON           NOT NULL,
    payment       VARCHAR(20)    NOT NULL,
    subtotal      DECIMAL(10,2)  NOT NULL,
    discount      DECIMAL(10,2)  NOT NULL DEFAULT 0,
    shipping      DECIMAL(10,2)  NOT NULL DEFAULT 150,
    total         DECIMAL(10,2)  NOT NULL,
    coupon        VARCHAR(30)    DEFAULT '',
    status        VARCHAR(30)    NOT NULL DEFAULT 'Order Placed',
    created_at    TIMESTAMP      DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── ORDER STATUS HISTORY ──────────────────────────────────
CREATE TABLE IF NOT EXISTS order_history (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    order_ref  VARCHAR(30)  NOT NULL,
    status     VARCHAR(30)  NOT NULL,
    note       VARCHAR(300) DEFAULT '',
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_ref) REFERENCES orders(ref) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ════════════════════════════════════════════════════════════
--  SEED: DEFAULT PRODUCTS
-- ════════════════════════════════════════════════════════════
INSERT INTO products (name, cat, price, stock, total_stock, rating, review_count, badge, badge_text, description, specs_json, links_json, is_new) VALUES

('Pro Basketball', 'Basketball', 1500, 10, 20, 4.8, 124, 'best', 'BEST SELLER',
 'Official size & weight. Premium composite leather for indoor and outdoor courts. Superior grip channel design for better control.',
 '{"Brand":"Spalding","Size":"Size 7","Material":"Composite Leather","Weight":"620g"}',
 '[{"name":"Shopee PH","type":"shopee","url":"https://shopee.ph/search?keyword=spalding+basketball"},{"name":"Lazada PH","type":"lazada","url":"https://www.lazada.com.ph/catalog/?q=spalding+basketball"},{"name":"Spalding Official","type":"other","url":"https://www.spalding.com"}]',
 0),

('Basketball Hoop Set', 'Basketball', 4200, 6, 10, 4.6, 58, 'new', 'NEW ARRIVAL',
 'Portable adjustable hoop from 7.5ft to 10ft. Heavy-duty 180L base. Easy assembly for driveways and courts.',
 '{"Brand":"Lifetime","Type":"Portable","Height":"7.5–10ft","Base":"180L"}',
 '[{"name":"Shopee PH","type":"shopee","url":"https://shopee.ph/search?keyword=basketball+hoop+set"},{"name":"Lazada PH","type":"lazada","url":"https://www.lazada.com.ph/catalog/?q=basketball+hoop+set"}]',
 1),

('Basketball Jersey Set', 'Basketball', 850, 18, 30, 4.5, 200, 'sale', 'SALE',
 'Moisture-wicking breathable mesh. Sleeveless for free movement. Available in multiple team colors, sizes S–3XL.',
 '{"Brand":"Nike","Fabric":"100% Polyester","Fit":"Regular","Sizes":"S–3XL"}',
 '[{"name":"Shopee PH","type":"shopee","url":"https://shopee.ph/search?keyword=basketball+jersey"},{"name":"Lazada PH","type":"lazada","url":"https://www.lazada.com.ph/catalog/?q=basketball+jersey"},{"name":"Nike Official","type":"other","url":"https://www.nike.com/ph"}]',
 0),

('Basketball Shoes', 'Basketball', 3600, 8, 15, 4.7, 93, 'sale', 'SALE',
 'High-top ankle support, Zoom Air cushioning. Herringbone traction pattern for multi-directional court grip.',
 '{"Brand":"Nike","Type":"High-Top","Cushion":"Zoom Air","Sizes":"6–14 US"}',
 '[{"name":"Shopee PH","type":"shopee","url":"https://shopee.ph/search?keyword=nike+basketball+shoes"},{"name":"Lazada PH","type":"lazada","url":"https://www.lazada.com.ph/catalog/?q=basketball+shoes"},{"name":"Nike Official","type":"other","url":"https://www.nike.com/ph"}]',
 0),

('Soccer Ball Pro', 'Football', 1200, 14, 20, 4.9, 310, 'top', 'TOP RATED',
 'FIFA-approved match ball. Thermally bonded seamless surface for consistent flight. 32-panel true-round design.',
 '{"Brand":"Adidas","Size":"Size 5","Panels":"32","Approval":"FIFA Quality Pro"}',
 '[{"name":"Shopee PH","type":"shopee","url":"https://shopee.ph/search?keyword=adidas+soccer+ball"},{"name":"Lazada PH","type":"lazada","url":"https://www.lazada.com.ph/catalog/?q=soccer+ball"},{"name":"Adidas Official","type":"other","url":"https://www.adidas.com/ph"}]',
 0),

('Football Cleats', 'Football', 2800, 7, 15, 4.6, 87, 'sale', 'SALE',
 'Lightweight synthetic upper with conical firm-ground studs. Enhanced ankle support for grass surfaces.',
 '{"Brand":"Puma","Surface":"Firm Ground","Upper":"Synthetic","Sizes":"38–46 EU"}',
 '[{"name":"Shopee PH","type":"shopee","url":"https://shopee.ph/search?keyword=football+cleats"},{"name":"Lazada PH","type":"lazada","url":"https://www.lazada.com.ph/catalog/?q=football+cleats"},{"name":"Puma Official","type":"other","url":"https://ph.puma.com"}]',
 0),

('Goalkeeper Gloves', 'Football', 950, 12, 20, 4.7, 65, 'new', 'NEW ARRIVAL',
 'Super-grip palm latex. Flexible backhand with ventilation holes. Roll-finger cut for excellent ball feel.',
 '{"Brand":"Reusch","Cut":"Roll Finger","Palm":"4mm Latex","Sizes":"6–12"}',
 '[{"name":"Shopee PH","type":"shopee","url":"https://shopee.ph/search?keyword=goalkeeper+gloves"},{"name":"Lazada PH","type":"lazada","url":"https://www.lazada.com.ph/catalog/?q=goalkeeper+gloves"}]',
 1),

('Football Jersey', 'Football', 780, 22, 30, 4.4, 145, 'sale', 'SALE',
 'Official replica jersey. Dri-FIT technology for sweat management. Machine washable.',
 '{"Brand":"Adidas","Fit":"Slim","Fabric":"Recycled Polyester","Sizes":"XS–2XL"}',
 '[{"name":"Shopee PH","type":"shopee","url":"https://shopee.ph/search?keyword=football+jersey"},{"name":"Lazada PH","type":"lazada","url":"https://www.lazada.com.ph/catalog/?q=football+jersey"},{"name":"Adidas Official","type":"other","url":"https://www.adidas.com/ph"}]',
 0),

('Volleyball Set', 'Volleyball', 3200, 5, 12, 4.7, 42, 'sale', 'SALE',
 'Complete set: indoor volleyball, adjustable net 9.5m×1m, poles, stakes, hand pump.',
 '{"Brand":"Mikasa","Includes":"Ball+Net+Pump","Net":"9.5m×1m","Ball":"Size 5"}',
 '[{"name":"Shopee PH","type":"shopee","url":"https://shopee.ph/search?keyword=mikasa+volleyball+set"},{"name":"Lazada PH","type":"lazada","url":"https://www.lazada.com.ph/catalog/?q=volleyball+set"},{"name":"Mikasa Official","type":"other","url":"https://www.mikasasports.com"}]',
 0),

('Volleyball Knee Pads', 'Volleyball', 650, 20, 25, 4.4, 98, '', '',
 'High-density foam padding. Neoprene sleeve for compression. Anti-slip inner lining. Pair.',
 '{"Brand":"Asics","Material":"Neoprene+Foam","Sizes":"S–XL","Colors":"Multiple"}',
 '[{"name":"Shopee PH","type":"shopee","url":"https://shopee.ph/search?keyword=volleyball+knee+pads"},{"name":"Lazada PH","type":"lazada","url":"https://www.lazada.com.ph/catalog/?q=volleyball+knee+pads"},{"name":"Asics Official","type":"other","url":"https://www.asics.com/ph"}]',
 0),

('Tennis Racket Pro', 'Tennis', 3800, 9, 15, 4.8, 76, 'best', 'BEST SELLER',
 '100% graphite frame. Mid-plus 100 sq.in. head. Pre-strung at 55 lbs. Great for all skill levels.',
 '{"Brand":"Wilson","Head":"100 sq.in.","Weight":"285g","String":"Synthetic Gut"}',
 '[{"name":"Shopee PH","type":"shopee","url":"https://shopee.ph/search?keyword=wilson+tennis+racket"},{"name":"Lazada PH","type":"lazada","url":"https://www.lazada.com.ph/catalog/?q=tennis+racket"},{"name":"Wilson Official","type":"other","url":"https://www.wilson.com/ph"}]',
 0),

('Tennis Ball Set (3)', 'Tennis', 280, 40, 50, 4.5, 230, '', '',
 'ITF-approved extra-duty felt balls. Hard and clay courts. High visibility optic yellow. Pack of 3.',
 '{"Brand":"Penn","Count":"3 Balls","Surface":"All Court","Approval":"ITF Approved"}',
 '[{"name":"Shopee PH","type":"shopee","url":"https://shopee.ph/search?keyword=penn+tennis+balls"},{"name":"Lazada PH","type":"lazada","url":"https://www.lazada.com.ph/catalog/?q=tennis+balls"}]',
 0),

('Badminton Racket', 'Badminton', 1800, 14, 20, 4.7, 108, 'sale', 'SALE',
 'High-modulus graphite frame. Aerodynamic oval head. Pre-strung at 22 lbs. Excellent power and control.',
 '{"Brand":"Yonex","Frame":"Full Graphite","Weight":"88g","String":"Pre-strung 22lbs"}',
 '[{"name":"Shopee PH","type":"shopee","url":"https://shopee.ph/search?keyword=yonex+badminton+racket"},{"name":"Lazada PH","type":"lazada","url":"https://www.lazada.com.ph/catalog/?q=badminton+racket"},{"name":"Yonex Official","type":"other","url":"https://www.yonex.com"}]',
 0),

('Shuttlecock Set (12)', 'Badminton', 380, 35, 40, 4.5, 180, 'top', 'TOP RATED',
 'Feather shuttlecocks for tournament play. Consistent flight and great durability. Pack of 12.',
 '{"Brand":"Victor","Type":"Feather","Pack":"12 pieces","Speed":"75 (Medium)"}',
 '[{"name":"Shopee PH","type":"shopee","url":"https://shopee.ph/search?keyword=badminton+shuttlecock"},{"name":"Lazada PH","type":"lazada","url":"https://www.lazada.com.ph/catalog/?q=shuttlecock+set"}]',
 0),

('Swimming Goggles', 'Swimming', 720, 15, 20, 4.6, 143, 'sale', 'SALE',
 'Anti-fog UV400 lenses. Adjustable silicone nose bridge. Soft gasket for watertight seal.',
 '{"Brand":"Speedo","Lens":"Anti-fog UV400","Frame":"Polycarbonate","Type":"Competitive"}',
 '[{"name":"Shopee PH","type":"shopee","url":"https://shopee.ph/search?keyword=speedo+swimming+goggles"},{"name":"Lazada PH","type":"lazada","url":"https://www.lazada.com.ph/catalog/?q=swimming+goggles"},{"name":"Speedo Official","type":"other","url":"https://www.speedo.com.ph"}]',
 0),

('Silicone Swim Cap', 'Swimming', 350, 25, 30, 4.3, 89, '', '',
 '100% silicone for durability. Reduces drag in water. Fits most head sizes. Multiple colors.',
 '{"Brand":"Arena","Material":"100% Silicone","Type":"Training & Race","Size":"One Size"}',
 '[{"name":"Shopee PH","type":"shopee","url":"https://shopee.ph/search?keyword=swimming+cap"},{"name":"Lazada PH","type":"lazada","url":"https://www.lazada.com.ph/catalog/?q=swim+cap"}]',
 0),

('Baseball Glove', 'Baseball', 2200, 8, 12, 4.7, 54, 'new', 'NEW ARRIVAL',
 'Full-grain leather palm. Deep pocket for infielders. Adjustable wrist strap for secure fit.',
 '{"Brand":"Rawlings","Size":"11.5 inch","Position":"Infield","Material":"Full-grain Leather"}',
 '[{"name":"Shopee PH","type":"shopee","url":"https://shopee.ph/search?keyword=rawlings+baseball+glove"},{"name":"Lazada PH","type":"lazada","url":"https://www.lazada.com.ph/catalog/?q=baseball+glove"},{"name":"Rawlings Official","type":"other","url":"https://www.rawlings.com"}]',
 1),

('Baseball Bat Alloy', 'Baseball', 1800, 6, 10, 4.5, 38, 'sale', 'SALE',
 '7050 aircraft alloy barrel for maximum pop. 2-piece construction reduces vibration. BBCOR certified.',
 '{"Brand":"Easton","Length":"32 inch","Weight":"-3","Certification":"BBCOR"}',
 '[{"name":"Shopee PH","type":"shopee","url":"https://shopee.ph/search?keyword=easton+baseball+bat"},{"name":"Lazada PH","type":"lazada","url":"https://www.lazada.com.ph/catalog/?q=baseball+bat"},{"name":"Easton Official","type":"other","url":"https://www.easton.com"}]',
 0),

('Running Shoes', 'Running', 3400, 10, 18, 4.8, 256, 'best', 'BEST SELLER',
 'Responsive foam midsole for energy return. Breathable knit upper. Rubber outsole for road and track.',
 '{"Brand":"Brooks","Type":"Road Running","Midsole":"DNA Loft Foam","Sizes":"5–13 US"}',
 '[{"name":"Shopee PH","type":"shopee","url":"https://shopee.ph/search?keyword=brooks+running+shoes"},{"name":"Lazada PH","type":"lazada","url":"https://www.lazada.com.ph/catalog/?q=running+shoes"},{"name":"Brooks Official","type":"other","url":"https://www.brooksrunning.com"}]',
 0),

('Running Belt Pack', 'Running', 550, 18, 22, 4.5, 87, '', '',
 'Lightweight waist pack. Two zip pockets, adjustable band. Fits phone, keys, and energy gels.',
 '{"Brand":"Nathan","Volume":"1L","Closure":"Zippered x2","Strap":"Adjustable"}',
 '[{"name":"Shopee PH","type":"shopee","url":"https://shopee.ph/search?keyword=running+belt+pack"},{"name":"Lazada PH","type":"lazada","url":"https://www.lazada.com.ph/catalog/?q=running+belt"}]',
 0),

('Compression Socks', 'Running', 320, 28, 35, 4.6, 193, 'top', 'TOP RATED',
 'Graduated compression 20–30mmHg. Reduces fatigue and muscle vibration. Moisture-wicking nylon blend.',
 '{"Brand":"CEP","Compression":"20–30mmHg","Material":"Nylon Blend","Sizes":"S–XL"}',
 '[{"name":"Shopee PH","type":"shopee","url":"https://shopee.ph/search?keyword=compression+running+socks"},{"name":"Lazada PH","type":"lazada","url":"https://www.lazada.com.ph/catalog/?q=compression+socks"}]',
 0),

('Sports Bag XL', 'Accessories', 1750, 10, 15, 4.4, 167, '', '',
 '65L duffel with vented shoe compartment and multiple zip pockets. Water-resistant 600D nylon.',
 '{"Brand":"Under Armour","Volume":"65L","Material":"600D Nylon","Feature":"Vented Shoe Pocket"}',
 '[{"name":"Shopee PH","type":"shopee","url":"https://shopee.ph/search?keyword=under+armour+sports+bag"},{"name":"Lazada PH","type":"lazada","url":"https://www.lazada.com.ph/catalog/?q=sports+duffel+bag"},{"name":"Under Armour","type":"other","url":"https://www.underarmour.com.ph"}]',
 0),

('Water Bottle 750ml', 'Accessories', 420, 30, 40, 4.6, 312, 'top', 'TOP RATED',
 'BPA-free Tritan 750ml. Leak-proof flip lid. Wide mouth for easy cleaning. Fits standard cup holders.',
 '{"Brand":"Hydro Flask","Volume":"750ml","Material":"Tritan BPA-Free","Lid":"Flip-Top"}',
 '[{"name":"Shopee PH","type":"shopee","url":"https://shopee.ph/search?keyword=hydro+flask"},{"name":"Lazada PH","type":"lazada","url":"https://www.lazada.com.ph/catalog/?q=hydro+flask"},{"name":"Hydro Flask Official","type":"other","url":"https://www.hydroflask.com"}]',
 0),

('Resistance Band Set', 'Accessories', 680, 22, 30, 4.5, 134, 'sale', 'SALE',
 'Set of 5 bands with varying resistance levels. Latex-free material. For warm-up, rehab, and strength training.',
 '{"Brand":"TheraBand","Pieces":"5 bands","Material":"Latex-Free","Levels":"Light to X-Heavy"}',
 '[{"name":"Shopee PH","type":"shopee","url":"https://shopee.ph/search?keyword=resistance+band+set"},{"name":"Lazada PH","type":"lazada","url":"https://www.lazada.com.ph/catalog/?q=resistance+bands"}]',
 0),

('Sports First Aid Kit', 'Accessories', 890, 15, 20, 4.7, 79, 'new', 'NEW ARRIVAL',
 '80-piece portable first aid kit. Bandages, cold packs, sports tapes, and antiseptic wipes. Waterproof case.',
 '{"Brand":"Be Smart Get Prepared","Pieces":"80 items","Type":"Sports First Aid","Case":"Waterproof"}',
 '[{"name":"Shopee PH","type":"shopee","url":"https://shopee.ph/search?keyword=sports+first+aid+kit"},{"name":"Lazada PH","type":"lazada","url":"https://www.lazada.com.ph/catalog/?q=first+aid+kit"}]',
 1);