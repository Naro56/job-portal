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
    SELECT s.name 
    FROM skills s
    JOIN user_skills us ON s.id = us.skill_id
    WHERE us.user_id = ?
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$skills_result = $stmt->get_result();
$skills = [];
while ($skill = $skills_result->fetch_assoc()) {
    $skills[] = $skill['name'];
}

// Check if user has a resume
$hasResume = false;
$resumePath = '';
$resumeDate = '';

// Get user's resume from user_resumes table
$stmt = $conn->prepare("
    SELECT resume_path, uploaded_on 
    FROM user_resumes 
    WHERE user_id = ? 
    ORDER BY uploaded_on DESC 
    LIMIT 1
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$resume_result = $stmt->get_result();

if ($resume_result->num_rows > 0) {
    $resume = $resume_result->fetch_assoc();
    $hasResume = true;
    $resumePath = $resume['resume_path'];
    $resumeDate = $resume['uploaded_on'];
}
?>

<div class="container profile-container">
    <h1 class="profile-title">My Profile</h1>
    
    <div class="profile-content">
        <div class="profile-sidebar">
            <div class="profile-avatar">
                <div class="avatar-circle">
                    <span class="initials"><?php echo strtoupper(substr($user['name'], 0, 1)); ?></span>
                </div>
            </div>
            
            <div class="profile-nav">
                <a href="#personal-info" class="profile-nav-item active">
                    <i class="fas fa-user"></i> Personal Info
                </a>
                <a href="#skills" class="profile-nav-item">
                    <i class="fas fa-code"></i> Skills
                </a>
                <a href="#resume" class="profile-nav-item">
                    <i class="fas fa-file-alt"></i> Resume
                </a>
                <a href="edit-profile.php" class="profile-nav-item">
                    <i class="fas fa-edit"></i> Edit Profile
                </a>
            </div>
        </div>
        
        <div class="profile-main">
            <div class="profile-header">
                <h2 class="profile-name"><?php echo sanitize($user['name']); ?></h2>
                <p class="profile-email"><?php echo sanitize($user['email']); ?></p>
                <?php if (isset($user['phone']) && !empty($user['phone'])): ?>
                    <p class="profile-phone"><i class="fas fa-phone"></i> <?php echo sanitize($user['phone']); ?></p>
                <?php endif; ?>
            </div>
            
            <div class="profile-section" id="personal-info">
                <div class="section-header">
                    <h3><i class="fas fa-user-circle"></i> Personal Information</h3>
                </div>
                <div class="section-content">
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Full Name</span>
                            <span class="info-value"><?php echo sanitize($user['name']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Email Address</span>
                            <span class="info-value"><?php echo sanitize($user['email']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Phone Number</span>
                            <span class="info-value">
                                <?php echo (isset($user['phone']) && !empty($user['phone'])) ? sanitize($user['phone']) : 'Not provided'; ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Account Type</span>
                            <span class="info-value">
                                <?php echo ($user['role'] == 'recruiter') ? 'Recruiter' : 'Job Seeker'; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="profile-section" id="skills">
                <div class="section-header">
                    <h3><i class="fas fa-code"></i> Skills</h3>
                    <a href="manage-skills.php" class="btn btn-sm btn-outline">
                        <i class="fas fa-plus"></i> Manage Skills
                    </a>
                </div>
                <div class="section-content">
                    <?php if (empty($skills)): ?>
                        <div class="empty-state">
                            <i class="fas fa-lightbulb empty-icon"></i>
                            <p>You haven't added any skills yet.</p>
                            <a href="manage-skills.php" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Add Skills
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="skills-list">
                            <?php foreach ($skills as $skill): ?>
                                <span class="skill-badge"><?php echo sanitize($skill); ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="profile-section" id="resume">
                <div class="section-header">
                    <h3><i class="fas fa-file-alt"></i> Resume</h3>
                </div>
                <div class="section-content">
                    <?php if ($hasResume): ?>
                        <div class="resume-preview">
                            <div class="resume-info">
                                <i class="fas fa-file-pdf resume-icon"></i>
                                <div>
                                    <h4>Your Resume</h4>
                                    <p>Uploaded on: <?php echo date('F j, Y, g:i a', strtotime($resumeDate)); ?></p>
                                </div>
                            </div>
                            <div class="resume-actions">
                                <a href="<?php echo sanitize($resumePath); ?>" class="btn btn-sm btn-outline" target="_blank">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="upload-resume.php" class="btn btn-sm btn-primary">
                                    <i class="fas fa-sync-alt"></i> Update
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-file-upload empty-icon"></i>
                            <p>You haven't uploaded a resume yet.</p>
                            <a href="upload-resume.php" class="btn btn-primary btn-sm">
                                <i class="fas fa-upload"></i> Upload Resume
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Profile Page Styles */
.profile-container {
    max-width: 1000px;
    margin: 2rem auto;
}

.profile-title {
    font-size: 2rem;
    color: #333;
    margin-bottom: 2rem;
    text-align: center;
    position: relative;
}

.profile-title:after {
    content: '';
    display: block;
    width: 50px;
    height: 4px;
    background: #2563eb;
    margin: 0.5rem auto;
    border-radius: 2px;
}

.profile-content {
    display: flex;
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
}

/* Sidebar Styles */
.profile-sidebar {
    width: 250px;
    background: #f8f9fa;
    padding: 2rem 0;
    border-right: 1px solid #eee;
}

.profile-avatar {
    display: flex;
    justify-content: center;
    margin-bottom: 2rem;
}

.avatar-circle {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, #2563eb, #3b82f6);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2.5rem;
    font-weight: bold;
    box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3);
}

.profile-nav {
    display: flex;
    flex-direction: column;
}

.profile-nav-item {
    padding: 0.75rem 1.5rem;
    color: #4b5563;
    text-decoration: none;
    transition: all 0.2s;
    border-left: 3px solid transparent;
}

.profile-nav-item:hover, .profile-nav-item.active {
    background: rgba(37, 99, 235, 0.05);
    color: #2563eb;
    border-left-color: #2563eb;
}

.profile-nav-item i {
    margin-right: 0.5rem;
    width: 20px;
    text-align: center;
}

/* Main Content Styles */
.profile-main {
    flex: 1;
    padding: 2rem;
}

.profile-header {
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid #eee;
}

.profile-name {
    font-size: 1.75rem;
    color: #1f2937;
    margin-bottom: 0.25rem;
}

.profile-email {
    color: #6b7280;
    font-size: 1rem;
    margin-bottom: 0.5rem;
}

.profile-phone {
    color: #6b7280;
    font-size: 1rem;
}

.profile-section {
    margin-bottom: 2.5rem;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.25rem;
}

.section-header h3 {
    font-size: 1.25rem;
    color: #374151;
    display: flex;
    align-items: center;
}

.section-header h3 i {
    margin-right: 0.5rem;
    color: #2563eb;
}

.section-content {
    background: #f9fafb;
    padding: 1.5rem;
    border-radius: 8px;
}

/* Info Grid */
.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1.5rem;
}

.info-item {
    display: flex;
    flex-direction: column;
}

.info-label {
    font-size: 0.875rem;
    color: #6b7280;
    margin-bottom: 0.25rem;
}

.info-value {
    font-size: 1rem;
    color: #1f2937;
    font-weight: 500;
}

/* Skills List */
.skills-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.skill-badge {
    background: rgba(37, 99, 235, 0.1);
    color: #2563eb;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
}

/* Resume Preview */
.resume-preview {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.resume-info {
    display: flex;
    align-items: center;
}

.resume-icon {
    font-size: 2.5rem;
    color: #ef4444;
    margin-right: 1rem;
}

.resume-info h4 {
    margin: 0;
    color: #1f2937;
    font-size: 1.125rem;
}

.resume-info p {
    margin: 0.25rem 0 0;
    color: #6b7280;
    font-size: 0.875rem;
}

.resume-actions {
    display: flex;
    gap: 0.5rem;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 2rem 1rem;
}

.empty-icon {
    font-size: 3rem;
    color: #d1d5db;
    margin-bottom: 1rem;
}

.empty-state p {
    color: #6b7280;
    margin-bottom: 1rem;
}

/* Buttons */
.btn-outline {
    background: transparent;
    border: 1px solid #d1d5db;
    color: #4b5563;
}

.btn-outline:hover {
    background: #f3f4f6;
    border-color: #9ca3af;
}

.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .profile-content {
        flex-direction: column;
    }
    
    .profile-sidebar {
        width: 100%;
        border-right: none;
        border-bottom: 1px solid #eee;
        padding: 1.5rem;
    }
    
    .profile-avatar {
        margin-bottom: 1.5rem;
    }
    
    .profile-nav {
        flex-direction: row;
        flex-wrap: wrap;
        justify-content: center;
        gap: 0.5rem;
    }
    
    .profile-nav-item {
        padding: 0.5rem 1rem;
        border-left: none;
        border-radius: 4px;
        background: #f3f4f6;
    }
    
    .profile-nav-item.active {
        background: rgba(37, 99, 235, 0.1);
    }
    
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .resume-preview {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .resume-actions {
        margin-top: 1rem;
        width: 100%;
        justify-content: flex-start;
    }
}
</style>

<script>
// Smooth scrolling for anchor links
document.querySelectorAll('.profile-nav-item').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        const href = this.getAttribute('href');
        
        if (href.startsWith('#')) {
            e.preventDefault();
            
            const targetElement = document.querySelector(href);
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 20,
                    behavior: 'smooth'
                });
            }
            
            // Update active class
            document.querySelectorAll('.profile-nav-item').forEach(item => {
                item.classList.remove('active');
            });
            this.classList.add('active');
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>


