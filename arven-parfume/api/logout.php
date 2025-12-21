<?php
/**
 * =============================================
 * ARVEN PARFUME - Logout API
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

try {
    $userId = getCurrentUserId();
    $sessionToken = $_SESSION['session_token'] ?? null;
    
    if ($userId && $sessionToken) {
        $db = getDB();
        
        // Delete session from database
        $stmt = $db->prepare("DELETE FROM user_sessions WHERE user_id = ? AND session_token = ?");
        $stmt->execute([$userId, $sessionToken]);
        
        // Log logout activity
        logActivity($userId, 'logout', 'User logged out');
    }
    
    // Clear all session data
    $_SESSION = [];
    
    // Destroy session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    // Destroy session
    session_destroy();
    
    // Send success response
    sendJSON([
        'success' => true,
        'message' => 'Logout berhasil',
        'redirect' => 'login.html'
    ], 200);
    
} catch (Exception $e) {
    error_log("Logout Error: " . $e->getMessage());
    
    // Still clear session even if database operation fails
    session_destroy();
    
    sendJSON([
        'success' => true,
        'message' => 'Logout berhasil',
        'redirect' => 'login.html'
    ], 200);
}