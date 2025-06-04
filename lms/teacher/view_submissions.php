<?php
session_start();
include("../config/db.php");
include("../includes/header.php");

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'teacher') {
    header("Location: ../auth/login.php");
    exit();
}

$teacher_id = $_SESSION['user']['id'];
?>

<style>
.m-container {
    max-width: 1100px;
    margin: 2rem auto;
    background: #fff;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 6px 15px rgba(0,0,0,0.1);
}
h2, h3 {
    color: #4a6fa5;
    margin-bottom: 1rem;
    border-bottom: 2px solid #e0e0e0;
    padding-bottom: 0.5rem;
}
.table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 2rem;
}
.table th, .table td {
    padding: 10px 12px;
    border: 1px solid #ddd;
}
.table th {
    background-color:rgb(89, 176, 179);
    text-align: center;
}
.badge {
    padding: 3px 7px;
    background-color: #d1ecf1;
    color: #0c5460;
    border-radius: 4px;
    font-size: 0.85rem;
}
.question-block {
    margin-bottom: 1rem;
    background:rgb(168, 214, 216);
    padding: 1rem;
    border-radius: 6px;
}
</style>

<div class="m-container">
    <h2>📂 Student Submissions Overview</h2>

    <!-- ASSIGNMENT SUBMISSIONS -->
    <h3>📝 Assignment Submissions</h3>
    <?php
    $assignment_sql = "
        SELECT s.id AS submission_id, u.name AS student_name, a.title AS assignment_title, 
               c.name AS course_name, s.file_path, s.submission_text, s.submitted_at
        FROM assignment_submissions s
        JOIN assignments a ON s.assignment_id = a.id
        JOIN courses c ON a.course_id = c.id
        JOIN users u ON s.student_id = u.id
        WHERE c.teacher_id = ?
        ORDER BY s.submitted_at DESC
    ";
    $stmt = $conn->prepare($assignment_sql);
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    $assignments = $stmt->get_result();

    if ($assignments->num_rows > 0): ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Course</th>
                    <th>Assignment</th>
                    <th>Text</th>
                    <th>File</th>
                    <th>Submitted At</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $assignments->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['assignment_title']); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($row['submission_text'])); ?></td>
                    <td>
                        <?php if (!empty($row['file_path'])): ?>
                            <a href="<?php echo htmlspecialchars($row['file_path']); ?>" target="_blank" class="badge">Download</a>
                        <?php else: ?>
                            <span class="badge">No file</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo date('M d, Y h:i A', strtotime($row['submitted_at'])); ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No assignment submissions found.</p>
    <?php endif; ?>


    <!-- QUIZ SUBMISSIONS -->
    <h3>🧠 Quiz Submissions</h3>
    <?php
    $quiz_sql = "
        SELECT DISTINCT qs.student_id, u.name AS student_name, c.name AS course_name, q.title AS quiz_title, q.id AS quiz_id
        FROM quiz_submissions qs
        JOIN users u ON qs.student_id = u.id
        JOIN quizzes q ON qs.quiz_id = q.id
        JOIN courses c ON q.course_id = c.id
        WHERE c.teacher_id = ?
        ORDER BY qs.student_id, qs.quiz_id
    ";
    $quiz_stmt = $conn->prepare($quiz_sql);
    $quiz_stmt->bind_param("i", $teacher_id);
    $quiz_stmt->execute();
    $quiz_result = $quiz_stmt->get_result();

    if ($quiz_result->num_rows > 0):
        while ($quiz = $quiz_result->fetch_assoc()):
            echo "<div class='question-block'>";
            echo "<strong>👤 " . htmlspecialchars($quiz['student_name']) . "</strong> | 
                  <span class='badge'>" . htmlspecialchars($quiz['course_name']) . "</span> <br>";
            echo "<strong>Quiz:</strong> " . htmlspecialchars($quiz['quiz_title']) . "<br><br>";

            // Fetch submitted answers
            $qa_stmt = $conn->prepare("
                SELECT qq.question_text, qs.answer
                FROM quiz_submissions qs
                JOIN quiz_questions qq ON qs.question_id = qq.id
                WHERE qs.student_id = ? AND qs.quiz_id = ?
            ");
            $qa_stmt->bind_param("ii", $quiz['student_id'], $quiz['quiz_id']);
            $qa_stmt->execute();
            $answers = $qa_stmt->get_result();

            if ($answers->num_rows > 0):
                while ($qa = $answers->fetch_assoc()):
                    echo "<strong>Q:</strong> " . htmlspecialchars($qa['question_text']) . "<br>";
                    echo "<strong>A:</strong> " . htmlspecialchars($qa['answer']) . "<br><br>";
                endwhile;
            else:
                echo "<em>No answers submitted.</em>";
            endif;

            echo "</div>";
        endwhile;
    else:
        echo "<p>No quiz submissions found.</p>";
    endif;
    ?>
</div>

<?php include("../includes/footer.php"); ?>
