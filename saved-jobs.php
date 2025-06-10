<?php
require_once 'includes/header.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    header('Location: /job-portal/index.php');
    exit;
}

// Get user's saved jobs
$stmt = $conn->prepare("
    SELECT s.*, j.title as job_title, j.location, j.salary, j.type, j.experience_required,
           u.company_name, j.id as job_id
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
    <div class="page-header">
        <h1>Saved Jobs</h1>
        <a href="index.php" class="btn btn-primary">Browse Jobs</a>
    </div>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php 
                echo $_SESSION['success']; 
                unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?php 
                echo $_SESSION['error']; 
                unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>
    
    <?php if ($savedJobs->num_rows === 0): ?>
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="fas fa-bookmark"></i>
            </div>
            <h3>No Saved Jobs</h3>
            <p>You haven't saved any jobs yet. Browse jobs and click the bookmark icon to save jobs for later.</p>
            <a href="index.php" class="btn btn-primary">Browse Jobs</a>
        </div>
    <?php else: ?>
        <div class="saved-jobs-grid">
            <?php while ($job = $savedJobs->fetch_assoc()): ?>
                <div class="job-card">
                    <div class="job-card-header">
                        <h3 class="job-title"><?php echo sanitize($job['job_title']); ?></h3>
                        <a href="save-job.php?id=<?php echo $job['job_id']; ?>" 
                           class="btn-remove" title="Remove from saved jobs">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                    <div class="company-name"><?php echo sanitize($job['company_name']); ?></div>
                    <div class="job-details">
                        <div class="job-detail"><i class="fas fa-map-marker-alt"></i> <?php echo sanitize($job['location']); ?></div>
                        <div class="job-detail"><i class="fas fa-money-bill-wave"></i> <?php echo sanitize($job['salary']); ?></div>
                        <div class="job-detail"><i class="fas fa-briefcase"></i> <?php echo sanitize($job['experience_required']); ?></div>
                        <div class="job-detail"><i class="fas fa-clock"></i> <?php echo sanitize($job['type']); ?></div>
                    </div>
                    <div class="saved-date">
                        <small>Saved on: <?php echo date('M j, Y', strtotime($job['saved_on'])); ?></small>
                    </div>
                    <div class="job-actions">
                        <a href="job-details.php?id=<?php echo $job['job_id']; ?>" class="btn btn-primary">View Details</a>
                        <a href="apply.php?job_id=<?php echo $job['job_id']; ?>" class="btn btn-secondary">Apply Now</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.empty-state-icon {
    font-size: 3rem;
    color: #d1d5db;
    margin-bottom: 1rem;
}

.empty-state h3 {
    font-size: 1.5rem;
    color: #374151;
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: #6b7280;
    margin-bottom: 1.5rem;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
}

.job-card {
    background: #fff;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    transition: transform 0.2s, box-shadow 0.2s;
}

.job-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.job-title {
    font-size: 1.25rem;
    color: #1f2937;
    margin: 0;
}

.company-name {
    color: #4b5563;
    font-weight: 500;
    margin-bottom: 1rem;
}

.job-details {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.job-detail {
    color: #6b7280;
    font-size: 0.875rem;
}

.job-detail i {
    margin-right: 0.5rem;
    color: #4b5563;
}

.saved-date {
    color: #9ca3af;
    margin-bottom: 1rem;
    font-size: 0.875rem;
}

.job-actions {
    display: flex;
    gap: 0.5rem;
}

.alert {
    padding: 1rem;
    margin-bottom: 1rem;
    border-radius: 4px;
}

.alert-success {
    background-color: #d1fae5;
    color: #065f46;
    border: 1px solid #a7f3d0;
}

.alert-danger {
    background-color: #fee2e2;
    color: #b91c1c;
    border: 1px solid #fecaca;
}

@media (max-width: 768px) {
    .saved-jobs-grid {
        grid-template-columns: 1fr;
    }
    
    .job-actions {
        flex-direction: column;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>

