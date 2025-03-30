<?php
header('Content-Type: application/json');
require_once '../functions.php';

$data = json_decode(file_get_contents('php://input'), true);
$username = sanitizeInput($data['username']);
$password = sanitizeInput($data['password']);
$remember = isset($data['remember']) ? (bool)$data['remember'] : false;

try {
    $user = authenticateUser($username, $password);

    if ($user) {
        session_start();
        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role']
        ];
        
        if ($remember) {
            // Set cookie to expire in 30 days
            $cookieValue = base64_encode($user['id'] . ':' . hash('sha256', $user['password_hash']));
            setcookie('remember_token', $cookieValue, time() + (86400 * 30), "/");
        }
        
        echo json_encode([
            'success' => true,
            'role' => $user['role'],
            'message' => 'Login successful'
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid username or password'
        ]);
    }
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>