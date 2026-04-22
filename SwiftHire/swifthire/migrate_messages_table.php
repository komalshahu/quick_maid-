<?php
/**
 * One-time migration helper:
 * - Ensures `messages` table exists with columns:
 *   id, sender_id, receiver_id, job_id, message_text, timestamp
 * - Adds foreign keys to `users(id)` and `vacancies(id)` (project uses `vacancies` for job posts)
 *
 * Run in browser once: migrate_messages_table.php
 */
session_start();
require 'db.php';

// Only allow logged-in admins to run migrations from UI
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    echo "Forbidden";
    exit;
}

// Create table if missing (fresh install)
$create_sql = "
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    job_id INT NULL,
    message_text TEXT NOT NULL,
    `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_read INT DEFAULT 0,
    INDEX idx_sender (sender_id),
    INDEX idx_receiver (receiver_id),
    INDEX idx_job (job_id),
    INDEX idx_ts (`timestamp`)
)";
if (!$conn->query($create_sql)) {
    echo "Error creating messages table: " . htmlspecialchars($conn->error);
    exit;
}

// Add missing columns / rename legacy columns
$cols = [];
$col_res = $conn->query("SHOW COLUMNS FROM messages");
while ($col_res && ($r = $col_res->fetch_assoc())) {
    $cols[strtolower($r['Field'])] = true;
}

// Legacy support: `message` -> `message_text`
if (isset($cols['message']) && !isset($cols['message_text'])) {
    if (!$conn->query("ALTER TABLE messages CHANGE `message` `message_text` TEXT NOT NULL")) {
        echo "Error renaming message -> message_text: " . htmlspecialchars($conn->error);
        exit;
    }
    $cols['message_text'] = true;
    unset($cols['message']);
}

// Legacy support: `created_at` -> `timestamp`
if (isset($cols['created_at']) && !isset($cols['timestamp'])) {
    if (!$conn->query("ALTER TABLE messages CHANGE `created_at` `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP")) {
        echo "Error renaming created_at -> timestamp: " . htmlspecialchars($conn->error);
        exit;
    }
    $cols['timestamp'] = true;
    unset($cols['created_at']);
}

if (!isset($cols['job_id'])) {
    if (!$conn->query("ALTER TABLE messages ADD COLUMN job_id INT NULL AFTER receiver_id")) {
        echo "Error adding job_id: " . htmlspecialchars($conn->error);
        exit;
    }
}

if (!isset($cols['timestamp'])) {
    if (!$conn->query("ALTER TABLE messages ADD COLUMN `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP")) {
        echo "Error adding timestamp: " . htmlspecialchars($conn->error);
        exit;
    }
}

if (!isset($cols['is_read'])) {
    $conn->query("ALTER TABLE messages ADD COLUMN is_read INT DEFAULT 0");
}

// Ensure indexes exist (ignore errors if already exist)
$conn->query("ALTER TABLE messages ADD INDEX idx_sender (sender_id)");
$conn->query("ALTER TABLE messages ADD INDEX idx_receiver (receiver_id)");
$conn->query("ALTER TABLE messages ADD INDEX idx_job (job_id)");
$conn->query("ALTER TABLE messages ADD INDEX idx_ts (`timestamp`)");

// Foreign keys: add if missing (best-effort; requires InnoDB)
// Check existing constraints
$fk = [];
$fk_res = $conn->query("
    SELECT CONSTRAINT_NAME
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'messages'
      AND REFERENCED_TABLE_NAME IS NOT NULL
");
while ($fk_res && ($r = $fk_res->fetch_assoc())) {
    $fk[$r['CONSTRAINT_NAME']] = true;
}

if (!isset($fk['fk_messages_sender'])) {
    $conn->query("ALTER TABLE messages ADD CONSTRAINT fk_messages_sender FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE");
}
if (!isset($fk['fk_messages_receiver'])) {
    $conn->query("ALTER TABLE messages ADD CONSTRAINT fk_messages_receiver FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE");
}
if (!isset($fk['fk_messages_job'])) {
    $conn->query("ALTER TABLE messages ADD CONSTRAINT fk_messages_job FOREIGN KEY (job_id) REFERENCES vacancies(id) ON DELETE CASCADE");
}

echo "Migration complete. You can delete this file after running.\n";
?>

