<?php
session_start();
include("../config/db.php");
include("../includes/header.php");

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit();
}

$student_id = $_SESSION['user']['id'];
$query = $conn->query("SELECT courses.name, attendance.date, attendance.status 
                      FROM attendance 
                      JOIN courses ON attendance.course_id = courses.id 
                      WHERE attendance.student_id = $student_id
                      ORDER BY attendance.date DESC");
?>

<div class="container attendance-container">
    <div class="attendance-header">
        <h2>Your Attendance Records</h2>
        <p>View your class attendance history</p>
    </div>

    <div class="attendance-content">
        <?php if ($query->num_rows > 0): ?>
            <table class="attendance-table">
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $query->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($row['date'])); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower($row['status']); ?>">
                                    <?php echo htmlspecialchars($row['status']); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-records">
                <p>No attendance records found.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include("../includes/footer.php"); ?>