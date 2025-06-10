<?php
require_once '../includes/header.php';

// Redirect if not logged in or not a recruiter
if (!isLoggedIn() || !isRecruiter()) {
    header('Location: /');
    exit;
}

// Get recruiter's jobs with applicant counts
$stmt = $conn->prepare("
    SELECT j.*, 
           COUNT(DISTINCT a.id) as applicant_count,
           SUM(CASE WHEN a.status = 'selected' THEN 1 ELSE 0 END) as selected_count,
           SUM(CASE WHEN a.status = 'rejected' THEN 1 ELSE 0 END) as rejected_count
    FROM jobs j
    LEFT JOIN applications a ON j.id = a.job_id
    WHERE j.company_id = ?
    GROUP BY j.id
    ORDER BY j.created_at DESC
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$jobs = $stmt->get_result();
?>

<div class="container">
    <div class="page-header">
        <div class="page-header-content">
            <h1>Recruiter Dashboard</h1>
            <p class="company-info">Managing jobs for: <strong><?php echo sanitize($_SESSION['company_name']); ?></strong></p>
        </div>
        <a href="/job-portal/post-job.php" class="btn btn-primary">📝 Post New Job</a>
    </div>
    
    <?php if ($jobs->num_rows === 0): ?>
        <div class="alert alert-info">
            You haven't posted any jobs yet. 
            <a href="/job-portal/post-job.php">Post your first job</a> to start receiving applications!
        </div>
    <?php else: ?>
        <div class="jobs-grid">
            <?php while ($job = $jobs->fetch_assoc()): ?>
                <div class="job-card">
                    <h2 class="job-title"><?php echo htmlspecialchars($job['title']); ?></h2>
                    <div class="job-status <?php echo $job['is_closed'] ? 'closed' : 'open'; ?>">
                        <?php echo $job['is_closed'] ? '🔒 Closed' : '🔓 Open'; ?>
                    </div>
                    
                    <!-- Job details -->
                    <div class="job-details">
                        <p>📍 <?php echo htmlspecialchars($job['location']); ?></p>
                        <p>💰 <?php echo htmlspecialchars($job['salary']); ?></p>
                        <p>👨‍💼 <?php echo htmlspecialchars($job['experience_required']); ?></p>
                        <p>⏰ <?php echo htmlspecialchars($job['type']); ?></p>
                    </div>
                    
                    <!-- Application stats -->
                    <div class="application-stats">
                        <p>👥 Total Applicants: <?php echo $job['applicant_count']; ?></p>
                        <p>✅ Selected: <?php echo $job['selected_count']; ?></p>
                        <p>❌ Rejected: <?php echo $job['rejected_count']; ?></p>
                    </div>
                    
                    <div class="job-actions">
                        <a href="/job-portal/job-details.php?id=<?php echo $job['id']; ?>" 
                           class="btn btn-secondary">👁️ View Details</a>
                        <a href="view-applicants.php?job_id=<?php echo $job['id']; ?>" 
                           class="btn btn-secondary">👥 View Applicants</a>
                        
                        <?php if (!$job['is_closed']): ?>
                            <form method="POST" action="close-job.php" class="d-inline">
                                <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                                <button type="submit" class="btn btn-secondary btn-danger" 
                                        onclick="return confirm('Are you sure you want to close this job posting?')">
                                    🔒 Close Job
                                </button>
                            </form>
                        <?php else: ?>
                            <form method="POST" action="reopen-job.php" class="d-inline">
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
