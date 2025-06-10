<?php
require_once '../includes/header.php';

// Redirect if not logged in or not a recruiter
if (!isLoggedIn() || !isRecruiter()) {
    header('Location: /');
    exit;
}

// Get job ID from URL
$job_id = isset($_GET['job_id']) ? (int)$_GET['job_id'] : 0;

// Verify job belongs to recruiter
$stmt = $conn->prepare("SELECT title FROM jobs WHERE id = ? AND company_id = ?");
$stmt->bind_param("ii", $job_id, $_SESSION['user_id']);
$stmt->execute();
$job = $stmt->get_result()->fetch_assoc();

if (!$job) {
    header('Location: /recruiter/dashboard.php');
    exit;
}

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $application_id = (int)$_POST['application_id'];
    $new_status = $_POST['status'];
    
    if (in_array($new_status, ['selected', 'rejected'])) {
        $stmt = $conn->prepare("UPDATE applications SET status = ? WHERE id = ? AND job_id = ?");
        $stmt->bind_param("sii", $new_status, $application_id, $job_id);
        $stmt->execute();
    }
}

// Get applicants
$stmt = $conn->prepare("
    SELECT a.*, u.name as applicant_name, u.email as applicant_email
    FROM applications a
    JOIN users u ON a.user_id = u.id
    WHERE a.job_id = ?
    ORDER BY a.applied_on DESC
");
$stmt->bind_param("i", $job_id);
$stmt->execute();
$applicants = $stmt->get_result();
?>

<div class="container">
    <div class="page-header">
        <h1>Applicants for: <?php echo htmlspecialchars($job['title']); ?></h1>
        <a href="/job-portal/recruiter/dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
    
    <?php if ($applicants->num_rows === 0): ?>
        <div class="alert alert-info">
            No applications received yet.
        </div>
    <?php else: ?>
        <div class="applicants-grid">
            <?php while ($applicant = $applicants->fetch_assoc()): ?>
                <div class="applicant-card">
                    <div class="applicant-header">
                        <h3><?php echo htmlspecialchars($applicant['applicant_name']); ?></h3>
                        <div class="status-badge status-<?php echo $applicant['status']; ?>">
                            <?php
                            switch ($applicant['status']) {
                                case 'selected':
                                    echo '🎉 Selected';
                                    break;
                                case 'rejected':
                                    echo '❌ Rejected';
                                    break;
                                default:
                                    echo '📝 Applied';
                            }
                            ?>
                        </div>
                    </div>
                    
                    <div class="applicant-details">
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($applicant['applicant_email']); ?></p>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($applicant['phone']); ?></p>
                        <p><strong>Applied on:</strong> <?php echo date('M d, Y', strtotime($applicant['applied_on'])); ?></p>
                        
                        <div class="mt-3">
                            <a href="/job-portal/<?php echo htmlspecialchars($applicant['resume_path']); ?>" 
                               class="btn btn-sm btn-info" target="_blank">View Resume</a>
                        </div>
                        
                        <?php if (!empty($applicant['cover_letter'])): ?>
                            <div class="mt-3">
                                <h4>Cover Letter</h4>
                                <div class="cover-letter-text">
                                    <?php echo nl2br(htmlspecialchars($applicant['cover_letter'])); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($applicant['additional_info'])): ?>
                            <div class="mt-3">
                                <h4>Additional Information</h4>
                                <div class="additional-info-text">
                                    <?php echo nl2br(htmlspecialchars($applicant['additional_info'])); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($applicant['status'] === 'applied'): ?>
                        <form method="POST" action="" class="status-update-form mt-4">
                            <input type="hidden" name="application_id" value="<?php echo $applicant['id']; ?>">
                            <div class="form-group">
                                <label for="status-<?php echo $applicant['id']; ?>">Update Application Status:</label>
                                <select id="status-<?php echo $applicant['id']; ?>" name="status" class="form-control">
                                    <option value="">Select Action</option>
                                    <option value="selected">Select Candidate</option>
                                    <option value="rejected">Reject Application</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary mt-2">Update Status</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.applicant-card {
    background: #fff;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    margin-bottom: 1.5rem;
}

.applicant-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.status-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.875rem;
    font-weight: 500;
}

.status-applied {
    background-color: #dbeafe;
    color: #1e40af;
}

.status-selected {
    background-color: #d1fae5;
    color: #065f46;
}

.status-rejected {
    background-color: #fee2e2;
    color: #b91c1c;
}

.applicant-details {
    margin-bottom: 1rem;
}

.cover-letter-text, .additional-info-text {
    background-color: #f9fafb;
    padding: 1rem;
    border-radius: 4px;
    margin-top: 0.5rem;
    white-space: pre-line;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.mt-2 {
    margin-top: 0.5rem;
}

.mt-3 {
    margin-top: 0.75rem;
}

.mt-4 {
    margin-top: 1rem;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}
</style>

<?php require_once '../includes/footer.php'; ?> 
