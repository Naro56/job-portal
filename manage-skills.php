<?php
require_once 'includes/header.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    header('Location: /job-portal/index.php');
    exit;
}

$error = '';
$success = '';

// Handle adding a new skill
if (isset($_POST['add_skill'])) {
    $skill_name = trim($_POST['skill_name']);
    
    if (empty($skill_name)) {
        $error = 'Please enter a skill name';
    } else {
        // Check if skill exists in the skills table
        $stmt = $conn->prepare("SELECT id FROM skills WHERE name = ?");
        $stmt->bind_param("s", $skill_name);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Skill exists, get its ID
            $skill_id = $result->fetch_assoc()['id'];
        } else {
            // Skill doesn't exist, insert it
            $stmt = $conn->prepare("INSERT INTO skills (name) VALUES (?)");
            $stmt->bind_param("s", $skill_name);
            $stmt->execute();
            $skill_id = $conn->insert_id;
        }
        
        // Check if user already has this skill
        $stmt = $conn->prepare("SELECT id FROM user_skills WHERE user_id = ? AND skill_id = ?");
        $stmt->bind_param("ii", $_SESSION['user_id'], $skill_id);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            $error = 'You already have this skill in your profile';
        } else {
            // Add skill to user's profile
            $stmt = $conn->prepare("INSERT INTO user_skills (user_id, skill_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $_SESSION['user_id'], $skill_id);
            
            if ($stmt->execute()) {
                $success = 'Skill added successfully!';
            } else {
                $error = 'Failed to add skill: ' . $stmt->error;
            }
        }
    }
}

// Handle removing a skill
if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    $skill_id = (int)$_GET['remove'];
    
    $stmt = $conn->prepare("DELETE FROM user_skills WHERE user_id = ? AND skill_id = ?");
    $stmt->bind_param("ii", $_SESSION['user_id'], $skill_id);
    
    if ($stmt->execute()) {
        $success = 'Skill removed successfully!';
    } else {
        $error = 'Failed to remove skill: ' . $stmt->error;
    }
}

// Get user's current skills
$stmt = $conn->prepare("
    SELECT s.id, s.name 
    FROM skills s
    JOIN user_skills us ON s.id = us.skill_id
    WHERE us.user_id = ?
    ORDER BY s.name
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$skills = $stmt->get_result();

// Get popular skills for suggestions
$stmt = $conn->prepare("
    SELECT s.name, COUNT(us.id) as count
    FROM skills s
    JOIN user_skills us ON s.id = us.skill_id
    GROUP BY s.id
    ORDER BY count DESC
    LIMIT 20
");
$stmt->execute();
$popular_skills = $stmt->get_result();
?>

<div class="container">
    <div class="form-container">
        <h1 class="page-title">Manage Skills</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card skills-card">
                    <div class="card-header">
                        <h3><i class="fas fa-list"></i> Your Skills</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($skills->num_rows === 0): ?>
                            <div class="empty-state">
                                <i class="fas fa-lightbulb empty-icon"></i>
                                <p>You haven't added any skills yet. Add skills to help recruiters find you.</p>
                            </div>
                        <?php else: ?>
                            <div class="skills-list">
                                <?php while ($skill = $skills->fetch_assoc()): ?>
                                    <div class="skill-item">
                                        <span class="skill-badge">
                                            <?php echo sanitize($skill['name']); ?>
                                        </span>
                                        <a href="?remove=<?php echo $skill['id']; ?>" class="skill-remove" 
                                           onclick="return confirm('Are you sure you want to remove this skill?')">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card skills-card">
                    <div class="card-header">
                        <h3><i class="fas fa-plus-circle"></i> Add New Skill</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="skill-form">
                            <div class="form-group">
                                <label for="skill_name">Skill Name</label>
                                <div class="input-group">
                                    <input type="text" id="skill_name" name="skill_name" class="form-control" required 
                                           placeholder="e.g., PHP, JavaScript, Project Management">
                                    <div class="input-group-append">
                                        <button type="submit" name="add_skill" class="btn btn-primary">
                                            <i class="fas fa-plus"></i> Add
                                        </button>
                                    </div>
                                </div>
                                <small class="form-text text-muted">Enter a skill relevant to your profession</small>
                            </div>
                        </form>
                        
                        <?php if ($popular_skills->num_rows > 0): ?>
                            <div class="popular-skills mt-4">
                                <h4><i class="fas fa-fire"></i> Popular Skills</h4>
                                <p>Click to add to your profile:</p>
                                <div class="popular-skills-list">
                                    <?php while ($skill = $popular_skills->fetch_assoc()): ?>
                                        <a href="#" class="skill-suggestion" 
                                           onclick="document.getElementById('skill_name').value='<?php echo sanitize($skill['name']); ?>'; return false;">
                                            <?php echo sanitize($skill['name']); ?>
                                        </a>
                                    <?php endwhile; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="form-actions mt-4">
            <a href="profile.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Profile
            </a>
        </div>
    </div>
</div>

<style>
.page-title {
    margin-bottom: 30px;
    color: #333;
    border-bottom: 2px solid #f0f0f0;
    padding-bottom: 15px;
}

.skills-card {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    margin-bottom: 20px;
    border: none;
    border-radius: 8px;
}

.skills-card .card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #eee;
    padding: 15px 20px;
}

.skills-card .card-header h3 {
    margin: 0;
    font-size: 18px;
    color: #333;
}

.skills-card .card-body {
    padding: 20px;
}

.skills-list {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.skill-item {
    position: relative;
    display: inline-flex;
    align-items: center;
}

.skill-badge {
    background-color: #e9f5ff;
    color: #0066cc;
    padding: 8px 15px;
    border-radius: 20px;
    font-size: 14px;
    display: inline-block;
    margin-right: 5px;
}

.skill-remove {
    color: #dc3545;
    font-size: 14px;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background-color: #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: all 0.2s;
}

.skill-remove:hover {
    background-color: #dc3545;
    color: #fff;
}

.popular-skills-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 10px;
}

.skill-suggestion {
    background-color: #f8f9fa;
    color: #495057;
    padding: 6px 12px;
    border-radius: 15px;
    font-size: 13px;
    text-decoration: none;
    transition: all 0.2s;
    border: 1px solid #e9ecef;
}

.skill-suggestion:hover {
    background-color: #e9ecef;
    text-decoration: none;
}

.empty-state {
    text-align: center;
    padding: 20px;
    color: #6c757d;
}

.empty-icon {
    font-size: 40px;
    margin-bottom: 15px;
    color: #adb5bd;
}

.skill-form {
    margin-bottom: 20px;
}

.form-actions {
    margin-top: 30px;
    display: flex;
    justify-content: flex-start;
}
</style>

<?php require_once 'includes/footer.php'; ?>


