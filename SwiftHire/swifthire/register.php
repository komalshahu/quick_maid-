<?php
session_start();
require 'db.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstname = htmlspecialchars($_POST['firstname']);
    $lastname = htmlspecialchars($_POST['lastname']);
    $email = htmlspecialchars($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $user_type = isset($_POST['user_type']) && $_POST['user_type'] === 'maid' ? 'maid' : 'owner';

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $error = "Email already registered!";
    } else {
        $stmt->close();
        $stmt = $conn->prepare("INSERT INTO users (firstname, lastname, email, password, user_type) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $firstname, $lastname, $email, $password, $user_type);
        if ($stmt->execute()) {
            $new_user_id = $stmt->insert_id;
            $_SESSION['user_id'] = $new_user_id;
            $_SESSION['user_firstname'] = $firstname;
            $_SESSION['user_lastname'] = $lastname;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_type'] = $user_type;

            if ($user_type === 'owner') {
                header("Location: owner_dashboard.php");
                exit;
            } else {
                header("Location: splash.php");
                exit;
            }
        } else {
            $error = "Error: " . $conn->error;
        }
    }
    if(isset($stmt)) $stmt->close();
}

$default_user_type = isset($_GET['user_type']) && $_GET['user_type'] === 'maid' ? 'maid' : 'owner';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - QuickMaid</title>
    <!-- Favicon -->
    <link rel="icon" href="images/logo.png" type="image/png">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background: #0f172a; margin: 0; padding: 0; height: 100vh; display: flex; overflow: hidden; }
        
        /* Left Side: Animated Brand Showcase */
        .brand-panel { 
            flex: 1; background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            position: relative; overflow: hidden; color: white; padding: 3rem;
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
            width: 550px; background: white; padding: 4rem 3rem; display: flex; flex-direction: column; justify-content: center;
            box-shadow: -10px 0 30px rgba(0,0,0,0.1); z-index: 5; overflow-y: auto;
        }

        .auth-title { font-size: 2.2rem; font-weight: 700; color: #1e293b; margin-bottom: 0.2rem; }
        .auth-subtitle { color: #64748b; font-size: 1.1rem; margin-bottom: 2.5rem; }

        .form-control { background: #f8fafc; border: 1px solid #e2e8f0; padding: 0.9rem 1.2rem; border-radius: 12px; font-size: 1rem; color: #334155; }
        .form-control:focus { background: white; border-color: #818cf8; box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15); }
        
        .btn-primary { background: linear-gradient(135deg, #4f46e5, #7c3aed); border: none; padding: 1rem; border-radius: 12px; font-weight: 600; font-size: 1.1rem; box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3); transition: all 0.3s; margin-top: 1rem; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(79, 70, 229, 0.5); }
        
        .link-muted { color: #64748b; text-decoration: none; font-weight: 500; transition: color 0.2s; }
        .link-muted:hover { color: #4f46e5; }
        
        .alert-danger { background: #fee2e2; color: #b91c1c; border: none; border-radius: 10px; padding: 1rem; }
        .alert-success { background: #dcfce7; color: #15803d; border: none; border-radius: 10px; padding: 1rem; }

        @media (max-width: 900px) {
            .brand-panel { display: none; }
            .form-panel { width: 100%; box-shadow: none; align-items: center; }
            .form-wrapper { width: 100%; max-width: 450px; }
        }
    </style>
</head>
<body>

<div class="brand-panel">
    <div class="floating-bg blob1"></div>
    <div class="floating-bg blob2"></div>
    
    <img src="images/logo.png" alt="QuickMaid Logo" class="motion-logo">
    
    <div class="inspire-box">
        <div class="inspire-title">Spotless<br>Living.</div>
        <div class="inspire-text">Join thousands of households relying on QuickMaid's verified professionals.</div>
    </div>
</div>

<div class="form-panel">
    <div class="form-wrapper">
        <h2 class="auth-title">Create Account</h2>
        <p class="auth-subtitle">Set up your account to hire maids or provide services.</p>
        
        <?php if($error): ?><div class="alert alert-danger mb-4"><i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?></div><?php endif; ?>
        <?php if($success): ?><div class="alert alert-success mb-4"><i class="fas fa-check-circle me-2"></i><?php echo $success; ?></div><?php endif; ?>
        
        <form method="POST">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold text-dark" style="font-size: 0.9rem;">First Name</label>
                    <input type="text" name="firstname" class="form-control" placeholder="John" required autofocus>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold text-dark" style="font-size: 0.9rem;">Last Name</label>
                    <input type="text" name="lastname" class="form-control" placeholder="Doe" required>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-bold text-dark" style="font-size: 0.9rem;">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="john@example.com" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold text-dark" style="font-size: 0.9rem;">Create Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            
            <div class="mb-4">
                <label class="form-label fw-bold text-dark" style="font-size: 0.9rem;">I want to</label>
                <div class="d-flex gap-4">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="user_type" id="typeClient" value="owner" <?php echo $default_user_type === 'owner' ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="typeClient">Hire a Maid</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="user_type" id="typeMaid" value="maid" <?php echo $default_user_type === 'maid' ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="typeMaid">Work as a Maid</label>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100">Create Account <i class="fas fa-rocket ms-2"></i></button>
        </form>
        
        <div class="text-center mt-4">
            <span class="text-muted">Already have a profile?</span> 
            <a href="login.php" class="link-muted ms-1" style="color:#4f46e5; font-weight:700;">Login here</a>
        </div>
    </div>
</div>

</body>
</html>
