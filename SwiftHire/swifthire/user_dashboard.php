<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user's applications
$stmt = $conn->prepare("SELECT * FROM job_applications WHERE user_id = ? ORDER BY applied_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Applicant Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style> body { background-color: #f8f9fa; } .card { border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.05); } </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">SwiftHire Portal</a>
    <div class="d-flex align-items-center">
        <span class="text-light me-3">Welcome, <?php echo htmlspecialchars($_SESSION['user_firstname']); ?>!</span>
        <a href="index.php" class="btn btn-success me-2">Submit New Application</a>
        <a href="user_logout.php" class="btn btn-danger">Logout</a>
    </div>
  </div>
</nav>

<div class="container">
    <h3 class="mb-4">My Applications</h3>
    
    <?php if ($result->num_rows > 0): ?>
        <div class="row">
        <?php while($row = $result->fetch_assoc()): ?>
            <div class="col-md-6 mb-4">
                <div class="card h-100 p-4">
                    <h5 class="card-title text-primary mb-3">Application #<?php echo $row['id']; ?></h5>
                    <p class="mb-1"><strong>Submitted On:</strong> <?php echo date("F j, Y, g:i a", strtotime($row['applied_at'])); ?></p>
                    <p class="mb-1"><strong>Contact Phone:</strong> <?php echo htmlspecialchars($row['areacode'] . '-' . $row['phone']); ?></p>
                    <p class="mb-3"><strong>Role / Gender:</strong> <?php echo htmlspecialchars($row['gender']); ?></p>
                    <p class="text-muted small border-top pt-3">"<?php echo htmlspecialchars(substr($row['message'], 0, 150)) . '...'; ?>"</p>
                    <div class="mt-auto pt-3 text-end">
                        <span class="badge bg-success px-3 py-2">Received Successfully</span>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info py-4">
            <h5 class="alert-heading">Welcome to the portal!</h5>
            <p>You haven't submitted any job applications yet.</p>
            <hr>
            <a href="index.php" class="btn btn-primary mt-2">Start a New Application</a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
<?php $stmt->close(); $conn->close(); ?>
