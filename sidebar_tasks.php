<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch current user's info
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

// Handle starting a task (mark in-progress) via JS fetch
if (isset($_GET['complete'])) {
    $task_id = (int)$_GET['complete'];
    $start_time = time();
    $remaining = isset($_GET['custom']) ? (int)$_GET['custom']*60 : 60;

    $stmt = $conn->prepare("UPDATE tasks SET status='in-progress', start_time=?, remaining=? WHERE id=? AND user_id=?");
    $stmt->bind_param("iiii", $start_time, $remaining, $task_id, $user_id);
    $stmt->execute();
    $stmt->close();

    echo "success";
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
.custom-time { width:50px; border-radius:5px; padding:2px; margin-left:10px; }
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
                <input type="number" class="custom-time" id="custom-<?php echo $task['id']; ?>" placeholder="25" min="1" max="180"> min
                <a href="#" onclick="startTimer(<?php echo $task['id']; ?>, this)" class="task-btn">▶ Start</a>
                <span class="timer" id="timer-<?php echo $task['id']; ?>">00:00</span>
                <button class="task-btn toggle-btn" id="toggle-<?php echo $task['id']; ?>" style="display:none;" onclick="toggleTimer(<?php echo $task['id']; ?>)">⏸ Pause</button>
                <button class="task-btn" style="background:#17a2b8; display:none;" id="complete-btn-<?php echo $task['id']; ?>" onclick="markComplete(<?php echo $task['id']; ?>)">✅ Mark Complete</button>
              <?php elseif ($task['status'] === 'in-progress' || $task['status'] === 'paused'): ?>
                <span class="timer" id="timer-<?php echo $task['id']; ?>">
                  <?php echo str_pad($task['remaining'] ?? 60, 2, '0', STR_PAD_LEFT); ?>:00
                </span>
                <button class="task-btn toggle-btn" id="toggle-<?php echo $task['id']; ?>" onclick="toggleTimer(<?php echo $task['id']; ?>)">
                  <?php echo $task['status'] === 'paused' ? '▶ Resume' : '⏸ Pause'; ?>
                </button>
                <button class="task-btn" style="background:#17a2b8;" id="complete-btn-<?php echo $task['id']; ?>" onclick="markComplete(<?php echo $task['id']; ?>)">✅ Mark Complete</button>
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
  const originalTimes = {};

  <?php foreach ($tasks as $task):
    $task_id = $task['id'];
    $status = $task['status'] ?? 'pending';
    $start_time = $task['start_time'] ?? time();
    $remaining = $task['remaining'] ?? 60;
  ?>
  const id<?php echo $task_id; ?> = <?php echo $task_id; ?>;
  let remainingTime<?php echo $task_id; ?> = <?php echo $remaining; ?>;
  if ("<?php echo $status; ?>" === 'in-progress') {
    let elapsed = Math.floor(Date.now()/1000) - <?php echo $start_time; ?>;
    remainingTime<?php echo $task_id; ?> = Math.max(0, remainingTime<?php echo $task_id; ?> - elapsed);
  } else if ("<?php echo $status; ?>" === 'paused') {
    remainingTime<?php echo $task_id; ?> = <?php echo $remaining; ?>;
  }

  remainingTimes[id<?php echo $task_id; ?>] = remainingTime<?php echo $task_id; ?>;
  originalTimes[id<?php echo $task_id; ?>] = remainingTime<?php echo $task_id; ?>;

  function updateSingleTimer(id) {
    const timerElem = document.getElementById('timer-' + id);
    const btn = document.getElementById('toggle-' + id);
    const completeBtn = document.getElementById('complete-btn-' + id);

    if (remainingTimes[id] <= 0) {
      clearInterval(intervals[id]);
      intervals[id] = null;
      timerElem.innerText = "⏰ Time's up!";
      if(btn) btn.style.display = 'none';
      if(completeBtn) completeBtn.style.display = 'inline-block';

      // ✅ FIX: Only show reset button if task is NOT completed
      if(timerElem.innerText !== "✔ Completed") {
        let resetBtn = document.getElementById('reset-btn-' + id);
        if(!resetBtn) {
          resetBtn = document.createElement('button');
          resetBtn.id = 'reset-btn-' + id;
          resetBtn.className = 'task-btn';
          resetBtn.style.background = '#17a2b8';
          resetBtn.innerText = '🔄 Reset Timer';
          resetBtn.onclick = () => resetTimer(id);
          completeBtn.parentNode.appendChild(resetBtn);
        }
      }
      return;
    }

    const mins = Math.floor(remainingTimes[id] / 60);
    const secs = remainingTimes[id] % 60;
    timerElem.innerText = `${String(mins).padStart(2,'0')}:${String(secs).padStart(2,'0')}`;
    remainingTimes[id]--;
  }

  if ("<?php echo $status; ?>" === 'in-progress') {
    intervals[id<?php echo $task_id; ?>] = setInterval(() => updateSingleTimer(id<?php echo $task_id; ?>), 1000);
  }
  <?php endforeach; ?>

  window.startTimer = function(id, startBtn) {
    const customInput = document.getElementById('custom-' + id);
    const customMinutes = parseInt(customInput?.value || 25);

    fetch(`sidebar_tasks.php?complete=${id}&custom=${customMinutes}`)
      .then(res => res.text())
      .then(() => {
        remainingTimes[id] = customMinutes * 60;
        originalTimes[id] = customMinutes * 60;
        const timerElem = document.getElementById('timer-' + id);
        const btn = document.getElementById('toggle-' + id);
        const completeBtn = document.getElementById('complete-btn-' + id);

        if(btn) btn.style.display = 'inline-block';
        if(completeBtn) completeBtn.style.display = 'none';
        if(startBtn) startBtn.style.display = 'none';

        clearInterval(intervals[id]);
        intervals[id] = setInterval(() => updateSingleTimer(id), 1000);
      });
  };

  window.toggleTimer = function(id) {
    const btn = document.getElementById('toggle-' + id);
    if(!btn) return;
    if(intervals[id]) {
      clearInterval(intervals[id]);
      intervals[id] = null;
      btn.innerText = "▶ Resume";
    } else {
      intervals[id] = setInterval(() => updateSingleTimer(id), 1000);
      btn.innerText = "⏸ Pause";
    }
  };

  window.resetTimer = function(id) {
    remainingTimes[id] = originalTimes[id];
    const timerElem = document.getElementById('timer-' + id);
    timerElem.innerText = `${String(Math.floor(remainingTimes[id]/60)).padStart(2,'0')}:${String(remainingTimes[id]%60).padStart(2,'0')}`;

    const startBtn = document.querySelector(`a[onclick="startTimer(${id}, this)"]`);
    const btn = document.getElementById('toggle-' + id);
    const completeBtn = document.getElementById('complete-btn-' + id);

    if(startBtn) startBtn.style.display = 'inline-block';
    if(btn) btn.style.display = 'none';
    if(completeBtn) completeBtn.style.display = 'none';

    const resetBtn = document.getElementById('reset-btn-' + id);
    if(resetBtn) resetBtn.remove();

    clearInterval(intervals[id]);
  };

  window.markComplete = function(id) {
    clearInterval(intervals[id]);
    const timerElem = document.getElementById('timer-' + id);
    const btn = document.getElementById('toggle-' + id);
    const completeBtn = document.getElementById('complete-btn-' + id);
    if(timerElem) timerElem.innerText = "✔ Completed";
    if(btn) btn.style.display = 'none';
    if(completeBtn) completeBtn.style.display = 'none';
    // ✅ FIX: Remove any existing reset button when marked complete
    const resetBtn = document.getElementById('reset-btn-' + id);
    if(resetBtn) resetBtn.remove();

    fetch(`update_task_status.php?id=${id}&status=completed`);
  };
});
</script>
</body>
</html>
