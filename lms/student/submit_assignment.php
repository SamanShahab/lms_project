<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include("../config/db.php");
include("../includes/header.php");

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit();
}

$student_id = $_SESSION['user']['id'];
$assignment_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$assignment_id) {
    header("Location: view_assignments.php");
    exit();
}

// Get assignment details
$stmt = $conn->prepare("SELECT a.*, c.name as course_name 
                      FROM assignments a
                      JOIN courses c ON a.course_id = c.id
                      WHERE a.id = ?");
$stmt->bind_param("i", $assignment_id);
$stmt->execute();
$assignment = $stmt->get_result()->fetch_assoc();

if (!$assignment) {
    header("Location: view_assignments.php");
    exit();
}

// Check if student is enrolled
$enrollment_stmt = $conn->prepare("SELECT 1 FROM enrollments 
                                 WHERE student_id = ? AND course_id = ?");
$enrollment_stmt->bind_param("ii", $student_id, $assignment['course_id']);
$enrollment_stmt->execute();
if ($enrollment_stmt->get_result()->num_rows === 0) {
    header("Location: view_assignments.php");
    exit();
}

// Check for existing submission
$sub_stmt = $conn->prepare("SELECT * FROM assignment_submissions 
                          WHERE assignment_id = ? AND student_id = ?");
$sub_stmt->bind_param("ii", $assignment_id, $student_id);
$sub_stmt->execute();
$submission = $sub_stmt->get_result()->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submission_text = $_POST['submission_text'] ?? '';
    $file_path = $submission['file_path'] ?? null;

    // Handle file upload
    if (isset($_FILES['submission_file']) && $_FILES['submission_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/submissions/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $filename = basename($_FILES['submission_file']['name']);
        $target_file = $upload_dir . time() . '_' . $filename;

        if (move_uploaded_file($_FILES['submission_file']['tmp_name'], $target_file)) {
            $file_path = $target_file;
        }
    }

    if (!empty($submission_text)) {
        if ($submission) {
            $update_stmt = $conn->prepare("UPDATE assignment_submissions 
                                         SET submission_text = ?, file_path = ?, submitted_at = NOW() 
                                         WHERE id = ?");
            $update_stmt->bind_param("ssi", $submission_text, $file_path, $submission['id']);
            $update_stmt->execute();
        } else {
            $insert_stmt = $conn->prepare("INSERT INTO assignment_submissions 
                                         (assignment_id, student_id, submission_text, file_path, submitted_at) 
                                         VALUES (?, ?, ?, ?, NOW())");
            $insert_stmt->bind_param("iiss", $assignment_id, $student_id, $submission_text, $file_path);
            $insert_stmt->execute();
        }

        // Update progress (simplified)
        $progress_stmt = $conn->prepare("INSERT INTO student_progress 
                                       (student_id, course_id, progress) 
                                       VALUES (?, ?, ?)
                                       ON DUPLICATE KEY UPDATE progress = ?");
        $new_progress = min(100, 80); // Example fixed increment
        $progress_stmt->bind_param("iiii", $student_id, $assignment['course_id'], $new_progress, $new_progress);
        $progress_stmt->execute();

        header("Location: view_assignments.php");
        exit();
    }
}
?>

<div class="container assignment-container">
    <div class="assignment-header">
        <h2>Submit Assignment: <?php echo htmlspecialchars($assignment['title']); ?></h2>
        <p>Course: <?php echo htmlspecialchars($assignment['course_name']); ?></p>
        <p>Due Date: <?php echo date('M j, Y', strtotime($assignment['due_date'])); ?></p>
    </div>

    <div class="assignment-form">
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="submission_text">Your Submission:</label>
                <textarea id="submission_text" name="submission_text" class="form-control" rows="10" required><?php 
                    echo $submission ? htmlspecialchars($submission['submission_text']) : ''; 
                ?></textarea>
            </div>

            <div class="form-group">
                <label for="submission_file">Attach File (optional):</label>
                <input type="file" name="submission_file" id="submission_file" class="form-control-file">
                <?php if (!empty($submission['file_path'])): ?>
                    <p>📎 Existing file: 
                        <a href="<?php echo $submission['file_path']; ?>" target="_blank">
                            View Submitted File
                        </a>
                    </p>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <?php echo $submission ? 'Update Submission' : 'Submit Assignment'; ?>
                </button>
                <a href="view_assignments.php" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include("../includes/footer.php"); ?>
