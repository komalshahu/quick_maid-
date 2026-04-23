<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$token = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $token;
$job_title = isset($_GET['job']) ? htmlspecialchars($_GET['job']) : "General Application";
$job_id = isset($_GET['job_id']) ? (int)$_GET['job_id'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Form - <?php echo $job_title; ?></title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f3f4f6;
            color: #1f2937;
        }
        .app-container { max-width: 800px; margin: 3rem auto; }
        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            overflow: hidden;
            background: #ffffff;
        }
        .card-header {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
            font-weight: 600;
            padding: 2rem;
            border-bottom: none;
            text-align: center;
        }
        .card-header h2 { font-weight: 700; margin: 0; font-size: 1.8rem;}
        .card-header p { margin: 5px 0 0; color: #e0e7ff; font-size: 1.1rem;}
        
        .card-body { padding: 3rem; }
        .form-label { font-weight: 600; color: #4b5563; margin-bottom: 8px;}
        .form-control, .form-select {
            border-radius: 10px;
            padding: 0.8rem 1rem;
            border: 1px solid #d1d5db;
            background-color: #f9fafb;
            font-size: 1rem;
        }
        .form-control:focus, .form-select:focus {
            border-color: #818cf8;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
            background-color: #ffffff;
        }
        .form-control[readonly] { background-color: #e5e7eb; color: #6b7280; cursor: not-allowed; }
        
        .btn-apply {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border: none;
            color: white;
            font-weight: 600;
            padding: 1rem 2rem;
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
            transition: all 0.3s;
            width: 100%;
            font-size: 1.2rem;
            margin-top: 1rem;
        }
        .btn-apply:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(79, 70, 229, 0.5);
            color: white;
        }
        .section-title { font-size: 1.3rem; font-weight: 700; color: #111827; margin: 2rem 0 1.5rem; border-bottom: 2px solid #e5e7eb; padding-bottom: 10px;}
    </style>
</head>
<body>

<?php $hide_nav = isset($_GET['nomdi']) && $_GET['nomdi'] == '1'; ?>

<div class="container app-container">
    <div class="card">
        <div class="card-header">
            <h2>Job Application</h2>
            <p>Applying for: <strong><?php echo $job_title; ?></strong></p>
        </div>
        <div class="card-body">
            <form id="contactForm" action="process_form.php" method="POST" enctype="multipart/form-data">
                
                <h4 class="section-title mt-0"><i class="fas fa-user-circle me-2 text-primary"></i> Personal Information</h4>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">First Name</label>
                        <input type="text" name="firstname" value="<?php echo htmlspecialchars($_SESSION['user_firstname']); ?>" class="form-control" readonly>
                    </div>
                    <div class="col-md-6 mt-3 mt-md-0">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="lastname" value="<?php echo htmlspecialchars($_SESSION['user_lastname']); ?>" class="form-control" readonly>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-7">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($_SESSION['user_email']); ?>" class="form-control" readonly>
                    </div>
                    <div class="col-md-5 mt-3 mt-md-0">
                        <label class="form-label">Gender</label>
                        <select name="gender" class="form-select" required>
                            <option value="" disabled selected>Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="others">Other</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Area Code</label>
                        <input type="text" name="areacode" placeholder="+1" class="form-control" required>
                    </div>
                    <div class="col-md-9 mt-3 mt-md-0">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" placeholder="Enter phone number" class="form-control" required>
                    </div>
                </div>
                
                <h4 class="section-title"><i class="fas fa-briefcase me-2 text-primary"></i> Additional Details</h4>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Age</label>
                        <input type="number" min="16" max="100" name="age" placeholder="Your age" class="form-control" required>
                    </div>
                    <div class="col-md-6 mt-3 mt-md-0">
                        <label class="form-label">Available Start Date</label>
                        <input type="date" name="startdate" class="form-control" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Street Address</label>
                    <input type="text" name="address" placeholder="1234 Main St" class="form-control" required>
                </div>
                <div class="mb-4">
                    <label class="form-label">Address Line 2 (Optional)</label>
                    <input type="text" name="address2" placeholder="Apartment, studio, or floor" class="form-control">
                </div>

                <h4 class="section-title"><i class="fas fa-file-alt me-2 text-primary"></i> Documentation</h4>
                <div class="mb-3">
                    <label class="form-label">Cover Letter / Message</label>
                    <textarea rows="5" name="message" class="form-control" placeholder="Tell us why you are the perfect fit for the <?php echo $job_title; ?> role..." required></textarea>
                </div>
                
                <div class="mb-4">
                    <label class="form-label d-block">Upload Resume <span class="text-danger">*</span></label>
                    <input type="file" name="resume" class="form-control" accept=".pdf,.doc,.docx" style="padding: 0.6rem 1rem;" required>
                    <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle"></i> Supported formats: PDF, DOC, DOCX. Max size 2MB.</small>
                </div>
                
                <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
                <input type="hidden" name="nomdi" value="<?php echo $hide_nav ? '1' : '0'; ?>">
                <input type="hidden" name="job_id" value="<?php echo $job_id; ?>">
                
                <button type="submit" name="submit" class="btn-apply"><i class="fas fa-paper-plane me-2"></i> Submit Application</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
