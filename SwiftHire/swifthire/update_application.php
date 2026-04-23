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
    SELECT ja.id, ja.user_id AS maid_id, v.job_title, ja.job_id, u.firstname AS owner_name 
    FROM job_applications ja
    JOIN vacancies v ON ja.job_id = v.id
    JOIN users u ON v.user_id = u.id
    WHERE ja.id = ? AND v.user_id = ?
");
$stmt->bind_param("ii", $app_id, $user_id);
$stmt->execute();
$app_result = $stmt->get_result();
if ($app_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Not authorized to modify this application.']);
    $stmt->close();
    exit;
}
$app_data = $app_result->fetch_assoc();
$stmt->close();

$valid_statuses = ['Accepted', 'Rejected', 'Completed'];
if (!in_array($status, $valid_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

$stmt = $conn->prepare("UPDATE job_applications SET status = ? WHERE id = ?");
$stmt->bind_param("si", $status, $app_id);

if ($stmt->execute()) {
    if ($status === 'Accepted') {
        $maid_id = $app_data['maid_id'];
        $job_title = $app_data['job_title'];
        $owner_name = $app_data['owner_name'];
        
        // Notify Maid
        $notif_msg = "Your application for '$job_title' has been accepted by $owner_name! Start chatting now.";
        $notif_stmt = $conn->prepare("INSERT INTO notifications (user_id, type, message_body, related_id) VALUES (?, 'application_accepted', ?, ?)");
        $notif_stmt->bind_param("isi", $maid_id, $notif_msg, $app_data['job_id']);
        $notif_stmt->execute();
        $notif_stmt->close();

        // Auto-initialize chat message from Owner to Maid
        $init_msg = "Hi! I have accepted your application for the $job_title position. Let's discuss further.";
        $msg_stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, job_id, message_text) VALUES (?, ?, ?, ?)");
        $msg_stmt->bind_param("iiis", $user_id, $maid_id, $app_data['job_id'], $init_msg);
        $msg_stmt->execute();
        $msg_stmt->close();
    }
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
$stmt->close();
?>
