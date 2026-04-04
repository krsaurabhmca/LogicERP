<?php
/**
 * REST API: User Authentication
 * LogicERP Modular Framework
 */
require_once __DIR__ . '/../../core/init.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => 'Email and Password are required.']);
    exit;
}

// Find user by email
$user = fetch_one("SELECT * FROM users WHERE email = ? AND is_active = 1", [$email]);

if ($user && password_verify($password, $user['password'])) {
    // Generate a simple API Token (for internal use, replace with JWT for production)
    $api_token = bin2hex(random_bytes(32));
    
    echo json_encode([
        'status' => 'success',
        'token' => $api_token,
        'user' => [
            'id' => $user['user_id'],
            'name' => $user['full_name'],
            'role' => $user['role_id']
        ]
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid credentials.']);
}
?>
