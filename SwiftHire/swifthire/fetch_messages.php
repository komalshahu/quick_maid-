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

$current_user_id = (int)$_SESSION['user_id'];
$other_user_id = isset($_GET['other_user_id']) ? (int)$_GET['other_user_id'] : 0;
$maid_id = isset($_GET['maid_id']) ? (int)$_GET['maid_id'] : 0;
$owner_id = isset($_GET['owner_id']) ? (int)$_GET['owner_id'] : 0;
$job_id = isset($_GET['job_id']) ? (int)$_GET['job_id'] : 0;
$since_id = isset($_GET['since_id']) ? (int)$_GET['since_id'] : 0;

if ($job_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing fields']);
    exit;
}

// Ensure the job exists and belongs to the owner_id
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

$owner_stmt = $conn->prepare("SELECT v.user_id, u.firstname, u.lastname FROM vacancies v LEFT JOIN users u ON v.user_id = u.id WHERE v.id = ? LIMIT 1");
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

// Always trust DB owner for this job to avoid stale URL issues.
$owner_id = (int)$job_row['user_id'];
$is_owner = ($current_user_id === $owner_id);
$other_user_id = $other_user_id > 0 ? $other_user_id : ($maid_id > 0 ? $maid_id : ($is_owner ? 0 : $owner_id));

if ($other_user_id <= 0 || $other_user_id === $current_user_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid conversation target']);
    exit;
}

if ($is_owner) {
    $auth_stmt = $conn->prepare("
        SELECT id
        FROM messages
        WHERE job_id = ?
          AND ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?))
        LIMIT 1
    ");
    $auth_stmt->bind_param("iiiii", $job_id, $current_user_id, $other_user_id, $other_user_id, $current_user_id);
    $auth_stmt->execute();
    $auth_res = $auth_stmt->get_result();
    $has_thread = $auth_res && $auth_res->num_rows > 0;
    $auth_stmt->close();

    if (!$has_thread) {
        $app_stmt = $conn->prepare("
            SELECT id
            FROM job_applications
            WHERE job_id = ? AND user_id = ?
            LIMIT 1
        ");
        $app_stmt->bind_param("ii", $job_id, $other_user_id);
        $app_stmt->execute();
        $app_res = $app_stmt->get_result();
        $has_thread = $app_res && $app_res->num_rows > 0;
        $app_stmt->close();
    }

    if (!$has_thread) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Conversation not available for this applicant']);
        exit;
    }
}

$user_stmt = $conn->prepare("SELECT firstname, lastname, user_type FROM users WHERE id = ? LIMIT 1");
$user_stmt->bind_param("i", $other_user_id);
$user_stmt->execute();
$user_res = $user_stmt->get_result();
$other_user = $user_res ? $user_res->fetch_assoc() : null;
$user_stmt->close();

$other_user_name = $other_user ? trim((string)$other_user['firstname'] . ' ' . (string)$other_user['lastname']) : 'User';

$sql = "
    SELECT id, sender_id, receiver_id, job_id, message_text, `timestamp`, is_read
    FROM messages
    WHERE job_id = ?
      AND ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?))
";

if ($since_id > 0) {
    $sql .= " AND id > ? ";
}

$sql .= " ORDER BY id ASC";

if ($since_id > 0) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiiii", $job_id, $current_user_id, $other_user_id, $other_user_id, $current_user_id, $since_id);
} else {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiii", $job_id, $current_user_id, $other_user_id, $other_user_id, $current_user_id);
}

$stmt->execute();
$res = $stmt->get_result();
$messages = [];
while ($row = $res->fetch_assoc()) {
    $messages[] = $row;
}
$stmt->close();

$read_stmt = $conn->prepare("
    UPDATE messages
    SET is_read = 1
    WHERE job_id = ?
      AND sender_id = ?
      AND receiver_id = ?
      AND is_read = 0
");
$read_stmt->bind_param("iii", $job_id, $other_user_id, $current_user_id);
$read_stmt->execute();
$read_stmt->close();

echo json_encode([
    'success' => true,
    'chat_key' => implode('-', [$job_id, min($owner_id, $other_user_id), max($owner_id, $other_user_id)]),
    'owner' => ['id' => $owner_id, 'name' => trim((string)($job_row['firstname'] ?? '') . ' ' . (string)($job_row['lastname'] ?? ''))],
    'other_user' => [
        'id' => $other_user_id,
        'name' => $other_user_name,
        'role' => $other_user['user_type'] ?? null,
    ],
    'messages' => $messages
]);
?>
