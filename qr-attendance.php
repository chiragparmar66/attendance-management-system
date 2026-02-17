<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
requireLogin();

$message = '';
$error = '';
$today = date('Y-m-d');

// Handle QR code attendance submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['qr_code'])) {
    $qr_code = trim($_POST['qr_code']);
    
    // Find student by QR code (we'll use roll_number as QR code for simplicity)
    $stmt = $conn->prepare("SELECT id, roll_number, name FROM students WHERE roll_number = ? AND status = 'active'");
    $stmt->bind_param("s", $qr_code);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();
    
    if ($student) {
        // Check if already marked today
        $check = $conn->prepare("SELECT id FROM attendance WHERE student_id = ? AND attendance_date = ?");
        $check->bind_param("is", $student['id'], $today);
        $check->execute();
        
        if ($check->get_result()->num_rows > 0) {
            $error = "Attendance already marked for " . htmlspecialchars($student['name']) . " today!";
        } else {
            // Mark attendance as present
            $stmt = $conn->prepare("INSERT INTO attendance (student_id, attendance_date, status, marked_by) VALUES (?, ?, 'present', ?)");
            $stmt->bind_param("isi", $student['id'], $today, $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                $message = "✓ Attendance marked for: " . htmlspecialchars($student['name']) . " (" . htmlspecialchars($student['roll_number']) . ")";
            } else {
                $error = "Failed to mark attendance!";
            }
        }
    } else {
        $error = "Student not found! Invalid QR code.";
    }
}

// Get today's QR attendance count
$qr_count = $conn->query("SELECT COUNT(*) as count FROM attendance WHERE attendance_date = '$today'")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Attendance - Attendance Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1 class="card-title">📱 QR-Based Attendance</h1>
            </div>
            
            <div class="alert alert-success" style="margin-bottom: 2rem;">
                <strong>Today's Date:</strong> <?php echo date('F d, Y'); ?><br>
                <strong>Marked via QR:</strong> <?php echo $qr_count; ?> students
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-success">
                    <strong><?php echo $message; ?></strong>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <strong><?php echo htmlspecialchars($error); ?></strong>
                </div>
            <?php endif; ?>
            
            <div style="max-width: 500px; margin: 0 auto;">
                <form method="POST" action="" id="qrForm">
                    <div class="form-group">
                        <label class="form-label">Scan QR Code / Enter Roll Number</label>
                        <input type="text" 
                               name="qr_code" 
                               id="qr_code" 
                               class="form-control" 
                               placeholder="Scan QR code or enter roll number" 
                               required 
                               autofocus
                               style="font-size: 1.2rem; padding: 1rem; text-align: center;">
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1.1rem;">
                        ✓ Mark Attendance
                    </button>
                </form>
                
                <div style="margin-top: 2rem; padding: 1.5rem; background-color: var(--light-color); border-radius: 8px;">
                    <h3 style="margin-bottom: 1rem;">📖 Instructions</h3>
                    <ol style="padding-left: 1.5rem; line-height: 1.8;">
                        <li>Students scan their QR code using a barcode scanner or mobile app</li>
                        <li>The system automatically marks them as present</li>
                        <li>Each student can be marked only once per day</li>
                        <li>For demo purposes, use the student's roll number (e.g., 2024001)</li>
                    </ol>
                </div>
                
                <div style="margin-top: 2rem; padding: 1.5rem; background-color: #fef3c7; border-radius: 8px; border-left: 4px solid var(--warning-color);">
                    <strong>💡 Note:</strong> In a production system, each student would have a unique QR code. 
                    For this demo, we're using roll numbers as QR identifiers.
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Auto-clear input after submission
        document.getElementById('qrForm').addEventListener('submit', function(e) {
            setTimeout(function() {
                document.getElementById('qr_code').value = '';
                document.getElementById('qr_code').focus();
            }, 100);
        });
        
        // Auto-focus on input
        document.getElementById('qr_code').focus();
    </script>
</body>
</html>
