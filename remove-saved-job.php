<?php
require_once 'includes/header.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    header('Location: /job-portal/index.php');
    exit;
}

// Get saved job ID
$saved_job_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Verify the saved job belongs to the user and delete it
$stmt = $conn->prepare("DELETE FROM saved_jobs WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $saved_job_id, $_SESSION['user_id']);
$stmt->execute();

if ($conn->affected_rows > 0) {
    $_SESSION['success'] = "Job removed from your saved jobs.";
} else {
    $_SESSION['error'] = "Failed to remove job from saved jobs.";
}

// Redirect back to saved jobs page
header('Location: /job-portal/saved-jobs.php');
exit;