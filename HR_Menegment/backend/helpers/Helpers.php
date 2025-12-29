<?php
/**
 * Global Helper Functions
 */

/**
 * Sanitize input
 */
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validate date format
 */
function isValidDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

/**
 * Hash password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Check password
 */
function checkPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Redirect with message
 */
function redirectWithMessage($url, $message, $type = 'success') {
    Session::set('message', ['text' => $message, 'type' => $type]);
    header('Location: ' . $url);
    exit;
}

/**
 * Get and clear message
 */
function getMessage() {
    $message = Session::get('message');
    Session::remove('message');
    return $message;
}

/**
 * Get file extension
 */
function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Generate unique filename
 */
function generateUniqueFilename($originalName) {
    $ext = getFileExtension($originalName);
    return uniqid('file_') . '_' . time() . '.' . $ext;
}

/**
 * Format date
 */
function formatDate($date, $format = 'd M Y') {
    return date($format, strtotime($date));
}

/**
 * Format time
 */
function formatTime($time, $format = 'h:i A') {
    return date($format, strtotime($time));
}

/**
 * Get initials from name
 */
function getInitials($firstName, $lastName) {
    return strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
}

/**
 * Get full URL for profile picture (handles relative/absolute paths)
 */
function getProfilePicUrl($profilePicPath) {
    if (empty($profilePicPath)) {
        return null;
    }

    // If already an URL
    if (preg_match('/^https?:\\/\\//i', $profilePicPath)) {
        return $profilePicPath;
    }

    $path = str_replace('\\', '/', $profilePicPath);

    // If stored as relative uploads path
    if (strpos($path, 'uploads/') === 0) {
        return APP_URL . '/' . $path;
    }

    // If stored as absolute path on disk, try to convert to relative URL
    $projectRoot = str_replace('\\', '/', realpath(__DIR__ . '/../../'));
    $normalizedPath = str_replace('\\', '/', $profilePicPath);

    if (strpos($normalizedPath, $projectRoot) === 0) {
        $relative = ltrim(str_replace($projectRoot, '', $normalizedPath), '/');
        return APP_URL . '/' . $relative;
    }

    // Fallback: return as-is
    return $profilePicPath;
}

/**
 * Convert camelCase to normal text
 */
function humanize($text) {
    return ucwords(str_replace('_', ' ', $text));
}

/**
 * Check if file upload is valid
 */
function isValidFileUpload($file, $maxSize = MAX_FILE_SIZE) {
    if (!isset($file['tmp_name']) || !isset($file['name'])) {
        return false;
    }
    
    if ($file['size'] > $maxSize) {
        return false;
    }
    
    $ext = getFileExtension($file['name']);
    if (!in_array($ext, ALLOWED_FILE_TYPES)) {
        return false;
    }
    
    return true;
}

/**
 * Save uploaded file
 */
function saveUploadedFile($file, $directory) {
    if (!isValidFileUpload($file)) {
        return false;
    }
    
    if (!is_dir($directory)) {
        mkdir($directory, 0755, true);
    }
    
    $newFilename = generateUniqueFilename($file['name']);
    $filePath = $directory . '/' . $newFilename;
    
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        return [
            'filename' => $newFilename,
            'path' => $filePath,
            'relative_path' => str_replace(__DIR__ . '/../../', '', $filePath)
        ];
    }
    
    return false;
}
?>