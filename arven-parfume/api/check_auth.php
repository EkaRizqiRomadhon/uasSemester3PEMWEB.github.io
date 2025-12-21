<?php
/**
 * =============================================
 * ARVEN PARFUME - Check Authentication API
 * =============================================
 */

define('ARVEN_ACCESS', true);
require_once __DIR__ . '/../config.php';

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendJSON([
        'success' => false,
        'message' => 'Invalid request method'
    ], 405);
}

try {
    // Check if user is logged in
    if (!isLoggedIn()) {
        sendJSON([
            'success' => false,
            'authenticated' => false,
            'message' => 'Not authenticated'
        ], 401);
    }
    
    $userId = getCurrentUserId();
    $sessionToken = $_SESSION['session_token'];
    
    $db = getDB();
    
    // Verify session in database
    $stmt = $db->prepare("
        SELECT s.expires_at, u.id, u.full_name, u.email, u.role, u.is_active, u.email_verified
        FROM user_sessions s
        JOIN users u ON s.user_id = u.id
        WHERE s.user_id = ? AND s.session_token = ?
    ");
    $stmt->execute([$userId, $sessionToken]);
    $session = $stmt->fetch();
    
    // Check if session exists and is valid
    if (!$session) {
        // Session not found in database
        session_destroy();
        sendJSON([
            'success' => false,
            'authenticated' => false,
            'message' => 'Invalid session'
        ], 401);
    }
    
    // Check if user is still active
    if (!$session['is_active']) {
        session_destroy();
        sendJSON([
            'success' => false,
            'authenticated' => false,
            'message' => 'Account deactivated'
        ], 403);
    }
    
    // Check if session has expired
    $now = new DateTime();
    $expiresAt = new DateTime($session['expires_at']);
    
    if ($now > $expiresAt) {
        // Session expired - delete from database
        $stmt = $db->prepare("DELETE FROM user_sessions WHERE user_id = ? AND session_token = ?");
        $stmt->execute([$userId, $sessionToken]);
        
        session_destroy();
        
        sendJSON([
            'success' => false,
            'authenticated' => false,
            'message' => 'Session expired. Please login again.'
        ], 401);
    }
    
    // Update session expiration (extend session)
    if (isset($_SESSION['remember_me']) && $_SESSION['remember_me']) {
        $newExpiresAt = date('Y-m-d H:i:s', time() + (30 * 24 * 60 * 60)); // 30 days
    } else {
        $newExpiresAt = date('Y-m-d H:i:s', time() + SESSION_LIFETIME); // 30 minutes
    }
    
    $stmt = $db->prepare("UPDATE user_sessions SET expires_at = ? WHERE user_id = ? AND session_token = ?");
    $stmt->execute([$newExpiresAt, $userId, $sessionToken]);
    
    // Return user data
    sendJSON([
        'success' => true,
        'authenticated' => true,
        'user' => [
            'id' => $session['id'],
            'name' => $session['full_name'],
            'email' => $session['email'],
            'role' => $session['role'],
            'email_verified' => (bool)$session['email_verified']
        ]
    ], 200);
    
} catch (Exception $e) {
    error_log("Check Auth Error: " . $e->getMessage());
    
    sendJSON([
        'success' => false,
        'authenticated' => false,
        'message' => 'Authentication check failed'
    ], 500);
}