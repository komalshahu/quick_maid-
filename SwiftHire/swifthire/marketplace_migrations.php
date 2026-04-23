<?php
require 'db.php';

$queries = [
    // 1. Alter maids table
    "ALTER TABLE maids ADD COLUMN IF NOT EXISTS bio TEXT",
    "ALTER TABLE maids ADD COLUMN IF NOT EXISTS skills VARCHAR(500)",
    "ALTER TABLE maids ADD COLUMN IF NOT EXISTS rating DECIMAL(3,2) DEFAULT 0.00",
    
    // 2. Alter job_applications table
    "ALTER TABLE job_applications ADD COLUMN IF NOT EXISTS job_id INT DEFAULT 0",
    "ALTER TABLE job_applications ADD COLUMN IF NOT EXISTS status ENUM('Pending', 'Accepted', 'Rejected', 'Completed') DEFAULT 'Pending'",
    
    // 3. Create reviews table
    "CREATE TABLE IF NOT EXISTS reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        job_id INT NOT NULL,
        reviewer_id INT NOT NULL,
        maid_id INT NOT NULL,
        rating INT NOT NULL CHECK(rating >= 1 AND rating <= 5),
        review_text TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_job (job_id),
        INDEX idx_reviewer (reviewer_id),
        INDEX idx_maid (maid_id)
    )"
];

foreach ($queries as $query) {
    if ($conn->query($query) === TRUE) {
        echo "Successfully executed: " . substr($query, 0, 50) . "...\n";
    } else {
        echo "Error executing query: " . $conn->error . "\n";
    }
}

echo "Database migrations completed successfully.\n";
?>
