<?php
session_start();

// Include DB (allow creation)
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $error = "⚠️ Email already registered!";
    } else {
        $stmt->close();
        $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $password);
        if ($stmt->execute()) {
            header("Location: login.php?signup=1");
            exit();
        } else {
            $error = "❌ Error creating account!";
        }
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sign Up - Study Buddy</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="auth-container">
<h2>Create Account ✨</h2>
<?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
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
