<?php
session_start();
include("../config/db.php");
include("../includes/header.php");

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'teacher') {
    header("Location: ../auth/login.php");
    exit();
}

$teacher_id = $_SESSION['user']['id'];
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['students'])) {
    $course_id = $_POST['course_id'];
    $date = $_POST['date'];
    $students = $_POST['students'];

    foreach ($students as $student_id => $status) {
        $stmt = $conn->prepare("INSERT INTO attendance (course_id, student_id, date, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $course_id, $student_id, $date, $status);
        $stmt->execute();
    }

    $message = "✅ Attendance marked successfully!";
}

$courses = $conn->query("SELECT * FROM courses WHERE teacher_id = $teacher_id");
$selected_course_id = isset($_POST['course_id']) ? $_POST['course_id'] : '';
?>

<style>
.attendance-container {
    max-width: 800px;
    margin: 2rem auto;
    background: #fff;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.attendance-container h2 {
    text-align: center;
    color: #4a6fa5;
    margin-bottom: 1.5rem;
}

.attendance-container label {
    display: block;
    margin: 10px 0 5px;
    font-weight: 600;
}

.attendance-container select,
.attendance-container input[type="date"] {
    width: 100%;
    padding: 10px;
    margin-bottom: 1rem;
    border: 1px solid #ccc;
    border-radius: 5px;
}

.attendance-container .student-select {
    margin-bottom: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.attendance-container .student-select select {
    width: 40%;
}

.attendance-container input[type="submit"] {
    background-color: #4a6fa5;
    color: white;
    border: none;
    padding: 10px 20px;
    font-size: 1rem;
    border-radius: 5px;
    cursor: pointer;
}

.attendance-container input[type="submit"]:hover {
    background-color: #2f4973;
}

.attendance-message {
    text-align: center;
    margin-bottom: 1rem;
    color: green;
    font-weight: bold;
}
</style>

<div class="attendance-container">
    <h2>Mark Attendance</h2>

    <?php if (!empty($message)): ?>
        <div class="attendance-message"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method="POST">
        <label for="course_id">Select Course:</label>
        <select name="course_id" id="course_id" onchange="this.form.submit()" required>
            <option value="">-- Select Course --</option>
            <?php while ($course = $courses->fetch_assoc()): ?>
                <option value="<?php echo $course['id']; ?>" <?php if ($selected_course_id == $course['id']) echo "selected"; ?>>
                    <?php echo htmlspecialchars($course['name']); ?>
                </option>
            <?php endwhile; ?>
        </select>
    </form>

    <?php
    if (!empty($selected_course_id)) {
        $students = $conn->query("SELECT u.id, u.name FROM enrollments e JOIN users u ON e.student_id = u.id WHERE e.course_id = $selected_course_id");

        if ($students->num_rows > 0):
    ?>
        <form method="POST">
            <input type="hidden" name="course_id" value="<?php echo $selected_course_id; ?>">

            <label for="date">Select Date:</label>
            <input type="date" name="date" required>

            <h3>Students:</h3>
            <?php while ($student = $students->fetch_assoc()): ?>
                <div class="student-select">
                    <label><?php echo htmlspecialchars($student['name']); ?></label>
                    <select name="students[<?php echo $student['id']; ?>]">
                        <option value="present">Present</option>
                        <option value="absent">Absent</option>
                        <option value="late">Late</option>
                    </select>
                </div>
            <?php endwhile; ?>

            <input type="submit" value="Submit Attendance">
        </form>
    <?php
        else:
            echo "<p>No students enrolled in this course.</p>";
        endif;
    }
    ?>
</div>

<?php include("../includes/footer.php"); ?>
