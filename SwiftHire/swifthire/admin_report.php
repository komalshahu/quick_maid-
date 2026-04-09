<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

require 'db.php';

// 1. Average Wage
// We will show average wage overall, and we can also group by service
$avg_wage_res = $conn->query("SELECT AVG(expected_salary) as avg_wage FROM maids");
$avg_wage = round($avg_wage_res->fetch_assoc()['avg_wage'] ?? 0, 2);

// 2. Demand Analysis (Maids by Category)
$cat_result = $conn->query("SELECT service_category, COUNT(*) as count FROM maids GROUP BY service_category");
$cat_labels = [];
$cat_counts = [];
while ($row = $cat_result->fetch_assoc()) {
    $cat_labels[] = '"' . htmlspecialchars($row['service_category']) . '"';
    $cat_counts[] = $row['count'];
}

// 3. Trust & Safety (Verification Status)
$ver_result = $conn->query("SELECT verification_status, COUNT(*) as count FROM maids GROUP BY verification_status");
$ver_labels = [];
$ver_counts = [];
while ($row = $ver_result->fetch_assoc()) {
    $ver_labels[] = '"' . htmlspecialchars($row['verification_status']) . '"';
    $ver_counts[] = $row['count'];
}

// 4. Total Bookings
$book_result = $conn->query("SELECT COUNT(id) as total FROM bookings");
$total_bookings = $book_result->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Analytics - QuickMaid Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background-color: #f8f9fa; font-family: sans-serif; }
        .navbar { margin-bottom: 2rem; }
        .stat-card {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            text-align: center;
            height: 100%;
        }
        .stat-number { font-size: 3rem; font-weight: 700; color: #4f46e5; }
    </style>
</head>
<body>

<?php $hide_nav = isset($_GET['nomdi']) && $_GET['nomdi'] == '1'; ?>
<?php if(!$hide_nav): ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">QuickMaid Admin</a>
    <div class="collapse navbar-collapse">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item"><a class="nav-link active" href="admin_report.php">Reports & Analytics</a></li>
        </ul>
    </div>
    <div class="d-flex"><a href="logout.php" class="btn btn-outline-light">Logout</a></div>
  </div>
</nav>
<?php endif; ?>

<div class="container mt-4 pb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>QuickMaid Analytics Dashboard</h2>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-6 mb-4">
            <div class="stat-card">
                <h5 class="text-muted text-uppercase mb-3">Total Processed Bookings</h5>
                <div class="stat-number"><?php echo $total_bookings; ?></div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="stat-card">
                <h5 class="text-muted text-uppercase mb-3">Average Expected Salary</h5>
                <div class="stat-number text-success">₹<?php echo number_format($avg_wage); ?></div>
                <div class="text-muted mt-2">per month</div>
            </div>
        </div>
    </div>

    <div class="row mb-5">
        <div class="col-md-6">
            <div class="stat-card py-4">
                <h5 class="text-muted text-uppercase mb-4">Demand by Service Category</h5>
                <div style="height: 300px; width: 100%; display: flex; justify-content: center;">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="stat-card py-4">
                <h5 class="text-muted text-uppercase mb-4">Trust & Safety (Verification)</h5>
                <div style="height: 300px; width: 100%; display: flex; justify-content: center;">
                    <canvas id="verificationChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Category Chart
    new Chart(document.getElementById('categoryChart').getContext('2d'), {
        type: 'pie',
        data: {
            labels: [<?php echo implode(',', $cat_labels); ?>],
            datasets: [{
                data: [<?php echo implode(',', $cat_counts); ?>],
                backgroundColor: ['#4f46e5', '#10b981', '#f59e0b', '#ec4899']
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    // Verification Chart
    new Chart(document.getElementById('verificationChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: [<?php echo implode(',', $ver_labels); ?>],
            datasets: [{
                data: [<?php echo implode(',', $ver_counts); ?>],
                backgroundColor: ['#ef4444', '#10b981', '#3b82f6', '#6b7280']
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
</script>

</body>
</html>
