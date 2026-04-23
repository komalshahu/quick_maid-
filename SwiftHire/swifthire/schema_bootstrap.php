<?php
/**
 * Lightweight runtime schema checks for chat.
 * Project uses `vacancies` as the job posts table.
 */
if (!isset($conn) || !($conn instanceof mysqli)) {
    return;
}

$conn->query("
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    job_id INT NOT NULL,
    message_text TEXT NOT NULL,
    `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_sender (sender_id),
    INDEX idx_receiver (receiver_id),
    INDEX idx_job (job_id),
    INDEX idx_time (`timestamp`),
    CONSTRAINT fk_messages_sender FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_messages_receiver FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_messages_job FOREIGN KEY (job_id) REFERENCES vacancies(id) ON DELETE CASCADE
)
");

// Backward compatibility for older deployments
$col_msg = $conn->query("SHOW COLUMNS FROM messages LIKE 'message'");
$col_msg_text = $conn->query("SHOW COLUMNS FROM messages LIKE 'message_text'");
if ($col_msg && $col_msg->num_rows > 0 && $col_msg_text && $col_msg_text->num_rows === 0) {
    $conn->query("ALTER TABLE messages CHANGE `message` `message_text` TEXT NOT NULL");
}

$col_created = $conn->query("SHOW COLUMNS FROM messages LIKE 'created_at'");
$col_ts = $conn->query("SHOW COLUMNS FROM messages LIKE 'timestamp'");
if ($col_created && $col_created->num_rows > 0 && $col_ts && $col_ts->num_rows === 0) {
    $conn->query("ALTER TABLE messages CHANGE `created_at` `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
}

$col_job = $conn->query("SHOW COLUMNS FROM messages LIKE 'job_id'");
if ($col_job && $col_job->num_rows === 0) {
    // If table existed previously, job_id may be absent.
    $conn->query("ALTER TABLE messages ADD COLUMN job_id INT NOT NULL DEFAULT 0 AFTER receiver_id");
}

$conn->query("
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    message_body TEXT NOT NULL,
    related_id INT DEFAULT 0,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_type (type),
    INDEX idx_read (is_read),
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

