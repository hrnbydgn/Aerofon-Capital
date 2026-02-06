<?php
/**
 * Evly - Veritabanı Kurulumu & Seed
 */
require_once __DIR__ . '/config.php';

function setupDatabase(): void {
    $db = getDB();

    // --- Tablolar ---
    $db->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT UNIQUE,
            avatar TEXT DEFAULT '👤',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            slug TEXT UNIQUE NOT NULL,
            icon TEXT DEFAULT '📦'
        );

        CREATE TABLE IF NOT EXISTS products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            icon TEXT DEFAULT '📦',
            category_id INTEGER,
            unit TEXT DEFAULT 'adet',
            quantity REAL DEFAULT 0,
            max_quantity REAL DEFAULT 1,
            predicted_end DATE,
            avg_consumption_days REAL DEFAULT 7,
            last_purchased DATE,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES categories(id)
        );

        CREATE TABLE IF NOT EXISTS receipts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            store_name TEXT NOT NULL,
            store_branch TEXT,
            total_amount REAL DEFAULT 0,
            item_count INTEGER DEFAULT 0,
            ocr_accuracy REAL DEFAULT 0,
            receipt_date DATETIME,
            scanned_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS receipt_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            receipt_id INTEGER NOT NULL,
            product_id INTEGER,
            name TEXT NOT NULL,
            quantity REAL DEFAULT 1,
            unit_price REAL DEFAULT 0,
            total_price REAL DEFAULT 0,
            FOREIGN KEY (receipt_id) REFERENCES receipts(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id)
        );

        CREATE TABLE IF NOT EXISTS shopping_list (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            product_id INTEGER,
            name TEXT NOT NULL,
            quantity INTEGER DEFAULT 1,
            is_checked INTEGER DEFAULT 0,
            is_ai_suggested INTEGER DEFAULT 0,
            reason TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id)
        );

        CREATE TABLE IF NOT EXISTS insights (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            type TEXT DEFAULT 'tip',
            icon TEXT DEFAULT '💡',
            title TEXT NOT NULL,
            description TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
    ");
}

function seedDatabase(): void {
    $db = getDB();

    // Zaten seed edilmiş mi?
    $count = $db->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    if ($count > 0) return;

    // --- Kullanıcı ---
    $db->exec("INSERT INTO users (name, email, avatar) VALUES ('Ahmet Yılmaz', 'ahmet@evly.app', '👤')");

    // --- Kategoriler ---
    $cats = [
        ['Süt Ürünleri', 'dairy', '🥛'],
        ['Et & Tavuk', 'meat', '🥩'],
        ['Meyve & Sebze', 'fruits', '🍎'],
        ['Fırın', 'bakery', '🍞'],
        ['Temizlik', 'cleaning', '🧹'],
        ['Atıştırmalık', 'snacks', '🍫'],
        ['İçecek', 'beverages', '🥤'],
        ['Temel Gıda', 'basics', '🌾'],
    ];
    $stmt = $db->prepare("INSERT INTO categories (name, slug, icon) VALUES (?, ?, ?)");
    foreach ($cats as $c) $stmt->execute($c);

    // --- Ürünler ---
    $today = date('Y-m-d');
    $products = [
        ['Süt (1L)', '🥛', 1, 'litre', 0.2, 1, date('Y-m-d', strtotime('+2 days')), 3, date('Y-m-d', strtotime('-2 days'))],
        ['Ekmek', '🍞', 4, 'adet', 0.1, 1, date('Y-m-d', strtotime('+1 day')), 2, date('Y-m-d', strtotime('-1 day'))],
        ['Kaşar Peyniri', '🧀', 1, 'gram', 150, 500, date('Y-m-d', strtotime('+4 days')), 7, date('Y-m-d', strtotime('-5 days'))],
        ['Yumurta (30\'lu)', '🥚', 1, 'adet', 8, 30, date('Y-m-d', strtotime('+5 days')), 10, date('Y-m-d', strtotime('-8 days'))],
        ['Tavuk Göğüs', '🍗', 2, 'gram', 500, 1000, date('Y-m-d', strtotime('+8 days')), 12, date('Y-m-d', strtotime('-4 days'))],
        ['Domates', '🍅', 3, 'kg', 1, 2, date('Y-m-d', strtotime('+6 days')), 5, date('Y-m-d', strtotime('-3 days'))],
        ['Bulaşık Deterjanı', '🧴', 5, 'ml', 600, 1000, date('Y-m-d', strtotime('+18 days')), 30, date('Y-m-d', strtotime('-12 days'))],
        ['Türk Kahvesi', '☕', 7, 'gram', 150, 250, date('Y-m-d', strtotime('+12 days')), 20, date('Y-m-d', strtotime('-8 days'))],
        ['Çikolata', '🍫', 6, 'adet', 3, 5, date('Y-m-d', strtotime('+10 days')), 8, date('Y-m-d', strtotime('-3 days'))],
        ['Muz', '🍌', 3, 'adet', 4, 8, date('Y-m-d', strtotime('+3 days')), 4, date('Y-m-d', strtotime('-2 days'))],
        ['Zeytinyağı', '🫒', 8, 'ml', 400, 1000, date('Y-m-d', strtotime('+25 days')), 45, date('Y-m-d', strtotime('-20 days'))],
        ['Makarna', '🍝', 8, 'adet', 2, 5, date('Y-m-d', strtotime('+15 days')), 14, date('Y-m-d', strtotime('-7 days'))],
    ];
    $stmt = $db->prepare("INSERT INTO products (name, icon, category_id, unit, quantity, max_quantity, predicted_end, avg_consumption_days, last_purchased) VALUES (?,?,?,?,?,?,?,?,?)");
    foreach ($products as $p) $stmt->execute($p);

    // --- Fişler ---
    $receipts = [
        ['Migros', 'Ataşehir', 345, 12, 97, date('Y-m-d H:i:s', strtotime('-2 hours'))],
        ['BİM', 'Kadıköy', 189, 8, 94, date('Y-m-d H:i:s', strtotime('-1 day'))],
        ['A101', 'Üsküdar', 412, 15, 96, date('Y-m-d H:i:s', strtotime('-3 days'))],
        ['ŞOK', 'Maltepe', 156, 6, 93, date('Y-m-d H:i:s', strtotime('-5 days'))],
        ['CarrefourSA', 'Bostancı', 523, 18, 95, date('Y-m-d H:i:s', strtotime('-7 days'))],
    ];
    $stmt = $db->prepare("INSERT INTO receipts (store_name, store_branch, total_amount, item_count, ocr_accuracy, receipt_date) VALUES (?,?,?,?,?,?)");
    foreach ($receipts as $r) $stmt->execute($r);

    // --- Alışveriş Listesi ---
    $shopItems = [
        [1, 'Süt (1L)', 2, 0, 1, 'AI: 2 gün içinde bitecek'],
        [2, 'Ekmek', 1, 0, 1, 'AI: Yarın bitecek'],
        [4, 'Yumurta (30\'lu)', 1, 0, 1, 'AI: 5 gün sonra bitecek'],
        [3, 'Kaşar Peyniri', 1, 0, 1, 'AI: 4 gün kaldı'],
        [null, 'Zeytin', 1, 0, 0, 'Manuel eklendi'],
        [null, 'Makarna', 2, 0, 0, 'Manuel eklendi'],
        [null, 'Tereyağı', 1, 1, 0, 'Manuel eklendi'],
    ];
    $stmt = $db->prepare("INSERT INTO shopping_list (product_id, name, quantity, is_checked, is_ai_suggested, reason) VALUES (?,?,?,?,?,?)");
    foreach ($shopItems as $s) $stmt->execute($s);

    // --- AI Öngörüler ---
    $insights = [
        ['tip', '🎯', 'Tasarruf Fırsatı', 'Süt alımlarınızı toplu yaparsanız aylık ₺45 tasarruf edebilirsiniz.'],
        ['save', '📉', 'Harcama Trendi', 'Bu ay geçen aya göre %12 daha az harcadınız. Harika!'],
        ['warn', '⏰', 'Israf Uyarısı', 'Son 2 ayda 3 kez meyve çürümüş. Daha az miktarda alım önerilir.'],
    ];
    $stmt = $db->prepare("INSERT INTO insights (type, icon, title, description) VALUES (?,?,?,?)");
    foreach ($insights as $i) $stmt->execute($i);
}

// Çalıştır
setupDatabase();
seedDatabase();
