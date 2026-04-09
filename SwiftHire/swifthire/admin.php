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
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
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

<?php $hide_nav = isset($_GET['nomdi']) && $_GET['nomdi'] == '1'; ?>
<?php if(!$hide_nav): ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">QuickMaid Admin</a>
    <div class="collapse navbar-collapse">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item">
                <a class="nav-link active" href="admin.php">Data Table</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="admin_report.php">Reports & Analytics</a>
            </li>
        </ul>
    </div>
    <div class="d-flex">
        <a href="logout.php" class="btn btn-outline-light">Logout</a>
    </div>
  </div>
</nav>
<?php else: ?>
    <!-- Navbar hidden in MDI mode. Add margin for aesthetic spacing if needed -->
    <div class="mb-4"></div>
<?php endif; ?>

<div class="container-fluid px-4">
    <h2 class="mb-4">Submitted Applications</h2>
    
    <div class="table-container table-responsive">
        <table id="applicationsTable" class="table table-hover table-bordered table-striped">
            <thead class="table-dark">
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

<!-- jQuery and DataTables JS -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        $('#applicationsTable').DataTable({
            "order": [[ 5, "desc" ]],
            "pageLength": 10,
            "language": {
                "search": "Quick Filter:"
            }
        });
    });
</script>
</body>
</html>
<?php
$conn->close();
?>
