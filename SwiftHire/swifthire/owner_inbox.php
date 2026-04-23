<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch applications for jobs posted by this user
$query = "
    SELECT ja.*, v.job_title, m.rating, m.bio 
    FROM job_applications ja
    JOIN vacancies v ON ja.job_id = v.id
    LEFT JOIN maids m ON ja.user_id = m.user_id
    WHERE v.user_id = ?
    ORDER BY ja.applied_at DESC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$applications = [];
while ($row = $result->fetch_assoc()) {
    $applications[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Owner Inbox - QuickMaid</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #f8fafc; color: #1e293b; }
        .navbar-custom { background: #0f172a; padding: 1rem 2rem; }
        .app-card { background: white; border-radius: 16px; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; }
        .badge-status { padding: 5px 12px; border-radius: 50px; font-weight: 600; font-size: 0.85rem; }
        .status-Pending { background: #fef3c7; color: #d97706; }
        .status-Accepted { background: #dcfce7; color: #15803d; }
        .status-Rejected { background: #fee2e2; color: #b91c1c; }
        .status-Completed { background: #e0e7ff; color: #4338ca; }
        .btn-action { border-radius: 8px; font-weight: 600; padding: 8px 16px; font-size: 0.9rem; }
    </style>
</head>
<body>
<nav class="navbar navbar-dark navbar-custom">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="owner_dashboard.php"><i class="fas fa-inbox me-2"></i> Owner Inbox</a>
        <div class="d-flex gap-2">
            <a href="owner_dashboard.php" class="btn btn-outline-light rounded-pill px-4">Dashboard</a>
            <a href="post_job.php" class="btn btn-primary rounded-pill px-4">Post a Job</a>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h2 class="mb-4 fw-bold">Applicant Management</h2>
    
    <?php if (empty($applications)): ?>
        <div class="text-center py-5">
            <h4 class="text-muted">No applications received yet.</h4>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($applications as $app): ?>
                <div class="col-md-6">
                    <div class="app-card">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($app['firstname'] . ' ' . $app['lastname']); ?></h5>
                                <p class="text-muted mb-0" style="font-size: 0.9rem;">Applied for: <strong><?php echo htmlspecialchars($app['job_title']); ?></strong></p>
                            </div>
                            <span class="badge-status status-<?php echo $app['status']; ?>"><?php echo $app['status']; ?></span>
                        </div>
                        
                        <div class="row g-2 mb-3 text-muted" style="font-size: 0.9rem;">
                            <div class="col-6"><i class="fas fa-star text-warning me-1"></i> Rating: <?php echo $app['rating'] ? number_format($app['rating'], 1) : 'New'; ?></div>
                            <div class="col-6"><i class="fas fa-phone me-1"></i> <?php echo htmlspecialchars($app['phone']); ?></div>
                            <div class="col-6"><i class="fas fa-venus-mars me-1"></i> Gender: <?php echo ucfirst($app['gender']); ?></div>
                            <div class="col-6"><i class="fas fa-birthday-cake me-1"></i> Age: <?php echo $app['age']; ?></div>
                            <div class="col-12"><i class="fas fa-envelope me-1"></i> <?php echo htmlspecialchars($app['email']); ?></div>
                            <div class="col-12"><i class="fas fa-calendar-check me-1"></i> Available from: <?php echo date('M d, Y', strtotime($app['startdate'])); ?></div>
                            <div class="col-12"><i class="fas fa-map-marker-alt me-1"></i> <?php echo htmlspecialchars($app['address']); ?><?php echo !empty($app['address2']) ? ', ' . htmlspecialchars($app['address2']) : ''; ?></div>
                        </div>
                        
                        <?php if(!empty($app['message'])): ?>
                            <div class="p-3 bg-light rounded mb-3" style="font-size: 0.9rem; border-left: 4px solid #6366f1;">
                                <strong class="d-block mb-1 text-dark"><i class="fas fa-comment-dots me-1"></i> Message from Applicant:</strong>
                                <?php echo nl2br(htmlspecialchars($app['message'])); ?>
                            </div>
                        <?php endif; ?>

                        <div class="d-flex gap-2 mb-3">
                            <?php if(!empty($app['resume_path'])): ?>
                                <a href="<?php echo htmlspecialchars($app['resume_path']); ?>" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill">
                                    <i class="fas fa-file-pdf me-1"></i> View Resume
                                </a>
                            <?php else: ?>
                                <button class="btn btn-outline-secondary btn-sm rounded-pill" disabled>No Resume Uploaded</button>
                            <?php endif; ?>
                            
                            <?php if(!empty($app['bio'])): ?>
                                <button class="btn btn-outline-info btn-sm rounded-pill" data-bs-toggle="collapse" data-bs-target="#bio-<?php echo $app['id']; ?>">
                                    <i class="fas fa-user-circle me-1"></i> View Profile Bio
                                </button>
                            <?php endif; ?>
                        </div>

                        <?php if(!empty($app['bio'])): ?>
                            <div class="collapse mb-3" id="bio-<?php echo $app['id']; ?>">
                                <div class="p-3 bg-light rounded" style="font-size: 0.9rem;">
                                    <strong>Maid Profile Bio:</strong> <?php echo nl2br(htmlspecialchars($app['bio'])); ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <?php if ($app['status'] == 'Pending'): ?>
                                <button onclick="updateStatus(<?php echo $app['id']; ?>, 'Accepted')" class="btn btn-success btn-action"><i class="fas fa-check"></i> Accept Request</button>
                                <button onclick="updateStatus(<?php echo $app['id']; ?>, 'Rejected')" class="btn btn-danger btn-action"><i class="fas fa-times"></i> Reject</button>
                            <?php elseif ($app['status'] == 'Accepted'): ?>
                                <a href="job_chat.php?job_id=<?php echo $app['job_id']; ?>&maid_id=<?php echo $app['user_id']; ?>" class="btn btn-primary btn-action"><i class="fas fa-comments"></i> Chat with Maid</a>
                                <button onclick="markComplete(<?php echo $app['id']; ?>, <?php echo $app['job_id']; ?>, <?php echo $app['user_id']; ?>)" class="btn btn-dark btn-action"><i class="fas fa-check-circle"></i> Mark as Complete</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Review Modal -->
<div class="modal fade" id="reviewModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 bg-dark text-white rounded-top-4">
                <h5 class="modal-title">Rate your Experience</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="submit_review.php" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="application_id" id="review_app_id">
                    <input type="hidden" name="job_id" id="review_job_id">
                    <input type="hidden" name="maid_id" id="review_maid_id">
                    
                    <div class="mb-3 text-center">
                        <label class="form-label fw-bold">Rating (1 to 5)</label>
                        <select name="rating" class="form-select w-50 mx-auto" required>
                            <option value="5">5 - Excellent</option>
                            <option value="4">4 - Good</option>
                            <option value="3">3 - Average</option>
                            <option value="2">2 - Poor</option>
                            <option value="1">1 - Terrible</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Feedback</label>
                        <textarea name="review_text" class="form-control" rows="3" placeholder="Leave a review for this maid..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-primary w-100 fw-bold py-2">Submit Review & Complete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function updateStatus(appId, status) {
    if(confirm('Are you sure you want to ' + status.toLowerCase() + ' this request?')) {
        fetch('update_application.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id=' + appId + '&status=' + status
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                location.reload();
            } else {
                alert(data.message || 'Error updating status');
            }
        });
    }
}

function markComplete(appId, jobId, maidId) {
    document.getElementById('review_app_id').value = appId;
    document.getElementById('review_job_id').value = jobId;
    document.getElementById('review_maid_id').value = maidId;
    var reviewModal = new bootstrap.Modal(document.getElementById('reviewModal'));
    reviewModal.show();
}
</script>
</body>
</html>
