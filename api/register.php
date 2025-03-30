<?php
header('Content-Type: application/json');
require_once '../functions.php';

$data = json_decode(file_get_contents('php://input'), true);
$username = sanitizeInput($data['username']);
$password = sanitizeInput($data['password']);

try {
    // Check if username already exists
    $conn = connectDB();
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    
    if ($stmt->rowCount() > 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'username_exists',
            'message' => 'Username already taken'
        ]);
        exit;
    }

    // Create new user
    if (createUser($username, $password)) {
        echo json_encode([
            'success' => true,
            'message' => 'User registered successfully'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to register user'
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