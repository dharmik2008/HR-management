<?php
/**
 * Session Management Class
 */

class Session {
    
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }
    
    public static function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }
    
    public static function has($key) {
        return isset($_SESSION[$key]);
    }
    
    public static function remove($key) {
        unset($_SESSION[$key]);
    }
    
    public static function destroy() {
        session_destroy();
    }
    
    public static function isLoggedIn() {
        return self::has('user') && self::has('user_type');
    }
    
    public static function isAdmin() {
        return self::get('user_type') === 'hr_admin';
    }
    
    public static function isEmployee() {
        return self::get('user_type') === 'employee';
    }
    
    public static function getUserId() {
        if (self::isAdmin()) {
            return self::get('user')['Hr_id'] ?? null;
        } elseif (self::isEmployee()) {
            return self::get('user')['Emp_id'] ?? null;
        }
        return null;
    }
    
    public static function getUser() {
        return self::get('user');
    }
}
?>