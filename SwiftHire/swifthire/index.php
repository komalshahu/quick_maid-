<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuickMaid - Professional Maid Hiring</title>
    <link rel="icon" href="images/logo.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-glow: rgba(79, 70, 229, 0.4);
            --secondary: #e11d48;
            --secondary-glow: rgba(225, 29, 72, 0.4);
            --dark-bg: #0f172a;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: #0f172a;
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* Navbar */
        .navbar-custom {
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(15px);
            border-bottom: 1px solid rgba(255,255,255,0.05);
            padding: 1rem 2rem;
            z-index: 1000;
        }
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            letter-spacing: 1px;
            color: #fff !important;
            display: flex;
            align-items: center;
            text-decoration: none;
        }
        .navbar-brand img { width: 35px; margin-right: 12px; border-radius: 8px;}

        /* Hero Section */
        .hero {
            flex-grow: 1;
            display: flex;
            align-items: center;
            position: relative;
            padding-top: 80px; 
            min-height: 100vh;
        }

        /* Dynamic Background Shapes */
        .hero-bg-shapes {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            z-index: 0; pointer-events: none;
            overflow: hidden;
        }
        
        @keyframes drift1 {
            0% { transform: translate(0, 0) scale(1) rotate(0deg); }
            33% { transform: translate(30px, -50px) scale(1.1) rotate(5deg); }
            66% { transform: translate(-20px, 20px) scale(0.9) rotate(-5deg); }
            100% { transform: translate(0, 0) scale(1) rotate(0deg); }
        }

        @keyframes drift2 {
            0% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(-40px, 40px) scale(1.05); }
            100% { transform: translate(0, 0) scale(1); }
        }

        .shape-1 {
            position: absolute; top: -10%; right: -5%;
            width: 600px; height: 600px;
            background: radial-gradient(circle, var(--primary-glow) 0%, transparent 70%);
            filter: blur(60px);
            animation: drift1 15s infinite ease-in-out;
        }
        .shape-2 {
            position: absolute; bottom: -20%; left: -10%;
            width: 700px; height: 700px;
            background: radial-gradient(circle, rgba(168, 85, 247, 0.3) 0%, transparent 70%);
            filter: blur(80px);
            animation: drift2 20s infinite ease-in-out reverse;
        }
        
        .shape-3 {
            position: absolute; top: 40%; left: 30%;
            width: 400px; height: 400px;
            background: radial-gradient(circle, var(--secondary-glow) 0%, transparent 60%);
            filter: blur(50px);
            animation: drift1 18s infinite ease-in-out;
            opacity: 0.6;
        }

        .hero-content {
            z-index: 1;
            padding: 4rem 0;
            position: relative;
        }

        .hero h1 {
            font-size: 4.5rem;
            font-weight: 700;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, #ffffff 0%, #cbd5e1 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            position: relative;
        }
        .hero h1 span {
            background: linear-gradient(135deg, #818cf8, #c084fc, #f43f5e);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-size: 200% auto;
            animation: shine 3s linear infinite;
        }
        
        @keyframes shine {
            to { background-position: 200% center; }
        }

        .hero p {
            font-size: 1.2rem;
            color: #94a3b8;
            margin-bottom: 2.5rem;
            font-weight: 300;
            max-width: 600px;
            line-height: 1.8;
        }

        /* Buttons */
        .btn-custom {
            font-size: 1.1rem;
            font-weight: 600;
            padding: 0.8rem 2.5rem;
            border-radius: 50px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
        }
        
        .btn-apply {
            background: linear-gradient(135deg, var(--primary), #7c3aed);
            color: white;
            box-shadow: 0 4px 15px var(--primary-glow);
        }
        .btn-apply:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 10px 25px rgba(124, 58, 237, 0.6);
            color: white;
        }

        .btn-portal {
            background: rgba(255, 255, 255, 0.05);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }
        .btn-portal:hover {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.3);
            color: white;
            transform: translateY(-3px);
        }

        /* Feature Cards - Glassmorphism */
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            position: relative;
        }

        .feature-card {
            background: rgba(30, 41, 59, 0.4);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 20px;
            padding: 2rem;
            backdrop-filter: blur(16px);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        
        .feature-card::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 50%; height: 100%;
            background: linear-gradient(to right, transparent, rgba(255,255,255,0.05), transparent);
            transform: skewX(-20deg);
            transition: 0.5s;
        }
        
        .feature-card:hover::before {
            left: 150%;
        }

        .feature-card:hover { 
            transform: translateY(-10px); 
            border-color: rgba(129, 140, 248, 0.4); 
            box-shadow: 0 20px 40px rgba(0,0,0,0.4), 0 0 20px rgba(129, 140, 248, 0.2);
            background: rgba(30, 41, 59, 0.6);
        }

        .icon-box {
            width: 50px; height: 50px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1.2rem;
            background: rgba(129, 140, 248, 0.1);
            color: #818cf8;
            border: 1px solid rgba(129, 140, 248, 0.2);
        }
        
        .card-2 .icon-box { color: #f43f5e; background: rgba(244, 63, 94, 0.1); border-color: rgba(244, 63, 94, 0.2); }
        .card-3 .icon-box { color: #10b981; background: rgba(16, 185, 129, 0.1); border-color: rgba(16, 185, 129, 0.2); }
        .card-4 .icon-box { color: #f59e0b; background: rgba(245, 158, 11, 0.1); border-color: rgba(245, 158, 11, 0.2); }

        .feature-card h5 {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
            color: #fff;
        }
        
        .feature-card p {
            color: #94a3b8;
            font-size: 0.9rem;
            margin-bottom: 0;
            line-height: 1.5;
        }

        /* Floating Badges */
        .floating-badge {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 10px 20px;
            border-radius: 50px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            font-size: 0.9rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            animation: float 6s ease-in-out infinite;
            z-index: 2;
        }

        .badge-1 { top: 15%; right: -5%; animation-delay: 0s; }
        .badge-2 { bottom: 20%; left: -10%; animation-delay: 2s; }
        .badge-3 { top: 50%; right: -15%; animation-delay: 4s; }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }

        .stars { color: #fbbf24; }
        
        /* Stats row slightly below */
        .stats-row {
            display: flex;
            gap: 2rem;
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .stat-item h3 {
            font-size: 2rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 0.2rem;
        }
        .stat-item p {
            font-size: 0.85rem;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0;
        }

        @media (max-width: 991px) {
            .hero h1 { font-size: 3rem; }
            .floating-badge { display: none; }
            .feature-grid { margin-top: 3rem; }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-custom fixed-top w-100">
    <div class="container-fluid px-4">
        <a class="navbar-brand" href="index.php">
            <img src="images/logo.png" alt="Logo" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MCIgaGVpZ2h0PSI0MCIgdmlld0JveD0iMCAwIDQwIDQwIj48cmVjdCB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIGZpbGw9IiM0ZjQ2ZTUiIHJ4PSI4Ii8+PHBhdGggZD0iTTIwIDI4bDgtMTBIMTJMMjAgaHoiIGZpbGw9IiNmZmYiLz48L3N2Zz4='"> QuickMaid
        </a>
        <div class="d-flex gap-3">
            <a href="login.php" class="btn btn-portal px-4 rounded-pill">Log In</a>
            <a href="register.php?user_type=maid" class="btn btn-apply px-4 rounded-pill">Sign Up</a>
        </div>
    </div>
</nav>

<div class="hero">
    <div class="hero-bg-shapes">
        <div class="shape-1"></div>
        <div class="shape-2"></div>
        <div class="shape-3"></div>
    </div>
    
    <div class="container hero-content">
        <div class="row align-items-center justify-content-between">
            <div class="col-lg-6 mb-5 mb-lg-0 relative">
                
                <div class="floating-badge badge-1">
                    <div class="stars">★★★★★</div>
                    <span>4.9/5 Average Rating</span>
                </div>
                
                <div class="floating-badge badge-2">
                    <i class="fas fa-shield-check text-success"></i>
                    <span>100% Background Checked</span>
                </div>
                
                <h1>Find Trusted Maids for Your Home, <span>Instantly.</span></h1>
                <p>Welcome to QuickMaid. The premier platform connecting you with thoroughly vetted, top-rated professional maids for spotless cleaning and dedicated household support.</p>
                
                <div class="d-flex flex-wrap gap-3">
                    <a href="register.php?user_type=maid" class="btn btn-custom btn-portal"><i class="fas fa-hands-holding-circle"></i> Join as a Maid</a>
                    <a href="register.php?user_type=owner" class="btn btn-custom btn-apply"><i class="fas fa-user-tie"></i> Hire a Maid - Sign Up</a>
                </div>
                
                <div class="stats-row">
                    <div class="stat-item">
                        <h3>10k+</h3>
                        <p>Happy Homes</p>
                    </div>
                    <div class="stat-item">
                        <h3>5k+</h3>
                        <p>Verified Maids</p>
                    </div>
                    <div class="stat-item">
                        <h3>24/7</h3>
                        <p>Support</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-5 position-relative">
                <div class="floating-badge badge-3">
                    <i class="fas fa-clock text-warning"></i>
                    <span>Same Day Availability</span>
                </div>
                
                <div class="feature-grid">
                    <div class="feature-card card-1 mt-4">
                        <div class="icon-box"><i class="fas fa-shield-halved"></i></div>
                        <h5>Verified Pros</h5>
                        <p>Every maid undergoes strict background and identity checks.</p>
                    </div>
                    <div class="feature-card card-2">
                        <div class="icon-box"><i class="fas fa-sparkles"></i></div>
                        <h5>Spotless Clean</h5>
                        <p>Experience premium cleaning with high-quality standards.</p>
                    </div>
                    <div class="feature-card card-3 mt-4">
                        <div class="icon-box"><i class="fas fa-bolt"></i></div>
                        <h5>Quick Booking</h5>
                        <p>Schedule your maid in under 60 seconds with our platform.</p>
                    </div>
                    <div class="feature-card card-4">
                        <div class="icon-box"><i class="fas fa-star"></i></div>
                        <h5>Top Rated</h5>
                        <p>Consistently 5-star rated services by our community.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
