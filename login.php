<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE user_id = ? AND password = ?");
    $hashed_password = md5($password);
    $stmt->bind_param("ss", $username, $hashed_password);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (!$result) {
        error_log("Login query failed: " . $conn->error);
        $error = "Login failed";
    } else if ($result->num_rows > 0) {
        $_SESSION['user_id'] = $username;
        header('Location: /');
        exit;
    } else {
        $error = "Invalid credentials";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Login</h1>
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    <form method="POST">
        <div>
            <label>Username:</label>
            <input type="text" name="username" required>
        </div>
        <div>
            <label>Password:</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit">Login</button>
    </form>
    <div>
        <a href="/">Home</a> | 
        <a href="/register.php">Register</a>
    </div>
</body>
</html>
