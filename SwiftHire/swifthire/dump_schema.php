<?php
require 'db.php';

$tables = $conn->query("SHOW TABLES");
while ($row = $tables->fetch_array()) {
    $table = $row[0];
    echo "TABLE: $table\n";
    $columns = $conn->query("SHOW COLUMNS FROM $table");
    while ($col = $columns->fetch_assoc()) {
        echo "  " . $col['Field'] . " - " . $col['Type'] . "\n";
    }
    echo "\n";
}
?>
