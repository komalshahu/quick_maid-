<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'owner') {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Owner Dashboard - QuickMaid</title>
    <!-- Favicon -->
    <link rel="icon" href="images/logo.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #f8fafc; color: #1e293b; }
        .navbar-custom { background: #0f172a; padding: 1rem 2rem; }
        .dashboard-container { margin-top: 3rem; }
        .welcome-card {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
            border-radius: 20px;
            padding: 3rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(79, 70, 229, 0.2);
        }
        .action-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            text-decoration: none;
            color: #1e293b;
            display: block;
            height: 100%;
        }
        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            color: #4f46e5;
        }
        .icon-circle {
            width: 80px; height: 80px;
            background: #f1f5f9;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 2rem; color: #4f46e5;
            margin: 0 auto 1.5rem auto;
        }
        .action-card:hover .icon-circle {
            background: #e0e7ff;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-dark navbar-custom">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="owner_dashboard.php"><i class="fas fa-home me-2"></i> QuickMaid Owner</a>
        <div class="d-flex gap-3 align-items-center">
            <?php include 'notifications_ui.php'; ?>
            <a href="owner_inbox.php" class="btn btn-outline-light rounded-pill px-4"><i class="fas fa-envelope"></i> Inbox</a>
            <a href="logout.php" class="btn btn-light text-dark rounded-pill px-4">Logout</a>
        </div>
    </div>
</nav>

<div class="container dashboard-container">
    <div class="welcome-card">
        <h2 class="fw-bold mb-2">Welcome back, <?php echo htmlspecialchars($_SESSION['user_firstname']); ?>! 👋</h2>
        <p class="mb-0 opacity-75 fs-5">Manage your job postings and find the perfect maid for your home.</p>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <a href="post_job.php" class="action-card">
                <div class="icon-circle"><i class="fas fa-plus-circle"></i></div>
                <h4 class="fw-bold">Post a New Job</h4>
                <p class="text-muted">Create a new vacancy to hire a professional maid.</p>
            </a>
        </div>
        <div class="col-md-4">
            <a href="owner_jobs.php" class="action-card">
                <div class="icon-circle"><i class="fas fa-list"></i></div>
                <h4 class="fw-bold">My Job Posts</h4>
                <p class="text-muted">View and manage the jobs you have already posted.</p>
            </a>
        </div>
        <div class="col-md-4">
            <a href="book_maid.php" class="action-card">
                <div class="icon-circle"><i class="fas fa-search"></i></div>
                <h4 class="fw-bold">Browse Maids</h4>
                <p class="text-muted">Directly search and view profiles of available maids.</p>
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
