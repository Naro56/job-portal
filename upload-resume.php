<?php
require_once 'includes/header.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    header('Location: /job-portal/index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
                // Insert resume record
                $stmt = $conn->prepare("INSERT INTO user_resumes (user_id, resume_path) VALUES (?, ?)");
                $stmt->bind_param("is", $_SESSION['user_id'], $upload_path);
                
                if ($stmt->execute()) {
                    $success = 'Resume uploaded successfully!';
                } else {
                    $error = 'Failed to save resume information: ' . $stmt->error;
                }
            } else {
                $error = 'Failed to upload resume. Please try again.';
            }
        }
    }
}

// Get user's current resume
$stmt = $conn->prepare("SELECT resume_path, uploaded_on FROM user_resumes WHERE user_id = ? ORDER BY uploaded_on DESC LIMIT 1");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$current_resume = $stmt->get_result()->fetch_assoc();
?>

<div class="container">
    <div class="form-container">
        <h1>Upload Resume</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php if ($current_resume): ?>
            <div class="current-resume">
                <h3>Current Resume</h3>
                <p>
                    <a href="<?php echo sanitize($current_resume['resume_path']); ?>" target="_blank">
                        <i class="fas fa-file-pdf"></i> View Current Resume
                    </a>
                </p>
                <p class="text-muted">
                    Uploaded on: <?php echo date('F j, Y, g:i a', strtotime($current_resume['uploaded_on'])); ?>
                </p>
            </div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="resume">Upload New Resume (PDF, DOC, DOCX)</label>
                <input type="file" id="resume" name="resume" class="form-control" required accept=".pdf,.doc,.docx">
                <small class="form-text text-muted">Max file size: 5MB</small>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Upload Resume</button>
                <a href="profile.php" class="btn btn-secondary">Back to Profile</a>
            </div>
        </form>
        
        <div class="resume-tips">
            <h3>Resume Tips</h3>
            <ul>
                <li>Keep your resume concise and relevant to the jobs you're applying for</li>
                <li>Highlight your achievements rather than just listing responsibilities</li>
                <li>Use bullet points for better readability</li>
                <li>Include keywords relevant to your industry to pass through ATS systems</li>
                <li>Proofread carefully for spelling and grammar errors</li>
            </ul>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>