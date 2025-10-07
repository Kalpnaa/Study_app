<?php 
session_start();
require 'db.php'; // your MySQL connection

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo "Unauthorized";
    exit();
}

$user_id = $_SESSION['user_id'];
$task_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$status = $_GET['status'] ?? '';
$remaining = isset($_GET['remaining']) ? (int)$_GET['remaining'] : null;

// Only allow valid statuses
$valid_statuses = ['pending', 'in-progress', 'paused', 'completed', 'stopped'];
if ($task_id > 0 && in_array($status, $valid_statuses)) {

    if ($status === 'paused' && $remaining !== null) {
        // 🟡 Pause: update status AND remaining time
        $stmt = $conn->prepare("UPDATE tasks SET status = ?, remaining = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("siii", $status, $remaining, $task_id, $user_id);

    } elseif ($status === 'in-progress' && $remaining !== null) {
        // ▶ Resume: update status AND start_time based on remaining time
        $new_start_time = time() - ($remaining); // subtract remaining seconds to sync timer
        $stmt = $conn->prepare("UPDATE tasks SET status = ?, start_time = ?, remaining = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("siiii", $status, $new_start_time, $remaining, $task_id, $user_id);

    } elseif ($status === 'completed') {
        // ✅ Mark completed and reset remaining
        $stmt = $conn->prepare("UPDATE tasks SET status = ?, remaining = 0 WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sii", $status, $task_id, $user_id);

    } else {
        // Default for 'pending' or 'stopped': update status only
        $stmt = $conn->prepare("UPDATE tasks SET status = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sii", $status, $task_id, $user_id);
    }

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
