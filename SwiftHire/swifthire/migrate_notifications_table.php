<?php
/**
 * One-time migration helper:
 * - Creates the `notifications` table for real-time alerts.
 * Run in browser once: migrate_notifications_table.php
 */
session_start();
require 'db.php';

// Create table if missing
$create_sql = "
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
";

if (!$conn->query($create_sql)) {
    echo "Error creating notifications table: " . htmlspecialchars($conn->error);
    exit;
}

echo "Migration complete. The notifications table has been created successfully.\n";
?>
