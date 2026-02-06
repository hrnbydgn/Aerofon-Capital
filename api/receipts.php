<?php
/**
 * Evly - Fiş API
 * GET    /api/receipts.php          → Tüm fişler
 * POST   /api/receipts.php          → Fiş ekle
 * DELETE /api/receipts.php?id=1     → Fiş sil
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';

header('Content-Type: application/json; charset=utf-8');

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

switch ($method) {
    case 'GET':
        if ($id) {
            $stmt = $db->prepare("SELECT * FROM receipts WHERE id = ?");
            $stmt->execute([$id]);
            $receipt = $stmt->fetch();
            if ($receipt) {
                $items = $db->prepare("SELECT * FROM receipt_items WHERE receipt_id = ?");
                $items->execute([$id]);
                $receipt['items'] = $items->fetchAll();
                jsonResponse(['success' => true, 'data' => $receipt]);
            } else {
                jsonResponse(['success' => false, 'error' => 'Fiş bulunamadı'], 404);
            }
        } else {
            $limit = intval($_GET['limit'] ?? 20);
            $offset = intval($_GET['offset'] ?? 0);
            $stmt = $db->prepare("SELECT * FROM receipts ORDER BY receipt_date DESC LIMIT ? OFFSET ?");
            $stmt->execute([$limit, $offset]);
            $total = $db->query("SELECT COUNT(*) FROM receipts")->fetchColumn();
            jsonResponse(['success' => true, 'data' => $stmt->fetchAll(), 'total' => $total]);
        }
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $store = trim($input['store_name'] ?? '');
        if (!$store) {
            jsonResponse(['success' => false, 'error' => 'Market adı gerekli'], 400);
        }
        $stmt = $db->prepare("INSERT INTO receipts (store_name, store_branch, total_amount, item_count, ocr_accuracy, receipt_date) VALUES (?,?,?,?,?,?)");
        $stmt->execute([
            $store,
            $input['store_branch'] ?? '',
            floatval($input['total_amount'] ?? 0),
            intval($input['item_count'] ?? 0),
            floatval($input['ocr_accuracy'] ?? 0),
            $input['receipt_date'] ?? date('Y-m-d H:i:s'),
        ]);
        jsonResponse(['success' => true, 'id' => $db->lastInsertId()], 201);
        break;

    case 'DELETE':
        if (!$id) jsonResponse(['success' => false, 'error' => 'ID gerekli'], 400);
        $db->prepare("DELETE FROM receipts WHERE id = ?")->execute([$id]);
        jsonResponse(['success' => true]);
        break;

    default:
        jsonResponse(['success' => false, 'error' => 'Geçersiz metod'], 405);
}
