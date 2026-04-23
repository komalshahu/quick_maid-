<?php
require 'db.php';
$res = $conn->query('SELECT id, job_title, user_id FROM vacancies WHERE user_id IS NULL OR user_id = 0');
$vacancies = $res->fetch_all(MYSQLI_ASSOC);
echo "Vacancies with no owner:\n";
print_r($vacancies);
