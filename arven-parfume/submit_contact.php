<?php
/**
 * Contact Form Submission Handler
 * ARVEN PARFUME - Production Version
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

header('Content-Type: application/json; charset=utf-8');

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get and trim POST data
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Validate required fields
    if (empty($name)) throw new Exception('Nama tidak boleh kosong');
    if (empty($email)) throw new Exception('Email tidak boleh kosong');
    if (empty($subject)) throw new Exception('Subjek tidak boleh kosong');
    if (empty($message)) throw new Exception('Pesan tidak boleh kosong');
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Format email tidak valid');
    }
    
    // Validate length
    if (strlen($name) > 100) throw new Exception('Nama terlalu panjang (max 100 karakter)');
    if (strlen($email) > 100) throw new Exception('Email terlalu panjang (max 100 karakter)');
    if (strlen($subject) > 200) throw new Exception('Subjek terlalu panjang (max 200 karakter)');
    if (strlen($message) > 5000) throw new Exception('Pesan terlalu panjang (max 5000 karakter)');
    
    // Sanitize input
    $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    $subject = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
    $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    
    // Get IP address
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    
    // Database connection
    $conn = getDBConnection();
    
    // Prepare statement
    $stmt = $conn->prepare(
        "INSERT INTO contact_messages (name, email, subject, message, ip_address, status, created_at) 
         VALUES (?, ?, ?, ?, ?, 'unread', NOW())"
    );
    
    if (!$stmt) {
        throw new Exception('Gagal menyiapkan query database');
    }
    
    $stmt->bind_param("sssss", $name, $email, $subject, $message, $ip_address);
    
    if ($stmt->execute()) {
        $insert_id = $stmt->insert_id;
        
        // Log success
        error_log(sprintf(
            "✅ Contact message saved - ID: %d, From: %s (%s)",
            $insert_id,
            $name,
            $email
        ));
        
        $stmt->close();
        $conn->close();
        
        // Success response
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Terima kasih! Pesan Anda berhasil dikirim. Kami akan segera menghubungi Anda.',
            'data' => [
                'id' => $insert_id,
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ]);
        
    } else {
        throw new Exception('Gagal menyimpan pesan ke database');
    }
    
} catch (Exception $e) {
    error_log("❌ Contact form error: " . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>