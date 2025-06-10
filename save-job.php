<?php
require_once 'includes/header.php';

// Redirect if not logged in or if user is a recruiter
if (!isLoggedIn() || isRecruiter()) {
    header('Location: /job-portal/index.php');
    exit;
}

// Get job ID from URL
$job_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Validate job exists and is open
$stmt = $conn->prepare("SELECT id FROM jobs WHERE id = ? AND is_closed = 0");
$stmt->bind_param("i", $job_id);
$stmt->execute();
$job_result = $stmt->get_result();

if ($job_result->num_rows === 0) {
    // Job doesn't exist or is closed
    $_SESSION['error'] = "The job you're trying to save doesn't exist or is no longer available.";
    header('Location: /job-portal/index.php');
    exit;
}

// Check if job is already saved
$stmt = $conn->prepare("SELECT id FROM saved_jobs WHERE user_id = ? AND job_id = ?");
$stmt->bind_param("ii", $_SESSION['user_id'], $job_id);
$stmt->execute();
$already_saved = $stmt->get_result()->num_rows > 0;

if ($already_saved) {
    // Job is already saved - remove it (toggle functionality)
    $stmt = $conn->prepare("DELETE FROM saved_jobs WHERE user_id = ? AND job_id = ?");
    $stmt->bind_param("ii", $_SESSION['user_id'], $job_id);
    $stmt->execute();
    
    $_SESSION['success'] = "Job removed from your saved jobs.";
} else {
    // Save the job
    $stmt = $conn->prepare("INSERT INTO saved_jobs (user_id, job_id, saved_on) VALUES (?, ?, NOW())");
    $stmt->bind_param("ii", $_SESSION['user_id'], $job_id);
    $stmt->execute();
    
    $_SESSION['success'] = "Job saved successfully! You can view it in your Saved Jobs.";
}

// Redirect back to the referring page or job details
$redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "/job-portal/job-details.php?id=$job_id";
header("Location: $redirect");
exit;