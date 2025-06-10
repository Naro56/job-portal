<?php
require_once 'includes/header.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    header('Location: /job-portal/index.php');
    exit;
}

// Get user's saved jobs
$stmt = $conn->prepare("
    SELECT s.*, j.title as job_title, j.location, j.salary, j.type, 
           u.company_name
    FROM saved_jobs s
    JOIN jobs j ON s.job_id = j.id
    JOIN users u ON j.company_id = u.id
    WHERE s.user_id = ?
    ORDER BY s.saved_on DESC
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$savedJobs = $stmt->get_result();
?>

<div class="container">
    <h1>Saved Jobs</h1>
    
    <?php if ($savedJobs->num_rows === 0): ?>
        <div class="alert alert-info">
            You haven't saved any jobs yet. 
            <a href="/job-portal/index.php">Browse jobs</a> and click the bookmark icon to save jobs for later.
        </div>
    <?php else: ?>
        <div class="saved-jobs-grid">
            <?php while ($job = $savedJobs->fetch_assoc()): ?>
                <div class="job-card">
                    <div class="job-card-header">
                        <h3 class="job-title"><?php echo sanitize($job['job_title']); ?></h3>
                        <a href="remove-saved-job.php?id=<?php echo $job['id']; ?>" 
                           class="btn-remove" title="Remove from saved jobs">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                    <div class="company-name"><?php echo sanitize($job['company_name']); ?></div>
                    <div class="job-details">
                        <div class="job-detail"><i class="fas fa-map-marker-alt"></i> <?php echo sanitize($job['location']); ?></div>
                        <div class="job-detail"><i class="fas fa-money-bill-wave"></i> <?php echo sanitize($job['salary']); ?></div>
                        <div class="job-detail"><i class="fas fa-clock"></i> <?php echo sanitize($job['type']); ?></div>
                    </div>
                    <div class="job-actions">
                        <a href="job-details.php?id=<?php echo $job['job_id']; ?>" class="btn btn-primary">View Details</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>