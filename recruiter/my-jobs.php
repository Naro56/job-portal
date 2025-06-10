<?php
require_once '../includes/header.php';

// Redirect if not logged in or not a recruiter
if (!isLoggedIn() || !isRecruiter()) {
    header('Location: /job-portal/index.php');
    exit;
}

// Get recruiter's jobs
$stmt = $conn->prepare("
    SELECT j.*, 
           COUNT(DISTINCT a.id) as applicant_count
    FROM jobs j
    LEFT JOIN applications a ON j.id = a.job_id
    WHERE j.company_id = ?
    GROUP BY j.id
    ORDER BY j.created_at DESC
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$jobs = $stmt->get_result();

// Check if recruiter has any jobs
$hasJobs = ($jobs->num_rows > 0);
?>

<div class="container">
    <div class="page-header">
        <div class="page-header-content">
            <h1>My Posted Jobs</h1>
            <p class="company-info">Company: <strong><?php echo sanitize($_SESSION['company_name']); ?></strong></p>
        </div>
        <a href="/job-portal/post-job.php" class="btn btn-primary">📝 Post New Job</a>
    </div>
    
    <?php if (!$hasJobs): ?>
        <div class="alert alert-info">
            <p>You haven't posted any jobs yet.</p>
            <a href="/job-portal/post-job.php" class="btn btn-primary mt-3">Post Your First Job</a>
        </div>
    <?php else: ?>
        <div class="jobs-grid">
            <?php while ($job = $jobs->fetch_assoc()): ?>
                <div class="job-card">
                    <div class="job-header">
                        <h2 class="job-title"><?php echo sanitize($job['title']); ?></h2>
                        <div class="job-status <?php echo $job['is_closed'] ? 'closed' : 'open'; ?>">
                            <?php echo $job['is_closed'] ? '🔒 Closed' : '🔓 Open'; ?>
                        </div>
                    </div>
                    
                    <div class="job-details">
                        <p>📍 <?php echo sanitize($job['location']); ?></p>
                        <p>💰 <?php echo sanitize($job['salary']); ?></p>
                        <p>👨‍💼 <?php echo sanitize($job['experience_required']); ?> experience</p>
                        <p>⏰ <?php echo sanitize($job['type']); ?></p>
                    </div>
                    
                    <div class="applicant-stats">
                        <div class="stat">
                            <span class="stat-label">👥 Total Applicants:</span>
                            <span class="stat-value"><?php echo $job['applicant_count']; ?></span>
                        </div>
                    </div>
                    
                    <div class="job-description">
                        <h3>Description</h3>
                        <p><?php echo nl2br(sanitize(substr($job['description'], 0, 150))); ?>...</p>
                    </div>
                    
                    <div class="job-actions">
                        <a href="/job-portal/recruiter/view-applicants.php?job_id=<?php echo $job['id']; ?>" 
                           class="btn btn-secondary">👥 View Applicants</a>
                        
                        <?php if (!$job['is_closed']): ?>
                            <form method="POST" action="/job-portal/recruiter/close-job.php" class="d-inline">
                                <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                                <button type="submit" class="btn btn-secondary btn-danger" 
                                        onclick="return confirm('Are you sure you want to close this job posting?')">
                                    🔒 Close Job
                                </button>
                            </form>
                        <?php else: ?>
                            <form method="POST" action="/job-portal/recruiter/reopen-job.php" class="d-inline">
                                <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                                <button type="submit" class="btn btn-secondary btn-success" 
                                        onclick="return confirm('Are you sure you want to reopen this job posting?')">
                                    🔓 Reopen Job
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>




