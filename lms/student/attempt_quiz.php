<?php
session_start();
include("../config/db.php");
include("../includes/header.php");

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit();
}

$student_id = $_SESSION['user']['id'];
$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quiz_id'], $_POST['answers'])) {
    $quiz_id = $_POST['quiz_id'];
    $answers = $_POST['answers'];

    foreach ($answers as $question_id => $answer) {
        $stmt = $conn->prepare("INSERT INTO quiz_submissions (student_id, quiz_id, question_id, answer) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $student_id, $quiz_id, $question_id, $answer);
        $stmt->execute();
    }

    $message = "✅ Quiz submitted successfully!";
}

// Show available quizzes
$quizzes = $conn->prepare("
    SELECT q.id, q.title, c.name as course_name
    FROM quizzes q
    JOIN courses c ON q.course_id = c.id
    JOIN enrollments e ON e.course_id = c.id
    WHERE e.student_id = ?
");
$quizzes->bind_param("i", $student_id);
$quizzes->execute();
$quiz_result = $quizzes->get_result();
?>

<style>
.box-container {
    max-width: 800px;
    margin: 2rem auto;
    background: #fff;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

h2, h3 {
    color: #4a6fa5;
    text-align: center;
}

.quiz-block {
    margin-bottom: 2rem;
    border: 1px solid #ddd;
    padding: 1.2rem;
    border-radius: 8px;
}

.quiz-block h4 {
    margin-bottom: 0.5rem;
    color: #333;
}

.quiz-block form {
    margin-top: 1rem;
}

input[type="text"] {
    width: 100%;
    padding: 8px;
    margin: 6px 0 12px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

input[type="submit"] {
    background-color: #4a6fa5;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 1rem;
}

input[type="submit"]:hover {
    background-color: #2f4973;
}

.message {
    text-align: center;
    font-weight: bold;
    color: green;
    margin-bottom: 1rem;
}
</style>

<div class="box-container">
    <h2>Available Quizzes</h2>

    <?php if (!empty($message)): ?>
        <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>

    <?php if ($quiz_result->num_rows > 0): ?>
        <?php while ($quiz = $quiz_result->fetch_assoc()): ?>
            <?php
            // Check if already attempted
            $check = $conn->prepare("SELECT 1 FROM quiz_submissions WHERE student_id = ? AND quiz_id = ?");
            $check->bind_param("ii", $student_id, $quiz['id']);
            $check->execute();
            $already_attempted = $check->get_result()->num_rows > 0;
            ?>

            <div class="quiz-block">
                <h4><?php echo htmlspecialchars($quiz['title']); ?> <small>(<?php echo htmlspecialchars($quiz['course_name']); ?>)</small></h4>

                <?php if ($already_attempted): ?>
                    <p><strong>✅ Already Attempted</strong></p>
                <?php else: ?>
                    <?php
                    $questions = $conn->query("SELECT * FROM quiz_questions WHERE quiz_id = " . $quiz['id']);
                    if ($questions->num_rows > 0):
                    ?>
                        <form method="POST">
                            <input type="hidden" name="quiz_id" value="<?php echo $quiz['id']; ?>">
                            <?php while ($q = $questions->fetch_assoc()): ?>
                                <p><strong><?php echo htmlspecialchars($q['question_text']); ?></strong></p>
                                <input type="text" name="answers[<?php echo $q['id']; ?>]" required placeholder="Your answer">
                            <?php endwhile; ?>
                            <input type="submit" value="Submit Quiz">
                        </form>
                    <?php else: ?>
                        <p>No questions available for this quiz.</p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No quizzes available at this time.</p>
    <?php endif; ?>
</div>

<?php include("../includes/footer.php"); ?>
