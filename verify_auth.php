<?php
session_start();
require_once 'db.php';

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!isset($data['facialId']) || empty($data['facialId'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing facialId']);
    exit;
}

$facialId = $data['facialId'];

if (!checkUserExistsByFacialId($facialId, $conn)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not found or not enrolled.']);
    closeDatabaseConnection($conn);
    exit;
}

$userId = getUserIdByFacialId($facialId, $conn);

if ($userId) {
    $_SESSION['user_id'] = $userId;
    echo json_encode(['success' => true, 'message' => 'Authentication successful']);
} else {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication failed']);
}
closeDatabaseConnection($conn);
?>
