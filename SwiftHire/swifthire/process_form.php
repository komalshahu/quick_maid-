<?php
// Verify if the form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["submit"])) {
    // Verify CSRF token
    session_start();
    
    if (!isset($_SESSION['user_id'])) {
        die("User authentication failed. You must be logged in.");
    }
    $user_id = $_SESSION['user_id'];

    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed");
    }

    // Bypass Google reCAPTCHA for local testing
    // Verification skipped logic removed so form submits easily

    // Validate input
    $firstname = $_POST['firstname'] ?? '';
    $lastname = $_POST['lastname'] ?? '';
    $email = $_POST['email'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $areacode = $_POST['areacode'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $age = $_POST['age'] ?? '';
    $startdate = $_POST['startdate'] ?? '';
    $address = $_POST['address'] ?? '';
    $address2 = $_POST['address2'] ?? '';
    $message = $_POST['message'] ?? '';
    $resume_path = $_FILES['resume']['tmp_name'] ?? '';

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format");
    }

    // Sanitize input
    $firstname = htmlspecialchars($firstname);
    $lastname = htmlspecialchars($lastname);
    $email = htmlspecialchars($email);
    $gender = htmlspecialchars($gender);
    $areacode = htmlspecialchars($areacode);
    $phone = htmlspecialchars($phone);
    $age = htmlspecialchars($age);
    $startdate = htmlspecialchars($startdate);
    $address = htmlspecialchars($address);
    $address2 = htmlspecialchars($address2);
    $message = htmlspecialchars($message);

    // Database connection parameters
    $servername = "127.0.0.1";
    $username = "root";
    $password = "";
    $db_name = "job_applications_db";
    $port = 3307; // port for MySQL

    // Establish database connection
    $conn = new mysqli($servername, $username, $password, $db_name, $port);
    // Check database connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Insert data into database
    $stmt = $conn->prepare("INSERT INTO job_applications (user_id, firstname, lastname, email, gender, areacode, phone, age, startdate, address, address2, message, resume_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssssisssss", $user_id, $firstname, $lastname, $email, $gender, $areacode, $phone, $age, $startdate, $address, $address2, $message, $resume_path);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}
?>

<?php
    // Email notification disabled for local server testing

    // Redirect to thank you page
    header("Location: thank_you.php");
    exit();
?>
