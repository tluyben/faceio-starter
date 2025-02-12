<?php
require_once 'db.php';

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!isset($data['facialId'], $data['username']) || empty($data['facialId']) || empty($data['username'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing facialId or username']);
    exit;
}

$facialId = $data['facialId'];
$username = $data['username'];

if (associateFacialId($username, $facialId, $conn)) {
    echo json_encode(['success' => true, 'message' => 'User enrolled successfully']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
closeDatabaseConnection($conn);
?>
