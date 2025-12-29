<?php
/**
 * Secure Document Download Handler
 * Ensures only authorized users can download documents
 */

require_once __DIR__ . '/bootstrap.php';

// Check if user is logged in
if (!Session::isLoggedIn()) {
    http_response_code(403);
    die('Access denied. Please login to download documents.');
}

$docId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($docId <= 0) {
    http_response_code(400);
    die('Invalid document ID.');
}

$docModel = new DocumentModel($db);
$document = $docModel->getDocumentById($docId);

if (!$document) {
    http_response_code(404);
    die('Document not found.');
}

$userId = Session::getUserId();

// Check permissions:
// - Employees can only download their own documents
// - Admins can download any document
$canDownload = false;

if (Session::isAdmin()) {
    // Admin can download any document
    $canDownload = true;
} elseif (Session::isEmployee()) {
    // Employee can only download their own documents
    $canDownload = ($document['Emp_id'] == $userId);
}

if (!$canDownload) {
    http_response_code(403);
    die('You do not have permission to download this document.');
}

// Get the file path
$filePath = $document['File_path'];

// Normalize path separators
$filePath = str_replace('\\', '/', $filePath);

// Try different path resolutions
$possiblePaths = [];

// 1. Try as-is (if absolute path)
if (file_exists($filePath)) {
    $possiblePaths[] = $filePath;
}

// 2. Try relative from backend directory
$possiblePaths[] = __DIR__ . '/../' . ltrim($filePath, '/');

// 3. Try relative from uploads directory
if (strpos($filePath, 'uploads/') !== false) {
    $possiblePaths[] = __DIR__ . '/../' . $filePath;
} else {
    // 4. Try in documents folder
    $possiblePaths[] = __DIR__ . '/../uploads/documents/' . basename($filePath);
}

// 5. Try with UPLOADS_DOCUMENTS constant
$possiblePaths[] = UPLOADS_DOCUMENTS . '/' . basename($filePath);

// Find the first existing file
$actualPath = null;
foreach ($possiblePaths as $path) {
    if (file_exists($path) && is_file($path)) {
        $actualPath = $path;
        break;
    }
}

// Final check if file exists
if (!$actualPath) {
    http_response_code(404);
    die('File not found on server: ' . htmlspecialchars($filePath));
}

$filePath = $actualPath;

// Get file info
$fileName = $document['File_name'];
$fileSize = filesize($filePath);
$mimeType = mime_content_type($filePath);

// Check if viewing in browser (for PDFs, images)
$viewInBrowser = isset($_GET['view']) && $_GET['view'] == '1';
$canViewInline = in_array(strtolower($document['File_type']), ['pdf', 'jpg', 'jpeg', 'png', 'gif']);

// Set headers
header('Content-Type: ' . $mimeType);
header('Content-Length: ' . $fileSize);
header('Cache-Control: must-revalidate');
header('Pragma: public');

if ($viewInBrowser && $canViewInline) {
    // View in browser
    header('Content-Disposition: inline; filename="' . addslashes($fileName) . '"');
} else {
    // Force download
    header('Content-Disposition: attachment; filename="' . addslashes($fileName) . '"');
}

// Output file
readfile($filePath);
exit;

