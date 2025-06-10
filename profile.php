<?php
require_once 'includes/header.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    header('Location: /job-portal/index.php');
    exit;
}

// Get user profile
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get user skills
$stmt = $conn->prepare("
    SELECT s.* FROM skills s
    JOIN user_skills us ON s.id = us.skill_id
    WHERE us.user_id = ?
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$skills = $stmt->get_result();

// Check if user has a resume
$stmt = $conn->prepare("SELECT resume_path FROM user_resumes WHERE user_id = ? ORDER BY uploaded_on DESC LIMIT 1");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$resume = $stmt->get_result()->fetch_assoc();
?>

<div class="container">
    <h1>My Profile</h1>
    
    <div class="profile-card">
        <div class="profile-header">
            <div class="profile-avatar">
                <i class="fas fa-user-circle fa-5x"></i>
            </div>
            <div class="profile-name">
                <h2><?php echo sanitize($user['name']); ?></h2>
                <p><?php echo sanitize($user['email']); ?></p>
            </div>
        </div>
        
        <div class="profile-section">
            <h3>Personal Information</h3>
            <div class="profile-info">
                <p><strong>Name:</strong> <?php echo sanitize($user['name']); ?></p>
                <p><strong>Email:</strong> <?php echo sanitize($user['email']); ?></p>
            </div>
            
            <div class="profile-actions">
                <a href="/job-portal/edit-profile.php" class="btn btn-primary">Edit Profile</a>
            </div>
        </div>
        
        <div class="profile-section">
            <h3>Skills</h3>
            <?php if ($skills->num_rows === 0): ?>
                <p>No skills added yet. Add skills to help recruiters find you.</p>
            <?php else: ?>
                <div class="skills-list">
                    <?php while ($skill = $skills->fetch_assoc()): ?>
                        <span class="skill-badge"><?php echo sanitize($skill['name']); ?></span>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>
            
            <div class="profile-actions">
                <a href="/job-portal/manage-skills.php" class="btn btn-primary">Manage Skills</a>
            </div>
        </div>
        
        <div class="profile-section">
            <h3>Resume</h3>
            <?php if ($resume): ?>
                <p>Current resume: <a href="<?php echo sanitize($resume['resume_path']); ?>" target="_blank">View Resume</a></p>
            <?php else: ?>
                <p>No resume uploaded yet. Upload a resume to apply for jobs more quickly.</p>
            <?php endif; ?>
            
            <div class="profile-actions">
                <a href="/job-portal/upload-resume.php" class="btn btn-primary">Upload Resume</a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>