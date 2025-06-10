<?php
require_once 'includes/header.php';

// Get job ID from URL
$job_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get job details
$stmt = $conn->prepare("
    SELECT j.*, u.company_name, u.company_website, u.company_description 
    FROM jobs j 
    JOIN users u ON j.company_id = u.id 
    WHERE j.id = ?
");
$stmt->bind_param("i", $job_id);
$stmt->execute();
$job = $stmt->get_result()->fetch_assoc();

if (!$job) {
    header('Location: /job-portal/index.php');
    exit;
}

// Check if job is saved by current user
$is_saved = false;
if (isLoggedIn() && !isRecruiter()) {
    $stmt = $conn->prepare("SELECT id FROM saved_jobs WHERE user_id = ? AND job_id = ?");
    $stmt->bind_param("ii", $_SESSION['user_id'], $job_id);
    $stmt->execute();
    $is_saved = $stmt->get_result()->num_rows > 0;
}
?>

<div class="container">
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

    <div class="job-details-page">
        <div class="page-header">
            <h1><?php echo sanitize($job['title']); ?></h1>
            <div class="header-actions">
                <a href="index.php" class="btn btn-secondary">Back to Jobs</a>
                
                <?php if (isLoggedIn() && !isRecruiter()): ?>
                    <a href="save-job.php?id=<?php echo $job['id']; ?>" class="btn <?php echo $is_saved ? 'btn-saved' : 'btn-outline-primary'; ?>">
                        <i class="fas <?php echo $is_saved ? 'fa-bookmark' : 'fa-bookmark-o'; ?>"></i>
                        <?php echo $is_saved ? 'Saved' : 'Save Job'; ?>
                    </a>
                <?php endif; ?>
            </div>
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
        
        <?php if (isLoggedIn() && !isRecruiter()): ?>
            <div class="apply-section">
                <a href="apply.php?job_id=<?php echo $job['id']; ?>" class="btn btn-primary btn-lg">Apply Now</a>
                
                <a href="save-job.php?id=<?php echo $job['id']; ?>" class="btn <?php echo $is_saved ? 'btn-saved' : 'btn-outline-primary'; ?> btn-lg">
                    <i class="fas <?php echo $is_saved ? 'fa-bookmark' : 'fa-bookmark-o'; ?>"></i>
                    <?php echo $is_saved ? 'Saved' : 'Save Job'; ?>
                </a>
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

.header-actions {
    display: flex;
    gap: 0.5rem;
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
    color: #4b5563;
}

.apply-section {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e5e7eb;
}

.btn-saved {
    background-color: #fef3c7;
    color: #d97706;
    border: 1px solid #fde68a;
}

.btn-saved:hover {
    background-color: #fde68a;
}

.btn-outline-primary {
    background: transparent;
    border: 1px solid #2563eb;
    color: #2563eb;
}

.btn-outline-primary:hover {
    background: rgba(37, 99, 235, 0.1);
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
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .header-actions {
        margin-top: 1rem;
    }
    
    .apply-section {
        flex-direction: column;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>


