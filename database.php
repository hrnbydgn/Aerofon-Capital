<?php
/**
 * SQLite veritabanı bağlantısı ve şema
 */
require_once __DIR__ . '/config.php';

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO('sqlite:' . DB_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('PRAGMA foreign_keys = ON');
        initSchema($pdo);
    }
    return $pdo;
}

function initSchema(PDO $pdo): void {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS tez_projeleri (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            baslik TEXT NOT NULL,
            yazar TEXT,
            danisman TEXT,
            universite TEXT,
            enstitu TEXT,
            bolum TEXT,
            yil INTEGER,
            ayarlar TEXT,
            olusturma_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS tez_bolumleri (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            proje_id INTEGER NOT NULL,
            sira INTEGER NOT NULL,
            baslik TEXT NOT NULL,
            icerik TEXT,
            FOREIGN KEY (proje_id) REFERENCES tez_projeleri(id) ON DELETE CASCADE
        )
    ");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_bolum_proje ON tez_bolumleri(proje_id)");
}

function getProjeById(int $id): array {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM tez_projeleri WHERE id = ?");
    $stmt->execute([$id]);
    $proje = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$proje) throw new Exception('Proje bulunamadı');
    $proje['ayarlar'] = json_decode($proje['ayarlar'] ?? '{}', true) ?? [];
    $stmt = $pdo->prepare("SELECT id, sira, baslik, icerik FROM tez_bolumleri WHERE proje_id = ? ORDER BY sira");
    $stmt->execute([$id]);
    $proje['bolumler'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $proje;
}
