<?php
/**
 * =============================================
 * ARVEN PARFUME - Login API
 * =============================================
 */

define('ARVEN_ACCESS', true);
require_once __DIR__ . '/../config.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSON([
        'success' => false,
        'message' => 'Invalid request method'
    ], 405);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
$email = sanitize($input['email'] ?? '');
$password = $input['password'] ?? '';
$rememberMe = $input['rememberMe'] ?? false;

// Validation
if (empty($email)) {
    sendJSON([
        'success' => false,
        'message' => 'Email harus diisi'
    ], 400);
}

if (empty($password)) {
    sendJSON([
        'success' => false,
        'message' => 'Password harus diisi'
    ], 400);
}

if (!isValidEmail($email)) {
    sendJSON([
        'success' => false,
        'message' => 'Format email tidak valid'
    ], 400);
}

try {
    $db = getDB();
    
    // Get user from database
    $stmt = $db->prepare("
        SELECT id, full_name, email, password, role, is_active, email_verified 
        FROM users 
        WHERE email = ?
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    // Check if user exists
    if (!$user) {
        sendJSON([
            'success' => false,
            'message' => 'Email tidak terdaftar'
        ], 401);
    }
    
    // Check if user is active
    if (!$user['is_active']) {
        sendJSON([
            'success' => false,
            'message' => 'Akun Anda telah dinonaktifkan. Silakan hubungi administrator.'
        ], 403);
    }
    
    // Verify password
    if (!verifyPassword($password, $user['password'])) {
        // Log failed login attempt
        logActivity($user['id'], 'login_failed', 'Failed login attempt');
        
        sendJSON([
            'success' => false,
            'message' => 'Password salah'
        ], 401);
    }
    
    // Generate session token
    $sessionToken = generateToken();
    
    // Calculate expiration time
    $sessionLifetime = $rememberMe ? (30 * 24 * 60 * 60) : SESSION_LIFETIME; // 30 days or 30 minutes
    $expiresAt = date('Y-m-d H:i:s', time() + $sessionLifetime);
    
    // Delete old sessions for this user (keep only last 5)
    $stmt = $db->prepare("
        DELETE FROM user_sessions 
        WHERE user_id = ? 
        AND id NOT IN (
            SELECT id FROM (
                SELECT id FROM user_sessions 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT 5
            ) AS temp
        )
    ");
    $stmt->execute([$user['id'], $user['id']]);
    
    // Save new session to database
    $stmt = $db->prepare("
        INSERT INTO user_sessions (user_id, session_token, ip_address, user_agent, expires_at) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $user['id'],
        $sessionToken,
        getClientIP(),
        getUserAgent(),
        $expiresAt
    ]);
    
    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['session_token'] = $sessionToken;
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name'] = $user['full_name'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['remember_me'] = $rememberMe;
    
    // Update last login time
    $stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $stmt->execute([$user['id']]);
    
    // Log successful login
    logActivity($user['id'], 'login', 'User logged in successfully');
    
    // Send success response
    sendJSON([
        'success' => true,
        'message' => 'Login berhasil! Selamat datang kembali.',
        'user' => [
            'id' => $user['id'],
            'name' => $user['full_name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'email_verified' => (bool)$user['email_verified']
        ],
        'session_token' => $sessionToken,
        'remember_me' => $rememberMe,
        'redirect' => 'index.html'
    ], 200);
    
} catch (PDOException $e) {
    error_log("Login Error: " . $e->getMessage());
    
    sendJSON([
        'success' => false,
        'message' => 'Terjadi kesalahan saat login. Silakan coba lagi.'
    ], 500);
}