<?php
require_once 'includes/header.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ' . (isRecruiter() ? 'recruiter/dashboard.php' : 'dashboard.php'));
    exit;
}

$error = '';
$success = '';
$role = isset($_POST['role']) ? $_POST['role'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? '';
    
    // Company details for recruiters
    $company_name = trim($_POST['company_name'] ?? '');
    $company_website = trim($_POST['company_website'] ?? '');
    $company_description = trim($_POST['company_description'] ?? '');
    
    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password) || empty($role)) {
        $error = 'Please fill in all required fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } elseif ($role === 'recruiter' && (empty($company_name) || empty($company_description))) {
        $error = 'Company name and description are required for recruiter accounts';
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Email already exists. Please use a different email or login.';
        } else {
            // If validation passes, create user account
            if (empty($error)) {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Begin transaction
                $conn->begin_transaction();
                
                try {
                    // Insert user
                    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, company_name, company_website, company_description) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssssss", $name, $email, $hashed_password, $role, $company_name, $company_website, $company_description);
                    $stmt->execute();
                    
                    // Get user ID
                    $user_id = $conn->insert_id;
                    
                    // Commit transaction
                    $conn->commit();
                    
                    // Set session variables
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_role'] = $role;
                    
                    if ($role === 'recruiter') {
                        $_SESSION['company_name'] = $company_name;
                        header('Location: /job-portal/recruiter/dashboard.php');
                    } else {
                        header('Location: /job-portal/dashboard.php');
                    }
                    exit;
                } catch (Exception $e) {
                    // Rollback transaction on error
                    $conn->rollback();
                    $error = "An error occurred. Please try again.";
                }
            }
        }
    }
}
?>

<div class="container">
    <div class="form-container">
        <h1>Create an Account</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success); ?>
                <br>
                <a href="login.php">Click here to login</a>
            </div>
        <?php else: ?>
            <form method="POST" action="signup.php" id="signupForm">
                <div class="form-group">
                    <label for="name">Full Name *</label>
                    <input type="text" id="name" name="name" class="form-control" required 
                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" class="form-control" required
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" class="form-control" required
                           minlength="6">
                    <small class="form-text text-muted">Password must be at least 6 characters long</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="role">I am a *</label>
                    <select id="role" name="role" class="form-control" required onchange="toggleCompanyFields()">
                        <option value="">Select Role</option>
                        <option value="jobseeker" <?php echo isset($_POST['role']) && $_POST['role'] === 'jobseeker' ? 'selected' : ''; ?>>
                            Job Seeker
                        </option>
                        <option value="recruiter" <?php echo isset($_POST['role']) && $_POST['role'] === 'recruiter' ? 'selected' : ''; ?>>
                            Recruiter
                        </option>
                    </select>
                </div>

                <div id="companyFields" style="display: none;">
                    <h3>Company Details</h3>
                    
                    <div class="form-group">
                        <label for="company_name">Company Name *</label>
                        <input type="text" id="company_name" name="company_name" class="form-control"
                               value="<?php echo isset($_POST['company_name']) ? htmlspecialchars($_POST['company_name']) : ''; ?>">
                        <small class="form-text text-muted">The name of the company you represent</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="company_website">Company Website</label>
                        <input type="url" id="company_website" name="company_website" class="form-control"
                               placeholder="https://example.com"
                               value="<?php echo isset($_POST['company_website']) ? htmlspecialchars($_POST['company_website']) : ''; ?>">
                        <small class="form-text text-muted">Optional</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="company_description">Company Description *</label>
                        <textarea id="company_description" name="company_description" class="form-control" rows="4"><?php 
                            echo isset($_POST['company_description']) ? htmlspecialchars($_POST['company_description']) : ''; 
                        ?></textarea>
                        <small class="form-text text-muted">Brief description of your company</small>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Sign Up</button>
            </form>
            
            <p class="mt-3">
                Already have an account? 
                <a href="login.php">Login</a>
            </p>
            
            <script>
                // Function to toggle company fields based on role selection
                function toggleCompanyFields() {
                    const roleSelect = document.getElementById('role');
                    const companyFields = document.getElementById('companyFields');
                    
                    if (roleSelect.value === 'recruiter') {
                        companyFields.style.display = 'block';
                        document.getElementById('company_name').required = true;
                        document.getElementById('company_description').required = true;
                    } else {
                        companyFields.style.display = 'none';
                        document.getElementById('company_name').required = false;
                        document.getElementById('company_description').required = false;
                    }
                }
                
                // Initialize on page load
                document.addEventListener('DOMContentLoaded', function() {
                    toggleCompanyFields();
                });
            </script>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
