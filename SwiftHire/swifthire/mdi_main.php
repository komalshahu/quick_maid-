<?php
session_start();
if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}
$is_admin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
$name = $is_admin ? "Administrator" : htmlspecialchars($_SESSION['user_firstname']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>QuickMaid - Hiring Management System</title>
    <!-- Favicon -->
    <link rel="icon" href="images/logo.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { margin: 0; padding: 0; display: flex; height: 100vh; background-color: #f0f2f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; overflow: hidden; }
        .sidebar { width: 300px; background: linear-gradient(180deg, #1e1e2d 0%, #151521 100%); color: #fff; display: flex; flex-direction: column; box-shadow: 4px 0 10px rgba(0,0,0,0.1); z-index: 10; }
        .sidebar-header { padding: 20px; display: flex; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .sidebar-header img { width: 40px; height: 40px; border-radius: 8px; margin-right: 12px; }
        .sidebar-header h4 { margin: 0; font-size: 1.2rem; font-weight: 600; background: linear-gradient(90deg, #a5b4fc, #818cf8); -webkit-background-clip: text; -webkit-text-fill-color: transparent;}
        .user-info { padding: 15px 20px; font-size: 0.9rem; color: #a1a5b7; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .user-info span { color: #fff; font-weight: 500; }
        .nav-menu { padding: 15px 0; flex-grow: 1; overflow-y: auto; }
        .nav-category { padding: 10px 20px 5px; font-size: 0.75rem; text-transform: uppercase; color: #565674; font-weight: 600; letter-spacing: 1px; }
        .nav-link { padding: 12px 20px; color: #a1a5b7; display: flex; align-items: center; text-decoration: none; transition: all 0.3s; border-left: 3px solid transparent; }
        .nav-link:hover, .nav-link.active { background-color: rgba(255,255,255,0.03); color: #fff; border-left-color: #6366f1; }
        .nav-link i { width: 25px; font-size: 1.1rem; color: #6366f1; }
        .main-frame { flex-grow: 1; display: flex; flex-direction: column; background-color: #f8f9fa; }
        .topbar { height: 60px; background: white; border-bottom: 1px solid #eef0f8; display: flex; align-items: center; justify-content: space-between; padding: 0 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.02); z-index: 5;}
        .topbar h5 { margin: 0; color: #3f4254; font-weight: 600; }
        .mdi-container { flex-grow: 1; padding: 15px; overflow: hidden; background: #eef0f8; }
        .mdi-frame { width: 100%; height: 100%; border: none; border-radius: 10px; background: white; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        
        @keyframes ring {
            0% { transform: rotate(0); }
            10% { transform: rotate(15deg); }
            20% { transform: rotate(-10deg); }
            30% { transform: rotate(5deg); }
            40% { transform: rotate(-5deg); }
            50% { transform: rotate(0); }
            100% { transform: rotate(0); }
        }
        .bell-ringing {
            animation: ring 2s infinite ease-in-out;
            transform-origin: top center;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">
        <img src="images/logo.png" alt="Logo">
        <div>
            <h4>QuickMaid</h4>
            <div style="font-size: 0.7rem; color: #565674; margin-top:2px;">Hiring Management System</div>
        </div>
    </div>
    <div class="user-info">
        Logged in as: <br><span style="font-size: 1rem;"><i class="fas fa-user-circle me-1"></i> <?php echo $name; ?></span>
    </div>
    
    <div class="nav-menu">
        <div class="nav-category">System Modules</div>
        
        <?php if(!$is_admin): ?>
        <a href="profile.php?nomdi=1" target="contentFrame" class="nav-link" onclick="setActive(this, 'My Professional Profile')">
            <i class="fas fa-id-badge"></i> My Profile
        </a>
        <a href="maid_vacancies.php?nomdi=1" target="contentFrame" class="nav-link active" onclick="setActive(this, 'Job Board & Vacancies')">
            <i class="fas fa-search"></i> Job Board & Vacancies
        </a>
        <a href="user_dashboard.php?nomdi=1" target="contentFrame" class="nav-link" onclick="setActive(this, 'Application History')">
            <i class="fas fa-history"></i> Application History
        </a>
        <?php endif; ?>

        <?php if($is_admin): ?>
        <a href="admin.php?nomdi=1" target="contentFrame" class="nav-link active" onclick="setActive(this, 'Module 5/6: Processing / Output Data')">
            <i class="fas fa-database"></i> Datatable / Output
        </a>
        <a href="admin_report.php?nomdi=1" target="contentFrame" class="nav-link" onclick="setActive(this, 'Module 7: Report Analytics Module')">
            <i class="fas fa-chart-pie"></i> Report Module
        </a>
        <?php endif; ?>

        <div class="nav-category mt-4">Session Control</div>
        <a href="#" class="nav-link text-danger" onclick="confirmLogout(event, '<?php echo $is_admin ? 'logout.php' : 'user_logout.php'; ?>')">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>

<div class="main-frame">
    <div class="topbar">
        <div class="d-flex align-items-center">
            <h5 class="m-0 text-secondary fw-normal d-flex align-items-center">
                <i class="fas fa-layer-group me-2" style="color:#a1a5b7;"></i> Workspace 
                <i class="fas fa-chevron-right mx-2 text-muted" style="font-size: 0.8rem;"></i> 
                <span id="moduleTitle" style="color:#4f46e5; font-weight:700;"><?php echo $is_admin ? 'Datatable / Output' : 'Job Board & Vacancies'; ?></span>
            </h5>
        </div>
        <div class="d-flex align-items-center gap-4">
            <span class="text-muted small fw-semibold d-none d-md-block" id="liveClock">Loading time. . .</span>
            <div class="position-relative" style="cursor:pointer;" title="Notifications" onclick="showNotifications()">
                <i class="fas fa-bell bell-ringing" style="color:#a1a5b7; font-size:1.2rem; transition: color 0.2s;" onmouseover="this.style.color='#4f46e5'" onmouseout="this.style.color='#a1a5b7'"></i>
                <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle" style="width:10px; height:10px;"></span>
            </div>
        </div>
    </div>
    <div class="mdi-container">
        <!-- MDI FRAME CONTROLS CENTER CONTENT -->
        <iframe name="contentFrame" class="mdi-frame" src="<?php echo $is_admin ? 'admin.php?nomdi=1' : 'maid_vacancies.php?nomdi=1'; ?>"></iframe>
    </div>
</div>

<script>
    function setActive(el, title) {
        document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
        el.classList.add('active');
        document.getElementById('moduleTitle').innerText = title;
    }

    function confirmLogout(event, url) {
        event.preventDefault();
        Swal.fire({
            title: 'Ready to Leave?',
            text: "Are you sure you want to securely end your session?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#4f46e5', // Theme indigo
            cancelButtonColor: '#ef4444', // Danger red
            confirmButtonText: '<i class="fas fa-sign-out-alt me-1"></i> Yes, Logout',
            cancelButtonText: 'Cancel',
            background: '#1e1e2d', // Match dark sidebar
            color: '#ffffff',
            customClass: {
                popup: 'rounded-4 border border-secondary shadow-lg'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    }

    // Dynamic Live Clock
    function updateClock() {
        const now = new Date();
        const options = { weekday: 'short', month: 'short', day: 'numeric', hour: '2-digit', minute:'2-digit' };
        const timeString = now.toLocaleDateString('en-US', options);
        const clockEl = document.getElementById('liveClock');
        if(clockEl) clockEl.innerText = timeString;
    }
    setInterval(updateClock, 1000);
    updateClock();

    function showNotifications() {
        Swal.fire({
            html: `
                <div style="text-align: left; font-family: 'Outfit', sans-serif;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;">
                        <h5 style="font-weight:700; color:#0f172a; margin:0;">Recent Notifications</h5>
                        <span style="background: #eef2ff; color: #4f46e5; padding: 2px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 600;">3 New</span>
                    </div>

                    <div style="padding: 12px; background:#fff; border: 1px solid #f1f5f9; border-radius:12px; margin-bottom:12px; display:flex; align-items:start; transition: all 0.2s;">
                        <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(16, 185, 129, 0.1); color: #10b981; display:flex; align-items:center; justify-content:center; margin-right:15px; flex-shrink:0;">
                            <i class="fas fa-check-shield" style="font-size:1.2rem;"></i>
                        </div>
                        <div style="flex-grow: 1;">
                            <div style="display: flex; justify-content: space-between;">
                                <div style="font-weight:700; color:#1e293b; font-size:0.95rem;">Profile Verified Successfully</div>
                                <span style="font-size:0.7rem; color:#94a3b8;">2m ago</span>
                            </div>
                            <div style="font-size:0.85rem; color:#64748b; line-height:1.4; margin-top:2px;">Your Aadhar & Identity checks are complete. You now have a "Verified" badge on your profile.</div>
                        </div>
                    </div>

                    <div style="padding: 12px; background:#fff; border: 1px solid #f1f5f9; border-radius:12px; margin-bottom:12px; display:flex; align-items:start;">
                        <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(79, 70, 229, 0.1); color: #4f46e5; display:flex; align-items:center; justify-content:center; margin-right:15px; flex-shrink:0;">
                            <i class="fas fa-briefcase" style="font-size:1.1rem;"></i>
                        </div>
                        <div style="flex-grow: 1;">
                            <div style="display: flex; justify-content: space-between;">
                                <div style="font-weight:700; color:#1e293b; font-size:0.95rem;">New Vacancies in Dubai</div>
                                <span style="font-size:0.7rem; color:#94a3b8;">1h ago</span>
                            </div>
                            <div style="font-size:0.85rem; color:#64748b; line-height:1.4; margin-top:2px;">5 new premium positions matched your "Cook" specialization in Palm Jumeirah.</div>
                        </div>
                    </div>

                    <div style="padding: 12px; background:#fff; border: 1px solid #f1f5f9; border-radius:12px; display:flex; align-items:start;">
                        <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(245, 158, 11, 0.1); color: #f59e0b; display:flex; align-items:center; justify-content:center; margin-right:15px; flex-shrink:0;">
                            <i class="fas fa-calendar-clock" style="font-size:1.1rem;"></i>
                        </div>
                        <div style="flex-grow: 1;">
                            <div style="display: flex; justify-content: space-between;">
                                <div style="font-weight:700; color:#1e293b; font-size:0.95rem;">Trial Session Reminder</div>
                                <span style="font-size:0.7rem; color:#94a3b8;">3h ago</span>
                            </div>
                            <div style="font-size:0.85rem; color:#64748b; line-height:1.4; margin-top:2px;">Don't forget your scheduled trial with Gupta Residence tomorrow at 10:00 AM.</div>
                        </div>
                    </div>
                </div>
            `,
            showConfirmButton: true,
            confirmButtonText: 'Mark all as read',
            confirmButtonColor: '#4f46e5',
            width: 400,
            position: 'top-end',
            backdrop: false,
            showCloseButton: true,
            customClass: { popup: 'shadow-lg border border-light' }
        }).then((result) => {
            if (result.isConfirmed) {
                // If they click mark read, stop the animation and hide the red dot
                document.querySelector('.bell-ringing').classList.remove('bell-ringing');
                document.querySelector('.bg-danger.rounded-circle').style.display = 'none';
            }
        });
    }
</script>

</body>
</html>
