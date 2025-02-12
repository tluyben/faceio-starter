<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Check if user already exists
    $check_query = "SELECT 1 FROM users WHERE user_id = $1";
    $check_result = pg_query_params($conn, array($username));
    
    if (pg_num_rows($check_result) > 0) {
        $error = "Username already exists";
    } else {
        $query = "INSERT INTO users (user_id, password) VALUES ($1, $2)";
        $result = pg_query_params($conn, $query, array($username, md5($password)));
        
        if ($result) {
        $_SESSION['user_id'] = $username;
        header('Location: /enroll.php');
        exit;
    } else {
        $error = "Registration failed";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Register</h1>
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
        <button type="submit">Register</button>
    </form>
    <div>
        <a href="/">Home</a> | 
        <a href="/login.php">Login</a>
    </div>
</body>
</html>
