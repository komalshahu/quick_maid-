<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$hide_nav = isset($_GET['nomdi']) && $_GET['nomdi'] == '1';
$nomdi_param = $hide_nav ? '&nomdi=1' : '';

// Search logic
$category = $_GET['category'] ?? ''; 
$shift = $_GET['shift'] ?? '';

$query = "SELECT m.*, u.firstname, u.lastname FROM maids m JOIN users u ON m.user_id = u.id WHERE 1=1";
$params = [];
$types = "";

if (!empty($category)) {
    $query .= " AND m.service_category = ?";
    $params[] = $category;
    $types .= "s";
}
if (!empty($shift)) {
    $query .= " AND m.shift_preference = ?";
    $params[] = $shift;
    $types .= "s";
}

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$maids = [];
while ($row = $result->fetch_assoc()) {
    $maids[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuickMaid - Find A Maid</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #f0f2f5; color: #1e1e2d; }
        .hero-header { background: linear-gradient(135deg, #1e1e2d 0%, #3f4254 100%); padding: 4rem 0 5rem; color: white; border-radius: 0 0 25px 25px; margin-bottom: 2rem; position: relative;}
        
        .filter-form { background: white; padding: 1.5rem; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.03); max-width: 800px; margin: 0 auto; position: absolute; bottom: 0; left: 50%; transform: translate(-50%, 50%); width: 90%; display: flex; gap: 15px;}
        .filter-form select { flex: 1; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px; outline: none; }
        .filter-form .btn-search { background: #6366f1; color: white; border-radius: 8px; padding: 10px 30px; font-weight: 600; border: none; }
        
        .job-card { background: white; border-radius: 15px; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 5px 15px rgba(0,0,0,0.03); border: 1px solid transparent; transition: all 0.2s; display: flex; align-items: center; justify-content: space-between; }
        .job-card:hover { transform: translateY(-3px); border-color: #a5b4fc; box-shadow: 0 10px 25px rgba(99, 102, 241, 0.15); }
        .profile-icon { width: 60px; height: 60px; border-radius: 50%; background: #e0e7ff; color: #6366f1; display:flex; align-items:center; justify-content:center; font-size: 1.5rem; margin-right: 1.5rem; }
        .job-title { font-weight: 700; font-size: 1.2rem; color: #1e1e2d; margin-bottom: 5px; }
        .company-name { color: #6366f1; font-weight: 600; font-size: 0.95rem; margin-bottom: 8px;}
        .job-meta span { background: #f4f6f9; color: #565674; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; margin-right: 8px; display: inline-block; margin-bottom: 5px;}
        
        .btn-apply { background: white; color: #6366f1; border: 2px solid #6366f1; font-weight: 600; border-radius: 10px; padding: 8px 25px; transition: all 0.2s; text-decoration: none; margin-right: 8px;}
        .btn-apply:hover { background: #6366f1; color: white; }
        .btn-chat { background: #10b981; color: white; border: 2px solid #10b981; font-weight: 600; border-radius: 10px; padding: 8px 25px; transition: all 0.2s; text-decoration: none; }
        .btn-chat:hover { background: #059669; border-color: #059669; color: white; }
        .button-group { display: flex; align-items: center; gap: 8px; }
    </style>
</head>
<body>

<?php if(!$hide_nav): ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="<?php echo (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'owner') ? 'owner_dashboard.php' : 'index.php'; ?>">QuickMaid Portal</a>
    <div class="d-flex">
        <?php if(isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'owner'): ?>
            <a href="owner_dashboard.php" class="btn btn-outline-light me-2">Dashboard</a>
            <a href="owner_jobs.php" class="btn btn-outline-light me-2">My Job Posts</a>
        <?php else: ?>
            <a href="user_dashboard.php" class="btn btn-outline-light me-2">Dashboard</a>
            <a href="register_maid.php" class="btn btn-warning me-2 fw-bold text-dark">Register as Maid</a>
        <?php endif; ?>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
  </div>
</nav>
<?php endif; ?>

<div class="hero-header text-center">
    <div class="container">
        <h1 style="font-weight: 700;">Find Trusted Professionals</h1>
        <p style="color: #a1a5b7; font-size: 1.1rem; margin-bottom: 2rem;">Hire verified maids for cooking, cleaning, and babysitting</p>
    </div>
    
    <form class="filter-form" method="GET">
        <?php if($hide_nav): ?><input type="hidden" name="nomdi" value="1"><?php endif; ?>
        <select name="category">
            <option value="">All Services</option>
            <option value="Cleaning" <?php if($category=='Cleaning') echo 'selected';?>>Cleaning</option>
            <option value="Cooking" <?php if($category=='Cooking') echo 'selected';?>>Cooking</option>
            <option value="Babysitting" <?php if($category=='Babysitting') echo 'selected';?>>Babysitting</option>
            <option value="All-Rounder" <?php if($category=='All-Rounder') echo 'selected';?>>All-Rounder</option>
        </select>
        <select name="shift">
            <option value="">Any Shift</option>
            <option value="Live-out" <?php if($shift=='Live-out') echo 'selected';?>>Live-out</option>
            <option value="Live-in" <?php if($shift=='Live-in') echo 'selected';?>>Live-in</option>
        </select>
        <button type="submit" class="btn-search"><i class="fas fa-search me-2"></i>Filter</button>
    </form>
</div>

<div class="container" style="margin-top: 5rem;">
    <div class="row justify-content-center">
        <div class="col-md-10">
            
            <?php if(empty($maids)): ?>
                <div class="text-center p-5 text-muted">
                    <i class="fas fa-search fa-3x mb-3"></i>
                    <h4>No maids found matching your criteria.</h4>
                </div>
            <?php else: ?>
                <?php foreach($maids as $m): ?>
                    <div class="job-card">
                        <div class="d-flex align-items-center">
                            <div class="profile-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <div class="company-name"><?php echo htmlspecialchars($m['firstname'] . ' ' . $m['lastname']); ?></div>
                                <div class="job-title"><?php echo htmlspecialchars($m['service_category']); ?> Specialist</div>
                                <div class="job-meta">
                                    <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($m['location_area']); ?></span>
                                    <span><i class="fas fa-clock"></i> <?php echo htmlspecialchars($m['shift_preference']); ?></span>
                                    <span><i class="fas fa-rupee-sign"></i> <?php echo htmlspecialchars($m['expected_salary']); ?>/mo</span>
                                    <span><i class="fas fa-briefcase"></i> <?php echo htmlspecialchars($m['experience_years']); ?> Years Exp</span>
                                    <span><i class="fas fa-utensils"></i> <?php echo isset($m['diet_preference']) ? htmlspecialchars($m['diet_preference']) : 'Any'; ?></span>
                                    <?php if($m['verification_status'] == 'Aadhar Verified' || $m['verification_status'] == 'Police Verified'): ?>
                                        <span class="text-success bg-white border border-success"><i class="fas fa-check-circle"></i> Verified</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="button-group">
                            <a href="messages.php?user_id=<?php echo $m['user_id']; ?><?php echo $nomdi_param; ?>" class="btn btn-chat"><i class="fas fa-comments me-2"></i>Chat</a>
                            <a href="book_maid.php?id=<?php echo $m['id']; ?><?php echo $nomdi_param; ?>" class="btn btn-apply">Request Maid</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>
    </div>
</div>

</body>
</html>
