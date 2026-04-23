<?php
session_start();
require 'db.php';
require 'schema_bootstrap.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$sender_id = (int)$_SESSION['user_id'];
$receiver_id = isset($_POST['receiver_id']) ? (int)$_POST['receiver_id'] : 0;
$job_id = isset($_POST['job_id']) ? (int)$_POST['job_id'] : 0;
$message_text = isset($_POST['message_text']) ? trim((string)$_POST['message_text']) : '';
$message_text = mb_substr($message_text, 0, 2000);

if ($job_id <= 0 || $message_text === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing fields']);
    exit;
}

// Ensure the job exists and belongs to the receiver (Owner)
$has_user_id = false;
$col_check = $conn->query("SHOW COLUMNS FROM vacancies LIKE 'user_id'");
if ($col_check && $col_check->num_rows > 0) {
    $has_user_id = true;
}

if (!$has_user_id) {
    http_response_code(409);
    echo json_encode(['success' => false, 'error' => 'Chat not available: vacancies.user_id missing. Run migrate_vacancies.php']);
    exit;
}

$owner_stmt = $conn->prepare("SELECT user_id FROM vacancies WHERE id = ? LIMIT 1");
$owner_stmt->bind_param("i", $job_id);
$owner_stmt->execute();
$owner_res = $owner_stmt->get_result();
$job_row = $owner_res ? $owner_res->fetch_assoc() : null;
$owner_stmt->close();

if (!$job_row || (int)$job_row['user_id'] <= 0) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid job/owner']);
    exit;
}

// Determine true receiver based on whether the sender is the job owner
$job_owner_id = (int)$job_row['user_id'];
$requested_receiver = isset($_POST['receiver_id']) ? (int)$_POST['receiver_id'] : 0;

if ($sender_id === $job_owner_id) {
    // Sender is owner, receiver must be the maid
    $receiver_id = $requested_receiver;
} else {
    // Sender is maid, receiver must be the owner
    $receiver_id = $job_owner_id;
}

// Prevent chatting with self or invalid receiver
if ($sender_id === $receiver_id || $receiver_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid receiver']);
    exit;
}

$insert_stmt = $conn->prepare("
    INSERT INTO messages (sender_id, receiver_id, job_id, message_text)
    VALUES (?, ?, ?, ?)
");
$insert_stmt->bind_param("iiis", $sender_id, $receiver_id, $job_id, $message_text);

if (!$insert_stmt->execute()) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
    $insert_stmt->close();
    exit;
}

$new_id = $insert_stmt->insert_id;
$insert_stmt->close();

$fetch_stmt = $conn->prepare("SELECT id, sender_id, receiver_id, job_id, message_text, `timestamp` FROM messages WHERE id = ? LIMIT 1");
$fetch_stmt->bind_param("i", $new_id);
$fetch_stmt->execute();
$msg_res = $fetch_stmt->get_result();
$msg = $msg_res ? $msg_res->fetch_assoc() : null;
$fetch_stmt->close();

// Insert notification for the receiver
$u_stmt = $conn->prepare("SELECT firstname FROM users WHERE id = ?");
$u_stmt->bind_param("i", $sender_id);
$u_stmt->execute();
$u_res = $u_stmt->get_result()->fetch_assoc();
$sender_name = $u_res['firstname'] ?? 'Someone';
$u_stmt->close();

$notif_body = "New message from $sender_name: " . mb_substr($message_text, 0, 50) . (mb_strlen($message_text) > 50 ? '...' : '');
$n_stmt = $conn->prepare("INSERT INTO notifications (user_id, type, message_body, related_id) VALUES (?, 'message', ?, ?)");
$n_stmt->bind_param("isi", $receiver_id, $notif_body, $job_id);
$n_stmt->execute();
$n_stmt->close();

echo json_encode(['success' => true, 'message' => $msg]);
?>
