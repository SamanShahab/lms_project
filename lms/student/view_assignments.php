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
$course_id = isset($_GET['course_id']) ? $_GET['course_id'] : null;

// Get assignments for the student, optionally filtered by course
if ($course_id) {
    $stmt = $conn->prepare("SELECT a.*, c.name as course_name 
                          FROM assignments a
                          JOIN enrollments e ON a.course_id = e.course_id
                          JOIN courses c ON a.course_id = c.id
                          WHERE e.student_id = ? AND a.course_id = ?
                          ORDER BY a.due_date ASC");
    $stmt->bind_param("ii", $student_id, $course_id);
} else {
    $stmt = $conn->prepare("SELECT a.*, c.name as course_name 
                          FROM assignments a
                          JOIN enrollments e ON a.course_id = e.course_id
                          JOIN courses c ON a.course_id = c.id
                          WHERE e.student_id = ?
                          ORDER BY a.due_date ASC");
    $stmt->bind_param("i", $student_id);
}

$stmt->execute();
$assignments = $stmt->get_result();
?>

<div class="container assignment-container">
    <div class="assignment-header">
        <h2><?php echo $course_id ? 'Course Assignments' : 'All Your Assignments'; ?></h2>
        <p><?php echo $course_id ? 'Assignments for this course' : 'View and submit all your assignments'; ?></p>
    </div>

    <div class="assignment-list">
        <?php if ($assignments->num_rows > 0): ?>
            <table class="assignment-table">
                <thead>
                    <tr>
                        <?php if (!$course_id): ?>
                            <th>Course</th>
                        <?php endif; ?>
                        <th>Assignment</th>
                        <th>Description</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($assignment = $assignments->fetch_assoc()): 
                        // Check if assignment has been submitted
                        $sub_stmt = $conn->prepare("SELECT 1 FROM assignment_submissions 
                                                  WHERE assignment_id = ? AND student_id = ?");
                        $sub_stmt->bind_param("ii", $assignment['id'], $student_id);
                        $sub_stmt->execute();
                        $is_submitted = $sub_stmt->get_result()->num_rows > 0;
                    ?>
                        <tr>
                            <?php if (!$course_id): ?>
                                <td><?php echo htmlspecialchars($assignment['course_name']); ?></td>
                            <?php endif; ?>
                            <td><?php echo htmlspecialchars($assignment['title']); ?></td>
                            <td><?php echo htmlspecialchars($assignment['description']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($assignment['due_date'])); ?></td>
                            <td>
                                <span class="status-badge <?php echo $is_submitted ? 'status-present' : 'status-absent'; ?>">
                                    <?php echo $is_submitted ? 'Submitted' : 'Pending'; ?>
                                </span>
                            </td>
                            <td>
                                <a href="submit_assignment.php?id=<?php echo $assignment['id']; ?>" 
                                   class="btn <?php echo $is_submitted ? 'btn-outline' : 'btn-primary'; ?>">
                                    <?php echo $is_submitted ? 'View/Edit' : 'Submit'; ?>
                                </a>

                                <?php if (!empty($assignment['file_path'])): ?>
                                    <br>
                                    <a href="<?php echo $assignment['file_path']; ?>" 
                                       class="btn-outline" 
                                       target="_blank" 
                                       style="margin-top: 6px; display: inline-block;">
                                       📎 Download File
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-assignments">
                <p>No assignments found.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include("../includes/footer.php"); ?>
