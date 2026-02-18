<?php
/**
 * Tez Yazım API - REST endpoints
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/database.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input') ?: '{}', true) ?? [];
$input = array_merge($input, $_GET);

try {
    switch (true) {
        case $method === 'GET' && empty($_GET['action']):
            echo json_encode(listProjeler());
            break;
        case $method === 'GET' && ($_GET['action'] ?? '') === 'proje':
            echo json_encode(getProje((int)($_GET['id'] ?? 0)));
            break;
        case $method === 'POST' && ($input['action'] ?? '') === 'proje_olustur':
            echo json_encode(projeOlustur($input));
            break;
        case $method === 'POST' && ($input['action'] ?? '') === 'proje_guncelle':
            echo json_encode(projeGuncelle($input));
            break;
        case $method === 'POST' && ($input['action'] ?? '') === 'bolum_ekle':
            echo json_encode(bolumEkle($input));
            break;
        case $method === 'POST' && ($input['action'] ?? '') === 'bolum_guncelle':
            echo json_encode(bolumGuncelle($input));
            break;
        case $method === 'POST' && ($input['action'] ?? '') === 'bolum_sil':
            echo json_encode(bolumSil($input));
            break;
        case $method === 'POST' && ($input['action'] ?? '') === 'proje_sil':
            echo json_encode(projeSil($input));
            break;
        default:
            http_response_code(400);
            echo json_encode(['hata' => 'Geçersiz istek']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['hata' => $e->getMessage()]);
}

function listProjeler(): array {
    $db = getDB();
    $stmt = $db->query("SELECT id, baslik, yazar, olusturma_tarihi FROM tez_projeleri ORDER BY olusturma_tarihi DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getProje(int $id): array {
    return getProjeById($id);
}

function projeOlustur(array $d): array {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO tez_projeleri (baslik, yazar, danisman, universite, enstitu, bolum, yil, ayarlar) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $d['baslik'] ?? 'Yeni Tez',
        $d['yazar'] ?? '',
        $d['danisman'] ?? '',
        $d['universite'] ?? '',
        $d['enstitu'] ?? '',
        $d['bolum'] ?? '',
        (int)($d['yil'] ?? date('Y')),
        json_encode($d['ayarlar'] ?? [])
    ]);
    return ['id' => (int)$db->lastInsertId()];
}

function projeGuncelle(array $d): array {
    $db = getDB();
    $id = (int)($d['id'] ?? 0);
    if (!$id) throw new Exception('Proje ID gerekli');
    $stmt = $db->prepare("UPDATE tez_projeleri SET baslik=?, yazar=?, danisman=?, universite=?, enstitu=?, bolum=?, yil=?, ayarlar=? WHERE id=?");
    $stmt->execute([
        $d['baslik'] ?? '',
        $d['yazar'] ?? '',
        $d['danisman'] ?? '',
        $d['universite'] ?? '',
        $d['enstitu'] ?? '',
        $d['bolum'] ?? '',
        (int)($d['yil'] ?? date('Y')),
        json_encode($d['ayarlar'] ?? []),
        $id
    ]);
    return ['ok' => true];
}

function bolumEkle(array $d): array {
    $db = getDB();
    $proje_id = (int)($d['proje_id'] ?? 0);
    if (!$proje_id) throw new Exception('Proje ID gerekli');
    $sira = (int)($d['sira'] ?? 0);
    if ($sira <= 0) {
        $r = $db->query("SELECT COALESCE(MAX(sira),0)+1 FROM tez_bolumleri WHERE proje_id=$proje_id")->fetchColumn();
        $sira = (int)$r;
    }
    $stmt = $db->prepare("INSERT INTO tez_bolumleri (proje_id, sira, baslik, icerik) VALUES (?, ?, ?, ?)");
    $stmt->execute([$proje_id, $sira, $d['baslik'] ?? 'Yeni Bölüm', $d['icerik'] ?? '']);
    return ['id' => (int)$db->lastInsertId(), 'sira' => $sira];
}

function bolumGuncelle(array $d): array {
    $db = getDB();
    $id = (int)($d['id'] ?? 0);
    if (!$id) throw new Exception('Bölüm ID gerekli');
    $stmt = $db->prepare("UPDATE tez_bolumleri SET baslik=?, icerik=?, sira=? WHERE id=?");
    $stmt->execute([
        $d['baslik'] ?? '',
        $d['icerik'] ?? '',
        (int)($d['sira'] ?? 0),
        $id
    ]);
    return ['ok' => true];
}

function bolumSil(array $d): array {
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM tez_bolumleri WHERE id = ?");
    $stmt->execute([(int)($d['id'] ?? 0)]);
    return ['ok' => true];
}

function projeSil(array $d): array {
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM tez_projeleri WHERE id = ?");
    $stmt->execute([(int)($d['id'] ?? 0)]);
    return ['ok' => true];
}
