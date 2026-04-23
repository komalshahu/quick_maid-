<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$current_user_id = (int)$_SESSION['user_id'];
$job_id = isset($_POST['job_id']) ? (int)$_POST['job_id'] : 0;

if ($job_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid job ID']);
    exit;
}

// Check if it already has an owner
$stmt = $conn->prepare("SELECT user_id FROM vacancies WHERE id = ?");
$stmt->bind_param("i", $job_id);
$stmt->execute();
$res = $stmt->get_result();
$job = $res->fetch_assoc();
$stmt->close();

if (!$job) {
    echo json_encode(['success' => false, 'error' => 'Job not found']);
    exit;
}

if (!empty($job['user_id']) && (int)$job['user_id'] > 0) {
    echo json_encode(['success' => false, 'error' => 'This job already has an owner']);
    exit;
}

// Claim the job
$update = $conn->prepare("UPDATE vacancies SET user_id = ? WHERE id = ?");
$update->bind_param("ii", $current_user_id, $job_id);
if ($update->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
}
$update->close();
$conn->close();
