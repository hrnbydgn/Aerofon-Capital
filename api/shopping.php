<?php
/**
 * Evly - Alışveriş Listesi API
 * GET    /api/shopping.php          → Tüm liste
 * POST   /api/shopping.php          → Ürün ekle
 * PUT    /api/shopping.php?id=1     → Ürün güncelle (toggle)
 * DELETE /api/shopping.php?id=1     → Ürün sil
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';

header('Content-Type: application/json; charset=utf-8');

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

switch ($method) {
    case 'GET':
        $aiItems = $db->query("SELECT * FROM shopping_list WHERE is_ai_suggested = 1 ORDER BY is_checked ASC, id ASC")->fetchAll();
        $manualItems = $db->query("SELECT * FROM shopping_list WHERE is_ai_suggested = 0 ORDER BY is_checked ASC, id ASC")->fetchAll();
        jsonResponse([
            'success' => true,
            'data' => [
                'ai' => $aiItems,
                'manual' => $manualItems,
            ],
            'stats' => [
                'total' => count($aiItems) + count($manualItems),
                'checked' => $db->query("SELECT COUNT(*) FROM shopping_list WHERE is_checked = 1")->fetchColumn(),
            ]
        ]);
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $name = trim($input['name'] ?? '');
        if (!$name) {
            jsonResponse(['success' => false, 'error' => 'Ürün adı gerekli'], 400);
        }
        $stmt = $db->prepare("INSERT INTO shopping_list (name, quantity, is_ai_suggested, reason, product_id) VALUES (?,?,?,?,?)");
        $stmt->execute([
            $name,
            max(1, intval($input['quantity'] ?? 1)),
            intval($input['is_ai_suggested'] ?? 0),
            $input['reason'] ?? 'Manuel eklendi',
            !empty($input['product_id']) ? intval($input['product_id']) : null,
        ]);
        jsonResponse(['success' => true, 'id' => $db->lastInsertId()], 201);
        break;

    case 'PUT':
        if (!$id) jsonResponse(['success' => false, 'error' => 'ID gerekli'], 400);
        $input = json_decode(file_get_contents('php://input'), true);
        if (isset($input['toggle'])) {
            $db->prepare("UPDATE shopping_list SET is_checked = CASE WHEN is_checked = 1 THEN 0 ELSE 1 END WHERE id = ?")->execute([$id]);
        } else {
            $fields = [];
            $params = [];
            foreach (['name','quantity','is_checked'] as $f) {
                if (isset($input[$f])) {
                    $fields[] = "$f = ?";
                    $params[] = $input[$f];
                }
            }
            if (!empty($fields)) {
                $params[] = $id;
                $db->prepare("UPDATE shopping_list SET " . implode(', ', $fields) . " WHERE id = ?")->execute($params);
            }
        }
        jsonResponse(['success' => true]);
        break;

    case 'DELETE':
        if (!$id) jsonResponse(['success' => false, 'error' => 'ID gerekli'], 400);
        $action = $_GET['action'] ?? 'single';
        if ($action === 'clear_checked') {
            $db->exec("DELETE FROM shopping_list WHERE is_checked = 1");
        } else {
            $db->prepare("DELETE FROM shopping_list WHERE id = ?")->execute([$id]);
        }
        jsonResponse(['success' => true]);
        break;

    default:
        jsonResponse(['success' => false, 'error' => 'Geçersiz metod'], 405);
}
