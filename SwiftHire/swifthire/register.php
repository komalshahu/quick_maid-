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

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $error = "Email already registered!";
    } else {
        $stmt->close();
        $stmt = $conn->prepare("INSERT INTO users (firstname, lastname, email, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $firstname, $lastname, $email, $password);
        if ($stmt->execute()) {
            $success = "Registration successful! You can now <a href='login.php'>log in</a>.";
        } else {
            $error = "Error: " . $conn->error;
        }
    }
    if(isset($stmt)) $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Job Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .card { width: 100%; max-width: 500px; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border: none; }
    </style>
</head>
<body>
<div class="card">
    <h2 class="text-center mb-4">Applicant Registration</h2>
    <?php if($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
    <?php if($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
    <form method="POST">
        <div class="row mb-3">
            <div class="col"><label class="form-label">First Name</label><input type="text" name="firstname" class="form-control" required></div>
            <div class="col"><label class="form-label">Last Name</label><input type="text" name="lastname" class="form-control" required></div>
        </div>
        <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
        <button type="submit" class="btn btn-primary w-100">Register</button>
    </form>
    <div class="text-center mt-3"><a href="login.php" class="text-muted">Already have an account? Log in</a></div>
</div>
</body>
</html>
