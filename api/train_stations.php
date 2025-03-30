<?php
header('Content-Type: application/json');
require_once '../functions.php';

$method = $_SERVER['REQUEST_METHOD'];
$conn = connectDB();

switch ($method) {
    case 'GET':
        try {
            if (isset($_GET['train_id'])) {
                $train_id = (int)sanitizeInput($_GET['train_id']);
                $stmt = $conn->prepare("
                    SELECT ts.*, s.name as station_name, s.city
                    FROM train_stations ts
                    JOIN stations s ON ts.station_id = s.id
                    WHERE ts.train_id = ?
                    ORDER BY ts.stop_order
                ");
                $stmt->execute([$train_id]);
                echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'train_id parameter is required']);
            }
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
        break;

    case 'POST':
        // Admin only
        session_start();
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $train_id = (int)sanitizeInput($data['train_id']);
            $station_id = (int)sanitizeInput($data['station_id']);
            $arrival_time = isset($data['arrival_time']) ? sanitizeInput($data['arrival_time']) : null;
            $departure_time = isset($data['departure_time']) ? sanitizeInput($data['departure_time']) : null;
            $stop_order = (int)sanitizeInput($data['stop_order']);

            $stmt = $conn->prepare("
                INSERT INTO train_stations 
                (train_id, station_id, arrival_time, departure_time, stop_order) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$train_id, $station_id, $arrival_time, $departure_time, $stop_order]);
            
            echo json_encode([
                'success' => true,
                'id' => $conn->lastInsertId(),
                'message' => 'Station added to train successfully'
            ]);
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?>