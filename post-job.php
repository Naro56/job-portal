<?php
require_once 'includes/header.php';

// Redirect if not logged in or not a recruiter
if (!isLoggedIn() || !isRecruiter()) {
    header('Location: /');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $type = $_POST['type'] ?? '';
    $location = trim($_POST['location'] ?? '');
    $salary = trim($_POST['salary'] ?? '');
    $experience = trim($_POST['experience'] ?? '');
    
    // Validate input
    if (empty($title) || empty($description) || empty($type) || empty($location) || empty($salary) || empty($experience)) {
        $error = "All fields are required.";
    } else {
        // Insert job
        $stmt = $conn->prepare("INSERT INTO jobs (title, description, type, location, salary, experience_required, company_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssi", $title, $description, $type, $location, $salary, $experience, $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            $success = "Job posted successfully!";
        } else {
            $error = "Error posting job. Please try again.";
        }
    }
}
?>

<div class="container">
    <div class="form-container">
        <h1>Post a New Job</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success); ?>
                <br>
                <a href="/job-portal/recruiter/dashboard.php">View your jobs</a>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="title">Job Title</label>
                <input type="text" id="title" name="title" class="form-control" required
                       value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="description">Job Description</label>
                <textarea id="description" name="description" class="form-control" rows="5" required><?php 
                    echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; 
                ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="type">Job Type</label>
                <select id="type" name="type" class="form-control" required>
                    <option value="">Select Type</option>
                    <option value="full-time" <?php echo isset($_POST['type']) && $_POST['type'] === 'full-time' ? 'selected' : ''; ?>>
                        Full Time
                    </option>
                    <option value="internship" <?php echo isset($_POST['type']) && $_POST['type'] === 'internship' ? 'selected' : ''; ?>>
                        Internship
                    </option>
                    <option value="part-time" <?php echo isset($_POST['type']) && $_POST['type'] === 'part-time' ? 'selected' : ''; ?>>
                        Part Time
                    </option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="location">Location</label>
                <input type="text" id="location" name="location" class="form-control" required
                       value="<?php echo isset($_POST['location']) ? htmlspecialchars($_POST['location']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="salary">Salary/CTC</label>
                <input type="text" id="salary" name="salary" class="form-control" required
                       placeholder="e.g., ₹10L - ₹15L or ₹25K/month"
                       value="<?php echo isset($_POST['salary']) ? htmlspecialchars($_POST['salary']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="experience">Experience Required</label>
                <input type="text" id="experience" name="experience" class="form-control" required
                       placeholder="e.g., 2-3 years or Fresher"
                       value="<?php echo isset($_POST['experience']) ? htmlspecialchars($_POST['experience']) : ''; ?>">
            </div>
            
            <button type="submit" class="btn btn-primary">Post Job</button>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 
