<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $reviewer_id = $_SESSION['user_id'];
    $application_id = (int)$_POST['application_id'];
    $job_id = (int)$_POST['job_id'];
    $maid_user_id = (int)$_POST['maid_id'];
    $rating = (int)$_POST['rating'];
    $review_text = htmlspecialchars($_POST['review_text'] ?? '');

    if ($rating < 1 || $rating > 5) {
        die("Invalid rating.");
    }

    // Verify owner
    $stmt = $conn->prepare("SELECT id FROM vacancies WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $job_id, $reviewer_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        die("Unauthorized");
    }
    $stmt->close();

    // Insert review
    $stmt = $conn->prepare("INSERT INTO reviews (job_id, reviewer_id, maid_id, rating, review_text) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiis", $job_id, $reviewer_id, $maid_user_id, $rating, $review_text);
    $stmt->execute();
    $stmt->close();

    // Update application status to Completed
    $stmt = $conn->prepare("UPDATE job_applications SET status = 'Completed' WHERE id = ?");
    $stmt->bind_param("i", $application_id);
    $stmt->execute();
    $stmt->close();

    // Recalculate average rating for maid
    $stmt = $conn->prepare("SELECT AVG(rating) as avg_rating FROM reviews WHERE maid_id = ?");
    $stmt->bind_param("i", $maid_user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $avg_rating = (float)$row['avg_rating'];
    $stmt->close();

    // Update maid profile
    $stmt = $conn->prepare("UPDATE maids SET rating = ? WHERE user_id = ?");
    $stmt->bind_param("di", $avg_rating, $maid_user_id);
    $stmt->execute();
    $stmt->close();

    header("Location: owner_inbox.php");
    exit;
}
?>
