<?php
/**
 * Proje Yönetimi API Endpoint
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
            // Kullanıcının tüm projelerini getir (sahibi olduğu veya üyesi olduğu)
            $stmt = $db->prepare("
                SELECT DISTINCT p.*, u.name as owner_name,
                    (SELECT COUNT(*) FROM tasks WHERE project_id = p.id) as task_count,
                    (SELECT COUNT(*) FROM tasks WHERE project_id = p.id AND status = 'Tamamlandı') as completed_count
                FROM projects p
                LEFT JOIN users u ON p.owner_id = u.id
                LEFT JOIN project_members pm ON p.id = pm.project_id
                WHERE p.owner_id = ? OR pm.user_id = ?
                ORDER BY p.created_at DESC
            ");
            $stmt->execute([$userId, $userId]);
            $projects = $stmt->fetchAll();
            
            jsonResponse(['success' => true, 'projects' => $projects]);
            break;
            
        case 'get':
            $projectId = $_GET['id'] ?? 0;
            
            if (!canAccessProject($userId, $projectId)) {
                jsonResponse(['success' => false, 'message' => 'Bu projeye erişim yetkiniz yok'], 403);
            }
            
            $stmt = $db->prepare("
                SELECT p.*, u.name as owner_name
                FROM projects p
                LEFT JOIN users u ON p.owner_id = u.id
                WHERE p.id = ?
            ");
            $stmt->execute([$projectId]);
            $project = $stmt->fetch();
            
            if (!$project) {
                jsonResponse(['success' => false, 'message' => 'Proje bulunamadı'], 404);
            }
            
            // Proje üyelerini getir
            $stmt = $db->prepare("
                SELECT pm.*, u.name, u.email, u.avatar
                FROM project_members pm
                JOIN users u ON pm.user_id = u.id
                WHERE pm.project_id = ?
            ");
            $stmt->execute([$projectId]);
            $project['members'] = $stmt->fetchAll();
            
            jsonResponse(['success' => true, 'project' => $project]);
            break;
            
        case 'create':
            if ($method !== 'POST') {
                jsonResponse(['success' => false, 'message' => 'Geçersiz istek metodu'], 405);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $name = trim($data['name'] ?? '');
            $description = trim($data['description'] ?? '');
            $startDate = $data['start_date'] ?? null;
            $endDate = $data['end_date'] ?? null;
            $status = $data['status'] ?? 'Aktif';
            $color = $data['color'] ?? '#3B82F6';
            
            if (empty($name)) {
                jsonResponse(['success' => false, 'message' => 'Proje adı gerekli'], 400);
            }
            
            $stmt = $db->prepare("
                INSERT INTO projects (name, description, start_date, end_date, status, color, owner_id)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $description, $startDate, $endDate, $status, $color, $userId]);
            
            $projectId = $db->lastInsertId();
            
            jsonResponse(['success' => true, 'message' => 'Proje oluşturuldu', 'project_id' => $projectId]);
            break;
            
        case 'update':
            if ($method !== 'PUT' && $method !== 'POST') {
                jsonResponse(['success' => false, 'message' => 'Geçersiz istek metodu'], 405);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $projectId = $data['id'] ?? 0;
            
            if (!canAccessProject($userId, $projectId)) {
                jsonResponse(['success' => false, 'message' => 'Bu projeyi düzenleme yetkiniz yok'], 403);
            }
            
            $role = getUserProjectRole($userId, $projectId);
            if ($role !== 'Yönetici') {
                jsonResponse(['success' => false, 'message' => 'Sadece yöneticiler projeyi düzenleyebilir'], 403);
            }
            
            $name = trim($data['name'] ?? '');
            $description = trim($data['description'] ?? '');
            $startDate = $data['start_date'] ?? null;
            $endDate = $data['end_date'] ?? null;
            $status = $data['status'] ?? 'Aktif';
            $color = $data['color'] ?? '#3B82F6';
            
            if (empty($name)) {
                jsonResponse(['success' => false, 'message' => 'Proje adı gerekli'], 400);
            }
            
            $stmt = $db->prepare("
                UPDATE projects 
                SET name = ?, description = ?, start_date = ?, end_date = ?, status = ?, color = ?
                WHERE id = ?
            ");
            $stmt->execute([$name, $description, $startDate, $endDate, $status, $color, $projectId]);
            
            jsonResponse(['success' => true, 'message' => 'Proje güncellendi']);
            break;
            
        case 'delete':
            if ($method !== 'DELETE' && $method !== 'POST') {
                jsonResponse(['success' => false, 'message' => 'Geçersiz istek metodu'], 405);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $projectId = $data['id'] ?? 0;
            
            // Sadece proje sahibi silebilir
            $stmt = $db->prepare("SELECT owner_id FROM projects WHERE id = ?");
            $stmt->execute([$projectId]);
            $project = $stmt->fetch();
            
            if (!$project || $project['owner_id'] != $userId) {
                jsonResponse(['success' => false, 'message' => 'Bu projeyi silme yetkiniz yok'], 403);
            }
            
            $stmt = $db->prepare("DELETE FROM projects WHERE id = ?");
            $stmt->execute([$projectId]);
            
            jsonResponse(['success' => true, 'message' => 'Proje silindi']);
            break;
            
        case 'add_member':
            if ($method !== 'POST') {
                jsonResponse(['success' => false, 'message' => 'Geçersiz istek metodu'], 405);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $projectId = $data['project_id'] ?? 0;
            $memberEmail = trim($data['email'] ?? '');
            $role = $data['role'] ?? 'Üye';
            
            if (!canAccessProject($userId, $projectId)) {
                jsonResponse(['success' => false, 'message' => 'Bu projeye erişim yetkiniz yok'], 403);
            }
            
            $userRole = getUserProjectRole($userId, $projectId);
            if ($userRole !== 'Yönetici') {
                jsonResponse(['success' => false, 'message' => 'Sadece yöneticiler üye ekleyebilir'], 403);
            }
            
            // Kullanıcıyı bul
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$memberEmail]);
            $member = $stmt->fetch();
            
            if (!$member) {
                jsonResponse(['success' => false, 'message' => 'Kullanıcı bulunamadı'], 404);
            }
            
            // Zaten üye mi kontrol et
            $stmt = $db->prepare("SELECT id FROM project_members WHERE project_id = ? AND user_id = ?");
            $stmt->execute([$projectId, $member['id']]);
            if ($stmt->fetch()) {
                jsonResponse(['success' => false, 'message' => 'Kullanıcı zaten üye'], 400);
            }
            
            // Üye ekle
            $stmt = $db->prepare("INSERT INTO project_members (project_id, user_id, role) VALUES (?, ?, ?)");
            $stmt->execute([$projectId, $member['id'], $role]);
            
            jsonResponse(['success' => true, 'message' => 'Üye eklendi']);
            break;
            
        case 'remove_member':
            if ($method !== 'DELETE' && $method !== 'POST') {
                jsonResponse(['success' => false, 'message' => 'Geçersiz istek metodu'], 405);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $projectId = $data['project_id'] ?? 0;
            $memberId = $data['member_id'] ?? 0;
            
            if (!canAccessProject($userId, $projectId)) {
                jsonResponse(['success' => false, 'message' => 'Bu projeye erişim yetkiniz yok'], 403);
            }
            
            $userRole = getUserProjectRole($userId, $projectId);
            if ($userRole !== 'Yönetici') {
                jsonResponse(['success' => false, 'message' => 'Sadece yöneticiler üye çıkarabilir'], 403);
            }
            
            $stmt = $db->prepare("DELETE FROM project_members WHERE project_id = ? AND user_id = ?");
            $stmt->execute([$projectId, $memberId]);
            
            jsonResponse(['success' => true, 'message' => 'Üye çıkarıldı']);
            break;
            
        default:
            jsonResponse(['success' => false, 'message' => 'Geçersiz aksiyon'], 400);
    }
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Bir hata oluştu: ' . $e->getMessage()], 500);
}
