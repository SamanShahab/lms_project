<?php
session_start();
include("../config/db.php");
include("../includes/header.php");

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'teacher') {
    header("Location: ../auth/login.php");
    exit();
}

$teacher_id = $_SESSION['user']['id'];
$courses_stmt = $conn->prepare("SELECT id, name, description FROM courses WHERE teacher_id = ?");
$courses_stmt->bind_param("i", $teacher_id);
$courses_stmt->execute();
$courses_result = $courses_stmt->get_result();
?>

<style>
    .teacher-welcome {
        background: linear-gradient(135deg, #4a6fa5, #3a5a8a);
        color: white;
        padding: 2rem;
        border-radius: 10px;
        margin-bottom: 2rem;
        text-align: center;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .course-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 25px;
    }

    .course-card {
        background: #fff;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        border: 1px solid #e0e0e0;
        transition: transform 0.3s ease;
    }

    .course-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 15px rgba(0,0,0,0.1);
    }

    .course-card h3 {
        color: #4a6fa5;
        margin-top: 0;
    }

    .course-description {
        color: #6c757d;
        margin-bottom: 10px;
        font-size: 0.95rem;
    }

    .course-meta-info {
        font-size: 0.9rem;
        margin-bottom: 10px;
    }

    .student-list {
        list-style: none;
        padding-left: 0;
        margin-top: 10px;
        margin-bottom: 15px;
    }

    .student-list li {
        padding: 6px 10px;
        border-bottom: 1px solid #eee;
        color: #333;
    }

    .no-students {
        font-style: italic;
        color: #999;
        margin-bottom: 15px;
    }

    .course-actions {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 15px;
        align-items: center;
    }

    .btn {
        padding: 10px 20px;
        border-radius: 5px;
        background: #4a6fa5;
        color: white;
        border: none;
        text-decoration: none;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.3s ease-in-out;
        width: 180px;
        text-align: center;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }

    .btn:hover {
        background: #2f4973;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        transform: translateY(-2px);
    }
</style>

<div class="container">
    <div class="teacher-welcome">
        <h2>My Courses</h2>
        <p>Overview of your classes and enrolled students</p>
    </div>

    <div class="course-grid">
        <?php while ($course = $courses_result->fetch_assoc()): ?>
            <?php
            $course_id = $course['id'];
            $students_stmt = $conn->prepare("
                SELECT u.name FROM enrollments e 
                JOIN users u ON e.student_id = u.id 
                WHERE e.course_id = ?
            ");
            $students_stmt->bind_param("i", $course_id);
            $students_stmt->execute();
            $students_result = $students_stmt->get_result();
            ?>
            
            <div class="course-card">
                <h3><?php echo htmlspecialchars($course['name']); ?></h3>
                <p class="course-description"><?php echo htmlspecialchars($course['description']); ?></p>
                <p class="course-meta-info"><strong>Total Students:</strong> <?php echo $students_result->num_rows; ?></p>

                <?php if ($students_result->num_rows > 0): ?>
                    <ul class="student-list">
                        <?php while ($student = $students_result->fetch_assoc()): ?>
                            <li>👤 <?php echo htmlspecialchars($student['name']); ?></li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p class="no-students">No students enrolled.</p>
                <?php endif; ?>

                <div class="course-actions">
                    <a href="upload_assignment.php?course_id=<?php echo $course_id; ?>" class="btn">Upload Assignment</a>
                    <a href="take_attendance.php?course_id=<?php echo $course_id; ?>" class="btn">Mark Attendance</a>
                    <a href="grades.php?course_id=<?php echo $course_id; ?>" class="btn">View Grades</a>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<?php include("../includes/footer.php"); ?>
