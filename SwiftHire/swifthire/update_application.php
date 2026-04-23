<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$app_id = (int)$_POST['id'];
$status = $_POST['status'];

// verify owner owns the job
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT ja.id 
    FROM job_applications ja
    JOIN vacancies v ON ja.job_id = v.id
    WHERE ja.id = ? AND v.user_id = ?
");
$stmt->bind_param("ii", $app_id, $user_id);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Not authorized to modify this application.']);
    $stmt->close();
    exit;
}
$stmt->close();

$valid_statuses = ['Accepted', 'Rejected', 'Completed'];
if (!in_array($status, $valid_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

$stmt = $conn->prepare("UPDATE job_applications SET status = ? WHERE id = ?");
$stmt->bind_param("si", $status, $app_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
$stmt->close();
?>
