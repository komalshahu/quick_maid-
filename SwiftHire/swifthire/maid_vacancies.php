<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$hide_nav = isset($_GET['nomdi']) && $_GET['nomdi'] == '1';
$nomdi_param = $hide_nav ? '&nomdi=1' : '';

// Filtering Logic
$keyword = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$location = $_GET['location'] ?? '';

// Detect whether vacancies table has a user_id column to join poster info
$has_user_id = false;
$col_check = $conn->query("SHOW COLUMNS FROM vacancies LIKE 'user_id'");
if ($col_check && $col_check->num_rows > 0) {
    $has_user_id = true;
}

if ($has_user_id) {
    $query = "SELECT v.*, u.id AS poster_user_id, u.firstname, u.lastname FROM vacancies v LEFT JOIN users u ON v.user_id = u.id WHERE v.country = 'India'";
} else {
    $query = "SELECT v.*, NULL AS poster_user_id, NULL AS firstname, NULL AS lastname FROM vacancies v WHERE v.country = 'India'";
}
$params = [];
$types = "";

if (!empty($keyword)) {
    $query .= " AND (job_title LIKE ? OR company_name LIKE ? OR description LIKE ?)";
    $searchTerm = "%$keyword%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "sss";
}

if (!empty($category)) {
    $query .= " AND category = ?";
    $params[] = $category;
    $types .= "s";
}

if (!empty($location)) {
    $query .= " AND location = ?";
    $params[] = $location;
    $types .= "s";
}

$query .= " ORDER BY created_at DESC LIMIT 50";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$vacancies = [];
while ($row = $result->fetch_assoc()) {
    $vacancies[] = $row;
}
$total_jobs = count($vacancies);
$stmt->close();

// Get unique locations for filter in India
$loc_res = $conn->query("SELECT DISTINCT location FROM vacancies WHERE country = 'India' ORDER BY location");
$india_locations = [];
while($l = $loc_res->fetch_assoc()) { $india_locations[] = $l['location']; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Openings - QuickMaid</title>
    <link rel="icon" href="images/logo.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-glow: rgba(99, 102, 241, 0.2);
            --dark-surface: #1e1e2d;
            --bg-body: #f8f9fa;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-body);
            color: #1e293b;
            overflow-x: hidden;
        }

        .hero-section {
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
            padding: 4rem 0 7rem;
            color: white;
            border-radius: 0 0 40px 40px;
            position: relative;
            overflow: hidden;
        }

        /* Glassmorphism Search Bar */
        .search-container {
            position: absolute;
            bottom: -35px;
            left: 50%;
            transform: translateX(-50%);
            width: 90%;
            max-width: 1000px;
            z-index: 10;
        }

        .filter-glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 1.25rem;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            border: 1px solid rgba(255,255,255,0.5);
        }

        .form-control-custom {
            border:1px solid #e2e8f0;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            font-weight: 500;
        }

        .form-control-custom:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px var(--primary-glow);
        }

        .btn-search {
            background: var(--primary);
            color: white;
            border-radius: 12px;
            padding: 0.75rem 2rem;
            font-weight: 700;
            border: none;
            transition: all 0.3s;
        }

        .btn-search:hover {
            background: #4f46e5;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.4);
        }

        /* Job Cards */
        .job-grid {
            margin-top: 6rem;
            margin-bottom: 5rem;
        }

        .result-count {
            margin-bottom: 2rem;
            font-weight: 600;
            color: #64748b;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .result-count span {
            background: var(--primary);
            color: white;
            padding: 2px 12px;
            border-radius: 50px;
            font-size: 0.9rem;
        }

        .job-card {
            background: white;
            border-radius: 24px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(0,0,0,0.03);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
        }

        .job-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.08);
            border-color: rgba(99, 102, 241, 0.3);
        }

        .job-card::after {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 100px; height: 100px;
            background: radial-gradient(circle at top right, var(--primary-glow), transparent 70%);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .job-card:hover::after { opacity: 1; }

        .category-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 1rem;
        }

        .badge-cleaning { background: #ecfdf5; color: #059669; }
        .badge-cooking { background: #fff7ed; color: #d97706; }
        .badge-babysitting { background: #fdf2f8; color: #db2777; }
        .badge-all-rounder { background: #eef2ff; color: #4f46e5; }

        .job-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.5rem;
        }

        .company-info {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #64748b;
            font-weight: 500;
            margin-bottom: 1.5rem;
        }

        .job-details {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px dashed #e2e8f0;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            color: #475569;
            background: #f1f5f9;
            padding: 6px 14px;
            border-radius: 12px;
            font-weight: 500;
        }

        .detail-item i { color: var(--primary); }

        .job-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .salary-text {
            font-size: 1.25rem;
            font-weight: 700;
            color: #0f172a;
        }

        .salary-text small {
            font-size: 0.8rem;
            color: #64748b;
            font-weight: 500;
            display: block;
        }

        .btn-apply-job {
            background: #0f172a;
            color: white;
            padding: 10px 28px;
            border-radius: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
        }

        .btn-apply-job:hover {
            background: var(--primary);
            color: white;
            box-shadow: 0 10px 20px var(--primary-glow);
        }
        
        .btn-chat-owner {
            background: #10b981;
            color: white;
            padding: 10px 20px;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-chat-owner:hover {
            background: #059669;
            box-shadow: 0 10px 20px rgba(16, 185, 129, 0.25);
            color: white;
            text-decoration: none;
        }

        .job-actions {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-section { padding-bottom: 10rem; }
            .filter-glass { padding: 1rem; }
            .form-control-custom { margin-bottom: 10px; }
            .job-card { padding: 1.5rem; }
        }

        /* Chat modal */
        .chat-messages {
            height: 360px;
            overflow-y: auto;
            background: #f8fafc;
            border-radius: 14px;
            padding: 14px;
            border: 1px solid #e2e8f0;
        }
        .chat-bubble {
            max-width: 75%;
            padding: 10px 14px;
            border-radius: 16px;
            word-wrap: break-word;
            white-space: pre-wrap;
        }
        .chat-bubble.me {
            background: var(--primary);
            color: #fff;
            border-bottom-right-radius: 6px;
        }
        .chat-bubble.them {
            background: #e2e8f0;
            color: #0f172a;
            border-bottom-left-radius: 6px;
        }
        .chat-time {
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 4px;
        }
    </style>
</head>
<body>

<?php if(!$hide_nav): ?>
<nav class="navbar navbar-expand-lg navbar-dark" style="background: #0f172a;">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <img src="images/logo.png" alt="Logo" width="30" height="30" class="me-2"> QuickMaid
        </a>
        <div class="ms-auto d-flex align-items-center gap-3">
            <a href="mdi_main.php" class="btn btn-outline-light btn-sm rounded-pill px-3">Main Workspace</a>
            <a href="user_dashboard.php" class="btn btn-primary btn-sm rounded-pill px-3">My Applications</a>
        </div>
    </div>
</nav>
<?php endif; ?>

<div class="hero-section text-center">
    <div class="container">
        <h1 class="display-4 fw-bold mb-3">Jobs in <span style="color: #818cf8;">India</span></h1>
        <p class="lead opacity-75">Explore premium household job opportunities across India.</p>
    </div>
    
    <div class="search-container">
        <form class="filter-glass" method="GET">
            <?php if($hide_nav): ?><input type="hidden" name="nomdi" value="1"><?php endif; ?>
            <div class="row g-3 align-items-center">
                <div class="col-lg-4">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control form-control-custom border-0 shadow-none ps-0" placeholder="Job title or keywords..." value="<?php echo htmlspecialchars($keyword); ?>">
                    </div>
                </div>
                <div class="col-lg-3">
                    <select name="category" class="form-select form-control-custom">
                        <option value="">All Categories</option>
                        <option value="Cleaning" <?php if($category=='Cleaning') echo 'selected'; ?>>Cleaning Only</option>
                        <option value="Cooking" <?php if($category=='Cooking') echo 'selected'; ?>>Cooking / Chef</option>
                        <option value="Babysitting" <?php if($category=='Babysitting') echo 'selected'; ?>>Nanny / Babysitter</option>
                        <option value="All-Rounder" <?php if($category=='All-Rounder') echo 'selected'; ?>>All-Rounder</option>
                    </select>
                </div>
                <div class="col-lg-3">
                    <select name="location" class="form-select form-control-custom">
                        <option value="">All Locations in India</option>
                        <?php foreach($india_locations as $loc): ?>
                            <option value="<?php echo htmlspecialchars($loc); ?>" <?php if($location==$loc) echo 'selected'; ?>><?php echo htmlspecialchars($loc); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-lg-2">
                    <button type="submit" class="btn btn-search w-100">Find Jobs</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="container job-grid">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            
            <div class="result-count">
                <i class="fas fa-briefcase"></i> Current Openings in India <span><?php echo $total_jobs; ?> Jobs</span>
            </div>

            <?php if(empty($vacancies)): ?>
                <div class="text-center py-5">
                    <img src="https://cdni.iconscout.com/illustration/premium/thumb/not-found-4064375-3363936.png" alt="None" style="max-width:300px; opacity:0.5;">
                    <h3 class="mt-4 fw-bold">No matches found</h3>
                    <p class="text-muted">Try adjusting your filters or search terms.</p>
                    <a href="maid_vacancies.php<?php echo $hide_nav ? '?nomdi=1' : ''; ?>" class="btn btn-primary mt-2">Clear All Filters</a>
                </div>
            <?php else: ?>
                <?php foreach($vacancies as $v): ?>
                    <div class="job-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="category-badge badge-<?php echo strtolower(str_replace(' ', '-', $v['category'])); ?>">
                                    <?php echo $v['category']; ?>
                                </span>
                                <h2 class="job-title"><?php echo htmlspecialchars($v['job_title']); ?></h2>
                                <div class="company-info">
                                    <i class="fas fa-building small"></i> <?php echo htmlspecialchars($v['company_name']); ?>
                                </div>
                            </div>
                            <div class="text-end d-none d-md-block">
                                <span class="badge bg-light text-dark border rounded-pill px-3 py-2 fw-normal">Posted <?php echo date('M d', strtotime($v['created_at'])); ?></span>
                            </div>
                        </div>

                            <div class="job-details">
                                <div class="detail-item">
                                    <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($v['location']); ?>, India
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-clock"></i> <?php echo htmlspecialchars($v['working_time']); ?>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-calendar-alt"></i> <?php echo htmlspecialchars($v['working_days']); ?>
                                </div>
                                <?php if(isset($v['home_type']) && !empty($v['home_type'])): ?>
                                <div class="detail-item" title="Post Owner's Home Diet Type">
                                    <i class="fas fa-home"></i> <?php echo htmlspecialchars($v['home_type']); ?> Home
                                </div>
                                <?php endif; ?>
                                <?php if(isset($v['maid_preference']) && !empty($v['maid_preference'])): ?>
                                <div class="detail-item" title="Required Maid Diet Preference">
                                    <i class="fas fa-utensils"></i> Maid: <?php echo htmlspecialchars($v['maid_preference']); ?>
                                </div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3 px-1">
                                <?php
                                    $work_info = "Cloth cleaning, toilet cleaning, ironing, dusting, sweeping, mopping, and other household works.";
                                    if(isset($v['work_types']) && !empty($v['work_types'])) {
                                        $work_info = htmlspecialchars($v['work_types']);
                                    } elseif (stripos($v['category'], 'Cooking') !== false) {
                                        $work_info = "Cooking full meals, kitchen cleaning, grocery prep, dishwashing, and other kitchen works.";
                                    } elseif (stripos($v['category'], 'Babysitting') !== false) {
                                        $work_info = "Childcare, feeding children, light ironing, playing with kids, and other related works.";
                                    } elseif (stripos($v['category'], 'Cleaning') !== false) {
                                        $work_info = "Cloth cleaning, toilet cleaning, ironing, dusting, sweeping, mopping, and other cleaning works.";
                                    } else {
                                        $work_info = "Cloth cleaning, toilet cleaning, ironing, dusting, basic cooking, and other household works.";
                                    }
                                ?>
                                <div class="work-types-preview" style="font-size: 0.95rem; color: #475569;">
                                    <i class="fas fa-tasks text-primary me-2"></i> <strong>Work:</strong> <?php echo substr($work_info, 0, 45) . '...'; ?> 
                                    <a class="fw-bold" style="color: var(--primary); text-decoration: none; cursor: pointer;" data-bs-toggle="collapse" href="#desc-<?php echo $v['id']; ?>" role="button" aria-expanded="false" aria-controls="desc-<?php echo $v['id']; ?>">Read more</a>
                                </div>
                                <div class="collapse mt-3" id="desc-<?php echo $v['id']; ?>">
                                    <div class="card card-body border-0 shadow-sm rounded-4 text-muted p-4" style="background: #f8fafc;">
                                        <h6 class="fw-bold text-dark mb-2"><i class="fas fa-clipboard-list me-2"></i> Work Types Involved</h6>
                                        <p class="mb-3"><?php echo $work_info; ?></p>
                                        
                                        <h6 class="fw-bold text-dark mb-2"><i class="fas fa-info-circle me-2"></i> Job Description</h6>
                                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($v['description'])); ?></p>
                                    </div>
                                </div>
                            </div>

                        <div class="job-footer">
                            <div class="salary-box">
                                <small>Monthly Package</small>
                                <div class="salary-text"><?php echo htmlspecialchars($v['salary_range']); ?></div>
                            </div>
                            <div class="job-actions">
                                <a
                                    href="job_chat.php?job_id=<?php echo (int)$v['id']; ?><?php echo $hide_nav ? '&nomdi=1' : ''; ?>"
                                    class="btn-chat-owner"
                                    title="Chat with Owner"
                                >
                                    <i class="fas fa-comments"></i> Chat Owner
                                </a>
                                <a href="apply.php?job=<?php echo urlencode($v['job_title']); ?><?php echo $nomdi_param; ?>" class="btn-apply-job">
                                    Apply Now <i class="fas fa-external-link-alt ms-2"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Force reliable navigation inside iframe for chat links.
  document.querySelectorAll('.btn-chat-owner[href]').forEach((link) => {
    link.addEventListener('click', function (e) {
      e.preventDefault();
      window.location.href = this.getAttribute('href');
    });
  });
</script>
</body>
</html>
