<?php
session_start();
require_once '../config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user']['id'];
$role = $_SESSION['user']['role'];

// Only students should access this page
if ($role !== 'student') {
    header("Location: ../student/dashboard.php");
    exit();
}

// Fetch student details
$stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

// Corrected query without created_at
$grades_query = "
    SELECT 
        g.id,
        g.grade,
        g.feedback,
        c.name AS course_name,
        a.title AS assignment_title,
        q.title AS quiz_title,
        CASE 
            WHEN g.grade >= 90 THEN 'Excellent'
            WHEN g.grade >= 80 THEN 'Good'
            WHEN g.grade >= 70 THEN 'Average'
            WHEN g.grade >= 60 THEN 'Pass'
            ELSE 'Fail'
        END AS grade_category
    FROM grades g
    LEFT JOIN assignments a ON g.assignment_id = a.id
    LEFT JOIN quizzes q ON g.quiz_id = q.id
    LEFT JOIN courses c ON (a.course_id = c.id OR q.course_id = c.id)
    WHERE g.student_id = ?
    ORDER BY g.id DESC
";

$stmt = $conn->prepare($grades_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$grades = $result->fetch_all(MYSQLI_ASSOC);

// Calculate overall average
$total_grades = 0;
$grade_count = count($grades);
foreach ($grades as $grade) {
    $total_grades += $grade['grade'];
}
$average_grade = $grade_count > 0 ? round($total_grades / $grade_count, 2) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Grades | Learning Management System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main class="grades-container">
        <div class="grades-header">
            <h2>My Grades</h2>
            <p>Welcome, <?php echo htmlspecialchars($student['name']); ?> | <?php echo htmlspecialchars($student['email']); ?></p>
        </div>

        <div class="performance-summary">
            <h3>Performance Overview</h3>
            <div class="progress-grid">
                <div class="progress-card">
                    <h4>Overall Average</h4>
                    <div class="progress-bar-container">
                        <div class="progress-bar" style="width: <?php echo $average_grade; ?>%;">
                            <span><?php echo $average_grade; ?>%</span>
                        </div>
                    </div>
                    <p>Based on <?php echo $grade_count; ?> graded items</p>
                </div>

                <div class="progress-card">
                    <h4>Grade Distribution</h4>
                    <?php
                    $grade_categories = ['Excellent', 'Good', 'Average', 'Pass', 'Fail'];
                    $category_counts = array_count_values(array_column($grades, 'grade_category'));
                    
                    foreach ($grade_categories as $category) {
                        $count = $category_counts[$category] ?? 0;
                        $percentage = $grade_count > 0 ? round(($count / $grade_count) * 100) : 0;
                        echo "<p>{$category}: {$count} ({$percentage}%)</p>";
                    }
                    ?>
                </div>
            </div>
        </div>

        <?php if ($grade_count > 0): ?>
            <table class="grades-table">
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Item</th>
                        <th>Grade</th>
                        <th>Category</th>
                        <th>Feedback</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($grades as $grade): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($grade['course_name']); ?></td>
                            <td>
                                <?php 
                                if (!empty($grade['assignment_title'])) {
                                    echo "Assignment: " . htmlspecialchars($grade['assignment_title']);
                                } elseif (!empty($grade['quiz_title'])) {
                                    echo "Quiz: " . htmlspecialchars($grade['quiz_title']);
                                } else {
                                    echo "N/A";
                                }
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($grade['grade']); ?>%</td>
                            <td>
                                <span class="grade-badge grade-<?php echo strtolower($grade['grade_category']); ?>">
                                    <?php echo htmlspecialchars($grade['grade_category']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($grade['feedback']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-grades">
                <p>No grades have been recorded yet.</p>
            </div>
        <?php endif; ?>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>