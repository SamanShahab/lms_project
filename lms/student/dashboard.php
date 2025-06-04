<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit();
}

include("../includes/header.php");
?>

<div class="container">
    <div class="student-dashboard">
        <div class="welcome-banner">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['user']['name']); ?></h2>
            <p>Student Dashboard | <?php echo htmlspecialchars($_SESSION['user']['email']); ?></p>
        </div>
        
        <div class="dashboard-grid">
            <div class="dashboard-card" onclick="location.href='view_courses.php'">
                <div class="card-icon">📚</div>
                <h3>My Courses</h3>
                <p>View your enrolled courses</p>
            </div>
            
            <div class="dashboard-card" onclick="location.href='view_assignments.php'">
                <div class="card-icon">📝</div>
                <h3>Assignments</h3>
                <p>View and submit assignments</p>
            </div>
            
            <div class="dashboard-card" onclick="location.href='view_attendance.php'">
                <div class="card-icon">✅</div>
                <h3>Attendance</h3>
                <p>View your attendance records</p>
            </div>
            
            <div class="dashboard-card" onclick="location.href='view_grades.php'">
                <div class="card-icon">📊</div>
                <h3>Grades</h3>
                <p>Check your grades</p>
            </div>

            <div class="dashboard-card" onclick="location.href='attempt_quiz.php'">
                <div class="card-icon">🧠</div>
                <h3>Attempt Quiz</h3>
                <p>View and take available quizzes</p>
            </div>
        </div>
    </div>
</div>

<?php include("../includes/footer.php"); ?>
