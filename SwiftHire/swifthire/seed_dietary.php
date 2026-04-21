<?php
require 'db.php';
$conn->query("UPDATE vacancies SET home_type = 'Vegetarian', maid_preference = 'Vegetarian' WHERE id % 2 = 0");
$conn->query("UPDATE vacancies SET home_type = 'Non-Vegetarian', maid_preference = 'Any' WHERE id % 2 != 0");
echo "Seed updated.\n";
$conn->close();
?>
