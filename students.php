<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
requireLogin();

$message = '';
$error = '';

// Handle student addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
    $roll_number = $_POST['roll_number'];
    $name = $_POST['name'];
    $class = $_POST['class'];
    $section = $_POST['section'];
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    
    $stmt = $conn->prepare("INSERT INTO students (roll_number, name, class, section, email, phone) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $roll_number, $name, $class, $section, $email, $phone);
    
    if ($stmt->execute()) {
        $message = "Student added successfully!";
    } else {
        $error = "Failed to add student. Roll number might already exist.";
    }
}

// Handle student deletion
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("UPDATE students SET status = 'inactive' WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $message = "Student removed successfully!";
    } else {
        $error = "Failed to remove student.";
    }
}

// Get students
$students = $conn->query("SELECT * FROM students WHERE status = 'active' ORDER BY roll_number");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - Attendance Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container">
        <!-- Add Student Form -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Add New Student</h2>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Roll Number *</label>
                        <input type="text" name="roll_number" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Name *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Class *</label>
                        <input type="text" name="class" class="form-control" placeholder="e.g., BSc Computer Science" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Section *</label>
                        <input type="text" name="section" class="form-control" placeholder="e.g., A" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Phone</label>
                        <input type="tel" name="phone" class="form-control">
                    </div>
                </div>
                
                <button type="submit" name="add_student" class="btn btn-success">➕ Add Student</button>
            </form>
        </div>
        
        <!-- Students List -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Students List (<?php echo $students->num_rows; ?>)</h2>
            </div>
            
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Roll Number</th>
                            <th>Name</th>
                            <th>Class</th>
                            <th>Section</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($student = $students->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['roll_number']); ?></td>
                                <td><?php echo htmlspecialchars($student['name']); ?></td>
                                <td><?php echo htmlspecialchars($student['class']); ?></td>
                                <td><?php echo htmlspecialchars($student['section']); ?></td>
                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                <td><?php echo htmlspecialchars($student['phone']); ?></td>
                                <td>
                                    <a href="?delete=<?php echo $student['id']; ?>" 
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Are you sure you want to remove this student?')">
                                        🗑️ Remove
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        
                        <?php if ($students->num_rows === 0): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; color: #6b7280;">No students found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
