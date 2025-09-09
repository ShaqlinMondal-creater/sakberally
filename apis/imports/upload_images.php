<?php
// download_images.php
require '../configs/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(405, ['success' => false, 'message' => 'Method Not Allowed']);
}

$in    = read_json_body();
$id    = isset($in['id']) ? (int)$in['id'] : 0;     // process single product if >0
$limit = isset($in['limit']) ? max(1, (int)$in['limit']) : 50; // batch size when id=0

// ---------- helpers ----------
function ensure_dir(string $dir): void {
    if (!is_dir($dir)) { @mkdir($dir, 0777, true); }
}
function safe_ext_from_name(string $name): string {
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    return preg_replace('/[^a-z0-9]+/i', '', $ext);
}
function ext_from_mime(string $mime): ?string {
    static $map = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];
    return $map[$mime] ?? null;
}
function fetch_url_to_tmp(string $url, int $timeout = 20): array {
    // returns [bool $ok, string $tmpFile, string $error]
    $tmp = tempnam(sys_get_temp_dir(), 'dl_');
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        $fp = fopen($tmp, 'wb');
        curl_setopt_array($ch, [
            CURLOPT_FILE           => $fp,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (compatible; downloader)',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);
        $ok = curl_exec($ch);
        $err = $ok ? '' : (curl_error($ch) ?: 'download failed');
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        fclose($fp);
        if (!$ok || ($status && $status >= 400)) {
            @unlink($tmp);
            return [false, '', $err ?: ('http status ' . $status)];
        }
        return [true, $tmp, ''];
    } else {
        $ctx = stream_context_create([
            'http' => ['method' => 'GET', 'timeout' => $timeout, 'header' => "User-Agent: downloader\r\n"],
            'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false],
        ]);
        $data = @file_get_contents($url, false, $ctx);
        if ($data === false) {
            @unlink($tmp);
            return [false, '', 'download failed'];
        }
        file_put_contents($tmp, $data);
        return [true, $tmp, ''];
    }
}
function normalize_slashes(string $p): string { return str_replace('\\', '/', $p); }

function process_product(mysqli $mysqli, array $prod, string $destDir): array {
    // returns ['id'=>..., 'status'=>'ok'|'skip'|'error', 'message'=>..., 'upload_id'=>..., 'path'=>...]
    $pid = (int)$prod['id'];
    $link = trim((string)$prod['upd_link']);
    $existingUpload = (int)($prod['upload_id'] ?? 0);

    if ($existingUpload > 0) {
        return ['id'=>$pid, 'status'=>'skip', 'message'=>'upload_id already set'];
    }
    if ($link === '') {
        return ['id'=>$pid, 'status'=>'skip', 'message'=>'empty upd_link'];
    }

    // 1) download
    [$ok, $tmp, $err] = fetch_url_to_tmp($link);
    if (!$ok) {
        return ['id'=>$pid, 'status'=>'error', 'message'=>"download error: $err"];
    }

    // 2) detect mime
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($tmp) ?: 'application/octet-stream';
    $allowed = ['image/jpeg','image/png','image/webp','image/gif'];
    if (!in_array($mime, $allowed, true)) {
        @unlink($tmp);
        return ['id'=>$pid, 'status'=>'error', 'message'=>"unsupported mime: $mime"];
    }

    // 3) choose extension
    $origName = basename(parse_url($link, PHP_URL_PATH) ?: '');
    $ext = safe_ext_from_name($origName);
    if ($ext === '') { $ext = ext_from_mime($mime) ?? 'jpg'; }

    // 4) move to final path
    ensure_dir($destDir);
    $filename = time() . '_' . $pid . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
    $final = rtrim($destDir, '/\\') . DIRECTORY_SEPARATOR . $filename;
    if (!@rename($tmp, $final)) {
        @unlink($tmp);
        return ['id'=>$pid, 'status'=>'error', 'message'=>'failed to move file'];
    }
    $final = normalize_slashes($final);
    $size  = (int)@filesize($final);

    // 5) insert into t_uploads
    $purpose = 'products';
    $ins = $mysqli->prepare("INSERT INTO t_uploads (purpose, file_original_name, file_path, size, extension) VALUES (?,?,?,?,?)");
    $fnForDb = $origName !== '' ? $origName : $filename;
    $ins->bind_param('sssds', $purpose, $fnForDb, $final, $size, $ext);
    if (!$ins->execute()) {
        @unlink($final);
        return ['id'=>$pid, 'status'=>'error', 'message'=>'db insert upload failed: '.$ins->error];
    }
    $uploadId = (int)$ins->insert_id;
    $ins->close();

    // 6) update product.upload_id
    $up = $mysqli->prepare("UPDATE t_products SET upload_id = ?, updated_at = NOW() WHERE id = ?");
    $up->bind_param('ii', $uploadId, $pid);
    if (!$up->execute()) {
        // optional: rollback upload row + delete file
        $d = $mysqli->prepare("DELETE FROM t_uploads WHERE id = ? LIMIT 1");
        $d->bind_param('i', $uploadId);
        $d->execute();
        $d->close();
        @unlink($final);
        return ['id'=>$pid, 'status'=>'error', 'message'=>'db update product failed: '.$up->error];
    }
    $up->close();

    return ['id'=>$pid, 'status'=>'ok', 'message'=>'saved', 'upload_id'=>$uploadId, 'path'=>$final];
}

// ---------- choose workload ----------
$destDir = '../uploads/products';
$results = [];

if ($id > 0) {
    // single product
    $q = $mysqli->prepare("SELECT id, upd_link, upload_id FROM t_products WHERE id = ? LIMIT 1");
    $q->bind_param('i', $id);
    $q->execute();
    $prod = $q->get_result()->fetch_assoc();
    $q->close();

    if (!$prod) {
        json_out(404, ['success'=>false, 'message'=>'Product not found']);
    }
    $results[] = process_product($mysqli, $prod, $destDir);
} else {
    // batch: all missing upload_id & having a non-empty upd_link
    $sql = "SELECT id, upd_link, upload_id 
            FROM t_products 
            WHERE (upload_id IS NULL OR upload_id = 0) 
              AND upd_link IS NOT NULL AND upd_link <> ''
            ORDER BY id ASC
            LIMIT ?";
    $q = $mysqli->prepare($sql);
    $q->bind_param('i', $limit);
    $q->execute();
    $rs = $q->get_result();
    while ($row = $rs->fetch_assoc()) {
        $results[] = process_product($mysqli, $row, $destDir);
    }
    $q->close();
}

$okCount   = count(array_filter($results, fn($r) => $r['status'] === 'ok'));
$skipCount = count(array_filter($results, fn($r) => $r['status'] === 'skip'));
$errCount  = count(array_filter($results, fn($r) => $r['status'] === 'error'));

json_out(200, [
    'success' => true,
    'message' => 'Download job finished',
    'data' => [
        'requested_id' => $id > 0 ? $id : null,
        'processed'    => count($results),
        'ok'           => $okCount,
        'skipped'      => $skipCount,
        'errors'       => $errCount,
        'details'      => $results
    ]
]);
