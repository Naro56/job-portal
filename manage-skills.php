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
        <h1>Manage Skills</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Your Skills</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($skills->num_rows === 0): ?>
                            <p>You haven't added any skills yet. Add skills to help recruiters find you.</p>
                        <?php else: ?>
                            <div class="skills-list">
                                <?php while ($skill = $skills->fetch_assoc()): ?>
                                    <div class="skill-item">
                                        <span class="skill-badge"><?php echo sanitize($skill['name']); ?></span>
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
                <div class="card">
                    <div class="card-header">
                        <h3>Add New Skill</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="form-group">
                                <label for="skill_name">Skill Name</label>
                                <input type="text" id="skill_name" name="skill_name" class="form-control" required>
                                <small class="form-text text-muted">Enter a skill relevant to your profession</small>
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" name="add_skill" class="btn btn-primary">Add Skill</button>
                            </div>
                        </form>
                        
                        <?php if ($popular_skills->num_rows > 0): ?>
                            <div class="popular-skills mt-4">
                                <h4>Popular Skills</h4>
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
            <a href="profile.php" class="btn btn-secondary">Back to Profile</a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>