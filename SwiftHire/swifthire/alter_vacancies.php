<?php
require 'db.php';
$conn->query("ALTER TABLE vacancies ADD COLUMN home_type VARCHAR(50) DEFAULT 'Any'");
$conn->query("ALTER TABLE vacancies ADD COLUMN maid_preference VARCHAR(50) DEFAULT 'Any'");
echo "Columns added (or already exist).\n";
$conn->close();
?>
