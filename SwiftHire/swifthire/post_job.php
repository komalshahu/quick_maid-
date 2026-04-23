<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $job_title = htmlspecialchars($_POST['job_title'] ?? '');
    $location = htmlspecialchars($_POST['location'] ?? '');
    $category = htmlspecialchars($_POST['category'] ?? 'All-Rounder');
    $salary_range = htmlspecialchars($_POST['budget'] ?? '');
    $working_time = htmlspecialchars($_POST['timing'] ?? '');
    $work_types = isset($_POST['tasks']) ? implode(', ', $_POST['tasks']) : '';
    $description = htmlspecialchars($_POST['description'] ?? '');

    if (empty($job_title) || empty($location)) {
        $error = "Job Title and Location are required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO vacancies (user_id, job_title, company_name, country, location, category, salary_range, working_time, work_types, description) VALUES (?, ?, 'Private Home', 'India', ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssss", $user_id, $job_title, $location, $category, $salary_range, $working_time, $work_types, $description);
        
        if ($stmt->execute()) {
            $success = "Job posted successfully! Maids can now apply to this post.";
        } else {
            $error = "Error: " . $conn->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Post a Job - QuickMaid</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #f8fafc; color: #1e293b; }
        .navbar-custom { background: #0f172a; padding: 1rem 2rem; }
        .form-panel { background: white; border-radius: 20px; padding: 2.5rem; box-shadow: 0 10px 30px rgba(0,0,0,0.05); margin-top: 2rem; margin-bottom: 2rem; }
        .btn-primary { background: linear-gradient(135deg, #4f46e5, #7c3aed); border: none; padding: 0.8rem; font-weight: 600; }
        .btn-primary:hover { box-shadow: 0 8px 25px rgba(79, 70, 229, 0.4); transform: translateY(-2px); }
        .form-control, .form-select { border-radius: 12px; padding: 0.75rem 1rem; border: 1px solid #e2e8f0; }
        .form-control:focus, .form-select:focus { border-color: #818cf8; box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15); }
    </style>
</head>
<body>
<nav class="navbar navbar-dark navbar-custom">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="owner_dashboard.php"><i class="fas fa-home me-2"></i> QuickMaid Dashboard</a>
        <a href="owner_inbox.php" class="btn btn-outline-light rounded-pill px-4">My Inbox</a>
    </div>
</nav>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="form-panel">
                <h2 class="mb-4 fw-bold">Post a Job</h2>
                <p class="text-muted mb-4">Fill out the details below to find the perfect maid for your needs.</p>
                
                <?php if($error): ?><div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?></div><?php endif; ?>
                <?php if($success): ?><div class="alert alert-success"><i class="fas fa-check-circle me-2"></i> <?php echo $success; ?></div><?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Job Title</label>
                        <input type="text" name="job_title" class="form-control" placeholder="e.g. Experienced Housekeeper Needed" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Location</label>
                            <input type="text" name="location" class="form-control" placeholder="City or Neighborhood" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Budget (Monthly)</label>
                            <input type="text" name="budget" class="form-control" placeholder="e.g. ₹15,000" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Date / Timing</label>
                            <input type="text" name="timing" class="form-control" placeholder="e.g. 9 AM - 5 PM, Mon-Fri" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Primary Category</label>
                            <select name="category" class="form-select">
                                <option value="Cleaning">Cleaning</option>
                                <option value="Cooking">Cooking</option>
                                <option value="Babysitting">Babysitting</option>
                                <option value="All-Rounder" selected>All-Rounder</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Specific Tasks</label>
                        <div class="d-flex flex-wrap gap-3 mt-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="tasks[]" value="Cleaning" id="task1">
                                <label class="form-check-label" for="task1">Cleaning</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="tasks[]" value="Cooking" id="task2">
                                <label class="form-check-label" for="task2">Cooking</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="tasks[]" value="Laundry" id="task3">
                                <label class="form-check-label" for="task3">Laundry</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="tasks[]" value="Babysitting" id="task4">
                                <label class="form-check-label" for="task4">Babysitting</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="tasks[]" value="Elder Care" id="task5">
                                <label class="form-check-label" for="task5">Elder Care</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Additional Description</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Any specific requirements or home details..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Publish Job Post</button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
