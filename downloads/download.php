<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/db.php';

if (!isset($_GET['file'])) {
    http_response_code(400);
    die('Missing file parameter.');
}

$filename = basename($_GET['file']);
$stmt = $pdo->prepare("SELECT * FROM files WHERE filename = ?");
$stmt->execute([$filename]);
$file = $stmt->fetch();

if (!$file || !file_exists($file['filepath'])) {
    http_response_code(404);
    die('File not found.');
}

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($file['filepath']));
readfile($file['filepath']);
exit;
