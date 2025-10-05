<?php
session_start();

// Initialize "users" array in session if not exists
if (!isset($_SESSION['users'])) {
    $_SESSION['users'] = [];
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    // Check if email already exists
    $existingUser = null;
    foreach ($_SESSION['users'] as $user) {
        if ($user['email'] === $email) {
            $existingUser = $user;
            break;
        }
    }

    if ($existingUser) {
        $error = "⚠️ Email already registered!";
    } else {
        // Save user in session
        $_SESSION['users'][] = [
            "name" => $name,
            "email" => $email,
            "password" => $password
        ];

        // ✅ Redirect to login page instead of dashboard
        header("Location: login.php?signup=1");
        exit();
    }
}

if (isset($_GET['logout'])) {
    $success = "✅ You have been logged out successfully.";
}

if (isset($_GET['timeout'])) {
    $error = "⏳ Session expired due to inactivity. Please log in again.";
}   
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sign Up - Study Buddy</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    .success {
      color: green;
      font-weight: bold;
      margin-bottom: 10px;
    }
    .error {
      color: red;
      font-weight: bold;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>
  <div class="auth-container">
    <h2>Create Account ✨</h2>
    <?php 
        if (!empty($error)) echo "<p class='error'>$error</p>"; 
        if (!empty($success)) echo "<p class='success'>$success</p>";
    ?>
    <form method="POST">
      <input type="text" name="name" placeholder="Full Name" required>
      <input type="email" name="email" placeholder="Email Address" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Sign Up</button>
      <p>Already have an account? <a href="login.php">Login here</a></p>
    </form>
  </div>
</body>
</html>
