<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = ""; 
$db_name = "job_applications_db";
$port = 3307; // We are specifically connecting to Port 3307 for XAMPP!

// Create connection
$conn = new mysqli($servername, $username, $password, $db_name, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch applications
$sql = "SELECT * FROM job_applications ORDER BY applied_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Job Applications</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .navbar { margin-bottom: 2rem; }
        .table-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">SwiftHire Admin</a>
    <div class="d-flex">
        <a href="logout.php" class="btn btn-outline-light">Logout</a>
    </div>
  </div>
</nav>

<div class="container-fluid px-4">
    <h2 class="mb-4">Submitted Applications</h2>
    
    <div class="table-container table-responsive">
        <table class="table table-hover table-bordered">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role/Gender</th>
                    <th>Applied At</th>
                    <th>Message/Cover Letter</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result && $result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['firstname'] . ' ' . $row['lastname']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['areacode'] . '-' . $row['phone']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['gender']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['applied_at']) . "</td>";
                        echo "<td><small>" . nl2br(htmlspecialchars($row['message'])) . "</small></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7' class='text-center text-muted py-4'>No applications found. You haven't submitted any yet!</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
<?php
$conn->close();
?>
