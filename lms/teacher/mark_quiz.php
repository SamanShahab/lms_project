<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'teacher') {
    header("Location: ../auth/login.php");
    exit();
}

$teacher_id = $_SESSION['user']['id'];

// Fetch all quizzes created by this teacher
$quizzes_query = "
    SELECT q.id, q.title, c.name AS course_name 
    FROM quizzes q
    JOIN courses c ON q.course_id = c.id
    WHERE c.teacher_id = ?
    ORDER BY c.name, q.title
";
$stmt = $conn->prepare($quizzes_query);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$quizzes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Handle form submission for grading
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['grade_submission'])) {
    $submission_id = $_POST['submission_id'];
    $grade = $_POST['grade'];
    $feedback = $_POST['feedback'];

    // Update the quiz submission with marks
    $update_stmt = $conn->prepare("UPDATE quiz_submissions SET marks = ? WHERE id = ?");
    $update_stmt->bind_param("di", $grade, $submission_id);
    $update_stmt->execute();

    // Insert or update the grade in grades table
    $grade_stmt = $conn->prepare("
        INSERT INTO grades (student_id, quiz_id, grade, feedback)
        VALUES (
            (SELECT student_id FROM quiz_submissions WHERE id = ?),
            (SELECT quiz_id FROM quiz_submissions WHERE id = ?),
            ?, ?
        )
        ON DUPLICATE KEY UPDATE grade = VALUES(grade), feedback = VALUES(feedback)
    ");
    $grade_stmt->bind_param("iids", $submission_id, $submission_id, $grade, $feedback);
    $grade_stmt->execute();

    $_SESSION['success_message'] = "Grades updated successfully!";
    header("Location: mark_quiz.php" . (isset($_GET['quiz_id']) ? "?quiz_id=" . intval($_GET['quiz_id']) : ""));
    exit();
}

// Get submissions for selected quiz
$submissions = [];
if (isset($_GET['quiz_id'])) {
    $quiz_id = $_GET['quiz_id'];
    
    $submissions_query = "
        SELECT 
            qs.id,
            u.id AS student_id,
            u.name AS student_name,
            q.title AS quiz_title,
            qq.question_text,
            qs.answer,
            qs.marks,
            g.grade,
            g.feedback,
            qs.file_path
        FROM quiz_submissions qs
        JOIN users u ON qs.student_id = u.id
        JOIN quiz_questions qq ON qs.question_id = qq.id
        JOIN quizzes q ON qs.quiz_id = q.id
        LEFT JOIN grades g ON g.student_id = qs.student_id AND g.quiz_id = q.id
        WHERE qs.quiz_id = ?
        ORDER BY u.name, qq.id
    ";
    
    $stmt = $conn->prepare($submissions_query);
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $submissions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

include("../includes/header.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Grade Quiz Submissions | Learning Management System</title>
    <link rel="stylesheet" href="../css/style.css" />
    <style>
        .grading-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 1rem;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .quiz-selector {
            margin-bottom: 2rem;
            padding: 1rem;
            background: #f5f5f5;
            border-radius: 5px;
        }
        
        .submission-list {
            margin-top: 2rem;
        }
        
        .submission-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 1rem;
            margin-bottom: 1rem;
            background: #f9f9f9;
        }
        
        .submission-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #eee;
        }
        
        .grade-form {
            margin-top: 1rem;
            padding: 1rem;
            background: #fff;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }
        
        .form-control {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .btn {
            padding: 0.5rem 1rem;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .btn:hover {
            background: #45a049;
        }
        
        .success-message {
            padding: 1rem;
            background: #dff0d8;
            color: #3c763d;
            border-radius: 4px;
            margin-bottom: 1rem;
        }

        a {
            color: #0066cc;
            text-decoration: underline;
        }
        a:hover {
            color: #003399;
        }

        .file-link {
            margin-top: 0.5rem;
            display: block;
        }
    </style>
</head>
<body>
    <main class="grading-container">
        <h1>Grade Quiz Submissions</h1>
        
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="success-message">
                <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        
        <div class="quiz-selector">
            <h2>Select Quiz to Grade</h2>
            <form method="GET" action="mark_quiz.php">
                <div class="form-group">
                    <label for="quiz_id">Quiz:</label>
                    <select name="quiz_id" id="quiz_id" class="form-control" required>
                        <option value="">-- Select Quiz --</option>
                        <?php foreach ($quizzes as $quiz): ?>
                            <option value="<?php echo $quiz['id']; ?>" 
                                <?php if (isset($_GET['quiz_id']) && $_GET['quiz_id'] == $quiz['id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($quiz['course_name'] . ' - ' . $quiz['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn">Load Submissions</button>
            </form>
        </div>
        
        <?php if (!empty($submissions)): ?>
            <div class="submission-list">
                <h2>Submissions for <?php echo htmlspecialchars($submissions[0]['quiz_title']); ?></h2>
                
                <?php
                // Group submissions by student
                $grouped_submissions = [];
                foreach ($submissions as $sub) {
                    $grouped_submissions[$sub['student_id']]['student_name'] = $sub['student_name'];
                    $grouped_submissions[$sub['student_id']]['questions'][] = $sub;
                }
                ?>
                
                <?php foreach ($grouped_submissions as $student_id => $student_data): ?>
                    <div class="submission-card">
                        <div class="submission-header">
                            <h3><?php echo htmlspecialchars($student_data['student_name']); ?></h3>
                        </div>
                        
                        <div class="submission-questions">
                            <?php foreach ($student_data['questions'] as $question): ?>
                                <div class="question">
                                    <p><strong>Question:</strong> <?php echo htmlspecialchars($question['question_text']); ?></p>
                                    <p><strong>Answer:</strong> <?php echo nl2br(htmlspecialchars($question['answer'])); ?></p>
                                    <?php if (!empty($question['file_path'])): ?>
                                        <a href="<?php echo htmlspecialchars($question['file_path']); ?>" target="_blank" class="file-link">View Uploaded File</a>
                                    <?php endif; ?>
                                    <hr>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <form method="POST" class="grade-form">
                            <input type="hidden" name="submission_id" value="<?php echo htmlspecialchars($student_data['questions'][0]['id']); ?>" />
                            <input type="hidden" name="grade_submission" value="1" />
                            
                            <div class="form-group">
                                <label for="grade_<?php echo $student_id; ?>">Grade (%)</label>
                                <input 
                                    type="number" 
                                    min="0" max="100" step="0.01" 
                                    id="grade_<?php echo $student_id; ?>" 
                                    name="grade" 
                                    value="<?php echo isset($student_data['questions'][0]['marks']) ? htmlspecialchars($student_data['questions'][0]['marks']) : ''; ?>" 
                                    class="form-control" 
                                    required
                                />
                            </div>
                            
                            <div class="form-group">
                                <label for="feedback_<?php echo $student_id; ?>">Feedback</label>
                                <textarea 
                                    id="feedback_<?php echo $student_id; ?>" 
                                    name="feedback" 
                                    class="form-control" 
                                    rows="3"
                                ><?php echo isset($student_data['questions'][0]['feedback']) ? htmlspecialchars($student_data['questions'][0]['feedback']) : ''; ?></textarea>
                            </div>
                            
                            <button type="submit" class="btn">Save Grade</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif (isset($_GET['quiz_id'])): ?>
            <p>No submissions found for this quiz.</p>
        <?php endif; ?>
    </main>
</body>
</html>

<?php
include("../includes/footer.php");
?>
