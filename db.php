<?php
// Database configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'train_schedule');

try {
    // Create connection with PDO
    $conn = new PDO("mysql:host=".DB_SERVER, DB_USERNAME, DB_PASSWORD);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create database if not exists
    $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
    $conn->exec($sql);
    
    // Connect to the database
    $conn = new PDO("mysql:host=".DB_SERVER.";dbname=".DB_NAME, DB_USERNAME, DB_PASSWORD);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create tables
    $sql = "CREATE TABLE IF NOT EXISTS stations (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL UNIQUE,
        city VARCHAR(50) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $conn->exec($sql);

    $sql = "CREATE TABLE IF NOT EXISTS trains (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL,
        departure_station_id INT(6) UNSIGNED NOT NULL,
        arrival_station_id INT(6) UNSIGNED NOT NULL,
        departure_time TIME NOT NULL,
        arrival_time TIME NOT NULL,
        seats_available INT(6) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (departure_station_id) REFERENCES stations(id),
        FOREIGN KEY (arrival_station_id) REFERENCES stations(id)
    )";
    
    $conn->exec($sql);
    
    $sql = "CREATE TABLE IF NOT EXISTS train_stations (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        train_id INT(6) UNSIGNED NOT NULL,
        station_id INT(6) UNSIGNED NOT NULL,
        arrival_time TIME,
        departure_time TIME,
        stop_order INT(6) NOT NULL,
        FOREIGN KEY (train_id) REFERENCES trains(id),
        FOREIGN KEY (station_id) REFERENCES stations(id)
    )";
    $conn->exec($sql);
    
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(30) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        role ENUM('admin','user') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    
    $sql = "CREATE TABLE IF NOT EXISTS bookings (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT(6) UNSIGNED NOT NULL,
        train_id INT(6) UNSIGNED NOT NULL,
        booking_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        seats_booked INT(6) NOT NULL,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (train_id) REFERENCES trains(id)
    )";
    $conn->exec($sql);
    
    // Seed admin user if not exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE username='admin'");
    $stmt->execute();
    if ($stmt->rowCount() == 0) {
        $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, password_hash, role) VALUES ('admin', :password_hash, 'admin')");
        $stmt->bindParam(':password_hash', $password_hash);
        $stmt->execute();
    }
} catch(PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>