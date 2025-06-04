<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'teacher') {
    header("Location: ../auth/login.php");
    exit();
}

include("../includes/header.php");

?>

<style>
    /* Teacher Dashboard Footer Fix */
    .teacher-dashboard + footer {
        width: 100vw;
        left: 50%;
        transform: translateX(-50%);
        position: relative;
        margin-top: 2rem;
    }
    
    /* Ensure no horizontal scroll */
    body {
        overflow-x: hidden;
    }
</style>

<main class="teacher-dashboard"> <div class="teacher-welcome"> <h2>Welcome, <?php echo htmlspecialchars($_SESSION['user']['name']); ?></h2> <p>Teacher Dashboard</p> </div>
<div class="dashboard-actions">
    <!-- Courses & Students -->
    <div class="dashboard-card">
        <a href="courses.php">
            <div class="card-icon">📚</div>
            <h3>My Courses & Students</h3>
            <p>Manage your courses and view enrolled students with detailed analytics</p>
            <div class="card-badge">Manage</div>
        </a>
    </div>

    <!-- Upload Quiz -->
    <div class="dashboard-card">
        <a href="upload_quiz.php">
            <div class="card-icon">✏️</div>
            <h3>Create Quiz</h3>
            <p>Design new quizzes with multiple question types and automated grading</p>
            <div class="card-badge">New</div>
        </a>
    </div>

    <!-- Mark Quizzes -->
    <div class="dashboard-card">
        <a href="mark_quiz.php">
            <div class="card-icon">🔖</div>
            <h3>Grade Assessments</h3>
            <p>Evaluate student submissions and provide personalized feedback</p>
            <div class="card-badge">Grade</div>
        </a>
    </div>

    <!-- Attendance -->
    <div class="dashboard-card">
        <a href="take_attendance.php">
            <div class="card-icon">📅</div>
            <h3>Attendance System</h3>
            <p>Track and analyze student attendance patterns and reports</p>
            <div class="card-badge">Track</div>
        </a>
    </div>

    <!-- Upload Assignments -->
    <div class="dashboard-card">
        <a href="upload_assignment.php">
            <div class="card-icon">📝</div>
            <h3>Create Assignments</h3>
            <p>Post new assignments with deadlines and submission guidelines</p>
            <div class="card-badge">Create</div>
        </a>
    </div>

    <!-- View Submissions -->
    <div class="dashboard-card">
        <a href="view_submissions.php">
            <div class="card-icon">📂</div>
            <h3>Submissions Portal</h3>
            <p>Review, grade, and provide feedback on student work submissions</p>
            <div class="card-badge">Review</div>
        </a>
    </div>
</div>


<?php include("../includes/footer.php"); ?>