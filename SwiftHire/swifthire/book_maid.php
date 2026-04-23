<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$employer_id = $_SESSION['user_id'];
$maid_id = $_GET['id'] ?? null;
$error = '';
$success = '';

if (!$maid_id) {
    header("Location: job_board.php");
    exit;
}

// Fetch maid info
$stmt = $conn->prepare("SELECT m.*, u.firstname, u.lastname FROM maids m JOIN users u ON m.user_id = u.id WHERE m.id = ?");
$stmt->bind_param("i", $maid_id);
$stmt->execute();
$maid = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$maid) {
    $error = "Maid not found.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $maid) {
    $start_date = $_POST['start_date'];
    $notes = htmlspecialchars($_POST['notes']);
    
    if (empty($start_date)) {
        $error = "Start date is required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO bookings (employer_id, maid_id, start_date, notes, status) VALUES (?, ?, ?, ?, 'Pending')");
        $stmt->bind_param("iiss", $employer_id, $maid_id, $start_date, $notes);
        
        if ($stmt->execute()) {
            $success = "Booking request submitted successfully! The agency will schedule a trial shortly. Check your Dashboard.";
            
            // Notify Maid
            $maid_user_id = $maid['user_id'];
            $owner_name = $_SESSION['user_firstname'] ?? 'An Owner';
            
            $notif_msg = "You have a new booking request from $owner_name! Check your messages.";
            $notif_stmt = $conn->prepare("INSERT INTO notifications (user_id, type, message_body) VALUES (?, 'request', ?)");
            $notif_stmt->bind_param("is", $maid_user_id, $notif_msg);
            $notif_stmt->execute();
            $notif_stmt->close();

            // Auto-init chat
            $init_msg = "Hi! I have requested a booking with you starting on $start_date. Notes: $notes";
            $msg_stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, job_id, message_text) VALUES (?, ?, 0, ?)");
            $msg_stmt->bind_param("iis", $employer_id, $maid_user_id, $init_msg);
            $msg_stmt->execute();
            $msg_stmt->close();
        } else {
            $error = "Error: " . $conn->error;
        }
        $stmt->close();
    }
}
$hide_nav = isset($_GET['nomdi']) && $_GET['nomdi'] == '1';
$nomdi_param = $hide_nav ? '?nomdi=1' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Request Maid - QuickMaid</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #f0f2f5; }
        .form-panel { max-width: 600px; margin: 3rem auto; background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .btn-primary { background: linear-gradient(135deg, #4f46e5, #7c3aed); border: none; }
        .maid-card { background: #f8fafc; padding: 1.5rem; border-radius: 10px; margin-bottom: 1.5rem; border: 1px solid #e2e8f0; }
    </style>
</head>
<body>
<?php if(!$hide_nav): ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">QuickMaid Form</a>
    <a href="job_board.php" class="btn btn-outline-light">Back to Search</a>
  </div>
</nav>
<?php endif; ?>

<div class="container">
    <div class="form-panel">
        <h2 class="mb-4">Request a Maid</h2>
        
        <?php if($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
        <?php if($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
        
        <?php if($maid): ?>
            <div class="maid-card">
                <h4 style="font-weight: 700; color: #1e293b;"><?php echo htmlspecialchars($maid['firstname'] . ' ' . $maid['lastname']); ?></h4>
                <div><strong>Service:</strong> <?php echo htmlspecialchars($maid['service_category']); ?></div>
                <div><strong>Location:</strong> <?php echo htmlspecialchars($maid['location_area']); ?></div>
                <div><strong>Expected Salary:</strong> ₹<?php echo htmlspecialchars($maid['expected_salary']); ?>/mo</div>
            </div>
            
            <?php if(!$success): ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Required Start Date</label>
                    <input type="date" name="start_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Additional Notes/Requirements</label>
                    <textarea name="notes" class="form-control" rows="4" placeholder="Mention any specific duties or timings..." required></textarea>
                </div>
                <button type="submit" class="btn btn-primary w-100 mt-2 p-2 fw-bold">Submit Booking Request</button>
            </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
