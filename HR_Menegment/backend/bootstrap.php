<?php
/**
 * Bootstrap - Load all required classes and configurations
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load configuration
require_once __DIR__ . '/config/config.php';

// Check database connection
if (!isset($db)) {
    die('Database connection failed. Check config.php');
}

// Load middleware
require_once __DIR__ . '/middleware/Session.php';
require_once __DIR__ . '/middleware/Auth.php';

// Load models
require_once __DIR__ . '/models/EmployeeModel.php';
require_once __DIR__ . '/models/AttendanceModel.php';
require_once __DIR__ . '/models/LeaveModel.php';
require_once __DIR__ . '/models/TaskModel.php';
require_once __DIR__ . '/models/DocumentModel.php';
require_once __DIR__ . '/models/ProjectModel.php';
require_once __DIR__ . '/models/ProjectCategoryModel.php';
require_once __DIR__ . '/models/NotificationModel.php';

// Load helpers
require_once __DIR__ . '/helpers/Helpers.php';

// Initialize Auth
$auth = new Auth($db);

?>