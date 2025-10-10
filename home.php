<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Study Buddy - Home</title>
    <link rel="stylesheet" href="css/home.css">
</head>
<body>
    <!-- Floating icons container -->
    <div class="floating-icons">
        <span class="icon">📚</span>
        <span class="icon">💻</span>
        <span class="icon">📝</span>
        <span class="icon">🎓</span>
        <span class="icon">🖊️</span>
        <span class="icon">📖</span>
    </div>

    <div class="home-container">
        <header>
            <marquee behavior="scroll" direction="left" scrollamount="10" style="font-size: 36px; font-weight: bold; color: #1e40af; background-color: #ffe4e1; padding: 15px; border-radius: 8px;"><u>
                🌟 Welcome to Study Buddy — Organize Tasks, Notes, Flashcards & Boost Your Productivity! 🚀📚</u>
            </marquee>
            <p class="fade-in delay-1"><h2>
                <center>Your personal study companion to organize tasks, notes, and flashcards efficiently!!!</center>
            </p></h2>
        </header>

        <div class="home-content">
            <div class="home-card fade-in delay-2">
                <h2>Organize Your Tasks</h2>
                <p>Keep track of your daily study tasks, timers, and progress all in one place.</p>
            </div>
            <div class="home-card fade-in delay-3">
                <h2>Flashcards & Notes</h2>
                <p>Learn efficiently using interactive flashcards and save important notes for quick reference.</p>
            </div>
            <div class="home-card fade-in delay-4">
                <h2>Smart Calendar & Reminders</h2>
                <p>Stay on top of your deadlines with the built-in calendar! Add important submission dates, project deadlines, or personal goals — and get daily reminders to keep your studies on track.</p>
            </div>

        </div>
        <div class="home-card fade-in delay-5" style="background: linear-gradient(135deg, #f6df86ff, #f78fd3ff); color: #fff; box-shadow: 0 8px 20px rgba(0,0,0,0.3);">
        <h2>🌟 Why Study Buddy?</h2>
        <p style="font-size:18px;">
            Study Buddy helps students manage time, stay organized, and collaborate with peers — all in one platform! Boost productivity and achieve your study goals.
        </p>
        </div>
        </div>
<center>
        <div class="get-started fade-in delay-5">
            <a href="signup.php" class="btn-get-started">Get Started 🚀</a>
        </div>
</center>

        <footer class="footer fade-in delay-6">
            <p>© 2025 Study Buddy. All Rights Reserved.</p>
        </footer>
    </div>
</body>
</html>
