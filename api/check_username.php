<?php
header('Content-Type: application/json');
require_once '../functions.php';

$username = isset($_GET['username']) ? sanitizeInput($_GET['username']) : '';

if (empty($username)) {
    http_response_code(400);
    echo json_encode(['error' => 'Username parameter is required']);
    exit;
}

try {
    $conn = connectDB();
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    
    echo json_encode([
        'exists' => $stmt->rowCount() > 0
    ]);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>