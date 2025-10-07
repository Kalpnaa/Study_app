<?php
session_start();
require 'db.php';

// ✅ Ensure user is logged in (so user_id is available)
if (!isset($_SESSION['user_id'])) {
    die("User not logged in");
}

$user_id = (int)$_SESSION['user_id'];

// ✅ Fetch username (added this block)
$stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username);
$stmt->fetch();
$stmt->close();

// Add new flashcard
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['question'])) {
    $q = $conn->real_escape_string($_POST['question']);
    $a = $conn->real_escape_string($_POST['answer']);

    // ✅ insert user_id to satisfy foreign key
    $conn->query("INSERT INTO flashcards(user_id, question, answer) VALUES ($user_id, '$q', '$a')");
}

// ✅ Fetch only the logged-in user's flashcards
$flashcards = $conn->query("SELECT * FROM flashcards WHERE user_id = $user_id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Flashcards</title>
  <link rel="stylesheet" href="css/dashboard.css" />
</head>
<body>
  <div class="container">
    <aside class="sidebar">
      <h2><u>Study-Buddy 📚💻</u></h2>
      <ul>
        <li class="active"><a href="dashboard.php" style="color:white; text-decoration:none;">📊 Dashboard</a></li>
        <li><a href="sidebar_tasks.php" style="color:white; text-decoration:none;">📝 Tasks</a></li>
        <li><a href="flashcards.php" style="color:white; text-decoration:none;">📚 Flashcards</a></li>
        <li><a href="notes.php" style="color:white; text-decoration:none;">📂 Notes</a></li>
        <li>👥 Study Circle</li>
        <li><a href="logout.php" style="color:white; text-decoration:none;">🚪 Logout</a></li>
      </ul>
    </aside>
    <main class="main">
      <marquee behavior="scroll" direction="left" scrollamount="10">
        <h2>Your Flashcards, <?php echo htmlspecialchars($username); ?> 👋</h2>
      </marquee>
      <div class="cards">
        <div class="card auth-container">
          <h2>Add Flashcard</h2>
          <form method="POST">
            <input name="question" placeholder="Question" required>
            <input name="answer" placeholder="Answer" required>
            <button type="submit">Add</button>
          </form>
        </div>

        <div class="card">
          <h2>Flashcards</h2>
          <div class="flashcard-container">
            <?php while ($card = $flashcards->fetch_assoc()) : ?>
              <div class="flashcard" onclick="this.classList.toggle('flipped')">
                <div class="flashcard-inner">
                  <div class="flashcard-front"><?= htmlspecialchars($card['question']) ?></div>
                  <div class="flashcard-back"><?= htmlspecialchars($card['answer']) ?></div>
                </div>
              </div>
            <?php endwhile; ?>
          </div>
        </div>
      </div>
    </main>
  </div>
  <!-- Footer -->
<footer class="footer">
  <p>© 2025 Study Buddy. All Rights Reserved.</p>
</footer>
</body>
</html>
