<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuickMaid - Loading...</title>
    <!-- Favicon -->
    <link rel="icon" href="images/logo.png" type="image/png">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;500;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background: radial-gradient(circle at center, #1a1b26 0%, #0d0f18 100%);
            height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #fff;
            overflow: hidden;
        }

        .splash-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            animation: fadeIn 1s ease-out;
        }

        .logo-container {
            width: 150px;
            height: 150px;
            margin-bottom: 2rem;
            position: relative;
        }

        .logo-container img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 20px;
            box-shadow: 0 0 30px rgba(99, 102, 241, 0.4);
            animation: pulse 2s infinite ease-in-out;
        }

        .app-name {
            font-size: 2.5rem;
            font-weight: 700;
            letter-spacing: 2px;
            background: linear-gradient(135deg, #a5b4fc, #818cf8, #6366f1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
        }

        .version {
            font-size: 0.9rem;
            color: #94a3b8;
            font-weight: 300;
            letter-spacing: 4px;
            margin-bottom: 4rem;
        }

        .progress-wrapper {
            width: 300px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            height: 6px;
            border-radius: 10px;
            overflow: hidden;
            position: relative;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.3);
        }

        .progress-bar {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, #6366f1, #a855f7, #ec4899);
            box-shadow: 0 0 10px #a855f7;
            border-radius: 10px;
            transition: width 0.1s linear;
        }

        .loading-text {
            margin-top: 1rem;
            font-size: 0.85rem;
            color: #cbd5e1;
            font-weight: 300;
            letter-spacing: 1px;
            animation: blink 1.5s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(0.98); box-shadow: 0 0 20px rgba(99, 102, 241, 0.2); }
            50% { transform: scale(1.02); box-shadow: 0 0 40px rgba(99, 102, 241, 0.6); }
            100% { transform: scale(0.98); box-shadow: 0 0 20px rgba(99, 102, 241, 0.2); }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes blink {
            0%, 100% { opacity: 0.6; }
            50% { opacity: 1; }
        }
    </style>
</head>
<body>

<div class="splash-container">
    <div class="logo-container">
        <img src="images/logo.png" alt="QuickMaid Logo">
    </div>
    
    <div class="app-name">QuickMaid</div>
    <div class="version">VERSION 1.0.0</div>

    <div class="progress-wrapper">
        <div class="progress-bar" id="progressBar"></div>
    </div>
    <div class="loading-text" id="loadingText">Initializing application...</div>
</div>

<script>
    const messages = [
        "Initializing application...",
        "Loading secure environment...",
        "Fetching applicant data...",
        "Preparing dashboard...",
        "Almost ready..."
    ];
    
    let progress = 0;
    const progressBar = document.getElementById('progressBar');
    const loadingText = document.getElementById('loadingText');
    
    // Randomize slightly for realistic loading effect
    const interval = setInterval(() => {
        progress += Math.random() * 3;
        
        if (progress > 20 && progress < 40) loadingText.innerText = messages[1];
        else if (progress > 40 && progress < 60) loadingText.innerText = messages[2];
        else if (progress > 60 && progress < 85) loadingText.innerText = messages[3];
        else if (progress > 85) loadingText.innerText = messages[4];

        if (progress >= 100) {
            progress = 100;
            clearInterval(interval);
            setTimeout(() => {
                window.location.href = 'mdi_main.php';
            }, 300); // Tiny pause at 100% before redirect
        }
        
        progressBar.style.width = Math.min(progress, 100) + '%';
    }, 50); // Total load time approx 2.5 - 3 seconds
</script>

</body>
</html>
