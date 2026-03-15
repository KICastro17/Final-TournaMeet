<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

require_once 'config.php';

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

// ── ADMIN AUTH ──────────────────────────────
function isAdmin(): bool {
    $token = $_SERVER['HTTP_X_ADMIN_TOKEN'] ?? ($_COOKIE['admin_token'] ?? '');
    return $token === 'admin123';
}

// ════════════════════════════════════════════
//  ROUTE
// ════════════════════════════════════════════
switch ($action) {

    // ── GET ALL PRODUCTS ──────────────────
    case 'products':
        $db  = getDB();
        $cat = $_GET['cat'] ?? '';
        $q   = $_GET['q']   ?? '';
        $sql = "SELECT p.*, COALESCE(AVG(r.rating), p.rating) AS avg_rating,
                       (p.review_count + COUNT(r.id)) AS total_reviews
                FROM products p
                LEFT JOIN reviews r ON r.product_id = p.id";
        $where = []; $params = [];
        if ($cat && $cat !== 'All') { $where[] = 'p.cat = ?'; $params[] = $cat; }
        if ($q)                     { $where[] = '(p.name LIKE ? OR p.cat LIKE ? OR p.description LIKE ?)'; $params = array_merge($params, ["%$q%", "%$q%", "%$q%"]); }
        if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
        $sql .= ' GROUP BY p.id ORDER BY p.id';
        $stmt = $db->prepare($sql); $stmt->execute($params);
        $rows = $stmt->fetchAll();
        foreach ($rows as &$row) {
            $row['specs'] = json_decode($row['specs_json'] ?? '{}', true) ?: [];
            $row['links'] = json_decode($row['links_json'] ?? '[]', true) ?: [];
            $row['price'] = (float)$row['price'];
            $row['avg_rating'] = round((float)$row['avg_rating'], 1);
            $row['total_reviews'] = (int)$row['total_reviews'];
            unset($row['specs_json'], $row['links_json']);
        }
        jsonResponse($rows);

    // ── GET SINGLE PRODUCT ────────────────
    case 'product':
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) jsonResponse(['error' => 'Missing id'], 400);
        $db = getDB();
        $stmt = $db->prepare("SELECT p.*, COALESCE(AVG(r.rating), p.rating) AS avg_rating,
                              (p.review_count + COUNT(r.id)) AS total_reviews
                              FROM products p LEFT JOIN reviews r ON r.product_id = p.id
                              WHERE p.id = ? GROUP BY p.id");
        $stmt->execute([$id]); $row = $stmt->fetch();
        if (!$row) jsonResponse(['error' => 'Not found'], 404);
        $row['specs'] = json_decode($row['specs_json'] ?? '{}', true) ?: [];
        $row['links'] = json_decode($row['links_json'] ?? '[]', true) ?: [];
        unset($row['specs_json'], $row['links_json']);
        jsonResponse($row);

    // ── GET REVIEWS FOR PRODUCT ───────────
    case 'reviews':
        $pid = (int)($_GET['product_id'] ?? 0);
        if (!$pid) jsonResponse(['error' => 'Missing product_id'], 400);
        $db = getDB();
        $stmt = $db->prepare("SELECT id, reviewer, rating, comment,
                              DATE_FORMAT(created_at, '%b %d, %Y') AS date
                              FROM reviews WHERE product_id = ? ORDER BY created_at DESC");
        $stmt->execute([$pid]);
        jsonResponse($stmt->fetchAll());

    // ── SUBMIT REVIEW ─────────────────────
    case 'add_review':
        if ($method !== 'POST') jsonResponse(['error' => 'POST required'], 405);
        $body    = getJsonBody();
        $pid     = (int)($body['product_id'] ?? 0);
        $name    = clean($body['reviewer']   ?? '');
        $rating  = (int)($body['rating']     ?? 0);
        $comment = clean($body['comment']    ?? '');
        if (!$pid || !$name || $rating < 1 || $rating > 5 || !$comment)
            jsonResponse(['error' => 'Missing fields'], 400);
        $db = getDB();
        $db->prepare("INSERT INTO reviews (product_id, reviewer, rating, comment) VALUES (?,?,?,?)")
           ->execute([$pid, $name, $rating, $comment]);
        jsonResponse(['success' => true, 'id' => $db->lastInsertId()]);

    // ── PLACE ORDER ───────────────────────
    case 'place_order':
        if ($method !== 'POST') jsonResponse(['error' => 'POST required'], 405);
        $body = getJsonBody();
        $required = ['customer', 'address', 'items', 'payment', 'subtotal', 'total'];
        foreach ($required as $k) {
            if (empty($body[$k])) jsonResponse(['error' => "Missing: $k"], 400);
        }
        $ref = 'BS-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
        $db  = getDB();
        $db->prepare("INSERT INTO orders (ref, customer_json, address_json, items_json, payment,
                      subtotal, discount, shipping, total, coupon, status)
                      VALUES (?,?,?,?,?,?,?,?,?,?,?)")
           ->execute([
               $ref,
               json_encode($body['customer']),
               json_encode($body['address']),
               json_encode($body['items']),
               $body['payment'],
               $body['subtotal'],
               $body['discount'] ?? 0,
               $body['shipping'] ?? 150,
               $body['total'],
               $body['coupon'] ?? '',
               'Order Placed'
           ]);
        // Insert initial history
        $db->prepare("INSERT INTO order_history (order_ref, status, note) VALUES (?,?,?)")
           ->execute([$ref, 'Order Placed', 'Your order has been received and is being processed.']);
        // Reduce stock
        foreach ($body['items'] as $item) {
            $db->prepare("UPDATE products SET stock = GREATEST(0, stock - ?) WHERE id = ?")
               ->execute([(int)$item['qty'], (int)$item['id']]);
        }
        jsonResponse(['success' => true, 'ref' => $ref]);

    // ── GET ORDERS (by email or all for admin) ──
    case 'orders':
        $db    = getDB();
        $email = $_GET['email'] ?? '';
        $q     = $_GET['q']    ?? '';
        $filt  = $_GET['status'] ?? '';
        if ($email) {
            // Customer lookup by email
            $stmt = $db->prepare("SELECT * FROM orders WHERE JSON_EXTRACT(customer_json,'$.em') = ? ORDER BY created_at DESC");
            $stmt->execute([$email]);
        } elseif (isAdmin()) {
            $sql = "SELECT * FROM orders WHERE 1=1";
            $params = [];
            if ($q) { $sql .= " AND (ref LIKE ? OR JSON_EXTRACT(customer_json,'$.fn') LIKE ? OR JSON_EXTRACT(customer_json,'$.ln') LIKE ?)"; $params = ["%$q%","%$q%","%$q%"]; }
            if ($filt && $filt !== 'all') { $sql .= " AND status = ?"; $params[] = $filt; }
            $sql .= " ORDER BY created_at DESC";
            $stmt = $db->prepare($sql); $stmt->execute($params);
        } else {
            jsonResponse(['error' => 'Unauthorized'], 401);
        }
        $orders = $stmt->fetchAll();
        foreach ($orders as &$o) {
            $o['customer'] = json_decode($o['customer_json'], true);
            $o['address']  = json_decode($o['address_json'],  true);
            $o['items']    = json_decode($o['items_json'],     true);
            unset($o['customer_json'], $o['address_json'], $o['items_json']);
            // Get history
            $h = $db->prepare("SELECT status, note, DATE_FORMAT(created_at,'%b %d, %Y') AS date FROM order_history WHERE order_ref = ? ORDER BY created_at ASC");
            $h->execute([$o['ref']]);
            $o['statusHistory'] = $h->fetchAll();
            $o['date'] = date('F j, Y g:i A', strtotime($o['created_at']));
        }
        jsonResponse($orders);

    // ── GET SINGLE ORDER (by ref) ─────────
    case 'order':
        $ref = $_GET['ref'] ?? '';
        if (!$ref) jsonResponse(['error' => 'Missing ref'], 400);
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM orders WHERE ref = ?");
        $stmt->execute([$ref]); $o = $stmt->fetch();
        if (!$o) jsonResponse(['error' => 'Not found'], 404);
        $o['customer'] = json_decode($o['customer_json'], true);
        $o['address']  = json_decode($o['address_json'],  true);
        $o['items']    = json_decode($o['items_json'],     true);
        unset($o['customer_json'], $o['address_json'], $o['items_json']);
        $h = $db->prepare("SELECT status, note, DATE_FORMAT(created_at,'%b %d, %Y') AS date FROM order_history WHERE order_ref = ? ORDER BY created_at ASC");
        $h->execute([$ref]); $o['statusHistory'] = $h->fetchAll();
        $o['date'] = date('F j, Y g:i A', strtotime($o['created_at']));
        jsonResponse($o);

    // ── CANCEL ORDER ──────────────────────
    case 'cancel_order':
        if ($method !== 'POST') jsonResponse(['error' => 'POST required'], 405);
        $body = getJsonBody();
        $ref  = $body['ref'] ?? '';
        if (!$ref) jsonResponse(['error' => 'Missing ref'], 400);
        $db   = getDB();
        $stmt = $db->prepare("SELECT status, items_json FROM orders WHERE ref = ?");
        $stmt->execute([$ref]); $o = $stmt->fetch();
        if (!$o) jsonResponse(['error' => 'Order not found'], 404);
        if (!in_array($o['status'], ['Order Placed', 'Confirmed']))
            jsonResponse(['error' => 'Order cannot be cancelled at this stage.'], 400);
        // Restore stock
        $items = json_decode($o['items_json'], true) ?? [];
        foreach ($items as $item) {
            $db->prepare("UPDATE products SET stock = LEAST(total_stock, stock + ?) WHERE id = ?")
               ->execute([(int)$item['qty'], (int)$item['id']]);
        }
        $db->prepare("UPDATE orders SET status = 'Cancelled' WHERE ref = ?")
           ->execute([$ref]);
        $db->prepare("INSERT INTO order_history (order_ref, status, note) VALUES (?,?,?)")
           ->execute([$ref, 'Cancelled', 'Order was cancelled by customer.']);
        jsonResponse(['success' => true]);

    // ── UPDATE ORDER STATUS (admin) ───────
    case 'update_order_status':
        if ($method !== 'POST') jsonResponse(['error' => 'POST required'], 405);
        if (!isAdmin()) jsonResponse(['error' => 'Unauthorized'], 401);
        $body   = getJsonBody();
        $ref    = $body['ref']    ?? '';
        $status = $body['status'] ?? '';
        $valid  = ['Order Placed','Confirmed','Packing','Shipped','Delivered','Cancelled'];
        if (!$ref || !in_array($status, $valid)) jsonResponse(['error' => 'Invalid data'], 400);
        $db = getDB();
        $db->prepare("UPDATE orders SET status = ? WHERE ref = ?")
           ->execute([$status, $ref]);
        // Add history if not duplicate
        $chk = $db->prepare("SELECT id FROM order_history WHERE order_ref = ? AND status = ?");
        $chk->execute([$ref, $status]);
        if (!$chk->fetch()) {
            $notes = [
                'Order Placed' => 'Order received.',
                'Confirmed'    => 'Order confirmed and being prepared.',
                'Packing'      => 'Items are being packed.',
                'Shipped'      => 'Package is on its way!',
                'Delivered'    => 'Order delivered successfully.',
                'Cancelled'    => 'Order cancelled.'
            ];
            $db->prepare("INSERT INTO order_history (order_ref, status, note) VALUES (?,?,?)")
               ->execute([$ref, $status, $notes[$status] ?? '']);
        }
        jsonResponse(['success' => true]);

    // ── ADMIN: ADD/UPDATE PRODUCT ─────────
    case 'save_product':
        if ($method !== 'POST') jsonResponse(['error' => 'POST required'], 405);
        if (!isAdmin()) jsonResponse(['error' => 'Unauthorized'], 401);
        $body = getJsonBody();
        $name  = clean($body['name']  ?? ''); $cat  = clean($body['cat']   ?? '');
        $price = (float)($body['price'] ?? 0); $desc = clean($body['description'] ?? '');
        if (!$name || !$cat || $price < 1 || !$desc) jsonResponse(['error' => 'Missing fields'], 400);
        $db = getDB();
        $data = [
            $name, $cat, $price,
            (int)($body['stock'] ?? 10), (int)($body['total_stock'] ?? 20),
            clean($body['badge'] ?? ''), clean($body['badge_text'] ?? ''),
            $desc,
            json_encode($body['specs'] ?? []),
            json_encode($body['links'] ?? []),
            (int)($body['is_new'] ?? 0)
        ];
        if (!empty($body['id'])) {
            $data[] = (int)$body['id'];
            $db->prepare("UPDATE products SET name=?,cat=?,price=?,stock=?,total_stock=?,badge=?,badge_text=?,description=?,specs_json=?,links_json=?,is_new=? WHERE id=?")
               ->execute($data);
            jsonResponse(['success' => true, 'id' => (int)$body['id']]);
        } else {
            $db->prepare("INSERT INTO products (name,cat,price,stock,total_stock,badge,badge_text,description,specs_json,links_json,is_new) VALUES (?,?,?,?,?,?,?,?,?,?,?)")
               ->execute($data);
            jsonResponse(['success' => true, 'id' => (int)$db->lastInsertId()]);
        }

    // ── ADMIN: DELETE PRODUCT ─────────────
    case 'delete_product':
        if ($method !== 'POST') jsonResponse(['error' => 'POST required'], 405);
        if (!isAdmin()) jsonResponse(['error' => 'Unauthorized'], 401);
        $id = (int)(getJsonBody()['id'] ?? 0);
        if (!$id) jsonResponse(['error' => 'Missing id'], 400);
        getDB()->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
        jsonResponse(['success' => true]);

    // ── ADMIN: STATS ──────────────────────
    case 'stats':
        if (!isAdmin()) jsonResponse(['error' => 'Unauthorized'], 401);
        $db = getDB();
        $stats = [];
        $stats['total_products']  = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
        $stats['in_stock']        = $db->query("SELECT COUNT(*) FROM products WHERE stock > 0")->fetchColumn();
        $stats['categories']      = $db->query("SELECT COUNT(DISTINCT cat) FROM products")->fetchColumn();
        $stats['avg_rating']      = round($db->query("SELECT AVG(rating) FROM products")->fetchColumn(), 1);
        $stats['total_orders']    = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
        $stats['delivered']       = $db->query("SELECT COUNT(*) FROM orders WHERE status='Delivered'")->fetchColumn();
        $stats['shipped']         = $db->query("SELECT COUNT(*) FROM orders WHERE status='Shipped'")->fetchColumn();
        $stats['cancelled']       = $db->query("SELECT COUNT(*) FROM orders WHERE status='Cancelled'")->fetchColumn();
        $stats['total_reviews']   = $db->query("SELECT COUNT(*) FROM reviews")->fetchColumn();
        $rev = $db->query("SELECT SUM(total) FROM orders WHERE status NOT IN ('Cancelled')")->fetchColumn();
        $stats['revenue']         = (float)($rev ?? 0);
        $stats['lowest_price']    = (float)$db->query("SELECT MIN(price) FROM products")->fetchColumn();
        $stats['highest_price']   = (float)$db->query("SELECT MAX(price) FROM products")->fetchColumn();
        jsonResponse($stats);

    // ── ADMIN LOGIN ───────────────────────
    case 'admin_login':
        if ($method !== 'POST') jsonResponse(['error' => 'POST required'], 405);
        $body = getJsonBody();
        if (($body['password'] ?? '') === 'admin123') {
            setcookie('admin_token', 'admin123', time() + 86400, '/', '', false, true);
            jsonResponse(['success' => true, 'token' => 'admin123']);
        } else {
            jsonResponse(['error' => 'Incorrect password'], 401);
        }

    default:
        jsonResponse(['error' => 'Unknown action: ' . $action], 404);
}