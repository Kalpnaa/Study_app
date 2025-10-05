<?php
session_start();

// Initialize "users" array if not exists (just in case)
if (!isset($_SESSION['users'])) {
    $_SESSION['users'] = [];
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $user = null;
    $user_index = null;

    // Find user in session array
    foreach ($_SESSION['users'] as $index => $u) {
        if ($u['email'] === $email) {
            $user = $u;
            $user_index = $index;
            break;
        }
    }

    if ($user && password_verify($password, $user["password"])) {
        $_SESSION["user_id"] = $user_index; // Save index as user_id
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "❌ Invalid email or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - Study Buddy</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div class="auth-container">
    <h2>Welcome Back 👋</h2>
    <?php 
      if (!empty($error)) echo "<p class='error'>$error</p>"; 
      if (!empty($success)) echo "<p class='success'>$success</p>";
    ?>
    <form method="POST">
      <input type="email" name="email" placeholder="Email Address" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Login</button>
      <p>Don’t have an account? <a href="signup.php">Sign Up</a></p>
    </form>
  </div>
</body>
</html>
