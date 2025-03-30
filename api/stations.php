<?php
header('Content-Type: application/json');
require_once '../functions.php';

$method = $_SERVER['REQUEST_METHOD'];
$conn = connectDB();

switch ($method) {
    case 'GET':
        try {
            if (isset($_GET['id'])) {
                $id = sanitizeInput($_GET['id']);
                $stmt = $conn->prepare("SELECT * FROM stations WHERE id = ?");
                $stmt->execute([$id]);
                $station = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($station) {
                    echo json_encode($station);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Station not found']);
                }
            } else {
                $stmt = $conn->query("SELECT * FROM stations ORDER BY name");
                echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
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
            $name = sanitizeInput($data['name']);
            $city = sanitizeInput($data['city']);

            $stmt = $conn->prepare("INSERT INTO stations (name, city) VALUES (?, ?)");
            $stmt->execute([$name, $city]);
            
            echo json_encode([
                'success' => true,
                'id' => $conn->lastInsertId(),
                'message' => 'Station added successfully'
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