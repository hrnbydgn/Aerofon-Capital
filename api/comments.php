<?php
/**
 * Yorum API Endpoint
 */

require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Giriş yapmanız gerekiyor'], 401);
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

$db = Database::getInstance()->getConnection();
$userId = $_SESSION['user_id'];

try {
    switch ($action) {
        case 'list':
            $taskId = $_GET['task_id'] ?? 0;
            
            // Görev kontrolü
            $stmt = $db->prepare("SELECT project_id FROM tasks WHERE id = ?");
            $stmt->execute([$taskId]);
            $task = $stmt->fetch();
            
            if (!$task || !canAccessProject($userId, $task['project_id'])) {
                jsonResponse(['success' => false, 'message' => 'Bu göreve erişim yetkiniz yok'], 403);
            }
            
            $stmt = $db->prepare("
                SELECT c.*, u.name, u.avatar
                FROM comments c
                JOIN users u ON c.user_id = u.id
                WHERE c.task_id = ?
                ORDER BY c.created_at ASC
            ");
            $stmt->execute([$taskId]);
            $comments = $stmt->fetchAll();
            
            jsonResponse(['success' => true, 'comments' => $comments]);
            break;
            
        case 'create':
            if ($method !== 'POST') {
                jsonResponse(['success' => false, 'message' => 'Geçersiz istek metodu'], 405);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $taskId = $data['task_id'] ?? 0;
            $comment = trim($data['comment'] ?? '');
            
            if (empty($comment)) {
                jsonResponse(['success' => false, 'message' => 'Yorum boş olamaz'], 400);
            }
            
            // Görev kontrolü
            $stmt = $db->prepare("SELECT project_id FROM tasks WHERE id = ?");
            $stmt->execute([$taskId]);
            $task = $stmt->fetch();
            
            if (!$task || !canAccessProject($userId, $task['project_id'])) {
                jsonResponse(['success' => false, 'message' => 'Bu göreve erişim yetkiniz yok'], 403);
            }
            
            $stmt = $db->prepare("INSERT INTO comments (task_id, user_id, comment) VALUES (?, ?, ?)");
            $stmt->execute([$taskId, $userId, $comment]);
            
            $commentId = $db->lastInsertId();
            
            // Yorum bilgisini döndür
            $stmt = $db->prepare("
                SELECT c.*, u.name, u.avatar
                FROM comments c
                JOIN users u ON c.user_id = u.id
                WHERE c.id = ?
            ");
            $stmt->execute([$commentId]);
            $newComment = $stmt->fetch();
            
            jsonResponse(['success' => true, 'message' => 'Yorum eklendi', 'comment' => $newComment]);
            break;
            
        case 'delete':
            if ($method !== 'DELETE' && $method !== 'POST') {
                jsonResponse(['success' => false, 'message' => 'Geçersiz istek metodu'], 405);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $commentId = $data['id'] ?? 0;
            
            // Yorum sahibi mi kontrol et
            $stmt = $db->prepare("SELECT user_id, task_id FROM comments WHERE id = ?");
            $stmt->execute([$commentId]);
            $comment = $stmt->fetch();
            
            if (!$comment) {
                jsonResponse(['success' => false, 'message' => 'Yorum bulunamadı'], 404);
            }
            
            if ($comment['user_id'] != $userId) {
                jsonResponse(['success' => false, 'message' => 'Bu yorumu silme yetkiniz yok'], 403);
            }
            
            $stmt = $db->prepare("DELETE FROM comments WHERE id = ?");
            $stmt->execute([$commentId]);
            
            jsonResponse(['success' => true, 'message' => 'Yorum silindi']);
            break;
            
        default:
            jsonResponse(['success' => false, 'message' => 'Geçersiz aksiyon'], 400);
    }
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Bir hata oluştu: ' . $e->getMessage()], 500);
}
