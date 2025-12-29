<?php
/**
 * Database Configuration & Connection
 * PDO-based connection with proper error handling
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'hrms_db');

// App settings
define('APP_NAME', 'HRMS - HR Management System');
define('APP_URL', 'http://localhost/dharmik/HR_Menegment');
define('UPLOADS_DIR', __DIR__ . '/../../uploads');
define('UPLOADS_DOCUMENTS', UPLOADS_DIR . '/documents');
define('UPLOADS_PROFILES', UPLOADS_DIR . '/profiles');

// Session settings
define('SESSION_TIMEOUT', 1800); // 30 minutes
define('REMEMBER_ME_DAYS', 7);

// File upload settings
define('MAX_FILE_SIZE', 10485760); // 10MB
define('ALLOWED_FILE_TYPES', ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'txt']);

// Pagination
define('ITEMS_PER_PAGE', 10);

/**
 * Create PDO Database Connection
 */
function getDB() {
    try {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        die('Database Connection Failed: ' . $e->getMessage());
    }
}

// Create global DB connection
$db = getDB();

?>