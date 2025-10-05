<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['users'][$user_id]['name'];

if (!isset($_SESSION['tasks'])) {
    $_SESSION['tasks'] = [];
}

// Handle task start (mark in-progress)
if (isset($_GET['complete'])) {
    $index = (int)$_GET['complete'];
    if (isset($_SESSION['tasks'][$index])) {
        $_SESSION['tasks'][$index]['status'] = 'in-progress';
        $_SESSION['tasks'][$index]['start_time'] = time();
        $_SESSION['tasks'][$index]['remaining'] = 60; // 1 minute in seconds
        header("Location: sidebar_tasks.php");
        exit();
    }
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
.stop-btn { background:#dc3545; }
.resume-btn { background:#ffc107; color:#000; }
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
      <?php if (!empty($_SESSION['tasks'])): ?>
        <ul>
          <?php foreach ($_SESSION['tasks'] as $index => $task): ?>
            <li>
              <strong class="<?php echo ($task['status'] ?? 'pending') === 'completed' ? 'completed' : ''; ?>">
                <?php echo $task['title']; ?>
              </strong>: <?php echo $task['desc'] ?: 'No description'; ?>

              <?php if (!isset($task['status']) || $task['status'] === 'pending'): ?>
                <a href="sidebar_tasks.php?complete=<?php echo $index; ?>" class="task-btn">✅ Start Timer</a>
              <?php elseif ($task['status'] === 'in-progress'): ?>
                <span class="timer" id="timer-<?php echo $index; ?>"><?php echo str_pad($task['remaining'] ?? 60,2,'0',STR_PAD_LEFT); ?>:00</span>
                <button class="task-btn stop-btn" onclick="stopTimer(<?php echo $index; ?>)">⏸ Stop Timer</button>
                <button class="task-btn resume-btn" onclick="resumeTimer(<?php echo $index; ?>)">▶ Resume Timer</button>
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
// Store intervals and remaining time for each task
const intervals = {};
const remainingTimes = {};
<?php foreach ($_SESSION['tasks'] as $index => $task):
if (($task['status'] ?? '') === 'in-progress'):
$start_time = $task['start_time'] ?? time();
$remaining = $task['remaining'] ?? 60;
?>
remainingTimes[<?php echo $index; ?>] = <?php echo $remaining; ?> - (Math.floor(Date.now()/1000) - <?php echo $start_time; ?>);
const timerElem<?php echo $index; ?> = document.getElementById('timer-<?php echo $index; ?>');
intervals[<?php echo $index; ?>] = null;

function startInterval<?php echo $index; ?>() {
    if (intervals[<?php echo $index; ?>]) return;
    intervals[<?php echo $index; ?>] = setInterval(() => {
        if (remainingTimes[<?php echo $index; ?>] <= 0) {
            clearInterval(intervals[<?php echo $index; ?>]);
            timerElem<?php echo $index; ?>.innerText = "✔ Completed";
            alert("🎉 Congratulations! You completed the task! Keep up the great work 💪");
            fetch('update_task_status.php?index=<?php echo $index; ?>&status=completed');
        } else {
            const mins = Math.floor(remainingTimes[<?php echo $index; ?>] / 60);
            const secs = remainingTimes[<?php echo $index; ?>] % 60;
            timerElem<?php echo $index; ?>.innerText = `${String(mins).padStart(2,'0')}:${String(secs).padStart(2,'0')}`;
            remainingTimes[<?php echo $index; ?>]--;
        }
    }, 1000);
}
startInterval<?php echo $index; ?>();
<?php endif; endforeach; ?>

function stopTimer(index) {
    if (intervals[index]) {
        clearInterval(intervals[index]);
        intervals[index] = null;
        alert("⏸ Timer stopped! You can resume it anytime 💪");
    }
}

function resumeTimer(index) {
    if (!intervals[index]) {
        startInterval<?php echo $index; ?>();
        alert("▶ Timer resumed! Keep going 💪");
    }
}
</script>
</body>
</html>
