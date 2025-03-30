<?php
header('Content-Type: application/json');
require_once '../functions.php';

// Check if user is authenticated
session_start();
if (!isset($_SESSION['user'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$train_id = (int)sanitizeInput($data['train_id']);
$passenger_name = sanitizeInput($data['passenger_name']);
$email = sanitizeInput($data['email']);
$seats = (int)sanitizeInput($data['seats']);
$payment_method = sanitizeInput($data['payment_method']);

try {
    // Check if the train exists and has enough available seats
    $train = getTrainById($train_id);
    if (!$train || $train['seats_available'] < $seats) {
        http_response_code(400);
        echo json_encode(['error' => 'Not enough seats available']);
        exit;
    }

    // Create booking
    $conn = connectDB();
    $conn->beginTransaction();
    
    // Insert booking
    $stmt = $conn->prepare("INSERT INTO bookings (user_id, train_id, seats_booked) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user']['id'], $train_id, $seats]);
    $booking_id = $conn->lastInsertId();
    
    // Update available seats
    $new_seats = $train['seats_available'] - $seats;
    $update_stmt = $conn->prepare("UPDATE trains SET seats_available = ? WHERE id = ?");
    $update_stmt->execute([$new_seats, $train_id]);
    
    $conn->commit();
    echo json_encode(['success' => true, 'booking_id' => $booking_id]);
} catch(PDOException $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>