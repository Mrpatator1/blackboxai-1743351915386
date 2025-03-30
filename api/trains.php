<?php
header('Content-Type: application/json');
require_once '../functions.php';

$method = $_SERVER['REQUEST_METHOD'];
$conn = connectDB();

switch ($method) {
    case 'GET':
        // Get all trains or specific train
        try {
            if (isset($_GET['id'])) {
                $id = sanitizeInput($_GET['id']);
                $train = getTrainById($id);
                if ($train) {
                    echo json_encode($train);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Train not found']);
                }
            } else {
                $trains = getTrainsWithStations();
                echo json_encode($trains);
            }
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
        break;

    case 'POST':
        // Add new train (admin only)
        session_start();
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $name = sanitizeInput($data['name']);
        $departure_station_id = (int)sanitizeInput($data['departure_station_id']);
        $arrival_station_id = (int)sanitizeInput($data['arrival_station_id']);
        $departure = sanitizeInput($data['departure_time']);
        $arrival = sanitizeInput($data['arrival_time']);
        $seats = (int)sanitizeInput($data['seats_available']);

        if (addTrain($name, $departure_station_id, $arrival_station_id, $departure, $arrival, $seats)) {
            echo json_encode(['success' => true, 'message' => 'Train added successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to add train']);
        }
        break;

    case 'PUT':
        // Update train (admin only)
        session_start();
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $id = (int)sanitizeInput($data['id']);
        $name = sanitizeInput($data['name']);
        $departure_station_id = (int)sanitizeInput($data['departure_station_id']);
        $arrival_station_id = (int)sanitizeInput($data['arrival_station_id']);
        $departure = sanitizeInput($data['departure_time']);
        $arrival = sanitizeInput($data['arrival_time']);
        $seats = (int)sanitizeInput($data['seats_available']);

        if (updateTrain($id, $name, $departure_station_id, $arrival_station_id, $departure, $arrival, $seats)) {
            echo json_encode(['success' => true, 'message' => 'Train updated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update train']);
        }
        break;

    case 'DELETE':
        // Delete train (admin only)
        session_start();
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        $id = (int)sanitizeInput($_GET['id']);
        if (deleteTrain($id)) {
            echo json_encode(['success' => true, 'message' => 'Train deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete train']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?>