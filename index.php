<?php
require_once 'includes/header.php';

// Get filter parameters
$type = isset($_GET['type']) ? $_GET['type'] : '';
$location = isset($_GET['location']) ? $_GET['location'] : '';
$experience = isset($_GET['experience']) ? $_GET['experience'] : '';
$salary = isset($_GET['salary']) ? $_GET['salary'] : '';
$company = isset($_GET['company']) ? $_GET['company'] : '';

// Build query
$query = "SELECT j.*, u.company_name as company_name, u.company_website, u.company_description 
          FROM jobs j 
          JOIN users u ON j.company_id = u.id 
          WHERE j.is_closed = 0";

$params = [];
$types = "";

if ($type) {
    $query .= " AND j.type = ?";
    $params[] = $type;
    $types .= "s";
}

if ($location) {
    $query .= " AND j.location LIKE ?";
    $params[] = "%$location%";
    $types .= "s";
}

if ($experience) {
    $query .= " AND j.experience_required LIKE ?";
    $params[] = "%$experience%";
    $types .= "s";
}

if ($salary) {
    $query .= " AND j.salary LIKE ?";
    $params[] = "%$salary%";
    $types .= "s";
}

if ($company) {
    $query .= " AND u.company_name LIKE ?";
    $params[] = "%$company%";
    $types .= "s";
}

$query .= " ORDER BY j.created_at DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container">
    <h1 class="page-title">Find Your Dream Job</h1>
    
    <!-- Filters -->
    <div class="filters">
        <form method="GET" action="index.php" class="horizontal-filter-form">
            <div class="filter-row">
                <div class="filter-item">
                    <label for="type">Job Type</label>
                    <select name="type" id="type" class="form-control">
                        <option value="">All Types</option>
                        <option value="full-time" <?php echo $type === 'full-time' ? 'selected' : ''; ?>>Full Time</option>
                        <option value="internship" <?php echo $type === 'internship' ? 'selected' : ''; ?>>Internship</option>
                        <option value="part-time" <?php echo $type === 'part-time' ? 'selected' : ''; ?>>Part Time</option>
                    </select>
                </div>
                
                <div class="filter-item">
                    <label for="location">Location</label>
                    <input type="text" name="location" id="location" class="form-control" 
                           value="<?php echo htmlspecialchars($location); ?>" placeholder="Enter location">
                </div>
                
                <div class="filter-item">
                    <label for="experience">Experience</label>
                    <input type="text" name="experience" id="experience" class="form-control" 
                           value="<?php echo htmlspecialchars($experience); ?>" placeholder="e.g., 2-3 years">
                </div>
                
                <div class="filter-item">
                    <label for="salary">Salary</label>
                    <input type="text" name="salary" id="salary" class="form-control" 
                           value="<?php echo htmlspecialchars($salary); ?>" placeholder="e.g., 10L">
                </div>
                
                <div class="filter-item">
                    <label for="company">Company</label>
                    <input type="text" name="company" id="company" class="form-control" 
                           value="<?php echo htmlspecialchars($company); ?>" placeholder="Company name">
                </div>
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">Apply Filters</button>
                <a href="index.php" class="btn btn-secondary">Clear Filters</a>
            </div>
        </form>
    </div>

    <!-- Job Listings -->
    <div class="job-grid">
        <?php if ($result->num_rows === 0): ?>
            <div class="no-jobs-message">
                <p>No jobs found matching your criteria. Try adjusting your filters.</p>
            </div>
        <?php else: ?>
            <?php while ($job = $result->fetch_assoc()): ?>
                <div class="job-card">
                    <div class="job-card-header">
                        <h2 class="job-title"><?php echo sanitize($job['title']); ?></h2>
                        
                        <?php if (isLoggedIn() && !isRecruiter()): 
                            // Check if job is saved
                            $stmt = $conn->prepare("SELECT id FROM saved_jobs WHERE user_id = ? AND job_id = ?");
                            $stmt->bind_param("ii", $_SESSION['user_id'], $job['id']);
                            $stmt->execute();
                            $is_saved = $stmt->get_result()->num_rows > 0;
                        ?>
                            <a href="save-job.php?id=<?php echo $job['id']; ?>" class="save-job-btn <?php echo $is_saved ? 'saved' : ''; ?>" title="<?php echo $is_saved ? 'Remove from saved jobs' : 'Save this job'; ?>">
                                <i class="fas <?php echo $is_saved ? 'fa-bookmark' : 'fa-bookmark-o'; ?>"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <div class="company-name">
                        🏢 <?php echo sanitize($job['company_name']); ?>
                    </div>
                    
                    <div class="job-details">
                        <div>📍 <?php echo sanitize($job['location']); ?></div>
                        <div>💰 <?php echo sanitize($job['salary']); ?></div>
                        <div>👨‍💼 <?php echo sanitize($job['experience_required']); ?></div>
                        <div>⏰ <?php echo sanitize($job['type']); ?></div>
                    </div>
                    
                    <div class="job-actions">
                        <a href="job-details.php?id=<?php echo $job['id']; ?>" class="btn btn-primary">View Details</a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 
