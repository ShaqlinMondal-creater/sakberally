<?php
/**
 * Core PHP importer:
 * - Reads t_sheets where status = 0
 * - Uses t_sheets.name as category name
 * - Downloads CSV from t_sheets.path (Google Sheets CSV URL or any CSV file path)
 * - Inserts/updates categories and products
 * - Optionally records uploads if t_uploads exists
 * - Finally sets t_sheets.status = 1
 */

ini_set('display_errors', '1');
error_reporting(E_ALL);

/* ====== DB CONFIG ====== */
// $dbHost = 'localhost';
// $dbName = 'sakberally_';
// $dbUser = 'sakberally_';
// $dbPass = '';

$dbHost = '127.0.0.1';
$dbName = 'sakberally_';
$dbUser = 'sakberally_';
$dbPass = '@7Uvqi429';

$charset = 'utf8mb4';

/* ====== CONNECTION ====== */
$dsn = "mysql:host=$dbHost;dbname=$dbName;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];
$pdo = new PDO($dsn, $dbUser, $dbPass, $options);

/* ====== HELPERS ====== */

/**
 * Download CSV content from a URL or read from local path.
 */
function fetchCsv(string $path): string {
    // Try file_get_contents first
    $ctx = stream_context_create(['http' => ['timeout' => 30]]);
    $data = @file_get_contents($path, false, $ctx);
    if ($data !== false) return $data;

    // Fallback to cURL
    if (function_exists('curl_init')) {
        $ch = curl_init($path);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $data = curl_exec($ch);
        if ($data === false) {
            $err = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException("Failed to download CSV: $err");
        }
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code >= 400) {
            throw new RuntimeException("CSV HTTP error code: $code");
        }
        return $data;
    }

    // Last resort: local file path?
    if (is_readable($path)) {
        return file_get_contents($path);
    }

    throw new RuntimeException("Unable to fetch CSV from path: $path");
}

/**
 * Parse CSV string into rows (array of associative arrays keyed by header).
 */
function parseCsvAssoc(string $csv): array {
    $rows = [];
    $lines = preg_split("/\r\n|\n|\r/", trim($csv));
    if (!$lines || !count($lines)) return $rows;

    $headers = str_getcsv(array_shift($lines));
    // Normalize headers: trim and unify spacing
    $headers = array_map(fn($h) => trim($h), $headers);

    foreach ($lines as $line) {
        if (trim($line) === '') continue;
        $cols = str_getcsv($line);
        // Pad columns if fewer than headers
        if (count($cols) < count($headers)) {
            $cols = array_pad($cols, count($headers), '');
        }
        $row = [];
        foreach ($headers as $i => $h) {
            $row[$h] = $cols[$i] ?? '';
        }
        $rows[] = $row;
    }
    return $rows;
}

/**
 * Keep only digits and dot in price; returns float-like string.
 */
function cleanPrice(string $raw): string {
    $raw = trim($raw);
    // remove currency symbols and commas/spaces etc.
    $n = preg_replace('/[^\d.]+/', '', $raw);
    if ($n === '' || $n === null) return '0';
    // collapse multiple dots if any
    $parts = explode('.', $n);
    if (count($parts) > 2) {
        $int = array_shift($parts);
        $n = $int . '.' . implode('', $parts);
    }
    return $n;
}

/**
 * Build Features HTML table from up to 10 pairs of (Features name N / Features value N)
 */
function buildFeaturesHtml(array $row): string {
    $html = [];
    $html[] = '<table class=" tbl tble1"><tbody>';
    $count = 0;

    for ($i = 1; $i <= 10; $i++) {
        $nameKey  = "Features name $i";
        $valueKey = "Features value $i";
        $fname  = isset($row[$nameKey])  ? trim($row[$nameKey])  : '';
        $fvalue = isset($row[$valueKey]) ? trim($row[$valueKey]) : '';
        if ($fname === '' && $fvalue === '') continue;

        $count++;
        // escape basic HTML
        $fnameEsc  = htmlspecialchars($fname, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $fvalueEsc = htmlspecialchars($fvalue, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $html[] = "<tr><td class=\"fw6\">{$fnameEsc}</td><td>{$fvalueEsc}</td></tr>";
    }

    $html[] = '</tbody></table>';
    return $count ? implode('', $html) : ''; // empty if no features
}

/**
 * Build Description HTML from points "Description point N" (we’ll accept 1..10 safely).
 */
function buildDescriptionHtml(array $row): string {
    $html = [];
    $html[] = '<div class="pro_desc">';
    $count = 0;

    for ($i = 1; $i <= 10; $i++) {
        $key = "Description point $i";
        if (!array_key_exists($key, $row)) continue;
        $val = trim($row[$key] ?? '');
        if ($val === '') continue;
        $count++;
        $valEsc = htmlspecialchars($val, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html[] = "<p>{$valEsc}</p>";
    }

    $html[] = '</div>';
    return $count ? implode('', $html) : '';
}

/**
 * Check if a table exists.
 */
function tableExists(PDO $pdo, string $table): bool {
    $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
    $stmt->execute([$table]);
    return (bool)$stmt->fetchColumn();
}

/**
 * Create or find category by name. Returns category id.
 * t_categories schema: id, name, category_image_id, sort_no, `create`, `update`
 */
function ensureCategory(PDO $pdo, string $name): int {
    $name = trim($name);
    if ($name === '') $name = 'Uncategorized';

    // find
    $stmt = $pdo->prepare("SELECT id FROM t_categories WHERE name = ? LIMIT 1");
    $stmt->execute([$name]);
    $id = $stmt->fetchColumn();
    if ($id) return (int)$id;

    // insert
    $now = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare("
        INSERT INTO t_categories (name, category_image_id, sort_no, `create`, `update`)
        VALUES (?, NULL, 0, ?, ?)
    ");
    $stmt->execute([$name, $now, $now]);
    return (int)$pdo->lastInsertId();
}

/**
 * If t_uploads exists with columns (id, name, path), record the thumbnail path.
 * Returns upload_id or null.
 */
function maybeInsertUpload(PDO $pdo, string $productName, string $thumbUrl): ?int {
    if ($thumbUrl === '' || !tableExists($pdo, 't_uploads')) return null;

    // check minimal columns
    $desc = $pdo->query("DESCRIBE t_uploads")->fetchAll(PDO::FETCH_COLUMN, 0);
    $need = ['id', 'name', 'path'];
    foreach ($need as $col) {
        if (!in_array($col, $desc, true)) return null;
    }

    $stmt = $pdo->prepare("INSERT INTO t_uploads (name, path) VALUES (?, ?)");
    $stmt->execute([$productName, $thumbUrl]);
    return (int)$pdo->lastInsertId();
}

/**
 * Insert or update product by (name, category_id).
 */
function upsertProduct(PDO $pdo, array $product) {
    // Check existing by name + category
    $stmt = $pdo->prepare("
        SELECT id FROM t_products WHERE name = ? AND category_id = ? LIMIT 1
    ");
    $stmt->execute([$product['name'], $product['category_id']]);
    $existingId = $stmt->fetchColumn();

    if ($existingId) {
        // Update
        $stmt = $pdo->prepare("
            UPDATE t_products
            SET price = ?, unit = ?, upd_link = ?, upload_id = ?, features = ?, description = ?, short_description = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([
            $product['price'],
            $product['unit'],
            $product['upd_link'],
            $product['upload_id'],
            $product['features'],
            $product['description'],
            $product['short_description'],
            $existingId
        ]);
        return (int)$existingId;
    } else {
        // Insert
        $stmt = $pdo->prepare("
            INSERT INTO t_products
            (name, price, unit, category_id, upd_link, upload_id, features, description, short_description, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([
            $product['name'],
            $product['price'],
            $product['unit'],
            $product['category_id'],
            $product['upd_link'],
            $product['upload_id'],
            $product['features'],
            $product['description'],
            $product['short_description'],
        ]);
        return (int)$pdo->lastInsertId();
    }
}

/* ====== MAIN ====== */

try {
    // 1) Get sheets to process
    $sheets = $pdo->query("SELECT id, name, path FROM t_sheets WHERE status = 0 ORDER BY id ASC")->fetchAll();
    if (!$sheets) {
        echo "No pending sheets.\n";
        exit;
    }

    foreach ($sheets as $sheet) {
        $sheetId   = (int)$sheet['id'];
        $catName   = trim($sheet['name'] ?? '');
        $csvPath   = trim($sheet['path'] ?? '');

        echo "Processing Sheet ID {$sheetId} (Category: {$catName})...\n";

        // 2) Ensure category
        $categoryId = ensureCategory($pdo, $catName);

        // 3) Fetch + parse CSV
        $csv = fetchCsv($csvPath);
        $rows = parseCsvAssoc($csv);
        if (!$rows) {
            echo "  - Empty or unreadable CSV. Marking sheet as processed.\n";
            $pdo->prepare("UPDATE t_sheets SET status = 1, updated_at = NOW() WHERE id = ?")->execute([$sheetId]);
            continue;
        }

        // Expected headers we’ll use:
        // SL NO, Product Name, Thumbnail, Price, Unit,
        // Features name 1..10, Features value 1..10,
        // Description point 1..10 (you listed 1..4 but we accept up to 10)
        $countImported = 0;

        foreach ($rows as $row) {
            $productName = trim($row['Product Name'] ?? '');
            if ($productName === '') continue; // skip blank

            $thumb      = trim($row['Thumbnail'] ?? '');
            $price      = cleanPrice($row['Price'] ?? '0');
            $unit       = trim($row['Unit'] ?? '');
            $features   = buildFeaturesHtml($row);
            $descHtml   = buildDescriptionHtml($row);
            $shortDesc  = ''; // keep empty unless you have a column

            // Optional upload insert
            $uploadId = maybeInsertUpload($pdo, $productName, $thumb);

            $product = [
                'name'              => $productName,
                'price'             => $price,
                'unit'              => $unit,
                'category_id'       => $categoryId,
                'upd_link'          => $thumb,         // store image link here
                'upload_id'         => $uploadId,      // nullable if no uploads table
                'features'          => $features,      // HTML table
                'description'       => $descHtml,      // HTML block
                'short_description' => $shortDesc,
            ];

            upsertProduct($pdo, $product);
            $countImported++;
        }

        // 4) Mark sheet as processed
        $pdo->prepare("UPDATE t_sheets SET status = 1, updated_at = NOW() WHERE id = ?")->execute([$sheetId]);

        echo "  - Imported/Updated: {$countImported} products. Marked sheet as processed.\n";
    }

    echo "All pending sheets processed.\n";

} catch (Throwable $e) {
    http_response_code(500);
    echo "Error: " . $e->getMessage() . "\n";
    // You can log $e->getTraceAsString() if needed
}
