<?php
session_start();

// Skip DB/table creation in login
$skip_create_db = true;
$skip_create_table = true;
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION["user_id"] = $id;
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "❌ Invalid email or password!";
        }
    } else {
        $error = "❌ Invalid email or password!";
    }
    $stmt->close();
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
if (!empty($_GET['signup'])) echo "<p class='success'>✅ Account created successfully! Please login.</p>";
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
