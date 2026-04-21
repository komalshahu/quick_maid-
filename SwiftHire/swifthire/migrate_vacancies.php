<?php
session_start();
require 'db.php';

$messages = [];

// Check if column exists
$check_column = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
                 WHERE TABLE_NAME = 'vacancies' AND COLUMN_NAME = 'user_id' AND TABLE_SCHEMA = DATABASE()";
$result = $conn->query($check_column);

if ($result && $result->num_rows == 0) {
    $alter_query = "ALTER TABLE vacancies ADD COLUMN user_id INT DEFAULT NULL AFTER id";
    if ($conn->query($alter_query)) {
        $messages[] = "✓ Successfully added user_id column to vacancies table";
    } else {
        $messages[] = "Error adding column: " . $conn->error;
    }
} else {
    $messages[] = "✓ user_id column already exists in vacancies table";
}

// Add foreign key constraint if possible
$fk_check = "SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
             WHERE TABLE_NAME = 'vacancies' AND COLUMN_NAME = 'user_id' AND REFERENCED_TABLE_NAME = 'users'";
$fk_result = $conn->query($fk_check);

if ($fk_result && $fk_result->num_rows == 0) {
    $fk_query = "ALTER TABLE vacancies ADD CONSTRAINT fk_vacancies_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL";
    if ($conn->query($fk_query)) {
        $messages[] = "✓ Successfully added foreign key constraint";
    } else {
        $messages[] = "Note: foreign key could not be added or already exists: " . $conn->error;
    }
} else {
    $messages[] = "✓ Foreign key already exists or not needed";
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Migration - Vacancies</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial; background:#f8fafc; }
        .card { max-width:720px; margin:4rem auto; padding:2rem; border-radius:12px; box-shadow:0 10px 30px rgba(2,6,23,0.07); }
        .success { background:#ecfdf5; color:#065f46; padding:0.5rem 0.75rem; border-radius:8px; margin-bottom:0.5rem; }
        .note { background:#fff7ed; color:#92400e; padding:0.5rem 0.75rem; border-radius:8px; margin-bottom:0.5rem; }
    </style>
</head>
<body>
<div class="card">
    <h3>Vacancies Migration</h3>
    <p>Run this once to ensure `vacancies` table has a `user_id` column and a foreign key to `users(id)`.</p>
    <div>
        <?php foreach($messages as $m): ?>
            <?php if(strpos($m, '✓') === 0): ?>
                <div class="success"><?php echo htmlspecialchars($m); ?></div>
            <?php else: ?>
                <div class="note"><?php echo htmlspecialchars($m); ?></div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    <div style="margin-top:1rem;">
        <a href="maid_vacancies.php" class="btn btn-primary">Back to Vacancies</a>
    </div>
</div>
</body>
</html>
