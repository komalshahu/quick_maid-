<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$first_name = htmlspecialchars($_SESSION['user_firstname']);

// Fetch user's applications
$stmt = $conn->prepare("SELECT * FROM job_applications WHERE user_id = ? ORDER BY applied_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$app_count = $result->num_rows;

$hide_nav = isset($_GET['nomdi']) && $_GET['nomdi'] == '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Tracking - QuickMaid</title>
    <link rel="icon" href="images/logo.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-glow: rgba(99, 102, 241, 0.4);
            --dark-bg: #0f172a;
            --glass: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.1);
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
            color: #f8fafc;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .dashboard-wrapper { padding: 3rem 0; }

        /* Header Styling */
        .page-header { margin-bottom: 3rem; position: relative; }
        .page-header h2 { font-weight: 700; font-size: 2.5rem; background: linear-gradient(to right, #fff, #a5b4fc); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .page-header p { color: #94a3b8; font-size: 1.1rem; }

        .header-stats { display: flex; gap: 2rem; margin-top: 1.5rem; }
        .stat-item { background: var(--glass); border: 1px solid var(--glass-border); padding: 10px 20px; border-radius: 12px; backdrop-filter: blur(10px); }
        .stat-val { font-weight: 700; color: #818cf8; }
        .stat-label { font-size: 0.8rem; color: #64748b; text-transform: uppercase; margin-left: 5px; }

        /* Application Cards */
        .app-card {
            background: rgba(30, 41, 59, 0.5);
            backdrop-filter: blur(16px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        .app-card:hover {
            transform: translateY(-8px);
            border-color: rgba(99, 102, 241, 0.3);
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }

        .status-badge {
            position: absolute;
            top: 2rem; right: 2rem;
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .status-review { 
            background: rgba(245, 158, 11, 0.1); 
            color: #f59e0b; 
            border: 1px solid rgba(245, 158, 11, 0.2);
            box-shadow: 0 0 15px rgba(245, 158, 11, 0.1);
            animation: pulse-orange 2s infinite;
        }
        .status-received { 
            background: rgba(16, 185, 129, 0.1); 
            color: #10b981; 
            border: 1px solid rgba(16, 185, 129, 0.2); 
        }

        @keyframes pulse-orange {
            0% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(245, 158, 11, 0); }
            100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0); }
        }

        .job-id-text { font-size: 0.85rem; color: #64748b; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 1px; }
        .job-title { font-size: 1.5rem; font-weight: 700; color: #fff; margin-bottom: 1.25rem; }

        .meta-line { display: flex; align-items: center; gap: 10px; margin-bottom: 0.75rem; color: #94a3b8; font-size: 0.95rem; }
        .meta-line i { color: #818cf8; width: 20px; text-align: center; }

        .message-box {
            background: rgba(255, 255, 255, 0.02);
            padding: 1rem;
            border-radius: 12px;
            margin-top: 1.5rem;
            font-size: 0.9rem;
            color: #cbd5e1;
            font-style: italic;
            border-left: 3px solid #4f46e5;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 5rem 2rem;
            background: var(--glass);
            border: 2px dashed var(--glass-border);
            border-radius: 30px;
        }
        .empty-state i { font-size: 4rem; color: #334155; margin-bottom: 1.5rem; }
        .empty-state h3 { font-weight: 700; margin-bottom: 1rem; }

        .btn-portal {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
            padding: 12px 35px;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
            box-shadow: 0 4px 15px var(--primary-glow);
        }
        .btn-portal:hover { transform: translateY(-2px); box-shadow: 0 8px 25px var(--primary-glow); color: white; }

        @media (max-width: 768px) {
            .status-badge { position: relative; top: 0; right: 0; margin-bottom: 1.5rem; display: inline-flex; }
        }
    </style>
</head>
<body>

<div class="container dashboard-wrapper">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            
            <div class="page-header">
                <h2>Application History</h2>
                <p>Track your professional journey and real-time pipeline status.</p>
                <div class="header-stats">
                    <div class="stat-item">
                        <span class="stat-val"><?php echo $app_count; ?></span>
                        <span class="stat-label">Total Submissions</span>
                    </div>
                </div>
            </div>

            <?php if ($app_count > 0): ?>
                <div class="row">
                <?php 
                $i = 0;
                while($row = $result->fetch_assoc()): 
                    $i++;
                    // First application is dynamic "Under Review"
                    $isReview = ($i === 1);
                    $status_class = $isReview ? 'status-review' : 'status-received';
                    $status_text = $isReview ? 'In Review' : 'Received';
                    $status_icon = $isReview ? 'fa-spinner fa-spin' : 'fa-check-circle';
                ?>
                    <div class="col-12">
                        <div class="app-card">
                            <div class="status-badge <?php echo $status_class; ?>">
                                <i class="fas <?php echo $status_icon; ?>"></i> <?php echo $status_text; ?>
                            </div>
                            
                            <div class="job-id-text">Reference ID: #QK-<?php echo str_pad($row['id'], 5, '0', STR_PAD_LEFT); ?></div>
                            <h3 class="job-title">Domestic Service Application</h3>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="meta-line">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span>Submitted: <?php echo date("D, M j, Y | g:i A", strtotime($row['applied_at'])); ?></span>
                                    </div>
                                    <div class="meta-line">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span>Area: <?php echo htmlspecialchars($row['address']); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="meta-line">
                                        <i class="fas fa-phone"></i>
                                        <span>Contact: <?php echo htmlspecialchars($row['areacode'] . ' ' . $row['phone']); ?></span>
                                    </div>
                                    <div class="meta-line">
                                        <i class="fas fa-envelope"></i>
                                        <span>Verified: <?php echo htmlspecialchars($row['email']); ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="message-box">
                                "<?php echo htmlspecialchars($row['message']); ?>"
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-paper-plane"></i>
                    <h3>No Active Submissions</h3>
                    <p class="text-secondary mb-4">You haven't applied for any domestic service roles yet. Start your journey today.</p>
                    <a href="maid_vacancies.php<?php echo $hide_nav ? '?nomdi=1' : ''; ?>" class="btn-portal">Explore Global Vacancies</a>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

</body>
</html>
<?php 
$stmt->close(); 
$conn->close(); 
?>
