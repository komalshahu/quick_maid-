<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    die("Access denied");
}

require 'db.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all applications
$sql = "SELECT id, user_id, firstname, lastname, email, gender, areacode, phone, age, startdate, applied_at FROM job_applications ORDER BY applied_at DESC";
$result = $conn->query($sql);

if (!$result) {
    die("Error fetching data");
}

// Set headers to trigger file download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=job_applications_report_' . date('Y-m-d') . '.csv');

// Create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

// Output the column headings
fputcsv($output, array('App ID', 'User ID', 'First Name', 'Last Name', 'Email', 'Gender', 'Phone', 'Age', 'Start Date', 'Application Date'));

// Loop over the rows, outputting them
while ($row = $result->fetch_assoc()) {
    $phone_formatted = $row['areacode'] . '-' . $row['phone'];
    fputcsv($output, array(
        $row['id'],
        $row['user_id'],
        $row['firstname'],
        $row['lastname'],
        $row['email'],
        $row['gender'],
        $phone_formatted,
        $row['age'],
        $row['startdate'],
        $row['applied_at']
    ));
}

$conn->close();
?>
