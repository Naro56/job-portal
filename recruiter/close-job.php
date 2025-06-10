<?php
require_once '../includes/header.php';

// Redirect if not logged in or not a recruiter
if (!isLoggedIn() || !isRecruiter()) {
    header('Location: /');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $job_id = (int)$_POST['job_id'];
    
    // Verify job belongs to recruiter and update status
    $stmt = $conn->prepare("UPDATE jobs SET is_closed = 1 WHERE id = ? AND company_id = ?");
    $stmt->bind_param("ii", $job_id, $_SESSION['user_id']);
    $stmt->execute();
}

// Redirect back to dashboard
header('Location: /job-portal/recruiter/dashboard.php');
exit;
