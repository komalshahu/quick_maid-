<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

require 'db.php';

// Calculate Analytics
// Total Applications
$total_result = $conn->query("SELECT COUNT(id) as total FROM job_applications");
$total_apps = $total_result->fetch_assoc()['total'];

// Average Age
$age_result = $conn->query("SELECT AVG(age) as avg_age FROM job_applications");
$avg_age = round($age_result->fetch_assoc()['avg_age'], 1);

// Gender Breakdown
$gender_result = $conn->query("SELECT gender, COUNT(*) as count FROM job_applications GROUP BY gender");
$gender_data = [];
$gender_labels = [];
$gender_counts = [];
while ($row = $gender_result->fetch_assoc()) {
    $gender_labels[] = '"' . htmlspecialchars($row['gender']) . '"';
    $gender_counts[] = $row['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Report Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background-color: #f8f9fa; }
        .navbar { margin-bottom: 2rem; }
        .stat-card {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            text-align: center;
        }
        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            color: #0d6efd;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">SwiftHire Admin</a>
    <div class="collapse navbar-collapse">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item">
                <a class="nav-link" href="admin.php">Data Table</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="admin_report.php">Reports & Analytics</a>
            </li>
        </ul>
    </div>
    <div class="d-flex">
        <a href="logout.php" class="btn btn-outline-light">Logout</a>
    </div>
  </div>
</nav>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Analytics Dashboard</h2>
        <a href="export_csv.php" class="btn btn-success btn-lg">⬇ Download Report (CSV)</a>
    </div>
    
    <div class="row mb-5">
        <div class="col-md-6">
            <div class="stat-card h-100">
                <h5 class="text-muted text-uppercase mb-3">Total Applications</h5>
                <div class="stat-number"><?php echo $total_apps; ?></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="stat-card h-100">
                <h5 class="text-muted text-uppercase mb-3">Average Applicant Age</h5>
                <div class="stat-number"><?php echo empty($avg_age) ? '0' : $avg_age; ?></div>
                <div class="text-muted mt-2">years old</div>
            </div>
        </div>
    </div>

    <div class="row mb-5">
        <div class="col-md-12">
            <div class="stat-card py-4">
                <h5 class="text-muted text-uppercase mb-4">Applicant Gender Demographics</h5>
                <div style="height: 300px; width: 100%; display: flex; justify-content: center;">
                    <canvas id="genderChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const ctx = document.getElementById('genderChart').getContext('2d');
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: [<?php echo implode(',', $gender_labels); ?>],
            datasets: [{
                data: [<?php echo implode(',', $gender_counts); ?>],
                backgroundColor: ['#0d6efd', '#e83e8c', '#ffc107', '#20c997'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });
</script>

</body>
</html>
<?php
$conn->close();
?>
