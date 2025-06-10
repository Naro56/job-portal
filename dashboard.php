<?php
require_once 'includes/header.php';

// Redirect if not logged in or if user is a recruiter
if (!isLoggedIn()) {
    header('Location: /job-portal/index.php');
    exit;
}

// Redirect recruiters to their dashboard
if (isRecruiter()) {
    header('Location: /job-portal/recruiter/dashboard.php');
    exit;
}

// Get user's applications
$stmt = $conn->prepare("
    SELECT a.*, j.title as job_title, u.name as company_name
    FROM applications a
    JOIN jobs j ON a.job_id = j.id
    JOIN users u ON j.company_id = u.id
    WHERE a.user_id = ?
    ORDER BY a.applied_on DESC
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$applications = $stmt->get_result();
?>

<div class="container">
    <div class="dashboard-header">
        <h1>My Dashboard</h1>
        <div class="dashboard-actions">
            <a href="profile.php" class="btn btn-secondary">
                <i class="fas fa-user"></i> My Profile
            </a>
            <a href="saved-jobs.php" class="btn btn-secondary">
                <i class="fas fa-bookmark"></i> Saved Jobs
            </a>
        </div>
    </div>
    
    <h2>My Applications</h2>
    
    <?php if ($applications->num_rows === 0): ?>
        <div class="alert alert-info">
            You haven't applied to any jobs yet. 
            <a href="/job-portal/index.php">Browse jobs</a> to find opportunities that match your skills.
        </div>
    <?php else: ?>
        <div class="applications-grid">
            <?php while ($app = $applications->fetch_assoc()): ?>
                <div class="application-card">
                    <div class="application-header">
                        <h2 class="job-title"><?php echo sanitize($app['job_title']); ?></h2>
                        <div class="status-badge status-<?php echo $app['status']; ?>">
                            <?php
                            switch ($app['status']) {
                                case 'selected':
                                    echo '🎉 Selected';
                                    break;
                                case 'rejected':
                                    echo '❌ Rejected';
                                    break;
                                default:
                                    echo '📝 Applied';
                            }
                            ?>
                        </div>
                    </div>
            
                    <div class="company-name"><?php echo sanitize($app['company_name']); ?></div>
                    <div class="applied-date">Applied on: <?php echo formatDate($app['applied_on']); ?></div>
            
                    <?php if ($app['status'] == 'selected'): ?>
                        <div class="selection-message">
                            <p>We are thrilled to have you join our team! Your skills and experience impressed us, and we believe you'll be a valuable addition to our company.</p>
                            <p>Our HR team will contact you shortly with next steps.</p>
                        </div>
                    <?php elseif ($app['status'] == 'rejected'): ?>
                        <div class="rejection-message">
                            <p>Thank you for your interest in this position. While we were impressed with your qualifications, we've decided to move forward with other candidates at this time.</p>
                            <p>Don't give up! Keep applying and improving your skills. The right opportunity is waiting for you.</p>
                        </div>
                    <?php endif; ?>
            
                    <div class="application-details mt-3">
                        <h4>Your Application</h4>
                        <p><strong>Resume:</strong> <a href="/job-portal/<?php echo sanitize($app['resume_path']); ?>" target="_blank">View Resume</a></p>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?> 
