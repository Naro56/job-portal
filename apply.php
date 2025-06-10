<?php
require_once 'includes/header.php';

// Redirect if not logged in or if user is a recruiter
if (!isLoggedIn() || isRecruiter()) {
    header('Location: /');
    exit;
}

// Get job ID from URL
$job_id = isset($_GET['job_id']) ? (int)$_GET['job_id'] : 0;

// Get job details
$stmt = $conn->prepare("
    SELECT j.*, u.name as company_name 
    FROM jobs j 
    JOIN users u ON j.company_id = u.id 
    WHERE j.id = ? AND j.is_closed = 0
");
$stmt->bind_param("i", $job_id);
$stmt->execute();
$job = $stmt->get_result()->fetch_assoc();

if (!$job) {
    header('Location: /');
    exit;
}

// Check if user has already applied
$stmt = $conn->prepare("SELECT id FROM applications WHERE user_id = ? AND job_id = ?");
$stmt->bind_param("ii", $_SESSION['user_id'], $job_id);
$stmt->execute();
$already_applied = $stmt->get_result()->num_rows > 0;

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$already_applied) {
    $phone = trim($_POST['phone'] ?? '');
    $additional_info = trim($_POST['additional_info'] ?? '');
    
    // Validate resume upload
    if (!isset($_FILES['resume']) || $_FILES['resume']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Please upload your resume';
    } elseif ($_FILES['resume']['size'] > 5 * 1024 * 1024) { // 5MB limit
        $error = 'Resume file size must be less than 5MB';
    } else {
        $file_info = pathinfo($_FILES['resume']['name']);
        $file_ext = strtolower($file_info['extension']);
        
        // Check file extension
        if (!in_array($file_ext, ['pdf', 'doc', 'docx'])) {
            $error = 'Only PDF, DOC, and DOCX files are allowed';
        } else {
            // Create uploads directory if it doesn't exist
            $upload_dir = 'uploads/resumes/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            // Generate unique filename
            $new_filename = uniqid('resume_') . '.' . $file_ext;
            $upload_path = $upload_dir . $new_filename;

            if (move_uploaded_file($_FILES['resume']['tmp_name'], $upload_path)) {
                // Insert application
                $query = "INSERT INTO applications (user_id, job_id, resume_path, phone, additional_info, status) 
                          VALUES (?, ?, ?, ?, ?, 'applied')";
                
                $stmt = $conn->prepare($query);
                
                if ($stmt === false) {
                    throw new Exception("Database error: " . $conn->error);
                }
                
                $stmt->bind_param("iisss", 
                    $_SESSION['user_id'], $job_id, $upload_path, $phone, $additional_info
                );
                
                if ($stmt->execute()) {
                    $success = 'Application submitted successfully!';
                } else {
                    $error = 'Failed to submit application: ' . $stmt->error;
                }
            } else {
                $error = 'Failed to upload resume. Please try again.';
            }
        }
    }
}
?>

<div class="container">
    <div class="form-container">
        <h1>Apply for: <?php echo htmlspecialchars($job['title']); ?></h1>
        <h3><?php echo htmlspecialchars($job['company_name']); ?></h3>
        
        <?php if ($already_applied): ?>
            <div class="alert alert-info">
                You have already applied for this job. You can view your application status in your dashboard.
                <br>
                <a href="/job-portal/dashboard.php" class="btn btn-primary mt-3">Go to Dashboard</a>
            </div>
        <?php else: ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                    <br>
                    <a href="/job-portal/dashboard.php" class="btn btn-primary mt-3">View Your Applications</a>
                </div>
            <?php else: ?>
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="phone">Your Phone Number</label>
                        <input type="tel" id="phone" name="phone" class="form-control" required
                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                        <small class="form-text text-muted">This will be used by the recruiter to contact you</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="resume">Resume (PDF, DOC, DOCX)</label>
                        <input type="file" id="resume" name="resume" class="form-control" required accept=".pdf,.doc,.docx">
                        <small class="form-text text-muted">Max file size: 5MB</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="additional_info">Additional Information</label>
                        <textarea id="additional_info" name="additional_info" class="form-control" rows="5"><?php 
                            echo isset($_POST['additional_info']) ? htmlspecialchars($_POST['additional_info']) : ''; 
                        ?></textarea>
                        <small class="form-text text-muted">Include any relevant experience, skills, or other information you'd like to share</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Submit Application</button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>




