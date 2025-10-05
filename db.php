<?php
$config = require 'config.php';

$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass']);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists (only in signup)
if (!isset($skip_create_db)) {
    $conn->query("CREATE DATABASE IF NOT EXISTS `{$config['db_name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
}

// Select database
$conn->select_db($config['db_name']);

// Create tables if not exists (only in signup)
if (!isset($skip_create_table)) {
    // Users table
    $conn->query("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    // Tasks table with timer support
    $conn->query("CREATE TABLE IF NOT EXISTS tasks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        status ENUM('pending','in-progress','completed') DEFAULT 'pending',
        start_time INT DEFAULT NULL,     -- timestamp when task timer started
        remaining INT DEFAULT NULL,      -- remaining seconds if paused
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");
}
?>