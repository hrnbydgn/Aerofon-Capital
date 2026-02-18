<?php
/**
 * Resim yükleme - base64 veya multipart
 */
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

$allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$maxSize = 5 * 1024 * 1024; // 5MB

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['hata' => 'POST gerekli']);
    exit;
}

try {
    if (!empty($_POST['base64'])) {
        $data = $_POST['base64'];
        if (preg_match('/^data:image\/(\w+);base64,/', $data, $m)) {
            $ext = $m[1] === 'jpeg' ? 'jpg' : $m[1];
            $data = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $data));
            if ($data && strlen($data) <= $maxSize) {
                $name = 'img_' . uniqid() . '.' . $ext;
                $path = UPLOAD_PATH . $name;
                if (file_put_contents($path, $data)) {
                    echo json_encode(['url' => 'data/uploads/' . $name]);
                    exit;
                }
            }
        }
    }
    if (!empty($_FILES['file']['tmp_name'])) {
        $f = $_FILES['file'];
        if ($f['error'] !== UPLOAD_ERR_OK || $f['size'] > $maxSize) {
            throw new Exception('Dosya boyutu 5MB\'ı aşamaz');
        }
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $f['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mime, $allowed)) {
            throw new Exception('Sadece JPG, PNG, GIF, WebP');
        }
        $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'][$mime];
        $name = 'img_' . uniqid() . '.' . $ext;
        if (move_uploaded_file($f['tmp_name'], UPLOAD_PATH . $name)) {
            echo json_encode(['url' => 'data/uploads/' . $name]);
            exit;
        }
    }
    throw new Exception('Geçersiz istek');
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['hata' => $e->getMessage()]);
}
