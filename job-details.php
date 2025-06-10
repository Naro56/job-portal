<?php
require_once 'includes/header.php';

// Get job ID from URL
$job_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get job details with company information
$stmt = $conn->prepare("
    SELECT j.*, u.company_name, u.company_website, u.company_description
    FROM jobs j
    JOIN users u ON j.company_id = u.id
    WHERE j.id = ?
");
$stmt->bind_param("i", $job_id);
$stmt->execute();
$job = $stmt->get_result()->fetch_assoc();

// Redirect if job not found
if (!$job) {
    header('Location: index.php');
    exit;
}
?>

<div class="container">
    <div class="job-details-page">
        <div class="page-header">
            <h1><?php echo sanitize($job['title']); ?></h1>
            <a href="index.php" class="btn btn-secondary">Back to Jobs</a>
        </div>
        
        <div class="job-header-info">
            <div class="company-name">
                <h3><?php echo sanitize($job['company_name']); ?></h3>
            </div>
            
            <div class="job-meta">
                <div class="job-meta-item">📍 <?php echo sanitize($job['location']); ?></div>
                <div class="job-meta-item">💰 <?php echo sanitize($job['salary']); ?></div>
                <div class="job-meta-item">👨‍💼 <?php echo sanitize($job['experience_required']); ?></div>
                <div class="job-meta-item">⏰ <?php echo sanitize($job['type']); ?></div>
            </div>
        </div>
        
        <div class="job-description">
            <h3>Job Description</h3>
            <div class="description-content">
                <?php echo nl2br(sanitize($job['description'])); ?>
            </div>
        </div>
        
        <div class="company-info">
            <h3>About <?php echo sanitize($job['company_name']); ?></h3>
            <?php if (!empty($job['company_description'])): ?>
                <p><?php echo nl2br(sanitize($job['company_description'])); ?></p>
            <?php endif; ?>
            
            <?php if (!empty($job['company_website'])): ?>
                <p>
                    <a href="<?php echo sanitize($job['company_website']); ?>" target="_blank" class="company-website">
                        <i class="fas fa-external-link-alt"></i> Visit Company Website
                    </a>
                </p>
            <?php endif; ?>
        </div>
        
        <?php if (isLoggedIn() && !isRecruiter()): ?>
            <div class="apply-section">
                <a href="apply.php?job_id=<?php echo $job['id']; ?>" class="btn btn-primary btn-lg">Apply Now</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.job-details-page {
    background: #fff;
    border-radius: 8px;
    padding: 2rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    margin-bottom: 2rem;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e5e7eb;
}

.job-header-info {
    margin-bottom: 2rem;
}

.job-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-top: 1rem;
}

.job-meta-item {
    background: #f3f4f6;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    font-size: 0.9rem;
    color: #4b5563;
}

.job-description {
    margin-bottom: 2rem;
}

.description-content {
    line-height: 1.6;
    white-space: pre-line;
}

.company-info {
    background: #f9fafb;
    padding: 1.5rem;
    border-radius: 8px;
    margin-bottom: 2rem;
}

.company-website {
    color: #2563eb;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
}

.apply-section {
    margin-top: 2rem;
    text-align: center;
}

.btn-lg {
    padding: 0.75rem 1.5rem;
    font-size: 1.1rem;
}
</style>

<?php require_once 'includes/footer.php'; ?>







