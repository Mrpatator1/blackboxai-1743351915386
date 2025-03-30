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

function getTrainsWithStations() {
    $conn = connectDB();
    $stmt = $conn->query("
        SELECT t.*, 
               ds.name as departure_station_name,
               ds.city as departure_city,
               as.name as arrival_station_name, 
               as.city as arrival_city
        FROM trains t
        JOIN stations ds ON t.departure_station_id = ds.id
        JOIN stations as ON t.arrival_station_id = as.id
        ORDER BY t.departure_time
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTrainById($id) {
    $conn = connectDB();
    $stmt = $conn->prepare("
        SELECT t.*, 
               ds.name as departure_station_name,
               ds.city as departure_city,
               as.name as arrival_station_name,
               as.city as arrival_city
        FROM trains t
        JOIN stations ds ON t.departure_station_id = ds.id
        JOIN stations as ON t.arrival_station_id = as.id
        WHERE t.id = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function addTrain($name, $departure_station_id, $arrival_station_id, $departure, $arrival, $seats) {
    $conn = connectDB();
    $stmt = $conn->prepare("INSERT INTO trains (name, departure_station_id, arrival_station_id, departure_time, arrival_time, seats_available) VALUES (?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$name, $departure_station_id, $arrival_station_id, $departure, $arrival, $seats]);
}

function updateTrain($id, $name, $departure_station_id, $arrival_station_id, $departure, $arrival, $seats) {
    $conn = connectDB();
    $stmt = $conn->prepare("UPDATE trains SET name=?, departure_station_id=?, arrival_station_id=?, departure_time=?, arrival_time=?, seats_available=? WHERE id=?");
    return $stmt->execute([$name, $departure_station_id, $arrival_station_id, $departure, $arrival, $seats, $id]);
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

// Station functions
function getStations() {
    $conn = connectDB();
    $stmt = $conn->query("SELECT * FROM stations ORDER BY name");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getStationById($id) {
    $conn = connectDB();
    $stmt = $conn->prepare("SELECT * FROM stations WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function addStation($name, $city) {
    $conn = connectDB();
    $stmt = $conn->prepare("INSERT INTO stations (name, city) VALUES (?, ?)");
    return $stmt->execute([$name, $city]);
}

function getTrainStations($train_id) {
    $conn = connectDB();
    $stmt = $conn->prepare("
        SELECT ts.*, s.name as station_name, s.city
        FROM train_stations ts
        JOIN stations s ON ts.station_id = s.id
        WHERE ts.train_id = ?
        ORDER BY ts.stop_order
    ");
    $stmt->execute([$train_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function addTrainStation($train_id, $station_id, $arrival_time, $departure_time, $stop_order) {
    $conn = connectDB();
    $stmt = $conn->prepare("
        INSERT INTO train_stations 
        (train_id, station_id, arrival_time, departure_time, stop_order) 
        VALUES (?, ?, ?, ?, ?)
    ");
    return $stmt->execute([$train_id, $station_id, $arrival_time, $departure_time, $stop_order]);
}

function getFullTrainDetails($train_id) {
    $conn = connectDB();
    $stmt = $conn->prepare("
        SELECT t.*, 
               ds.name as departure_station_name, 
               ds.city as departure_city,
               as.name as arrival_station_name,
               as.city as arrival_city
        FROM trains t
        JOIN stations ds ON t.departure_station_id = ds.id
        JOIN stations as ON t.arrival_station_id = as.id
        WHERE t.id = ?
    ");
    $stmt->execute([$train_id]);
    $train = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($train) {
        $train['stations'] = getTrainStations($train_id);
    }
    
    return $train;
}
?>