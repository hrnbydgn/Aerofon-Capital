<?php
/**
 * Evly - Ürün API
 * GET    /api/products.php          → Tüm ürünler
 * GET    /api/products.php?id=1     → Tek ürün
 * POST   /api/products.php          → Ürün ekle
 * PUT    /api/products.php?id=1     → Ürün güncelle
 * DELETE /api/products.php?id=1     → Ürün sil
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';

header('Content-Type: application/json; charset=utf-8');

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? intval($_GET['id']) : null;
$category = $_GET['category'] ?? null;
$search = $_GET['search'] ?? null;

switch ($method) {
    case 'GET':
        if ($id) {
            $stmt = $db->prepare("
                SELECT p.*, c.slug AS cat_slug, c.name AS cat_name,
                       ROUND((p.quantity / p.max_quantity) * 100) AS pct
                FROM products p
                LEFT JOIN categories c ON c.id = p.category_id
                WHERE p.id = ?
            ");
            $stmt->execute([$id]);
            $product = $stmt->fetch();
            if ($product) {
                jsonResponse(['success' => true, 'data' => $product]);
            } else {
                jsonResponse(['success' => false, 'error' => 'Ürün bulunamadı'], 404);
            }
        } else {
            $sql = "
                SELECT p.*, c.slug AS cat_slug, c.name AS cat_name,
                       ROUND((p.quantity / p.max_quantity) * 100) AS pct
                FROM products p
                LEFT JOIN categories c ON c.id = p.category_id
            ";
            $params = [];
            $where = [];

            if ($category && $category !== 'all') {
                $where[] = "c.slug = ?";
                $params[] = $category;
            }
            if ($search) {
                $where[] = "LOWER(p.name) LIKE ?";
                $params[] = '%' . mb_strtolower($search) . '%';
            }

            if ($where) {
                $sql .= " WHERE " . implode(' AND ', $where);
            }
            $sql .= " ORDER BY (p.quantity / p.max_quantity) ASC";

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            jsonResponse(['success' => true, 'data' => $stmt->fetchAll()]);
        }
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $name = trim($input['name'] ?? '');
        if (!$name) {
            jsonResponse(['success' => false, 'error' => 'Ürün adı gerekli'], 400);
        }
        $stmt = $db->prepare("INSERT INTO products (name, icon, category_id, unit, quantity, max_quantity, predicted_end, avg_consumption_days, last_purchased) VALUES (?,?,?,?,?,?,?,?,?)");
        $days = max(1, intval($input['avg_consumption_days'] ?? 7));
        $stmt->execute([
            $name,
            $input['icon'] ?? '📦',
            intval($input['category_id'] ?? 1),
            $input['unit'] ?? 'adet',
            floatval($input['quantity'] ?? 0),
            floatval($input['max_quantity'] ?? 1),
            date('Y-m-d', strtotime("+{$days} days")),
            $days,
            date('Y-m-d'),
        ]);
        jsonResponse(['success' => true, 'id' => $db->lastInsertId()], 201);
        break;

    case 'PUT':
        if (!$id) jsonResponse(['success' => false, 'error' => 'ID gerekli'], 400);
        $input = json_decode(file_get_contents('php://input'), true);
        $fields = [];
        $params = [];
        foreach (['name','icon','category_id','unit','quantity','max_quantity','avg_consumption_days'] as $f) {
            if (isset($input[$f])) {
                $fields[] = "$f = ?";
                $params[] = $input[$f];
            }
        }
        if (empty($fields)) jsonResponse(['success' => false, 'error' => 'Güncellenecek alan yok'], 400);
        $fields[] = "updated_at = CURRENT_TIMESTAMP";
        $params[] = $id;
        $db->prepare("UPDATE products SET " . implode(', ', $fields) . " WHERE id = ?")->execute($params);
        jsonResponse(['success' => true]);
        break;

    case 'DELETE':
        if (!$id) jsonResponse(['success' => false, 'error' => 'ID gerekli'], 400);
        $db->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
        jsonResponse(['success' => true]);
        break;

    default:
        jsonResponse(['success' => false, 'error' => 'Geçersiz metod'], 405);
}
