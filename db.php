<?php
// Load config
$config = require 'config.php';

// Ensure $config is an array
if (!is_array($config)) {
    die("Error: config.php did not return an array.");
}

// Connect to MySQL
$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass']);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists (skip if $skip_create_db is set)
if (!isset($skip_create_db)) {
    $sql = "CREATE DATABASE IF NOT EXISTS `{$config['db_name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
    if (!$conn->query($sql)) {
        die("Error creating database: " . $conn->error);
    }
}

// Select the database
if (!$conn->select_db($config['db_name'])) {
    die("Error selecting database: " . $conn->error);
}

// Create tables if not exists (skip if $skip_create_table is set)
if (!isset($skip_create_table)) {
    // Users table
    $sql_users = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB";

    if (!$conn->query($sql_users)) {
        die("Error creating users table: " . $conn->error);
    }

    // Tasks table
    $sql_tasks = "CREATE TABLE IF NOT EXISTS tasks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        status ENUM('pending','in-progress','completed') DEFAULT 'pending',
        start_time INT DEFAULT NULL,
        remaining INT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB";

    if (!$conn->query($sql_tasks)) {
        die("Error creating tasks table: " . $conn->error);
    }
}
?>
