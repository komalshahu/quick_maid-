<?php
require 'db.php';

$sql = "
CREATE TABLE IF NOT EXISTS maids (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    service_category ENUM('Cleaning', 'Cooking', 'Babysitting', 'All-Rounder') NOT NULL,
    shift_preference ENUM('Live-in', 'Live-out') NOT NULL,
    expected_salary DECIMAL(10, 2) NOT NULL,
    experience_years INT NOT NULL,
    verification_status ENUM('Pending', 'Aadhar Verified', 'Police Verified', 'Rejected') DEFAULT 'Pending',
    location_area VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
";

if ($conn->query($sql) === TRUE) {
    echo "Table 'maids' created successfully.\n";
} else {
    echo "Error creating table 'maids': " . $conn->error . "\n";
}

$sql2 = "
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employer_id INT NOT NULL,
    maid_id INT NOT NULL,
    status ENUM('Pending', 'Trial Scheduled', 'Hired', 'Cancelled', 'Completed') DEFAULT 'Pending',
    start_date DATE NOT NULL,
    end_date DATE NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (maid_id) REFERENCES maids(id) ON DELETE CASCADE
);
";

if ($conn->query($sql2) === TRUE) {
    echo "Table 'bookings' created successfully.\n";
} else {
    echo "Error creating table 'bookings': " . $conn->error . "\n";
}

$conn->close();
?>
