<?php
// create_inquiry.php
require  '../configs/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(405, ['success' => false, 'message' => 'Method Not Allowed']);
}

// ---------- helpers ----------
function ensure_dir(string $dir): void {
    if (!is_dir($dir)) { @mkdir($dir, 0777, true); }
}
function safe_ext(string $filename): string {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return preg_replace('/[^a-z0-9]+/i', '', $ext);
}
function mk_filename(string $ext): string {
    return time() . '_' . bin2hex(random_bytes(4)) . ($ext ? ('.' . $ext) : '');
}
function save_upload(mysqli $mysqli, array $file, string $destDir): array {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        json_out(422, ['success' => false, 'message' => 'Upload error', 'php_upload_error' => $file['error'] ?? 'unknown']);
    }
    $tmp  = $file['tmp_name'];
    $orig = $file['name'];
    $size = (int)$file['size'];

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($tmp) ?: 'application/octet-stream';
    // allow images and pdf
    $allowed = ['image/jpeg','image/png','image/webp','image/gif','application/pdf'];
    if (!in_array($mime, $allowed, true)) {
        json_out(422, ['success' => false, 'message' => 'Invalid file type', 'mime' => $mime]);
    }

    ensure_dir($destDir);
    $ext = safe_ext($orig);
    $filename  = mk_filename($ext);
    $targetAbs = rtrim($destDir, '/\\') . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($tmp, $targetAbs)) {
        json_out(500, ['success' => false, 'message' => 'Failed to move uploaded file']);
    }

    $filePath = str_replace('\\', '/', $targetAbs);

    // record in t_uploads
    $ins = $mysqli->prepare("INSERT INTO t_uploads (purpose, file_original_name, file_path, size, extension) VALUES (?,?,?,?,?)");
    $purpose = 'inquiry';
    $extDb = $ext ?: '';
    $ins->bind_param('sssds', $purpose, $orig, $filePath, $size, $extDb);
    if (!$ins->execute()) {
        json_out(500, ['success' => false, 'message' => 'Failed to insert upload record', 'error' => $ins->error]);
    }
    $uploadId = (int)$ins->insert_id;
    $ins->close();

    return ['id' => $uploadId, 'path' => $filePath];
}

// ---------- read form-data ----------
$name    = trim((string)($_POST['name'] ?? ''));
$mobile  = trim((string)($_POST['mobile'] ?? ''));
$email   = strtolower(trim((string)($_POST['email'] ?? '')));
$subject = strtolower(trim((string)($_POST['subject'] ?? '')));
$messege = trim((string)($_POST['messege'] ?? ''));

if ($name === '' || $mobile === '' || $email === '' || $subject === '' || $messege === '') {
    json_out(422, ['success' => false, 'message' => 'name, mobile, email, subject, messege are required']);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_out(422, ['success' => false, 'message' => 'Invalid email']);
}
if (!in_array($subject, ['contact','inquiry'], true)) {
    json_out(422, ['success' => false, 'message' => "subject must be 'contact' or 'inquiry'"]);
}

// ---------- optional attachment ----------
$uploadId = null;
$uploadPath = null;
if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE) {
    $saved = save_upload($mysqli, $_FILES['attachment'], '../uploads/inquiries');
    $uploadId   = $saved['id'];
    $uploadPath = $saved['path'];
}

// ---------- insert inquiry ----------
$sql = "INSERT INTO t_inquiry_contact (name, mobile, email, subject, messege, upload_id) VALUES (?,?,?,?,?,?)";
$stmt = $mysqli->prepare($sql);
if ($uploadId === null) {
    $null = null;
    $stmt->bind_param('sssssi', $name, $mobile, $email, $subject, $messege, $null);
} else {
    $stmt->bind_param('sssssi', $name, $mobile, $email, $subject, $messege, $uploadId);
}
if (!$stmt->execute()) {
    json_out(500, ['success' => false, 'message' => 'Failed to save inquiry', 'error' => $stmt->error]);
}
$inquiryId = (int)$stmt->insert_id;
$stmt->close();

// ---------- response ----------
json_out(201, [
    'success' => true,
    'message' => 'Inquiry saved',
    'data' => [
        'id'          => $inquiryId,
        'name'        => $name,
        'mobile'      => $mobile,
        'email'       => $email,
        'subject'     => $subject,
        'messege'     => $messege,
        'upload_id'   => $uploadId,
        'upload_path' => $uploadPath
    ]
]);
