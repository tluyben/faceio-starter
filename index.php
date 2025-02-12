<?php
session_start();

if (isset($_SESSION['user_id'])) {
    $loggedInMessage = "You are logged in as: " . $_SESSION['user_id'];
} else {
    $loggedInMessage = "You are not logged in.";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>FaceIO Example</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Welcome</h1>
    <p><?php echo $loggedInMessage; ?></p>

    <?php if (isset($_SESSION['user_id'])) : ?>
        <a href="/enroll.php">Enroll Face ID</a> |
        <a href="/logout.php">Logout</a>
    <?php else : ?>
        <a href="/register.php">Register</a> |
        <a href="/login.php">Login with Password</a> |
        <a href="/auth.php">Login with Face</a>
    <?php endif; ?>
</body>
</html>