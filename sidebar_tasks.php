<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch current user's info from DB
$stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username, $email);
$stmt->fetch();
$stmt->close();

// Fetch tasks for this user
$stmt = $conn->prepare("SELECT id, title, description, status, start_time, remaining FROM tasks WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$tasks = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Handle starting a task (mark in-progress)
if (isset($_GET['complete'])) {
    $task_id = (int)$_GET['complete'];
    $start_time = time();
    $remaining = 60; // 1 minute timer

    $stmt = $conn->prepare("UPDATE tasks SET status='in-progress', start_time=?, remaining=? WHERE id=? AND user_id=?");
    $stmt->bind_param("iiii", $start_time, $remaining, $task_id, $user_id);
    $stmt->execute();
    $stmt->close();

    header("Location: sidebar_tasks.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Study Buddy - Tasks</title>
<link rel="stylesheet" href="css/dashboard.css">
<style>
.timer { font-weight:bold; color:#007bff; margin-left:10px; }
.completed { color: #6c757d; text-decoration: line-through; }
.task-btn { margin-left:10px; color:#fff; background:#28a745; padding:2px 6px; text-decoration:none; border-radius:4px; cursor:pointer; }
.toggle-btn { background:#ffc107; color:#000; }
</style>
</head>
<body>
<div class="container">
<aside class="sidebar">
  <h2><u>Study-Buddy 📚💻</u></h2>
  <ul>
    <li><a href="dashboard.php" style="color:white; text-decoration:none;">📊 Dashboard</a></li>
    <li class="active">📝 Tasks</li>
    <li><a href="flashcards.php" style="color:white; text-decoration:none;">📚 Flashcards</a></li>
    <li><a href="notes.php" style="color:white; text-decoration:none;">📂 Notes</a></li>
    <li><a href="studycircle.php" style="color:white; text-decoration:none;">👥 Study Circle</a></li>
    <li><a href="logout.php" style="color:white; text-decoration:none;">🚪 Logout</a></li>
  </ul>
</aside>

<main class="main">
  <marquee behavior="scroll" direction="left" scrollamount="10">
    <h2>Your Tasks, <?php echo htmlspecialchars($username); ?> 👋</h2>
  </marquee>

  <div class="cards">
    <div class="card">
      <h3>📝 All Tasks</h3>
      <?php if (!empty($tasks)): ?>
        <ul>
          <?php foreach ($tasks as $task): ?>
            <li>
              <strong class="<?php echo ($task['status'] ?? 'pending') === 'completed' ? 'completed' : ''; ?>">
                <?php echo htmlspecialchars($task['title']); ?>
              </strong>: <?php echo htmlspecialchars($task['description'] ?? 'No description'); ?>

              <?php if (!isset($task['status']) || $task['status'] === 'pending'): ?>
                <a href="sidebar_tasks.php?complete=<?php echo $task['id']; ?>" class="task-btn">✅ Start Timer</a>
              <?php elseif ($task['status'] === 'in-progress'): ?>
                <span class="timer" id="timer-<?php echo $task['id']; ?>">
                  <?php echo str_pad($task['remaining'] ?? 60, 2, '0', STR_PAD_LEFT); ?>:00
                </span>
                <!-- ✅ Single toggle button -->
                <button class="task-btn toggle-btn" id="toggle-<?php echo $task['id']; ?>" onclick="toggleTimer(<?php echo $task['id']; ?>)">⏸ Stop</button>
              <?php else: ?>
                <span class="completed">✔ Completed</span>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p>No tasks yet. Add some from your <a href="dashboard.php">dashboard</a>!</p>
      <?php endif; ?>
    </div>
  </div>
</main>
</div>

<footer class="footer">
<p>© 2025 Study Buddy. All Rights Reserved.</p>
</footer>

<script>
window.addEventListener("DOMContentLoaded", function() {
  const intervals = {};
  const remainingTimes = {};

  <?php foreach ($tasks as $task):
  if (($task['status'] ?? '') === 'in-progress'):
  $start_time = $task['start_time'] ?? time();
  $remaining = $task['remaining'] ?? 60;
  $task_id = $task['id'];
  ?>
  const taskId<?php echo $task_id; ?> = <?php echo $task_id; ?>;
  const timerElem<?php echo $task_id; ?> = document.getElementById('timer-<?php echo $task_id; ?>');
  const toggleBtn<?php echo $task_id; ?> = document.getElementById('toggle-<?php echo $task_id; ?>');
  remainingTimes[taskId<?php echo $task_id; ?>] = Math.max(0, <?php echo $remaining; ?> - (Math.floor(Date.now()/1000) - <?php echo $start_time; ?>));

  function updateTimer<?php echo $task_id; ?>() {
      if (!timerElem<?php echo $task_id; ?>) return;
      if (remainingTimes[taskId<?php echo $task_id; ?>] <= 0) {
          clearInterval(intervals[taskId<?php echo $task_id; ?>]);
          timerElem<?php echo $task_id; ?>.innerText = "✔ Completed";
          toggleBtn<?php echo $task_id; ?>.style.display = 'none';
          alert("🎉 Task completed! 💪");
          fetch('update_task_status.php?id=<?php echo $task_id; ?>&status=completed');
          return;
      }
      const mins = Math.floor(remainingTimes[taskId<?php echo $task_id; ?>] / 60);
      const secs = remainingTimes[taskId<?php echo $task_id; ?>] % 60;
      timerElem<?php echo $task_id; ?>.innerText = `${String(mins).padStart(2,'0')}:${String(secs).padStart(2,'0')}`;
      remainingTimes[taskId<?php echo $task_id; ?>]--;
  }

  intervals[taskId<?php echo $task_id; ?>] = setInterval(updateTimer<?php echo $task_id; ?>, 1000);
  <?php endif; endforeach; ?>

  window.toggleTimer = function(id) {
      const btn = document.getElementById('toggle-' + id);
      const timerElem = document.getElementById('timer-' + id);
      if (!btn || !timerElem) return;

      if (intervals[id]) {
          // ✅ Stop the timer
          clearInterval(intervals[id]);
          intervals[id] = null;
          btn.innerText = "▶ Resume";
      } else {
          // ✅ Resume the timer
          intervals[id] = setInterval(function() {
              if (remainingTimes[id] <= 0) {
                  clearInterval(intervals[id]);
                  timerElem.innerText = "✔ Completed";
                  btn.style.display = 'none';
                  alert("🎉 Task completed! 💪");
                  fetch('update_task_status.php?id=' + id + '&status=completed');
                  return;
              }
              const mins = Math.floor(remainingTimes[id] / 60);
              const secs = remainingTimes[id] % 60;
              timerElem.innerText = `${String(mins).padStart(2,'0')}:${String(secs).padStart(2,'0')}`;
              remainingTimes[id]--;
          }, 1000);
          btn.innerText = "⏸ Stop";
      }
  }
});
</script>

</body>
</html>
