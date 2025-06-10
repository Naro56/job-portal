<?php
// If session hasn't been started yet, start it
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection and functions if not already included
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FindWork | Job Portal</title>
    <link rel="stylesheet" href="/job-portal/css/main.css">
</head>
<body>
    <header class="main-header">
        <nav class="nav-container">
            <div class="logo">
                <a href="/job-portal/index.php">FindWork</a>
            </div>
            
            <div class="user-info">
                <?php if (isLoggedIn()): ?>
                    Welcome, <?php echo sanitize($_SESSION['user_name']); ?> 
                    <?php if (isRecruiter()): ?>
                        (Recruiter - <?php echo sanitize($_SESSION['company_name']); ?>)
                    <?php else: ?>
                        (Job Seeker)
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            
            <div class="nav-links">
                <a href="/job-portal/index.php">Home</a>
                
                <?php if (isLoggedIn()): ?>
                    <?php if (isRecruiter()): ?>
                        <a href="/job-portal/recruiter/dashboard.php">Dashboard</a>
                        <a href="/job-portal/recruiter/my-jobs.php">My Jobs</a>
                        <a href="/job-portal/post-job.php">Post Job</a>
                    <?php else: ?>
                        <a href="/job-portal/dashboard.php">My Applications</a>
                        <a href="/job-portal/saved-jobs.php">Saved Jobs</a>
                        <a href="/job-portal/profile.php">My Profile</a>
                    <?php endif; ?>
                    <a href="/job-portal/logout.php">Logout</a>
                <?php else: ?>
                    <a href="/job-portal/login.php">Login</a>
                    <a href="/job-portal/signup.php">Sign Up</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>
    <main class="container">
