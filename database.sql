-- Attendance Management System Database Schema
-- Created for XAMPP/MySQL

CREATE DATABASE IF NOT EXISTS attendance_system;
USE attendance_system;

-- Users table (Admin/Teachers)
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'teacher') DEFAULT 'teacher',
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Students table
CREATE TABLE IF NOT EXISTS students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    roll_number VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    class VARCHAR(50) NOT NULL,
    section VARCHAR(10),
    email VARCHAR(100),
    phone VARCHAR(15),
    qr_code VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Attendance table
CREATE TABLE IF NOT EXISTS attendance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    status ENUM('present', 'absent') NOT NULL,
    marked_by INT NOT NULL,
    marked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    remarks TEXT,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (marked_by) REFERENCES users(id),
    UNIQUE KEY unique_attendance (student_id, attendance_date)
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password, full_name, role, email) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin', 'admin@college.edu'),
('teacher1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Teacher', 'teacher', 'teacher@college.edu');

-- Insert sample students
INSERT INTO students (roll_number, name, class, section, email, phone) VALUES
('2024001', 'Alice Johnson', 'BSc Computer Science', 'A', 'alice@student.edu', '1234567890'),
('2024002', 'Bob Smith', 'BSc Computer Science', 'A', 'bob@student.edu', '1234567891'),
('2024003', 'Charlie Brown', 'BSc Computer Science', 'A', 'charlie@student.edu', '1234567892'),
('2024004', 'Diana Prince', 'BSc Computer Science', 'B', 'diana@student.edu', '1234567893'),
('2024005', 'Eve Wilson', 'BSc Computer Science', 'B', 'eve@student.edu', '1234567894');
