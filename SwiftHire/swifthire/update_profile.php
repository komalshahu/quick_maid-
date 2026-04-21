<?php
session_start();
require 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error'] = "Security validation failed. Please try again.";
    header("Location: profile.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    
    // Validate and sanitize input
    $firstname = isset($_POST['firstname']) ? htmlspecialchars(trim($_POST['firstname'])) : '';
    $lastname = isset($_POST['lastname']) ? htmlspecialchars(trim($_POST['lastname'])) : '';
    $email = isset($_POST['email']) ? htmlspecialchars(trim($_POST['email'])) : '';
    
    // Validate inputs
    if (empty($firstname) || empty($lastname) || empty($email)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: profile.php");
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
        header("Location: profile.php");
        exit;
    }
    
    // Check if email is already used by another user
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->bind_param("si", $email, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Email already in use by another account.";
        $stmt->close();
        header("Location: profile.php");
        exit;
    }
    $stmt->close();
    
    // Update user in database
    $stmt = $conn->prepare("UPDATE users SET firstname = ?, lastname = ?, email = ? WHERE id = ?");
    $stmt->bind_param("sssi", $firstname, $lastname, $email, $user_id);
    
    if ($stmt->execute()) {
        // Update session variables
        $_SESSION['user_firstname'] = $firstname;
        $_SESSION['user_lastname'] = $lastname;
        $_SESSION['user_email'] = $email;
        
        $_SESSION['success'] = "Profile updated successfully!";
    } else {
        $_SESSION['error'] = "Error updating profile: " . $conn->error;
    }
    
    $stmt->close();
    $conn->close();
    
    header("Location: profile.php");
    exit;
} else {
    header("Location: profile.php");
    exit;
}
?>
