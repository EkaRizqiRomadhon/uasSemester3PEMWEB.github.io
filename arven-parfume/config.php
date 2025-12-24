<?php
/**
 * =============================================
 * ARVEN PARFUME - Database Configuration
 * =============================================
 */

// Prevent direct access
if (!defined('ARVEN_ACCESS')) {
    define('ARVEN_ACCESS', true);
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =============================================
// Database Configuration (XAMPP PORT 3307)
// =============================================
define('DB_HOST', '127.0.0.1');     // gunakan 127.0.0.1 agar stabil di Windows
define('DB_PORT', 3306);            // port MySQL XAMPP milik kamu
define('DB_USER', 'root');          // default user XAMPP
define('DB_PASS', '');              // default password XAMPP (kosong)
define('DB_NAME', 'arven-parfume'); // nama database
define('DB_CHARSET', 'utf8mb4');

// =============================================
// Site Configuration
// =============================================
define('SITE_URL', 'http://localhost/arven-parfume'); 
define('SITE_NAME', 'ARVEN PARFUME');

// =============================================
// Security Configuration
// =============================================
define('PASSWORD_HASH_ALGO', PASSWORD_BCRYPT);
define('PASSWORD_HASH_COST', 10);
define('SESSION_LIFETIME', 1800); // 30 minutes

// =============================================
// Database Connection Class
// =============================================
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ];

            if (defined('PDO::MYSQL_ATTR_INIT_COMMAND')) {
                $options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES " . DB_CHARSET;
            }

            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);

        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            die(json_encode([
                'success' => false,
                'message' => 'Database connection failed. Please try again later.'
            ]));
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    private function __clone() {}
    
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

// =============================================
// Helper Functions
// =============================================

function getDB() {
    return Database::getInstance()->getConnection();
}

function sanitize($data) {
    return htmlspecialchars(stripslashes(trim($data)), ENT_QUOTES, 'UTF-8');
}

function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_HASH_ALGO, ['cost' => PASSWORD_HASH_COST]);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function generateToken($length = 64) {
    return bin2hex(random_bytes($length / 2));
}

function getClientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return $_SERVER['HTTP_X_FORWARDED_FOR'];
    return $_SERVER['REMOTE_ADDR'] ?? '';
}

function getUserAgent() {
    return $_SERVER['HTTP_USER_AGENT'] ?? '';
}

function sendJSON($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function logActivity($userId, $action, $description = '') {
    try {
        $db = getDB();
        $stmt = $db->prepare("
            INSERT INTO activity_logs (user_id, action, description, ip_address) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $action, $description, getClientIP()]);
    } catch (Exception $e) {
        error_log("Activity Log Error: " . $e->getMessage());
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id'], $_SESSION['session_token']);
}

function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    
    try {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT id, full_name, email, role, is_active, email_verified, last_login
            FROM users
            WHERE id = ? AND is_active = 1
        ");
        $stmt->execute([getCurrentUserId()]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Get Current User Error: " . $e->getMessage());
        return null;
    }
}

function requireLogin() {
    if (!isLoggedIn()) {
        if (isAjaxRequest()) {
            sendJSON([
                'success' => false,
                'message' => 'Unauthorized. Please login first.',
                'redirect' => 'login.html'
            ], 401);
        } else {
            header('Location: login.html');
            exit;
        }
    }
}

function requireRole($role) {
    requireLogin();
    $user = getCurrentUser();

    if (!$user || $user['role'] !== $role) {
        if (isAjaxRequest()) {
            sendJSON([
                'success' => false,
                'message' => 'Access denied. Insufficient permissions.'
            ], 403);
        } else {
            header('Location: index.html');
            exit;
        }
    }
}

function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

// =============================================
// CSRF Protection
// =============================================

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = generateToken();
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) &&
           hash_equals($_SESSION['csrf_token'], $token);
}

// =============================================
// Error Handling
// =============================================

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("Error [$errno]: $errstr in $errfile on line $errline");

    if (isAjaxRequest()) {
        sendJSON([
            'success' => false,
            'message' => 'An error occurred. Please try again.'
        ], 500);
    }
});

// =============================================
// Timezone
// =============================================
date_default_timezone_set('Asia/Jakarta');
