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

// Get enrolled courses for the student
$stmt = $conn->prepare("SELECT c.* FROM enrollments e 
                       JOIN courses c ON e.course_id = c.id 
                       WHERE e.student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<div class='container'><p>You are not enrolled in any courses yet.</p></div>";
}
?>

<div class="container">
    <h2>My Courses</h2>
    
    <div class="course-grid">
        <?php while ($course = $result->fetch_assoc()): 
            // Get teacher name for each course
            $teacher_stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
            $teacher_stmt->bind_param("i", $course['teacher_id']);
            $teacher_stmt->execute();
            $teacher = $teacher_stmt->get_result()->fetch_assoc();
        ?>
        <div class="course-card">
            <h3><?php echo htmlspecialchars($course['name']); ?></h3>
            <p>Teacher: <?php echo htmlspecialchars($teacher['name']); ?></p>
            <p><?php echo htmlspecialchars($course['description']); ?></p>
            <div class="progress-bar">
                <?php 
                // Get student progress
                $progress_stmt = $conn->prepare("SELECT progress FROM student_progress 
                                               WHERE student_id = ? AND course_id = ?");
                $progress_stmt->bind_param("ii", $student_id, $course['id']);
                $progress_stmt->execute();
                $progress = $progress_stmt->get_result()->fetch_assoc();
                $progress_value = $progress ? $progress['progress'] : 0;
                ?>
                <div class="progress" style="width: <?php echo $progress_value; ?>%;">
                    <span><?php echo $progress_value; ?>%</span>
                </div>
            </div>
            <a href="course_details.php?id=<?php echo $course['id']; ?>" class="btn">View Course</a>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<?php include("../includes/footer.php"); ?>