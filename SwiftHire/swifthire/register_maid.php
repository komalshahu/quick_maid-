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
    $category = $_POST['service_category'];
    $shift = $_POST['shift_preference'];
    $diet = $_POST['diet_preference'];
    $salary = $_POST['expected_salary'];
    $experience = $_POST['experience_years'];
    $location = htmlspecialchars($_POST['location_area']);
    
    // Safety fields
    $phone = trim($_POST['phone']);
    $aadhar_id = trim($_POST['aadhar_id']);
    
    $errors = [];

    // Validation defined in plan
    if (!preg_match("/^[6-9]\d{9}$/", $phone)) {
        $errors[] = "Invalid phone number. Must be exactly 10 digits starting with 6-9.";
    }

    if (!preg_match("/^\d{12}$/", $aadhar_id)) {
        $errors[] = "Invalid Aadhar ID. Must be exactly 12 digits.";
    }

    if (!is_numeric($salary) || $salary <= 0) {
        $errors[] = "Expected salary must be a valid positive number.";
    }

    if (empty($errors)) {
        // Insert into maids
        $stmt = $conn->prepare("INSERT INTO maids (user_id, service_category, shift_preference, expected_salary, experience_years, location_area, diet_preference) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issiiss", $user_id, $category, $shift, $salary, $experience, $location, $diet);
        
        if ($stmt->execute()) {
            $success = "You are now registered as a Service Provider (Maid)! Your verification is Pending.";
        } else {
            $error = "Error: " . $conn->error;
        }
        $stmt->close();
    } else {
        $error = implode("<br>", $errors);
    }
}
$hide_nav = isset($_GET['nomdi']) && $_GET['nomdi'] == '1';
$nomdi_param = $hide_nav ? '?nomdi=1' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register as Maid - QuickMaid</title>
    <!-- Favicon -->
    <link rel="icon" href="images/logo.png" type="image/png">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background: #0f172a; margin: 0; padding: 0; min-height: 100vh; display: flex; overflow-x: hidden; }
        
        /* Left Side: Animated Brand Showcase */
        .brand-panel { 
            flex: 1; background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            position: relative; overflow: hidden; color: white; padding: 3rem;
            min-height: 100vh;
        }
        
        /* Background Animated Graphics */
        .floating-bg { position: absolute; border-radius: 50%; filter: blur(60px); z-index: 1; animation: drift 15s infinite alternate ease-in-out; }
        .blob1 { width: 400px; height: 400px; background: rgba(99, 102, 241, 0.3); top: -100px; left: -100px; animation-duration: 20s; }
        .blob2 { width: 300px; height: 300px; background: rgba(168, 85, 247, 0.2); bottom: -50px; right: -50px; animation-duration: 15s; }
        
        @keyframes drift {
            0% { transform: translateY(0) scale(1); }
            100% { transform: translateY(50px) scale(1.1); }
        }

        /* The Logo Animation */
        .motion-logo {
            width: 120px; z-index: 2; margin-bottom: 2rem;
            animation: floatLogo 4s infinite ease-in-out;
            filter: drop-shadow(0 0 20px rgba(99, 102, 241, 0.6));
        }
        
        @keyframes floatLogo {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-15px) rotate(3deg); }
        }

        /* Inspirational Text */
        .inspire-box { z-index: 2; text-align: center; max-width: 450px; }
        .inspire-title { font-size: 3.5rem; font-weight: 700; background: linear-gradient(to right, #fff, #a5b4fc); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin-bottom: 1rem; line-height: 1.1; }
        .inspire-text { font-size: 1.25rem; color: #cbd5e1; font-weight: 300; opacity: 0; animation: fadeInUp 1.5s ease forwards 0.5s; line-height: 1.6;}
        
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Right Side: Form Panel */
        .form-panel { 
            width: 550px; background: white; padding: 3rem; display: flex; flex-direction: column; justify-content: center;
            box-shadow: -10px 0 30px rgba(0,0,0,0.1); z-index: 5;
            overflow-y: auto;
            max-height: 100vh;
        }

        .auth-title { font-size: 2.2rem; font-weight: 700; color: #1e293b; margin-bottom: 0.2rem; }
        .auth-subtitle { color: #64748b; font-size: 1.1rem; margin-bottom: 2rem; }

        .form-control, .form-select { background: #f8fafc; border: 1px solid #e2e8f0; padding: 0.8rem 1rem; border-radius: 12px; font-size: 0.95rem; color: #334155; }
        .form-control:focus, .form-select:focus { background: white; border-color: #818cf8; box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15); }
        
        .btn-primary { background: linear-gradient(135deg, #4f46e5, #7c3aed); border: none; padding: 1rem; border-radius: 12px; font-weight: 600; font-size: 1.1rem; box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3); transition: all 0.3s; margin-top: 1rem; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(79, 70, 229, 0.5); color:white;}
        
        .link-muted { color: #64748b; text-decoration: none; font-weight: 500; transition: color 0.2s; }
        .link-muted:hover { color: #4f46e5; }
        
        .alert-danger { background: #fee2e2; color: #b91c1c; border: none; border-radius: 10px; padding: 1rem; }
        .alert-success { background: #dcfce7; color: #15803d; border: none; border-radius: 10px; padding: 1rem; }

        .section-title { font-size: 1rem; font-weight: 600; color: #475569; margin-top: 1rem; margin-bottom: 1rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.5rem; }

        @media (max-width: 900px) {
            body { flex-direction: column; }
            .brand-panel { min-height: 40vh; padding: 2rem 1rem; }
            .form-panel { width: 100%; box-shadow: none; align-items: center; max-height: none; }
            .form-wrapper { width: 100%; max-width: 450px; }
            .inspire-title { font-size: 2.5rem; }
            .inspire-text { font-size: 1.1rem; }
        }
    </style>
</head>
<body>

<div class="brand-panel">
    <div class="floating-bg blob1"></div>
    <div class="floating-bg blob2"></div>
    
    <img src="images/logo.png" alt="QuickMaid Logo" class="motion-logo">
    
    <div class="inspire-box">
        <div class="inspire-title">Empower<br>Your Career.</div>
        <div class="inspire-text">Offer your professional cleaning and household services to verified households directly.</div>
    </div>
</div>

<div class="form-panel">
    <div class="form-wrapper">
        <?php if(!$hide_nav): ?>
        <a href="user_dashboard.php" class="link-muted mb-3 d-inline-block"><i class="fas fa-arrow-left me-2"></i>Back to Dashboard</a>
        <?php endif; ?>
        
        <h2 class="auth-title">Register as Maid</h2>
        <p class="auth-subtitle">Complete your profile to start accepting bookings.</p>
        
        <?php if($error): ?><div class="alert alert-danger mb-4"><i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?></div><?php endif; ?>
        <?php if($success): ?><div class="alert alert-success mb-4"><i class="fas fa-check-circle me-2"></i><?php echo $success; ?></div><?php endif; ?>
        
        <form method="POST">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold text-dark" style="font-size: 0.9rem;">Service Category</label>
                    <select name="service_category" class="form-select" required>
                        <option value="Cleaning">Cleaning</option>
                        <option value="Cooking">Cooking</option>
                        <option value="Babysitting">Babysitting</option>
                        <option value="All-Rounder">All-Rounder</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold text-dark" style="font-size: 0.9rem;">Shift Preference</label>
                    <select name="shift_preference" class="form-select" required>
                        <option value="Live-out">Live-out (Day Shift)</option>
                        <option value="Live-in">Live-in (24 Hours)</option>
                    </select>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold text-dark" style="font-size: 0.9rem;">Expected Salary (₹)</label>
                    <input type="number" name="expected_salary" class="form-control" required placeholder="15000">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold text-dark" style="font-size: 0.9rem;">Experience (Years)</label>
                    <input type="number" name="experience_years" class="form-control" required placeholder="2">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold text-dark" style="font-size: 0.9rem;">Location / Area</label>
                    <input type="text" name="location_area" class="form-control" placeholder="e.g. Andheri West" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold text-dark" style="font-size: 0.9rem;">Diet Preference</label>
                    <select name="diet_preference" class="form-select" required>
                        <option value="Any">Any / No Preference</option>
                        <option value="Vegetarian">Strictly Vegetarian</option>
                        <option value="Non-Vegetarian">Non-Vegetarian</option>
                    </select>
                </div>
            </div>
            
            <div class="section-title">Safety & Verification</div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold text-dark" style="font-size: 0.9rem;">Phone Number</label>
                    <input type="text" name="phone" class="form-control" placeholder="10 Digits" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold text-dark" style="font-size: 0.9rem;">Aadhar ID Number</label>
                    <input type="text" name="aadhar_id" class="form-control" placeholder="12 Digits" required>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary w-100">Submit Application <i class="fas fa-check-circle ms-2"></i></button>
        </form>
    </div>
</div>

</body>
</html>
