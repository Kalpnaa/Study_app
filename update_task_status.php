<?php
session_start();
require 'db.php'; // make sure this connects to your $conn MySQL instance

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo "Unauthorized";
    exit();
}

$user_id = $_SESSION['user_id'];
$task_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$status = $_GET['status'] ?? '';

if ($task_id > 0 && in_array($status, ['pending', 'in-progress', 'completed'])) {
    $stmt = $conn->prepare("UPDATE tasks SET status = ?, remaining = 0 WHERE id = ? AND user_id = ?");
    $stmt->bind_param("sii", $status, $task_id, $user_id);
    if ($stmt->execute()) {
        echo "✅ Task updated successfully!";
    } else {
        echo "❌ Failed to update task.";
    }
    $stmt->close();
} else {
    echo "❌ Invalid task or status.";
}
?>
    