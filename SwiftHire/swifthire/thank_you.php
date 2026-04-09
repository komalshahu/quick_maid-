<?php
session_start();
$hide_nav = isset($_GET['nomdi']) && $_GET['nomdi'] == '1';
$nomdi_param = $hide_nav ? '?nomdi=1' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Received - QuickMaid</title>
    <link rel="icon" href="images/logo.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-glow: rgba(99, 102, 241, 0.4);
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
        }

        .success-card {
            background: rgba(30, 41, 59, 0.5);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 30px;
            padding: 4rem 2rem;
            max-width: 600px;
            width: 90%;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .check-circle {
            width: 100px;
            height: 100px;
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            margin: 0 auto 2rem;
            border: 2px solid rgba(16, 185, 129, 0.2);
            animation: scaleIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) 0.3s both;
        }

        @keyframes scaleIn {
            from { transform: scale(0); }
            to { transform: scale(1); }
        }

        h1 { font-weight: 700; font-size: 2.5rem; margin-bottom: 1rem; color: #fff; }
        p { color: #94a3b8; font-size: 1.1rem; line-height: 1.6; margin-bottom: 2.5rem; }

        .btn-group-custom { display: flex; flex-direction: column; gap: 15px; }

        .btn-primary-custom {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border: none;
            color: white;
            padding: 14px 30px;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
            box-shadow: 0 4px 15px var(--primary-glow);
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px var(--primary-glow);
            color: white;
        }

        .btn-outline-custom {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #f1f5f9;
            padding: 14px 30px;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
        }

        .btn-outline-custom:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.4);
            color: white;
        }

        /* Decorative Background Blobs */
        .blob {
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, var(--primary-glow) 0%, transparent 70%);
            z-index: -1;
            filter: blur(40px);
        }
    </style>
</head>
<body>

<div class="blob" style="top: -10%; left: -10%;"></div>
<div class="blob" style="bottom: -10%; right: -10%;"></div>

<div class="success-card">
    <div class="check-circle">
        <i class="fas fa-check"></i>
    </div>
    <h1>Application Sent!</h1>
    <p>Success! Your application has been reached to our hiring team. We're excited to review your profile and will get back to you shortly.</p>
    
    <div class="btn-group-custom px-md-5">
        <a href="maid_vacancies.php<?php echo $nomdi_param; ?>" class="btn-primary-custom">
            <i class="fas fa-search me-2"></i> Browse More Vacancies
        </a>
        <a href="user_dashboard.php<?php echo $nomdi_param; ?>" class="btn-outline-custom">
            <i class="fas fa-history me-2"></i> View Application History
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
