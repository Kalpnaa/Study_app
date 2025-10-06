<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// Handle note text submission
$upload_success = false;
$error_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
    $content = trim($_POST['content']);
    if ($content !== '') {
        $stmt = $conn->prepare("INSERT INTO notes (user_id, content) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $content);
        if ($stmt->execute()) {
            $upload_success = true;
        } else {
            $error_msg = "Failed to save note. Try again.";
        }
        $stmt->close();
    } else {
        $error_msg = "Note content cannot be empty.";
    }
}
// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['noteFile'])) {
    $file = $_FILES['noteFile'];
    if ($file['error'] === UPLOAD_ERR_OK) {
        $allowed_ext = ['pdf', 'doc', 'docx', 'txt'];
        $filename = basename($file['name']);
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed_ext)) {
            $upload_dir = 'uploaded_notes/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755);
            }
            $new_path = $upload_dir . uniqid() . '_' . $filename;
            if (move_uploaded_file($file['tmp_name'], $new_path)) {
                // Insert file info into 'notes' table
                $stmt = $conn->prepare("INSERT INTO notes (user_id, content, file_path) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $user_id, $filename, $new_path);
                $stmt->execute();
                $stmt->close();
                $upload_success = true;
            } else {
                $error_msg = "Failed to upload file.";
            }
        } else {
            $error_msg = "Invalid file type. Allowed: pdf, doc, docx, txt.";
        }
    } else {
        $error_msg = "Error uploading file.";
    }
}

// Fetch user notes/files
$stmt = $conn->prepare("SELECT content, file_path, created_at FROM notes WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$notes = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch username
$stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username);
$stmt->fetch();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>My Notes - Study Buddy</title>
<link rel="stylesheet" href="css/dashboard.css" />
</head>
<body>
<div class="container">
  <!-- Sidebar -->
  <aside class="sidebar">
    <h2><u>Study-Buddy 📚💻</u></h2>
    <ul>
      <li><a href="dashboard.php" style="color:white;">📊 Dashboard</a></li>
      <li><a href="sidebar_tasks.php" style="color:white;">📝 Tasks</a></li>
      <li><a href="flashcards.php" style="color:white;">📚 Flashcards</a></li>
      <li class="active"><a href="notes.php" style="color:white;">📂 Notes</a></li>
      <li>👥 Study Circle</li>
      <li><a href="logout.php" style="color:white;">🚪 Logout</a></li>
    </ul>
  </aside>

  <!-- Main Content -->
  <main class="main">
    <h2>Welcome, <?php echo htmlspecialchars($username); ?> 👋</h2>

    <!-- Add Text Note -->
    <div class="card auth-container">
      <h3>➕ Write a Note</h3>
      <form method="POST">
        <textarea name="content" placeholder="Write your note here..." required></textarea>
        <button type="submit">Save Note</button>
      </form>
      <?php
        if ($upload_success) echo '<p style="color:green;">✅ Note saved!</p>'; 
        elseif ($error_msg) echo '<p style="color:red;">❌ '.htmlspecialchars($error_msg).'</p>';
      ?>
    </div>

    <!-- Upload Notes File -->
    <div class="card auth-container">
      <h3>Upload Notes File</h3>
      <form method="POST" enctype="multipart/form-data">
        <input type="file" name="noteFile" accept=".pdf,.doc,.docx,.txt" required />
        <button type="submit">Upload</button>
      </form>
      <?php if ($error_msg && isset($_FILES['noteFile'])) echo '<p style="color:red;">' . htmlspecialchars($error_msg) . '</p>'; ?>
    </div>

    <!-- List of Notes and Files -->
    <div class="card">
      <h3>Your Notes & Files</h3>
      <?php if (empty($notes)): ?>
        <p>No notes or files uploaded yet.</p>
      <?php else: ?>
        <?php foreach ($notes as $note): ?>
          <div class="note">
            <p><?= nl2br(htmlspecialchars($note['content'])) ?></p>
            <?php if ($note['file_path']): ?>
              <p>File: <a href="<?= htmlspecialchars($note['file_path']) ?>" target="_blank"><?= htmlspecialchars($note['content']) ?></a></p>
            <?php endif; ?>
            <small><?= htmlspecialchars($note['created_at']) ?></small>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </main>
</div>

<!-- Footer -->
<footer class="footer">
  <p>© 2025 Study Buddy. All Rights Reserved.</p>
</footer>
</body>
</html>
