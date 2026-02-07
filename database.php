<?php
/**
 * Evly - Veritabanı Kurulumu & Zengin Seed Verileri
 */
require_once __DIR__ . '/config.php';

function setupDatabase(): void {
    $db = getDB();

    $db->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT UNIQUE,
            avatar TEXT DEFAULT '👤',
            household_size INTEGER DEFAULT 4,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            slug TEXT UNIQUE NOT NULL,
            icon TEXT DEFAULT '📦',
            color TEXT DEFAULT '#6366f1'
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
            avg_price REAL DEFAULT 0,
            purchase_count INTEGER DEFAULT 0,
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
            unit TEXT DEFAULT 'adet',
            unit_price REAL DEFAULT 0,
            total_price REAL DEFAULT 0,
            category_guess TEXT,
            FOREIGN KEY (receipt_id) REFERENCES receipts(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id)
        );

        CREATE TABLE IF NOT EXISTS shopping_list (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            product_id INTEGER,
            name TEXT NOT NULL,
            icon TEXT DEFAULT '🛒',
            category_id INTEGER,
            quantity INTEGER DEFAULT 1,
            unit TEXT DEFAULT 'adet',
            estimated_price REAL DEFAULT 0,
            is_checked INTEGER DEFAULT 0,
            is_ai_suggested INTEGER DEFAULT 0,
            priority INTEGER DEFAULT 0,
            reason TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id),
            FOREIGN KEY (category_id) REFERENCES categories(id)
        );

        CREATE TABLE IF NOT EXISTS insights (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            type TEXT DEFAULT 'tip',
            icon TEXT DEFAULT '💡',
            title TEXT NOT NULL,
            description TEXT,
            action_text TEXT,
            action_url TEXT,
            is_read INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS meal_plans (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            day_of_week INTEGER NOT NULL,
            meal_type TEXT NOT NULL,
            title TEXT NOT NULL,
            ingredients TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS price_history (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            product_id INTEGER NOT NULL,
            store_name TEXT,
            price REAL NOT NULL,
            recorded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id)
        );
    ");
}

function seedDatabase(): void {
    $db = getDB();
    $count = $db->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    if ($count > 0) return;

    // --- Kullanıcı ---
    $db->exec("INSERT INTO users (name, email, avatar, household_size) VALUES ('Ahmet Yılmaz', 'ahmet@evly.app', '👤', 4)");

    // --- Kategoriler ---
    $cats = [
        ['Süt Ürünleri', 'dairy', '🥛', '#3b82f6'],
        ['Et & Protein', 'meat', '🥩', '#ef4444'],
        ['Meyve & Sebze', 'fruits', '🍎', '#22c55e'],
        ['Fırın & Unlu', 'bakery', '🍞', '#f59e0b'],
        ['Temizlik', 'cleaning', '🧹', '#8b5cf6'],
        ['Atıştırmalık', 'snacks', '🍫', '#ec4899'],
        ['İçecek', 'beverages', '🥤', '#06b6d4'],
        ['Temel Gıda', 'basics', '🌾', '#84cc16'],
        ['Kişisel Bakım', 'personal', '🧴', '#f97316'],
        ['Bebek', 'baby', '🍼', '#a855f7'],
    ];
    $stmt = $db->prepare("INSERT INTO categories (name, slug, icon, color) VALUES (?,?,?,?)");
    foreach ($cats as $c) $stmt->execute($c);

    // --- Ürünler (zengin veri) ---
    $products = [
        ['Süt (1L)', '🥛', 1, 'litre', 0.2, 1, '+2 days', 3, '-2 days', 32.50, 15],
        ['Ekmek', '🍞', 4, 'adet', 0.5, 1, '+1 day', 2, '-1 day', 12.00, 45],
        ['Kaşar Peyniri (500g)', '🧀', 1, 'gram', 150, 500, '+4 days', 7, '-5 days', 89.90, 8],
        ['Yumurta (30lu)', '🥚', 1, 'adet', 8, 30, '+5 days', 10, '-8 days', 109.90, 12],
        ['Tavuk Göğüs (kg)', '🍗', 2, 'kg', 0.3, 1, '+3 days', 5, '-4 days', 159.90, 10],
        ['Domates', '🍅', 3, 'kg', 0.5, 2, '+4 days', 4, '-3 days', 34.90, 20],
        ['Bulaşık Deterjanı', '🧴', 5, 'ml', 300, 1000, '+18 days', 30, '-12 days', 67.50, 4],
        ['Türk Kahvesi (250g)', '☕', 7, 'gram', 80, 250, '+8 days', 15, '-8 days', 54.90, 6],
        ['Çikolata', '🍫', 6, 'adet', 2, 5, '+6 days', 7, '-3 days', 24.90, 9],
        ['Muz', '🍌', 3, 'adet', 3, 8, '+3 days', 4, '-2 days', 49.90, 16],
        ['Zeytinyağı (1L)', '🫒', 8, 'litre', 0.4, 1, '+25 days', 45, '-20 days', 189.90, 3],
        ['Makarna (500g)', '🍝', 8, 'paket', 2, 5, '+15 days', 14, '-7 days', 18.50, 11],
        ['Salatalık', '🥒', 3, 'kg', 0.8, 1.5, '+3 days', 4, '-2 days', 29.90, 18],
        ['Biber (Sivri)', '🌶️', 3, 'kg', 0.3, 1, '+2 days', 3, '-2 days', 39.90, 14],
        ['Tereyağı (250g)', '🧈', 1, 'gram', 100, 250, '+6 days', 10, '-4 days', 74.90, 7],
        ['Pirinç (1kg)', '🍚', 8, 'kg', 0.6, 1, '+20 days', 21, '-10 days', 42.50, 5],
        ['Soğan (kg)', '🧅', 3, 'kg', 1.5, 3, '+10 days', 10, '-5 days', 19.90, 13],
        ['Patates (kg)', '🥔', 3, 'kg', 2, 5, '+12 days', 10, '-6 days', 24.90, 12],
        ['Ayran (1L)', '🥛', 7, 'litre', 0.5, 1, '+2 days', 3, '-1 day', 22.50, 20],
        ['Tuvalet Kağıdı (12li)', '🧻', 5, 'paket', 1, 1, '+10 days', 14, '-4 days', 129.90, 4],
    ];
    $stmt = $db->prepare("INSERT INTO products (name, icon, category_id, unit, quantity, max_quantity, predicted_end, avg_consumption_days, last_purchased, avg_price, purchase_count) VALUES (?,?,?,?,?,?,date('now', ?),?,date('now', ?),?,?)");
    foreach ($products as $p) $stmt->execute($p);

    // --- Fişler + Fiş Kalemleri ---
    $receiptsData = [
        ['Migros', 'Ataşehir', 345.80, 97, '-2 hours', [
            ['Süt (1L)', 2, 'litre', 32.50, 65.00, 'Süt Ürünleri'],
            ['Ekmek', 1, 'adet', 12.00, 12.00, 'Fırın & Unlu'],
            ['Kaşar Peyniri (500g)', 1, 'paket', 89.90, 89.90, 'Süt Ürünleri'],
            ['Domates', 1.5, 'kg', 34.90, 52.35, 'Meyve & Sebze'],
            ['Salatalık', 1, 'kg', 29.90, 29.90, 'Meyve & Sebze'],
            ['Muz', 1.2, 'kg', 49.90, 59.88, 'Meyve & Sebze'],
            ['Ayran (1L)', 1, 'adet', 22.50, 22.50, 'İçecek'],
            ['Çikolata', 1, 'adet', 14.27, 14.27, 'Atıştırmalık'],
        ]],
        ['BİM', 'Kadıköy', 189.40, 94, '-1 day', [
            ['Yumurta (30lu)', 1, 'kutu', 99.90, 99.90, 'Süt Ürünleri'],
            ['Ekmek', 2, 'adet', 10.00, 20.00, 'Fırın & Unlu'],
            ['Makarna (500g)', 2, 'paket', 15.50, 31.00, 'Temel Gıda'],
            ['Biber (Sivri)', 0.5, 'kg', 39.90, 19.95, 'Meyve & Sebze'],
            ['Soğan', 1, 'kg', 18.55, 18.55, 'Meyve & Sebze'],
        ]],
        ['A101', 'Üsküdar', 412.60, 96, '-3 days', [
            ['Tavuk Göğüs (kg)', 1.5, 'kg', 149.90, 224.85, 'Et & Protein'],
            ['Pirinç (1kg)', 1, 'kg', 39.90, 39.90, 'Temel Gıda'],
            ['Zeytinyağı (1L)', 1, 'litre', 179.90, 179.90, 'Temel Gıda'],
            ['Tereyağı (250g)', 1, 'adet', 69.90, 69.90, 'Süt Ürünleri'],
            ['Tuvalet Kağıdı (12li)', 1, 'paket', 119.90, 119.90, 'Temizlik'],
        ]],
        ['ŞOK', 'Maltepe', 156.30, 93, '-5 days', [
            ['Süt (1L)', 3, 'litre', 29.90, 89.70, 'Süt Ürünleri'],
            ['Türk Kahvesi (250g)', 1, 'adet', 49.90, 49.90, 'İçecek'],
            ['Patates', 2, 'kg', 22.90, 45.80, 'Meyve & Sebze'],
        ]],
        ['CarrefourSA', 'Bostancı', 523.50, 95, '-7 days', [
            ['Kaşar Peyniri (500g)', 1, 'paket', 94.90, 94.90, 'Süt Ürünleri'],
            ['Tavuk Göğüs (kg)', 1, 'kg', 159.90, 159.90, 'Et & Protein'],
            ['Bulaşık Deterjanı', 1, 'adet', 62.50, 62.50, 'Temizlik'],
            ['Domates', 2, 'kg', 32.90, 65.80, 'Meyve & Sebze'],
            ['Salatalık', 1, 'kg', 27.90, 27.90, 'Meyve & Sebze'],
            ['Biber (Sivri)', 1, 'kg', 37.50, 37.50, 'Meyve & Sebze'],
            ['Çikolata', 3, 'adet', 24.90, 74.70, 'Atıştırmalık'],
        ]],
    ];

    $stmtR = $db->prepare("INSERT INTO receipts (store_name, store_branch, total_amount, item_count, ocr_accuracy, receipt_date) VALUES (?,?,?,?,?,datetime('now', ?))");
    $stmtRI = $db->prepare("INSERT INTO receipt_items (receipt_id, name, quantity, unit, unit_price, total_price, category_guess) VALUES (?,?,?,?,?,?,?)");

    foreach ($receiptsData as $r) {
        $stmtR->execute([$r[0], $r[1], $r[2], count($r[5]), $r[3], $r[4]]);
        $rid = $db->lastInsertId();
        foreach ($r[5] as $item) {
            $stmtRI->execute([$rid, $item[0], $item[1], $item[2], $item[3], $item[4], $item[5]]);
        }
    }

    // --- Fiyat geçmişi ---
    $stmtPH = $db->prepare("INSERT INTO price_history (product_id, store_name, price, recorded_at) VALUES (?,?,?,datetime('now', ?))");
    $priceData = [
        [1, 'Migros', 32.50, '-2 hours'], [1, 'BİM', 29.90, '-3 days'], [1, 'ŞOK', 29.90, '-5 days'],
        [5, 'A101', 149.90, '-3 days'], [5, 'CarrefourSA', 159.90, '-7 days'],
        [3, 'Migros', 89.90, '-2 hours'], [3, 'CarrefourSA', 94.90, '-7 days'],
        [6, 'Migros', 34.90, '-2 hours'], [6, 'CarrefourSA', 32.90, '-7 days'],
    ];
    foreach ($priceData as $ph) $stmtPH->execute($ph);

    // --- Alışveriş Listesi ---
    $shopItems = [
        [1, 'Süt (1L)', '🥛', 1, 2, 'litre', 32.50, 0, 1, 3, 'Tahmini bitiş: 2 gün — Acil alınmalı'],
        [2, 'Ekmek', '🍞', 4, 1, 'adet', 12.00, 0, 1, 3, 'Her gün tüketilen ürün'],
        [5, 'Tavuk Göğüs (kg)', '🍗', 2, 1, 'kg', 159.90, 0, 1, 2, 'Haftanın protein ihtiyacı'],
        [4, 'Yumurta (30lu)', '🥚', 1, 1, 'kutu', 109.90, 0, 1, 2, '5 gün sonra bitecek'],
        [14, 'Biber (Sivri)', '🌶️', 3, 1, 'kg', 39.90, 0, 1, 1, 'Stok azaldı'],
        [null, 'Zeytin', '🫒', 8, 1, 'kavanoz', 45.00, 0, 0, 0, null],
        [null, 'Bal', '🍯', 8, 1, 'kavanoz', 120.00, 0, 0, 0, null],
        [null, 'Limon', '🍋', 3, 1, 'kg', 29.90, 0, 0, 0, null],
        [null, 'Tereyağı', '🧈', 1, 1, 'adet', 74.90, 1, 0, 0, null],
    ];
    $stmtS = $db->prepare("INSERT INTO shopping_list (product_id, name, icon, category_id, quantity, unit, estimated_price, is_checked, is_ai_suggested, priority, reason) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
    foreach ($shopItems as $s) $stmtS->execute($s);

    // --- Insights ---
    $insights = [
        ['tip', '💰', 'Süt BİM\'de ₺2,60 daha ucuz', 'Migros\'ta ₺32,50 olan süt, BİM\'de ₺29,90. Haftalık ₺7,80 tasarruf edebilirsiniz.', 'Karşılaştır', '?page=analytics'],
        ['save', '📊', 'Bu ay %12 daha az harcadınız', 'Geçen aya göre ₺280 tasarruf ettiniz. Meyve-sebze harcamanız optimize edildi.', 'Detay gör', '?page=analytics'],
        ['warn', '🍌', 'Muz çürüme riski', 'Son 3 alımda muzların %30\'u çürümüş. Daha az miktarda, daha sık alım önerilir.', 'Planla', '?page=shopping'],
        ['ai', '🤖', 'Haftalık menü hazır', 'AI, mevcut stoklarınıza göre 7 günlük menü önerisi hazırladı.', 'Menüyü gör', '?page=meal-plan'],
        ['tip', '🏷️', 'Toplu alım fırsatı', 'Tuvalet kağıdı ve deterjan aynı marketten alınırsa %15 indirim yakalanabilir.', null, null],
    ];
    $stmtI = $db->prepare("INSERT INTO insights (type, icon, title, description, action_text, action_url) VALUES (?,?,?,?,?,?)");
    foreach ($insights as $i) $stmtI->execute($i);

    // --- Haftalık Yemek Planı ---
    $meals = [
        [1, 'breakfast', 'Menemen & Peynir Tabağı', 'Yumurta, Domates, Biber, Kaşar Peyniri'],
        [1, 'lunch', 'Tavuk Sote & Pilav', 'Tavuk Göğüs, Pirinç, Biber, Soğan'],
        [1, 'dinner', 'Makarna & Salata', 'Makarna, Domates, Salatalık, Zeytinyağı'],
        [2, 'breakfast', 'Sahanda Yumurta', 'Yumurta, Tereyağı, Ekmek'],
        [2, 'lunch', 'Mercimek Çorbası & Ekmek', 'Soğan, Patates, Ekmek'],
        [2, 'dinner', 'Tavuk Izgara & Patates', 'Tavuk Göğüs, Patates, Domates'],
        [3, 'breakfast', 'Peynirli Tost', 'Ekmek, Kaşar Peyniri, Domates'],
        [3, 'lunch', 'Sebzeli Makarna', 'Makarna, Biber, Domates, Zeytinyağı'],
        [3, 'dinner', 'Yumurtalı Patates', 'Yumurta, Patates, Soğan'],
    ];
    $stmtM = $db->prepare("INSERT INTO meal_plans (day_of_week, meal_type, title, ingredients) VALUES (?,?,?,?)");
    foreach ($meals as $m) $stmtM->execute($m);
}

setupDatabase();
seedDatabase();
