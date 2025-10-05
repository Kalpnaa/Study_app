<?php
session_start();

require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Initialize variables
$task_added = false;
$success = '';
$error = '';

// Fetch username
$stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username);
$stmt->fetch();
$stmt->close();

// Handle task addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['taskTitle'])) {
    $title = trim($_POST['taskTitle']);
    $desc  = trim($_POST['taskDesc']);

    if ($title) {
        $stmt = $conn->prepare("INSERT INTO tasks (user_id, title, description) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $title, $desc);
        if ($stmt->execute()) {
            $task_added = true;
            $success = "✅ Task added successfully! <a href='sidebar_tasks.php'>Click here to view all tasks and start working</a>";
        } else {
            $error = "❌ Failed to add task. Please try again.";
        }
        $stmt->close();
    } else {
        $error = "⚠️ Task title cannot be empty!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Study Buddy - Dashboard</title>
  <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
  <div class="container">
    <!-- Sidebar -->
    <aside class="sidebar">
      <h2><u>Study-Buddy 📚💻</u></h2>
      <ul>
        <li class="active"><a href="dashboard.php" style="color:white; text-decoration:none;">📊 Dashboard</a></li>
        <li><a href="sidebar_tasks.php" style="color:white; text-decoration:none;">📝 Tasks</a></li>
        <li>📚 Flashcards</li>
        <li>📂 Notes</li>
        <li>👥 Study Circle</li>
        <li><a href="logout.php" style="color:white; text-decoration:none;">🚪 Logout</a></li>
      </ul>
    </aside>

    <!-- Main Content -->
    <main class="main">
      <marquee behavior="scroll" direction="left" scrollamount="10">
        <h2>Welcome, <?php echo htmlspecialchars($username); ?> 👋</h2>
      </marquee>

      <div class="cards">
        <!-- How This Web Works Card -->
        <div class="card">
          <h3>💡 How this web works</h3>
          <p>Click the button to see the steps</p>
          <button onclick="openModal()">Click Me</button>
        </div>

        <!-- Add Task -->
        <div class="card">
          <h3>➕ Add Task</h3>
          <form method="POST" onsubmit="return validateTask()">
            <input type="text" id="taskTitle" name="taskTitle" placeholder="Task title">
            <textarea id="taskDesc" name="taskDesc" placeholder="Task description"></textarea>
            <button type="submit">Add Task</button>
          </form>

          <!-- Success / Error Messages -->
          <?php if ($task_added): ?>
             <p style="color:green;"><?php echo $success; ?></p>
          <?php elseif (!empty($error)): ?>
             <p style="color:red;"><?php echo $error; ?></p>
          <?php endif; ?>
        </div>

        <!-- Flashcards -->
        <div class="card">
          <h3>📚 Flashcards</h3>
          <p>Generate flashcards with AI to aid your studying.</p>
          <button onclick="generateFlashcards()">Generate Flashcards</button>
        </div>

        <!-- Upload Notes -->
        <div class="card">
          <h3>📂 Upload Notes</h3>
          <input type="file" id="noteFile">
          <button onclick="uploadNotes()">Upload</button>
        </div>
      </div>
    </main>
  </div>

  <!-- Modal for Steps -->
  <div id="stepsModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeModal()">&times;</span>
      <h3>Steps to use Study Buddy</h3>
      <ol>
        <li>Add your tasks in the "Add Task" section.</li>
        <li>Start working on a task and track time using timers.</li>
        <li>Generate flashcards to aid studying.</li>
        <li>Upload your notes for reference.</li>
        <li>Use the <strong>Study Circle</strong> feature to connect and study with peers.</li>
        <li>Review completed tasks and stay productive!</li>
      </ol>
    </div>
  </div>

  <!-- Footer -->
  <footer class="footer">
    <p>© 2025 Study Buddy. All Rights Reserved.</p>
  </footer>

  <script>
  /* ---------- Modal Functions ---------- */
  function openModal() {
    document.getElementById('stepsModal').style.display = 'block';
  }
  function closeModal() {
    document.getElementById('stepsModal').style.display = 'none';
  }
  window.onclick = function(event) {
    const modal = document.getElementById('stepsModal');
    if (event.target == modal) modal.style.display = 'none';
  }

  /* ---------- ADD TASK VALIDATION ---------- */
  function validateTask() {
    const title = document.getElementById("taskTitle").value.trim();
    if (!title) {
      alert("⚠️ Please enter a task title!");
      return false;
    }
    return true;
  }

  /* ---------- GENERATE FLASHCARDS ---------- */
  function generateFlashcards() {
    alert("📚 Flashcard generator coming soon! This feature will let you create AI-powered study flashcards.");
  }

  /* ---------- UPLOAD NOTES ---------- */
  function uploadNotes() {
    const fileInput = document.getElementById("noteFile");
    if (fileInput.files.length === 0) {
      alert("⚠️ Please choose a file to upload!");
      return;
    }
    const fileName = fileInput.files[0].name;
    alert(`📂 Successfully uploaded: ${fileName}`);
    fileInput.value = ""; // reset
  }
  </script>
</body>
</html>
