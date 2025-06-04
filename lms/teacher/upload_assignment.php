<?php
session_start();
include("../config/db.php");
include("../includes/header.php");

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'teacher') {
    header("Location: ../auth/login.php");
    exit();
}

$teacher_id = $_SESSION['user']['id'];
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = $_POST['course_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $due_date = $_POST['due_date'];

    // Handle file upload
    $file_path = "";
    if (isset($_FILES['assignment_file']) && $_FILES['assignment_file']['error'] === 0) {
        $target_dir = "../uploads/assignments/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $filename = basename($_FILES['assignment_file']['name']);
        $unique_name = time() . "_" . $filename;
        $target_file = $target_dir . $unique_name;

        if (move_uploaded_file($_FILES['assignment_file']['tmp_name'], $target_file)) {
            $file_path = $target_file;
        }
    }

    $stmt = $conn->prepare("INSERT INTO assignments (course_id, title, description, due_date, file_path) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $course_id, $title, $description, $due_date, $file_path);
    $stmt->execute();

    $success = "✅ Assignment uploaded successfully!";
}

$courses = $conn->query("SELECT * FROM courses WHERE teacher_id = $teacher_id");
?>

<style>
.assignment-form-container {
    max-width: 700px;
    margin: 3rem auto;
    background-color: #fff;
    padding: 2rem 2.5rem;
    border-radius: 10px;
    box-shadow: 0 6px 15px rgba(0,0,0,0.08);
}

.assignment-form-container h2 {
    color: #4a6fa5;
    text-align: center;
    margin-bottom: 1.5rem;
}

.assignment-form-container label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    color: #333;
}

.assignment-form-container input[type="text"],
.assignment-form-container input[type="date"],
.assignment-form-container select,
.assignment-form-container textarea,
.assignment-form-container input[type="file"] {
    width: 100%;
    padding: 10px;
    margin-bottom: 1.2rem;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 1rem;
}

.assignment-form-container input[type="submit"] {
    background-color: #4a6fa5;
    color: #fff;
    padding: 12px 20px;
    border: none;
    font-size: 1rem;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.3s;
}

.assignment-form-container input[type="submit"]:hover {
    background-color: #2f4973;
}

.success-msg {
    text-align: center;
    color: green;
    font-weight: bold;
    margin-bottom: 1rem;
}
</style>

<div class="assignment-form-container">
    <h2>Create New Assignment</h2>

    <?php if (!empty($success)): ?>
        <div class="success-msg"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label>Course:</label>
        <select name="course_id" required>
            <option value="">-- Select Course --</option>
            <?php while ($course = $courses->fetch_assoc()): ?>
                <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['name']); ?></option>
            <?php endwhile; ?>
        </select>

        <label>Title:</label>
        <input type="text" name="title" placeholder="Assignment title..." required>

        <label>Description:</label>
        <textarea name="description" rows="4" placeholder="Write assignment instructions..." required></textarea>

        <label>Due Date:</label>
        <input type="date" name="due_date" required>

        <label>Upload Assignment File (PDF, DOCX, etc):</label>
        <input type="file" name="assignment_file" accept=".pdf,.doc,.docx,.txt,.ppt,.pptx">

        <input type="submit" value="Upload Assignment">
    </form>
</div>

<?php include("../includes/footer.php"); ?>
