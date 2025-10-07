<?php
session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);

    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id);
        $stmt->fetch();

        // Generate secure token
        $token = bin2hex(random_bytes(16));
        $expiry = date("Y-m-d H:i:s", strtotime('+1 hour')); // 1 hour expiry

        // Save token and expiry in DB
        $update = $conn->prepare("UPDATE users SET reset_token=?, token_expiry=? WHERE id=?");
        $update->bind_param("ssi", $token, $expiry, $user_id);
        $update->execute();
        $update->close();

        // Normally send email; here we show link for testing
        $reset_link = "http://localhost/Study_app/reset_password.php?token=$token";
        $success = "✅ Password reset link: <a href='$reset_link'>$reset_link</a>";
    } else {
        $error = "❌ Email address not found!";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Forgot Password - Study Buddy</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="auth-container">
<h2>Forgot Password? 🔒</h2>
<?php 
if (!empty($error)) echo "<p class='error'>$error</p>"; 
if (!empty($success)) echo "<p class='success'>$success</p>";
?>
<form method="POST">
    <input type="email" name="email" placeholder="Enter your email" required>
    <button type="submit">Send Reset Link</button>
    <p style="margin-top:10px;">
        <a href="login.php">← Back to Login</a>
    </p>
</form>
</div>
</body>
</html>
