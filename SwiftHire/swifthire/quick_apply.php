<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to apply.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['job_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$job_id = (int)($_POST['job_id'] ?? 0);
$phone = $_POST['phone'] ?? '';
$age = (int)($_POST['age'] ?? 0);
$startdate = $_POST['startdate'] ?? '';
$message = $_POST['message'] ?? '';

$firstname = $_SESSION['user_firstname'] ?? 'Unknown';
$lastname = $_SESSION['user_lastname'] ?? 'Unknown';
$email = $_SESSION['user_email'] ?? 'Unknown';

// Check if already applied
$stmt = $conn->prepare("SELECT id FROM job_applications WHERE user_id = ? AND job_id = ?");
$stmt->bind_param("ii", $user_id, $job_id);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'You have already applied to this job.']);
    $stmt->close();
    exit;
}
$stmt->close();

$stmt = $conn->prepare("INSERT INTO job_applications (user_id, job_id, firstname, lastname, email, phone, age, startdate, message, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
$stmt->bind_param("iissssiss", $user_id, $job_id, $firstname, $lastname, $email, $phone, $age, $startdate, $message);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
}
$stmt->close();
?>
