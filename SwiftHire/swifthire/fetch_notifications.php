<?php
session_start();
require 'db.php';
require 'schema_bootstrap.php'; // Ensure tables exist

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$user_type = $_SESSION['user_type'] ?? '';

// Get all unread notifications
$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $row['target_url'] = '';

    if ($row['type'] === 'application_received') {
        $row['target_url'] = 'owner_inbox.php#app-' . (int)$row['related_id'];
    } elseif ($row['type'] === 'application_accepted' || $row['type'] === 'request') {
        $row['target_url'] = 'user_dashboard.php#app-' . (int)$row['related_id'];
    } elseif ($row['type'] === 'message') {
        if ($user_type === 'owner') {
            $app_stmt = $conn->prepare("
                SELECT ja.job_id, ja.user_id AS maid_id
                FROM job_applications ja
                JOIN vacancies v ON ja.job_id = v.id
                WHERE ja.id = ? AND v.user_id = ?
                LIMIT 1
            ");
            $app_stmt->bind_param("ii", $row['related_id'], $user_id);
            $app_stmt->execute();
            $app_res = $app_stmt->get_result();
            $app = $app_res ? $app_res->fetch_assoc() : null;
            $app_stmt->close();

            if ($app) {
                $row['target_url'] = 'owner_chat_dashboard.php?job_id=' . (int)$app['job_id'] . '&maid_id=' . (int)$app['maid_id'];
            } else {
                $msg_stmt = $conn->prepare("
                    SELECT m.job_id,
                           CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END AS maid_id
                    FROM messages m
                    JOIN vacancies v ON m.job_id = v.id
                    WHERE m.job_id = ? AND v.user_id = ?
                    ORDER BY m.id DESC
                    LIMIT 1
                ");
                $msg_stmt->bind_param("iii", $user_id, $row['related_id'], $user_id);
                $msg_stmt->execute();
                $msg_res = $msg_stmt->get_result();
                $msg = $msg_res ? $msg_res->fetch_assoc() : null;
                $msg_stmt->close();

                if ($msg) {
                    $row['target_url'] = 'owner_chat_dashboard.php?job_id=' . (int)$msg['job_id'] . '&maid_id=' . (int)$msg['maid_id'];
                }
            }
        } else {
            $job_stmt = $conn->prepare("
                SELECT v.id AS job_id, v.user_id AS owner_id
                FROM vacancies v
                WHERE v.id = ?
                LIMIT 1
            ");
            $job_stmt->bind_param("i", $row['related_id']);
            $job_stmt->execute();
            $job_res = $job_stmt->get_result();
            $job = $job_res ? $job_res->fetch_assoc() : null;
            $job_stmt->close();

            if ($job && (int)$job['owner_id'] > 0) {
                $row['target_url'] = 'job_chat.php?job_id=' . (int)$job['job_id'] . '&owner_id=' . (int)$job['owner_id'];
            }
        }
    }

    $notifications[] = $row;
}

$stmt->close();

echo json_encode(['success' => true, 'notifications' => $notifications]);
?>
