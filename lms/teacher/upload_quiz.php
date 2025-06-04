<?php
session_start();
include("../config/db.php");
include("../includes/header.php");

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'teacher') {
    header("Location: ../auth/login.php");
    exit();
}

$teacher_id = $_SESSION['user']['id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = $_POST['course_id'];
    $quiz_title = $_POST['quiz_title'];
    $questions = array_filter($_POST['questions']); // remove empty questions

    if (!empty($course_id) && !empty($quiz_title) && !empty($questions)) {
        // Insert quiz
        $stmt = $conn->prepare("INSERT INTO quizzes (course_id, title) VALUES (?, ?)");
        $stmt->bind_param("is", $course_id, $quiz_title);
        $stmt->execute();
        $quiz_id = $stmt->insert_id;

        // Insert questions
        $stmt_q = $conn->prepare("INSERT INTO quiz_questions (quiz_id, question_text) VALUES (?, ?)");
        foreach ($questions as $question_text) {
            $stmt_q->bind_param("is", $quiz_id, $question_text);
            $stmt_q->execute();
        }

        $message = "✅ Quiz uploaded successfully!";
    } else {
        $message = "❌ Please fill all required fields and at least one question.";
    }
}

$courses = $conn->query("SELECT * FROM courses WHERE teacher_id = $teacher_id");
?>

<style>
    .quiz-upload-container {
        max-width: 700px;
        margin: 2rem auto;
        background: #fff;
        padding: 2rem;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .quiz-upload-container h2 {
        text-align: center;
        color: #4a6fa5;
        margin-bottom: 1.5rem;
    }

    .quiz-upload-container form label {
        display: block;
        margin: 12px 0 6px;
        font-weight: 600;
        color: #333;
    }

    .quiz-upload-container select,
    .quiz-upload-container input[type="text"],
    .quiz-upload-container textarea {
        width: 100%;
        padding: 10px;
        border-radius: 5px;
        border: 1px solid #ccc;
        font-size: 1rem;
    }

    .quiz-upload-container textarea {
        resize: vertical;
    }

    .quiz-upload-container .question-group {
        margin-bottom: 1rem;
    }

    .quiz-upload-container input[type="submit"] {
        background: #4a6fa5;
        color: white;
        padding: 10px 25px;
        border: none;
        border-radius: 5px;
        font-size: 1rem;
        cursor: pointer;
        margin-top: 1rem;
        display: block;
        width: 100%;
        transition: background 0.3s ease;
    }

    .quiz-upload-container input[type="submit"]:hover {
        background: #2f4973;
    }

    .message {
        text-align: center;
        margin-bottom: 1rem;
        font-weight: bold;
        color: #155724;
    }
</style>

<div class="quiz-upload-container">
    <h2>Upload New Quiz</h2>

    <?php if (!empty($message)): ?>
        <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method="POST">
        <label for="course_id">Select Course</label>
        <select name="course_id" id="course_id" required>
            <option value="">-- Choose a Course --</option>
            <?php while ($course = $courses->fetch_assoc()): ?>
                <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['name']); ?></option>
            <?php endwhile; ?>
        </select>

        <label for="quiz_title">Quiz Title</label>
        <input type="text" name="quiz_title" id="quiz_title" placeholder="Enter quiz title" required>

        <label>Questions</label>
        <div class="question-group">
            <textarea name="questions[]" rows="2" placeholder="Enter question 1" required></textarea>
        </div>
        <div class="question-group">
            <textarea name="questions[]" rows="2" placeholder="Enter question 2"></textarea>
        </div>
        <div class="question-group">
            <textarea name="questions[]" rows="2" placeholder="Enter question 3"></textarea>
        </div>

        <!-- More textareas can be added if needed -->

        <input type="submit" value="Upload Quiz">
    </form>
</div>

<?php include("../includes/footer.php"); ?>
