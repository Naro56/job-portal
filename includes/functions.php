<?php
/**
 * Common functions for the FindWork job portal
 */

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to check if user is a recruiter
function isRecruiter() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'recruiter';
}

// Function to sanitize output
function sanitize($input) {
    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
}

// Function to format date
function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

// Function to redirect user based on role
function redirectBasedOnRole() {
    if (isLoggedIn()) {
        header('Location: ' . (isRecruiter() ? '/job-portal/recruiter/dashboard.php' : '/job-portal/dashboard.php'));
        exit;
    }
}

// Function to require login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /job-portal/login.php');
        exit;
    }
}

// Function to require recruiter role
function requireRecruiter() {
    if (!isLoggedIn() || !isRecruiter()) {
        header('Location: /job-portal/index.php');
        exit;
    }
}
?>

