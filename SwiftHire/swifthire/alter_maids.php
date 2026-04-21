<?php
require 'db.php';
$conn->query("ALTER TABLE maids ADD COLUMN diet_preference VARCHAR(50) DEFAULT 'Any'");
echo "Maids Column added (or already exists).\n";
$conn->query("UPDATE maids SET diet_preference = 'Vegetarian' WHERE id % 2 = 0");
$conn->query("UPDATE maids SET diet_preference = 'Non-Vegetarian' WHERE id % 2 != 0");
echo "Maids seeded.\n";
$conn->close();
?>
