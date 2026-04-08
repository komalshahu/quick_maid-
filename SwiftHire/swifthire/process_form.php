<?php
// Verify if the form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["submit"])) {
    // Verify CSRF token
    session_start();
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed");
    }

    // Verify Google reCAPTCHA response
    $response = $_POST['g-recaptcha-response'];
    $secretKey = ""; // Your secret key here

    // Initialize cURL session
    $ch = curl_init();
    // Set cURL options
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://www.google.com/recaptcha/api/siteverify',
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => [
            'secret' => $secretKey,
            'response' => $response,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        ],
        CURLOPT_RETURNTRANSFER => true
    ]);
    // Execute cURL session
    $output = curl_exec($ch);
    // Close cURL session
    curl_close($ch);

    // Decode JSON response
    $json = json_decode($output);

    // Check Google reCAPTCHA response
    if (!isset($json->success) || !$json->success) {
        die("Google reCAPTCHA verification failed");
    }

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
    $servername = "";
    $username = "";
    $password = "";
    $db_name = "";
    $port = ''; // port for MySQL

    // Establish database connection
    $conn = new mysqli($servername, $username, $password, $db_name, $port);
    // Check database connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Insert data into database
    $stmt = $conn->prepare("INSERT INTO job_applications (firstname, lastname, email, gender, areacode, phone, age, startdate, address, address2, message, resume_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssisssss", $firstname, $lastname, $email, $gender, $areacode, $phone, $age, $startdate, $address, $address2, $message, $resume_path);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}
?>

<?php
    // Send notification email to the admin 
    
    // Import PHPMailer classes into the global namespace
    // These must be at the top of your script, not inside a function
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

    // Send notification email to the admin
    require 'vendor/autoload.php'; // Load PHPMailer library

    try {
        // Create a PHPMailer instance
        $mail = new PHPMailer(true);

        // Set mailer to use SMTP
        $mail->isSMTP();
        // Set charset to utf8
        $mail->CharSet = "utf-8";
        // Enable SMTP authentication
        $mail->SMTPAuth = true;
        // Enable TLS encryption
        $mail->SMTPSecure = 'tls';
        // Specify main and backup SMTP servers
        $mail->Host = 'smtp.gmail.com';
        // TCP port to connect to
        $mail->Port = 587;
        // Set email format to HTML
        $mail->isHTML(true);

        // SMTP username
        $mail->Username = ''; // Your email address
        // SMTP password
        $mail->Password = ''; // Your email password

        // Your application name and email
        $mail->setFrom('', ''); // Your email address and name
        // Message subject
        $mail->Subject = 'New Job Application Submitted';
        // Message body
        $mail->Body = "A new job application has been submitted.\n\n";
        $mail->Body .= "Name: $firstname $lastname\n";
        $mail->Body .= "Email: $email\n";
        $mail->Body .= "Gender: $gender\n";
        $mail->Body .= "Phone: $areacode-$phone\n";
        $mail->Body .= "Age: $age\n";
        $mail->Body .= "Start Date: $startdate\n";
        $mail->Body .= "Address: $address $address2\n";
        $mail->Body .= "Message:\n$message";

        // Target email address
        $mail->addAddress('', 'Admin'); // Admin's email address

        // Attach resume file
        $mail->addAttachment($resume_path, 'Resume.pdf'); // Change 'Resume.pdf' to desired filename

        // Send email
        $mail->send();
        echo 'Job application submitted successfully. You will be contacted soon.';
    } catch (Exception $e) {
        // Display error message if email could not be sent
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }

    // Close SMTP connection
    $mail->smtpClose();

    // Redirect to thank you page
    header("Location: thank_you.php");
    exit();
?>
