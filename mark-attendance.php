<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
requireLogin();

$message = '';
$error = '';
$today = date('Y-m-d');

// Handle attendance submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['attendance'])) {
        $success_count = 0;
        $error_count = 0;
        
        foreach ($_POST['attendance'] as $student_id => $status) {
            // Check if already marked today
            $check = $conn->prepare("SELECT id FROM attendance WHERE student_id = ? AND attendance_date = ?");
            $check->bind_param("is", $student_id, $today);
            $check->execute();
            $exists = $check->get_result()->num_rows > 0;
            
            if ($exists) {
                // Update existing record
                $stmt = $conn->prepare("UPDATE attendance SET status = ?, marked_by = ? WHERE student_id = ? AND attendance_date = ?");
                $stmt->bind_param("siis", $status, $_SESSION['user_id'], $student_id, $today);
            } else {
                // Insert new record
                $stmt = $conn->prepare("INSERT INTO attendance (student_id, attendance_date, status, marked_by) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("issi", $student_id, $today, $status, $_SESSION['user_id']);
            }
            
            if ($stmt->execute()) {
                $success_count++;
            } else {
                $error_count++;
            }
        }
        
        if ($success_count > 0) {
            $message = "Attendance marked successfully for $success_count student(s)!";
        }
        if ($error_count > 0) {
            $error = "Failed to mark attendance for $error_count student(s).";
        }
    }
}

// Get filter parameters
$class_filter = $_GET['class'] ?? '';
$section_filter = $_GET['section'] ?? '';

// Build query
$query = "SELECT * FROM students WHERE status = 'active'";
$params = [];
$types = '';

if ($class_filter) {
    $query .= " AND class = ?";
    $params[] = $class_filter;
    $types .= 's';
}

if ($section_filter) {
    $query .= " AND section = ?";
    $params[] = $section_filter;
    $types .= 's';
}

$query .= " ORDER BY roll_number";

$stmt = $conn->prepare($query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$students = $stmt->get_result();

// Get distinct classes and sections for filters
$classes = $conn->query("SELECT DISTINCT class FROM students ORDER BY class")->fetch_all(MYSQLI_ASSOC);
$sections = $conn->query("SELECT DISTINCT section FROM students ORDER BY section")->fetch_all(MYSQLI_ASSOC);

// Get today's attendance
$attendance_query = $conn->prepare("SELECT student_id, status FROM attendance WHERE attendance_date = ?");
$attendance_query->bind_param("s", $today);
$attendance_query->execute();
$today_attendance = [];
foreach ($attendance_query->get_result()->fetch_all(MYSQLI_ASSOC) as $row) {
    $today_attendance[$row['student_id']] = $row['status'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Attendance - Attendance Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1 class="card-title">Mark Attendance - <?php echo date('F d, Y'); ?></h1>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <!-- Filters -->
            <form method="GET" class="mb-2">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Class</label>
                        <select name="class" class="form-control" onchange="this.form.submit()">
                            <option value="">All Classes</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo htmlspecialchars($class['class']); ?>" 
                                    <?php echo $class_filter === $class['class'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($class['class']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Section</label>
                        <select name="section" class="form-control" onchange="this.form.submit()">
                            <option value="">All Sections</option>
                            <?php foreach ($sections as $section): ?>
                                <option value="<?php echo htmlspecialchars($section['section']); ?>"
                                    <?php echo $section_filter === $section['section'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($section['section']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </form>
            
            <form method="POST" action="">
                <div class="attendance-grid">
                    <?php while ($student = $students->fetch_assoc()): ?>
                        <?php
                        $current_status = $today_attendance[$student['id']] ?? '';
                        $is_marked = !empty($current_status);
                        ?>
                        <div class="student-row">
                            <div>
                                <strong><?php echo htmlspecialchars($student['roll_number']); ?></strong>
                            </div>
                            <div>
                                <?php echo htmlspecialchars($student['name']); ?>
                                <small style="color: #6b7280; display: block;">
                                    <?php echo htmlspecialchars($student['class'] . ' - ' . $student['section']); ?>
                                </small>
                            </div>
                            <div class="attendance-buttons">
                                <label style="cursor: pointer;">
                                    <input type="radio" name="attendance[<?php echo $student['id']; ?>]" value="present" 
                                        <?php echo $current_status === 'present' ? 'checked' : ''; ?> required>
                                    <span class="btn btn-success btn-sm">✓ Present</span>
                                </label>
                                <label style="cursor: pointer;">
                                    <input type="radio" name="attendance[<?php echo $student['id']; ?>]" value="absent"
                                        <?php echo $current_status === 'absent' ? 'checked' : ''; ?>>
                                    <span class="btn btn-danger btn-sm">✗ Absent</span>
                                </label>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
                
                <?php if ($students->num_rows > 0): ?>
                    <div style="margin-top: 2rem; text-align: center;">
                        <button type="submit" class="btn btn-primary" style="padding: 1rem 3rem; font-size: 1.1rem;">
                            💾 Save Attendance
                        </button>
                    </div>
                <?php else: ?>
                    <div class="alert alert-danger">No students found matching the selected filters.</div>
                <?php endif; ?>
            </form>
        </div>
    </div>
    
    <style>
        input[type="radio"] {
            display: none;
        }
        
        input[type="radio"] + span {
            opacity: 0.5;
            transition: opacity 0.3s;
        }
        
        input[type="radio"]:checked + span {
            opacity: 1;
        }
    </style>
</body>
</html>
