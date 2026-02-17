<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
requireLogin();

// Get statistics
$today = date('Y-m-d');

// Total students
$total_students = $conn->query("SELECT COUNT(*) as count FROM students WHERE status = 'active'")->fetch_assoc()['count'];

// Today's attendance marked
$today_marked = $conn->query("SELECT COUNT(DISTINCT student_id) as count FROM attendance WHERE attendance_date = '$today'")->fetch_assoc()['count'];

// Today's present
$today_present = $conn->query("SELECT COUNT(*) as count FROM attendance WHERE attendance_date = '$today' AND status = 'present'")->fetch_assoc()['count'];

// Today's absent
$today_absent = $conn->query("SELECT COUNT(*) as count FROM attendance WHERE attendance_date = '$today' AND status = 'absent'")->fetch_assoc()['count'];

$attendance_percentage = $total_students > 0 ? round(($today_present / $total_students) * 100, 1) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Attendance Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1 class="card-title">Dashboard</h1>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $total_students; ?></div>
                    <div class="stat-label">Total Students</div>
                </div>
                
                <div class="stat-card" style="border-left-color: var(--success-color);">
                    <div class="stat-value" style="color: var(--success-color);"><?php echo $today_present; ?></div>
                    <div class="stat-label">Present Today</div>
                </div>
                
                <div class="stat-card" style="border-left-color: var(--danger-color);">
                    <div class="stat-value" style="color: var(--danger-color);"><?php echo $today_absent; ?></div>
                    <div class="stat-label">Absent Today</div>
                </div>
                
                <div class="stat-card" style="border-left-color: var(--warning-color);">
                    <div class="stat-value" style="color: var(--warning-color);"><?php echo $attendance_percentage; ?>%</div>
                    <div class="stat-label">Attendance Rate</div>
                </div>
            </div>
            
            <div style="margin-top: 2rem;">
                <h3 style="margin-bottom: 1rem;">Quick Actions</h3>
                <div class="flex gap-2" style="flex-wrap: wrap;">
                    <a href="mark-attendance.php" class="btn btn-primary">📝 Mark Attendance</a>
                    <a href="qr-attendance.php" class="btn btn-success">📱 QR Attendance</a>
                    <a href="students.php" class="btn btn-success">👥 Manage Students</a>
                    <a href="reports/daily-report.php" class="btn btn-primary">📊 View Reports</a>
                    <?php if (isAdmin()): ?>
                        <a href="admin/users.php" class="btn btn-danger">⚙️ Manage Users</a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div style="margin-top: 2rem;">
                <h3 style="margin-bottom: 1rem;">Today's Status (<?php echo date('F d, Y'); ?>)</h3>
                <?php if ($today_marked == 0): ?>
                    <div class="alert alert-danger">
                        ⚠️ No attendance has been marked today yet.
                    </div>
                <?php else: ?>
                    <div class="alert alert-success">
                        ✓ Attendance marked for <?php echo $today_marked; ?> out of <?php echo $total_students; ?> students.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
