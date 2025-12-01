<?php
/**
 * Görev Yönetimi API Endpoint
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
            $projectId = $_GET['project_id'] ?? 0;
            
            if ($projectId && !canAccessProject($userId, $projectId)) {
                jsonResponse(['success' => false, 'message' => 'Bu projeye erişim yetkiniz yok'], 403);
            }
            
            $query = "
                SELECT t.*, 
                    u1.name as assigned_name,
                    u2.name as creator_name
                FROM tasks t
                LEFT JOIN users u1 ON t.assigned_to = u1.id
                LEFT JOIN users u2 ON t.created_by = u2.id
                WHERE 1=1
            ";
            
            $params = [];
            
            if ($projectId) {
                $query .= " AND t.project_id = ?";
                $params[] = $projectId;
            } else {
                // Kullanıcının erişebileceği tüm görevler
                $query .= " AND (t.assigned_to = ? OR t.created_by = ? OR t.project_id IN 
                    (SELECT id FROM projects WHERE owner_id = ? 
                    UNION 
                    SELECT project_id FROM project_members WHERE user_id = ?))";
                $params[] = $userId;
                $params[] = $userId;
                $params[] = $userId;
                $params[] = $userId;
            }
            
            $query .= " ORDER BY t.created_at DESC";
            
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            $tasks = $stmt->fetchAll();
            
            jsonResponse(['success' => true, 'tasks' => $tasks]);
            break;
            
        case 'get':
            $taskId = $_GET['id'] ?? 0;
            
            $stmt = $db->prepare("
                SELECT t.*, 
                    u1.name as assigned_name, u1.email as assigned_email, u1.avatar as assigned_avatar,
                    u2.name as creator_name,
                    p.name as project_name
                FROM tasks t
                LEFT JOIN users u1 ON t.assigned_to = u1.id
                LEFT JOIN users u2 ON t.created_by = u2.id
                LEFT JOIN projects p ON t.project_id = p.id
                WHERE t.id = ?
            ");
            $stmt->execute([$taskId]);
            $task = $stmt->fetch();
            
            if (!$task) {
                jsonResponse(['success' => false, 'message' => 'Görev bulunamadı'], 404);
            }
            
            if (!canAccessProject($userId, $task['project_id'])) {
                jsonResponse(['success' => false, 'message' => 'Bu göreve erişim yetkiniz yok'], 403);
            }
            
            // Yorumları getir
            $stmt = $db->prepare("
                SELECT c.*, u.name, u.avatar
                FROM comments c
                JOIN users u ON c.user_id = u.id
                WHERE c.task_id = ?
                ORDER BY c.created_at ASC
            ");
            $stmt->execute([$taskId]);
            $task['comments'] = $stmt->fetchAll();
            
            // Geçmişi getir
            $stmt = $db->prepare("
                SELECT th.*, u.name
                FROM task_history th
                JOIN users u ON th.user_id = u.id
                WHERE th.task_id = ?
                ORDER BY th.created_at DESC
            ");
            $stmt->execute([$taskId]);
            $task['history'] = $stmt->fetchAll();
            
            jsonResponse(['success' => true, 'task' => $task]);
            break;
            
        case 'create':
            if ($method !== 'POST') {
                jsonResponse(['success' => false, 'message' => 'Geçersiz istek metodu'], 405);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $projectId = $data['project_id'] ?? 0;
            $title = trim($data['title'] ?? '');
            $description = trim($data['description'] ?? '');
            $assignedTo = $data['assigned_to'] ?? null;
            $status = $data['status'] ?? 'Yapılacak';
            $priority = $data['priority'] ?? 'Orta';
            $dueDate = $data['due_date'] ?? null;
            
            if (!canAccessProject($userId, $projectId)) {
                jsonResponse(['success' => false, 'message' => 'Bu projeye erişim yetkiniz yok'], 403);
            }
            
            if (empty($title)) {
                jsonResponse(['success' => false, 'message' => 'Görev başlığı gerekli'], 400);
            }
            
            $stmt = $db->prepare("
                INSERT INTO tasks (project_id, title, description, assigned_to, status, priority, due_date, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$projectId, $title, $description, $assignedTo, $status, $priority, $dueDate, $userId]);
            
            $taskId = $db->lastInsertId();
            
            // Geçmiş kaydı
            $stmt = $db->prepare("
                INSERT INTO task_history (task_id, user_id, action, new_value)
                VALUES (?, ?, 'Görev oluşturuldu', ?)
            ");
            $stmt->execute([$taskId, $userId, $title]);
            
            jsonResponse(['success' => true, 'message' => 'Görev oluşturuldu', 'task_id' => $taskId]);
            break;
            
        case 'update':
            if ($method !== 'PUT' && $method !== 'POST') {
                jsonResponse(['success' => false, 'message' => 'Geçersiz istek metodu'], 405);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $taskId = $data['id'] ?? 0;
            
            // Görev var mı kontrol
            $stmt = $db->prepare("SELECT * FROM tasks WHERE id = ?");
            $stmt->execute([$taskId]);
            $oldTask = $stmt->fetch();
            
            if (!$oldTask) {
                jsonResponse(['success' => false, 'message' => 'Görev bulunamadı'], 404);
            }
            
            if (!canAccessProject($userId, $oldTask['project_id'])) {
                jsonResponse(['success' => false, 'message' => 'Bu görevi düzenleme yetkiniz yok'], 403);
            }
            
            $title = trim($data['title'] ?? $oldTask['title']);
            $description = trim($data['description'] ?? $oldTask['description']);
            $assignedTo = $data['assigned_to'] ?? $oldTask['assigned_to'];
            $status = $data['status'] ?? $oldTask['status'];
            $priority = $data['priority'] ?? $oldTask['priority'];
            $dueDate = $data['due_date'] ?? $oldTask['due_date'];
            
            if (empty($title)) {
                jsonResponse(['success' => false, 'message' => 'Görev başlığı gerekli'], 400);
            }
            
            $stmt = $db->prepare("
                UPDATE tasks 
                SET title = ?, description = ?, assigned_to = ?, status = ?, priority = ?, due_date = ?
                WHERE id = ?
            ");
            $stmt->execute([$title, $description, $assignedTo, $status, $priority, $dueDate, $taskId]);
            
            // Değişiklikleri kaydet
            if ($oldTask['status'] !== $status) {
                $stmt = $db->prepare("
                    INSERT INTO task_history (task_id, user_id, action, old_value, new_value)
                    VALUES (?, ?, 'Durum değişti', ?, ?)
                ");
                $stmt->execute([$taskId, $userId, $oldTask['status'], $status]);
            }
            
            jsonResponse(['success' => true, 'message' => 'Görev güncellendi']);
            break;
            
        case 'delete':
            if ($method !== 'DELETE' && $method !== 'POST') {
                jsonResponse(['success' => false, 'message' => 'Geçersiz istek metodu'], 405);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $taskId = $data['id'] ?? 0;
            
            $stmt = $db->prepare("SELECT project_id, created_by FROM tasks WHERE id = ?");
            $stmt->execute([$taskId]);
            $task = $stmt->fetch();
            
            if (!$task) {
                jsonResponse(['success' => false, 'message' => 'Görev bulunamadı'], 404);
            }
            
            if (!canAccessProject($userId, $task['project_id'])) {
                jsonResponse(['success' => false, 'message' => 'Bu görevi silme yetkiniz yok'], 403);
            }
            
            $stmt = $db->prepare("DELETE FROM tasks WHERE id = ?");
            $stmt->execute([$taskId]);
            
            jsonResponse(['success' => true, 'message' => 'Görev silindi']);
            break;
            
        case 'update_status':
            if ($method !== 'POST') {
                jsonResponse(['success' => false, 'message' => 'Geçersiz istek metodu'], 405);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $taskId = $data['id'] ?? 0;
            $newStatus = $data['status'] ?? '';
            
            $stmt = $db->prepare("SELECT project_id, status FROM tasks WHERE id = ?");
            $stmt->execute([$taskId]);
            $task = $stmt->fetch();
            
            if (!$task) {
                jsonResponse(['success' => false, 'message' => 'Görev bulunamadı'], 404);
            }
            
            if (!canAccessProject($userId, $task['project_id'])) {
                jsonResponse(['success' => false, 'message' => 'Bu görevi düzenleme yetkiniz yok'], 403);
            }
            
            $stmt = $db->prepare("UPDATE tasks SET status = ? WHERE id = ?");
            $stmt->execute([$newStatus, $taskId]);
            
            // Geçmiş kaydı
            $stmt = $db->prepare("
                INSERT INTO task_history (task_id, user_id, action, old_value, new_value)
                VALUES (?, ?, 'Durum değişti', ?, ?)
            ");
            $stmt->execute([$taskId, $userId, $task['status'], $newStatus]);
            
            jsonResponse(['success' => true, 'message' => 'Görev durumu güncellendi']);
            break;
            
        default:
            jsonResponse(['success' => false, 'message' => 'Geçersiz aksiyon'], 400);
    }
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Bir hata oluştu: ' . $e->getMessage()], 500);
}
