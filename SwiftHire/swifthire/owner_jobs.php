<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'owner') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch jobs posted by the owner
$query = "SELECT * FROM vacancies WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$vacancies = [];
while ($row = $result->fetch_assoc()) {
    $vacancies[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Job Posts - QuickMaid</title>
    <link rel="icon" href="images/logo.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #f8fafc; color: #1e293b; }
        .navbar-custom { background: #0f172a; padding: 1rem 2rem; }
        .page-header { background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%); color: white; padding: 3rem 0; margin-bottom: 2rem; border-radius: 0 0 20px 20px;}
        .job-card { background: white; border-radius: 20px; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; }
        .category-badge { display: inline-block; padding: 5px 12px; border-radius: 50px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; margin-bottom: 0.5rem; background: #e0e7ff; color: #4f46e5; }
        .job-title { font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem; }
        .detail-item { font-size: 0.9rem; color: #64748b; margin-right: 15px; display: inline-block; }
        .detail-item i { color: #4f46e5; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark navbar-custom">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="owner_dashboard.php"><i class="fas fa-home me-2"></i> QuickMaid Owner</a>
        <div class="d-flex gap-3">
            <a href="owner_dashboard.php" class="btn btn-outline-light rounded-pill px-4">Dashboard</a>
            <a href="owner_inbox.php" class="btn btn-outline-light rounded-pill px-4"><i class="fas fa-envelope"></i> Inbox</a>
            <a href="post_job.php" class="btn btn-primary rounded-pill px-4">Post a Job</a>
        </div>
    </div>
</nav>

<div class="page-header text-center">
    <div class="container">
        <h2 class="fw-bold">My Job Posts</h2>
        <p class="mb-0 opacity-75">View and manage the jobs you have created.</p>
    </div>
</div>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <?php if (empty($vacancies)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-folder-open fa-4x text-muted mb-3 opacity-50"></i>
                    <h4>No job posts yet.</h4>
                    <p class="text-muted">You haven't posted any jobs. Create your first job post to hire a maid.</p>
                    <a href="post_job.php" class="btn btn-primary mt-3 px-4 py-2">Post a New Job</a>
                </div>
            <?php else: ?>
                <?php foreach ($vacancies as $v): ?>
                    <div class="job-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="category-badge"><?php echo htmlspecialchars($v['category']); ?></span>
                                <h3 class="job-title"><?php echo htmlspecialchars($v['job_title']); ?></h3>
                            </div>
                            <span class="badge bg-light text-dark border px-3 py-2">
                                Posted <?php echo date('M d, Y', strtotime($v['created_at'])); ?>
                            </span>
                        </div>
                        <div class="mt-2 mb-3">
                            <span class="detail-item"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($v['location']); ?></span>
                            <span class="detail-item"><i class="fas fa-clock"></i> <?php echo htmlspecialchars($v['working_time']); ?></span>
                            <span class="detail-item"><i class="fas fa-rupee-sign"></i> <?php echo htmlspecialchars($v['salary_range']); ?> / month</span>
                        </div>
                        <div class="text-muted small bg-light p-3 rounded">
                            <strong>Description:</strong> <?php echo nl2br(htmlspecialchars(substr($v['description'], 0, 150))) . '...'; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
