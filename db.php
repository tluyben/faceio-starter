<?php
$conn = new mysqli(
    $_ENV['DB_HOST'] ?? '0.0.0.0',
    $_ENV['DB_USER'] ?? 'root',
    $_ENV['DB_PASS'] ?? '',
    $_ENV['DB_NAME'] ?? 'mydatabase'
);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create users table if it doesn't exist
$createTable = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    facial_id VARCHAR(255) UNIQUE
)";

if (!$conn->query($createTable)) {
    die("Error creating table: " . $conn->error);
}


function associateFacialId($userId, $facialId, $conn) {
    $stmt = $conn->prepare("INSERT INTO users (user_id, facial_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE facial_id = ?");
    $stmt->bind_param("sss", $userId, $facialId, $facialId);

    if ($stmt->execute()) {
        return true;
    } else {
        error_log("Error associating facial ID: " . $stmt->error);
        return false;
    }
}

function getUserIdByFacialId($facialId, $conn) {
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE facial_id = ?");
    $stmt->bind_param("s", $facialId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['user_id'];
    }
    return null;
}

function checkUserExistsByFacialId($facialId, $conn) {
    $stmt = $conn->prepare("SELECT 1 FROM users WHERE facial_id = ?");
    $stmt->bind_param("s", $facialId);
    $stmt->execute();
    $stmt->store_result();
    return $stmt->num_rows > 0;
}

function closeDatabaseConnection($conn) {
    $conn->close();
}
?>