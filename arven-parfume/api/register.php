<?php
/**
 * =============================================
 * ARVEN PARFUME - Registration API
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
$fullName = sanitize($input['fullName'] ?? '');
$email = sanitize($input['email'] ?? '');
$password = $input['password'] ?? '';
$confirmPassword = $input['confirmPassword'] ?? '';
$agreeTerms = $input['agreeTerms'] ?? false;

// Validation array
$errors = [];

// Validate full name
if (empty($fullName)) {
    $errors[] = 'Nama lengkap harus diisi';
} elseif (strlen($fullName) < 3) {
    $errors[] = 'Nama lengkap minimal 3 karakter';
}

// Validate email
if (empty($email)) {
    $errors[] = 'Email harus diisi';
} elseif (!isValidEmail($email)) {
    $errors[] = 'Format email tidak valid';
}

// Validate password
if (empty($password)) {
    $errors[] = 'Password harus diisi';
} elseif (strlen($password) < 6) {
    $errors[] = 'Password minimal 6 karakter';
}

// Validate password confirmation
if ($password !== $confirmPassword) {
    $errors[] = 'Konfirmasi password tidak cocok';
}

// Validate terms agreement
if (!$agreeTerms) {
    $errors[] = 'Anda harus menyetujui syarat dan ketentuan';
}

// If there are validation errors
if (!empty($errors)) {
    sendJSON([
        'success' => false,
        'message' => implode(', ', $errors),
        'errors' => $errors
    ], 400);
}

try {
    $db = getDB();
    
    // Check if email already exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        sendJSON([
            'success' => false,
            'message' => 'Email sudah terdaftar. Silakan gunakan email lain atau login.'
        ], 409);
    }
    
    // Hash password
    $hashedPassword = hashPassword($password);
    
    // Insert new user
    $stmt = $db->prepare("
        INSERT INTO users (full_name, email, password, role, is_active, email_verified) 
        VALUES (?, ?, ?, 'user', 1, 0)
    ");
    
    $stmt->execute([
        $fullName,
        $email,
        $hashedPassword
    ]);
    
    $userId = $db->lastInsertId();
    
    // Log registration activity
    logActivity($userId, 'register', 'User registered successfully');
    
    // Auto login after registration
    // Generate session token
    $sessionToken = generateToken();
    $expiresAt = date('Y-m-d H:i:s', time() + SESSION_LIFETIME);
    
    // Save session to database
    $stmt = $db->prepare("
        INSERT INTO user_sessions (user_id, session_token, ip_address, user_agent, expires_at) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $userId,
        $sessionToken,
        getClientIP(),
        getUserAgent(),
        $expiresAt
    ]);
    
    // Set session variables
    $_SESSION['user_id'] = $userId;
    $_SESSION['session_token'] = $sessionToken;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_name'] = $fullName;
    $_SESSION['user_role'] = 'user';
    
    // Update last login
    $stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $stmt->execute([$userId]);
    
    // Log login activity
    logActivity($userId, 'login', 'Auto login after registration');
    
    // Send success response
    sendJSON([
        'success' => true,
        'message' => 'Registrasi berhasil! Selamat datang di ARVEN PARFUME.',
        'user' => [
            'id' => $userId,
            'name' => $fullName,
            'email' => $email,
            'role' => 'user'
        ],
        'session_token' => $sessionToken,
        'redirect' => 'index.html'
    ], 201);
    
} catch (PDOException $e) {
    error_log("Registration Error: " . $e->getMessage());
    
    sendJSON([
        'success' => false,
        'message' => 'Terjadi kesalahan saat registrasi. Silakan coba lagi.'
    ], 500);
}