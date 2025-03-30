<?php
require_once 'db.php';

function connectDB() {
    global $conn;
    return $conn;
}

function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

function getTrains() {
    $conn = connectDB();
    $stmt = $conn->query("SELECT * FROM trains ORDER BY departure_time");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTrainById($id) {
    $conn = connectDB();
    $stmt = $conn->prepare("SELECT * FROM trains WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function addTrain($name, $origin, $destination, $departure, $arrival, $seats) {
    $conn = connectDB();
    $stmt = $conn->prepare("INSERT INTO trains (name, origin, destination, departure_time, arrival_time, seats_available) VALUES (?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$name, $origin, $destination, $departure, $arrival, $seats]);
}

function updateTrain($id, $name, $origin, $destination, $departure, $arrival, $seats) {
    $conn = connectDB();
    $stmt = $conn->prepare("UPDATE trains SET name=?, origin=?, destination=?, departure_time=?, arrival_time=?, seats_available=? WHERE id=?");
    return $stmt->execute([$name, $origin, $destination, $departure, $arrival, $seats, $id]);
}

function deleteTrain($id) {
    $conn = connectDB();
    $stmt = $conn->prepare("DELETE FROM trains WHERE id = ?");
    return $stmt->execute([$id]);
}

function authenticateUser($username, $password) {
    $conn = connectDB();
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password_hash'])) {
        return $user;
    }
    return false;
}

function createUser($username, $password) {
    $conn = connectDB();
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
    return $stmt->execute([$username, $password_hash]);
}
?>