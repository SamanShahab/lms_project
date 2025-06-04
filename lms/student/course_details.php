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

if (!isset($_GET['id'])) {
    header("Location: view_courses.php");
    exit();
}

$course_id = $_GET['id'];
$student_id = $_SESSION['user']['id'];

// Check enrollment
$stmt = $conn->prepare("SELECT 1 FROM enrollments WHERE student_id = ? AND course_id = ?");
$stmt->bind_param("ii", $student_id, $course_id);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    header("Location: view_courses.php");
    exit();
}

// Get course details
$stmt = $conn->prepare("SELECT c.*, u.name as teacher_name FROM courses c 
                       JOIN users u ON c.teacher_id = u.id 
                       WHERE c.id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

if (!$course) {
    die("Course not found!");
}

// Get upcoming assignments
$assignments = [];
try {
    $stmt = $conn->prepare("SELECT * FROM assignments 
                          WHERE course_id = ? AND due_date >= CURDATE() 
                          ORDER BY due_date ASC LIMIT 3");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $assignments = $stmt->get_result();
} catch (mysqli_sql_exception $e) {
    $assignments = false;
}

// Get student progress
$progress = 0;
try {
    $stmt = $conn->prepare("SELECT progress FROM student_progress 
                          WHERE student_id = ? AND course_id = ?");
    $stmt->bind_param("ii", $student_id, $course_id);
    $stmt->execute();
    $progress_result = $stmt->get_result()->fetch_assoc();
    $progress = $progress_result ? $progress_result['progress'] : 0;
} catch (mysqli_sql_exception $e) {
    $progress = 0;
}
?>

<div class="container">
    <div class="course-details-container">
        <div class="course-header">
            <h1><?php echo htmlspecialchars($course['name']); ?></h1>
            <div class="course-meta">
                <span><i class="fas fa-user-tie"></i> <?php echo htmlspecialchars($course['teacher_name']); ?></span>
                <span><i class="fas fa-book"></i> <?php echo htmlspecialchars($course['code'] ?? 'N/A'); ?></span>
                <span><i class="fas fa-chart-line"></i> Progress: <?php echo $progress; ?>%</span>
            </div>
            <p><?php echo htmlspecialchars($course['description']); ?></p>
        </div>

        <div class="course-content">
            <div class="course-main">
                <section class="course-assignments">
                    <h2>Upcoming Assignments</h2>
                    <?php if ($assignments && $assignments->num_rows > 0): ?>
                        <?php while ($assignment = $assignments->fetch_assoc()): ?>
                            <div class="assignment">
                                <h3><?php echo htmlspecialchars($assignment['title']); ?></h3>
                                <p>Due: <?php echo date('M j, Y', strtotime($assignment['due_date'])); ?></p>
                                <p><?php echo htmlspecialchars($assignment['description']); ?></p>
                                <a href="submit_assignment.php?id=<?php echo $assignment['id']; ?>" class="btn">Submit Assignment</a>
                            </div>
                        <?php endwhile; ?>
                        <a href="view_assignments.php?course_id=<?php echo $course_id; ?>" class="btn-view-all">View All Assignments</a>
                    <?php else: ?>
                        <p>No upcoming assignments.</p>
                    <?php endif; ?>
                </section>
            </div>

            <div class="course-sidebar">
                <div class="sidebar-section">
                    <h3>Course Progress</h3>
                    <div class="progress-bar-container">
                        <div class="progress" style="width: <?php echo $progress; ?>%;">
                            <span><?php echo $progress; ?>%</span>
                        </div>
                    </div>
                    <a href="view_grades.php?course_id=<?php echo $course_id; ?>" class="btn-view-all">View Grades</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("../includes/footer.php"); ?>