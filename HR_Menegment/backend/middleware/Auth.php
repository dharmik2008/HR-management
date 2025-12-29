<?php
/**
 * Authentication & Authorization Middleware
 */

class Auth {
    
    private $db;
    private const HASH_OPTIONS = ['cost' => 12];
    
    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Verify password and upgrade legacy/plain values to bcrypt
     */
    private function verifyAndUpgradePassword($inputPassword, $storedPassword, $table, $passwordColumn, $idColumn, $userId) {
        // Accept bcrypt or (as a fallback) legacy plain-text passwords
        $isValid = password_verify($inputPassword, $storedPassword) || hash_equals((string)$storedPassword, (string)$inputPassword);
        
        // If valid and hash is missing/old/plain, upgrade to bcrypt for future logins
        if ($isValid && (hash_equals($storedPassword, $inputPassword) || password_needs_rehash($storedPassword, PASSWORD_BCRYPT, self::HASH_OPTIONS))) {
            $newHash = password_hash($inputPassword, PASSWORD_BCRYPT, self::HASH_OPTIONS);
            $sql = sprintf("UPDATE %s SET %s = ? WHERE %s = ? LIMIT 1", $table, $passwordColumn, $idColumn);
            $update = $this->db->prepare($sql);
            $update->execute([$newHash, $userId]);
        }

        return $isValid;
    }
    
    /**
     * Login HR Admin
     */
    public function loginHRAdmin($email, $password) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM HR_Admins WHERE Hr_email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && $this->verifyAndUpgradePassword($password, $user['Hr_password'], 'HR_Admins', 'Hr_password', 'Hr_id', $user['Hr_id'])) {
                Session::set('user', $user);
                Session::set('user_type', 'hr_admin');
                Session::set('logged_in_time', time());
                return true;
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Login Employee
     */
    public function loginEmployee($email, $password) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM Employees WHERE Emp_email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && $this->verifyAndUpgradePassword($password, $user['Emp_password'], 'Employees', 'Emp_password', 'Emp_id', $user['Emp_id'])) {
                // Check if employee is active
                if ($user['Status'] !== 'Active') {
                    return false;
                }
                Session::set('user', $user);
                Session::set('user_type', 'employee');
                Session::set('logged_in_time', time());
                return true;
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Logout
     */
    public function logout() {
        Session::destroy();
    }
    
    /**
     * Check session timeout
     */
    public static function checkTimeout() {
        if (Session::has('logged_in_time')) {
            if (time() - Session::get('logged_in_time') > SESSION_TIMEOUT) {
                Session::destroy();
                return false;
            }
            // Update last activity
            Session::set('logged_in_time', time());
        }
        return true;
    }
    
    /**
     * Require authentication
     */
    public static function requireLogin() {
        if (!Session::isLoggedIn()) {
            header('Location: ' . APP_URL . '/frontend/index.php');
            exit;
        }
        self::checkTimeout();
    }
    
    /**
     * Require admin role
     */
    public static function requireAdmin() {
        self::requireLogin();
        if (!Session::isAdmin()) {
            header('Location: ' . APP_URL . '/frontend/index.php');
            exit;
        }
    }
    
    /**
     * Require employee role
     */
    public static function requireEmployee() {
        self::requireLogin();
        if (!Session::isEmployee()) {
            header('Location: ' . APP_URL . '/frontend/index.php');
            exit;
        }
    }
}
?>