<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$first = htmlspecialchars($_SESSION['user_firstname']);
$last = htmlspecialchars($_SESSION['user_lastname']);
$email = htmlspecialchars($_SESSION['user_email']);
$initials = strtoupper(substr($first, 0, 1) . substr($last, 0, 1));

// Check if user is registered as a maid
$stmt = $conn->prepare("SELECT * FROM maids WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$maid_result = $stmt->get_result();
$maid = $maid_result->fetch_assoc();
$is_maid = ($maid !== null);
$stmt->close();

$hide_nav = isset($_GET['nomdi']) && $_GET['nomdi'] == '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professional Profile - QuickMaid</title>
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

        .profile-wrapper { padding: 2rem 0 5rem; }

        /* Premium Card Styling */
        .glass-card {
            background: rgba(30, 41, 59, 0.5);
            backdrop-filter: blur(16px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 2rem;
            margin-bottom: 2rem;
            transition: transform 0.3s ease, border-color 0.3s ease;
        }
        .glass-card:hover { border-color: rgba(99, 102, 241, 0.3); }

        /* Hero Header */
        .hero-banner {
            height: 220px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed, #f43f5e);
            background-size: 200% 200%;
            animation: gradientMove 10s infinite alternate;
            border-radius: 24px 24px 0 0;
            position: relative;
        }
        @keyframes gradientMove {
            0% { background-position: 0% 50%; }
            100% { background-position: 100% 50%; }
        }

        .profile-avatar-wrapper {
            position: relative;
            margin-top: -85px;
            margin-left: 2rem;
            display: inline-block;
        }

        .avatar-circle {
            width: 160px; height: 160px;
            background: #1e293b;
            border: 6px solid #0f172a;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 4rem; font-weight: 700; color: #818cf8;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }

        .verified-badge {
            position: absolute;
            bottom: 10px; right: 10px;
            background: #10b981;
            color: white;
            width: 40px; height: 40px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            border: 4px solid #0f172a;
            font-size: 1.1rem;
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.4);
        }

        .name-heading { font-size: 2.2rem; font-weight: 700; margin-top: 1.5rem; }
        .tagline { font-size: 1.1rem; color: #94a3b8; font-weight: 400; }

        /* Stats Grid */
        .stats-grid { display: flex; gap: 2rem; margin-top: 1.5rem; }
        .stat-box { text-align: left; }
        .stat-value { font-size: 1.5rem; font-weight: 700; color: #fff; display: block; }
        .stat-label { font-size: 0.8rem; text-transform: uppercase; color: #64748b; letter-spacing: 1px; }

        /* Section Title */
        .section-title { font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px; color: #fff; }
        .section-title i { color: #818cf8; }

        /* Pills & Chips */
        .pill-tag {
            display: inline-block;
            background: rgba(99, 102, 241, 0.1);
            color: #818cf8;
            padding: 8px 18px;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-right: 10px;
            margin-bottom: 10px;
            border: 1px solid rgba(99, 102, 241, 0.2);
        }

        /* Verification Steps */
        .step-item { display: flex; align-items: flex-start; gap: 15px; margin-bottom: 1.5rem; }
        .step-icon { width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .step-done { background: rgba(16, 185, 129, 0.1); color: #10b981; }
        .step-pending { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
        .step-text h6 { margin: 0; font-weight: 600; font-size: 0.95rem; }
        .step-text p { margin: 0; font-size: 0.8rem; color: #94a3b8; }

        .btn-premium {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            box-shadow: 0 4px 15px var(--primary-glow);
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        .btn-premium:hover { transform: translateY(-2px); box-shadow: 0 8px 25px var(--primary-glow); color: white; }

        .sidebar-widget { margin-bottom: 2rem; }
    </style>
</head>
<body>

<div class="container profile-wrapper">
    <div class="row justify-content-center">
        <div class="col-lg-11">
            
            <!-- Hero Card -->
            <div class="glass-card p-0 overflow-hidden">
                <div class="hero-banner"></div>
                <div class="profile-avatar-wrapper">
                    <div class="avatar-circle">
                        <?php echo $initials; ?>
                    </div>
                    <?php if($is_maid && $maid['verification_status'] != 'Pending' && $maid['verification_status'] != 'Rejected'): ?>
                        <div class="verified-badge"><i class="fas fa-check"></i></div>
                    <?php endif; ?>
                </div>
                
                <div class="p-4 pt-0 ps-5">
                    <div class="row align-items-end">
                        <div class="col-md-7">
                            <h1 class="name-heading"><?php echo $first . ' ' . $last; ?></h1>
                            <?php if($is_maid): ?>
                                <div class="tagline"><i class="fas fa-sparkles me-2 text-warning"></i> Professional <?php echo $maid['service_category']; ?> Specialist</div>
                            <?php else: ?>
                                <div class="tagline">Explore opportunities to serve and grow with QuickMaid</div>
                            <?php endif; ?>
                            
                            <div class="stats-grid">
                                <div class="stat-box">
                                    <span class="stat-value"><?php echo $is_maid ? $maid['experience_years'] . '+' : '0'; ?></span>
                                    <span class="stat-label">Years Exp.</span>
                                </div>
                                <div class="stat-box">
                                    <span class="stat-value"><?php echo $is_maid ? '₹' . number_format($maid['expected_salary']) : 'N/A'; ?></span>
                                    <span class="stat-label">Asking Salary</span>
                                </div>
                                <div class="stat-box">
                                    <span class="stat-value">5.0</span>
                                    <span class="stat-label">Rating</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5 text-md-end mt-4 mt-md-0">
                            <a href="#" class="btn-premium"><i class="fas fa-edit me-2"></i> Update Portfolio</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Main Content -->
                <div class="col-lg-8">
                    
                    <div class="glass-card">
                        <h2 class="section-title"><i class="fas fa-user-tie"></i> Professional Bio</h2>
                        <p class="text-secondary" style="line-height: 1.8;">
                            Dedicated professional listed on the QuickMaid premium network. Committed to excellence in household management, ensuring a spotless environment and high-quality service standards. Vetted for reliability and performance.
                        </p>
                    </div>

                    <div class="glass-card">
                        <h2 class="section-title"><i class="fas fa-tools"></i> Verified Skills</h2>
                        <div>
                            <span class="pill-tag">Deep Cleaning</span>
                            <span class="pill-tag">Laundry & Pressing</span>
                            <span class="pill-tag">Meal Preparation</span>
                            <span class="pill-tag">Sanitization</span>
                            <span class="pill-tag">Child Safety</span>
                            <span class="pill-tag">Organization</span>
                        </div>
                    </div>

                </div>

                <!-- Sidebar Content -->
                <div class="col-lg-4">
                    
                    <!-- Trust Box -->
                    <div class="glass-card sidebar-widget">
                        <h2 class="section-title"><i class="fas fa-shield-alt"></i> Trust & Safety</h2>
                        
                        <div class="step-item">
                            <div class="step-icon step-done"><i class="fas fa-id-card"></i></div>
                            <div class="step-text">
                                <h6>Identity Linked</h6>
                                <p>Email & Phone Verified</p>
                            </div>
                        </div>

                        <div class="step-item">
                            <div class="step-icon <?php echo ($is_maid && $maid['verification_status'] != 'Pending') ? 'step-done' : 'step-pending'; ?>">
                                <i class="fas fa-fingerprint"></i>
                            </div>
                            <div class="step-text">
                                <h6>Aadhar Verification</h6>
                                <p><?php echo ($is_maid && $maid['verification_status'] != 'Pending') ? 'Verified Successfully' : 'Pending Submission'; ?></p>
                            </div>
                        </div>

                        <div class="step-item">
                            <div class="step-icon <?php echo ($is_maid && $maid['verification_status'] == 'Police Verified') ? 'step-done' : 'step-pending'; ?>">
                                <i class="fas fa-user-shield"></i>
                            </div>
                            <div class="step-text">
                                <h6>Police Clearance</h6>
                                <p><?php echo ($is_maid && $maid['verification_status'] == 'Police Verified') ? 'Verified' : 'Verification Required'; ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Info -->
                    <div class="glass-card sidebar-widget">
                        <h2 class="section-title"><i class="fas fa-info-circle"></i> Service Details</h2>
                        <div class="mb-3">
                            <label class="stat-label">Preffered Shift</label>
                            <div class="fw-bold"><?php echo $is_maid ? $maid['shift_preference'] : 'Not Specified'; ?></div>
                        </div>
                        <div class="mb-3">
                            <label class="stat-label">Availability</label>
                            <div class="fw-bold text-success">Active / Available</div>
                        </div>
                        <div class="mb-0">
                            <label class="stat-label">Service Area</label>
                            <div class="fw-bold"><i class="fas fa-map-marker-alt text-danger me-1"></i> <?php echo $is_maid ? $maid['location_area'] : 'Unknown'; ?></div>
                        </div>
                    </div>

                </div>
            </div>

            <?php if(!$is_maid): ?>
                <div class="text-center mt-4">
                    <div class="glass-card" style="border: 2px dashed rgba(99, 102, 241, 0.4);">
                        <h3>Become a Certified Professional</h3>
                        <p class="text-secondary">Register your service profile to appear in the Job Openings and start accepting bookings.</p>
                        <a href="register_maid.php" class="btn-premium mt-2">Finish Maid Registration</a>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

</body>
</html>
