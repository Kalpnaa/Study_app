<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'study_app');

// Add new flashcard
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['question'])) {
    $q = $conn->real_escape_string($_POST['question']);
    $a = $conn->real_escape_string($_POST['answer']);
    $conn->query("INSERT INTO flashcards(question, answer) VALUES ('$q', '$a')");
}

// Fetch flashcards
$flashcards = $conn->query("SELECT * FROM flashcards");
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
        <li><a href="flashcards.php" style="color:white; text-decoration:none;">📚 Flashcards</a></li>       <!-- Added link -->
        <li><a href="notes.php" style="color:white; text-decoration:none;">📂 Notes</a></li>              <!-- Added link -->
        <li>👥 Study Circle</li>
        <li><a href="logout.php" style="color:white; text-decoration:none;">🚪 Logout</a></li>
      </ul>
    </aside>
    <main class="main">
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
</body>
</html>
