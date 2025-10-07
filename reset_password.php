<?php
session_start();
require 'db.php';

$token = $_GET['token'] ?? '';
if (!$token) {
    die("❌ Invalid token!");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $new_password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if ($new_password !== $confirm_password) {
        $error = "❌ Passwords do not match!";
    } else {
        // Verify token
        $stmt = $conn->prepare("SELECT id, token_expiry FROM users WHERE reset_token=?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($user_id, $expiry);
            $stmt->fetch();
            if (strtotime($expiry) < time()) {
                $error = "❌ Token expired!";
            } else {
                // Update password
                $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $update = $conn->prepare("UPDATE users SET password=?, reset_token=NULL, token_expiry=NULL WHERE id=?");
                $update->bind_param("si", $hashed, $user_id);
                $update->execute();
                $update->close();
                $success = "✅ Password reset successfully! <a href='login.php'>Login here</a>";
            }
        } else {
            $error = "❌ Invalid token!";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reset Password - Study Buddy</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="auth-container">
<h2>Reset Your Password 🔑</h2>
<?php 
if (!empty($error)) echo "<p class='error'>$error</p>"; 
if (!empty($success)) echo "<p class='success'>$success</p>";
?>
<?php if (empty($success)): ?>
<form method="POST">
    <input type="password" name="password" placeholder="New Password" required>
    <input type="password" name="confirm_password" placeholder="Confirm Password" required>
    <button type="submit">Reset Password</button>
</form>
<?php endif; ?>
</div>
</body>
</html>
