<?php
$host = 'localhost';
$username = 'root';  // Default XAMPP username
$password = '';      // Default XAMPP password

try {
    // First connect without specifying a database
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS codelens");
    
    // Select the database
    $pdo->exec("USE codelens");
    
    // Drop existing users table if you want to recreate it
    // $pdo->exec("DROP TABLE IF EXISTS users");
    
    // Create users table with all required columns
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        fullname VARCHAR(100) NOT NULL,
        uid VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) UNIQUE NOT NULL,
        phone VARCHAR(15) NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'candidate') NOT NULL DEFAULT 'candidate',
        remember_token VARCHAR(64) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql);

    // Add missing columns if they don't exist
    try {
        $pdo->exec("ALTER TABLE users 
            ADD COLUMN IF NOT EXISTS uid VARCHAR(50) UNIQUE AFTER fullname,
            ADD COLUMN IF NOT EXISTS role ENUM('admin', 'candidate') NOT NULL DEFAULT 'candidate' AFTER password");
    } catch(PDOException $e) {
        // Columns might already exist, ignore the error
    }

    // Create questions table
    $sql2 = "CREATE TABLE IF NOT EXISTS questions (
        id INT PRIMARY KEY AUTO_INCREMENT,
        admin_id INT NOT NULL,
        question_text TEXT NOT NULL,
        option_a TEXT NOT NULL,
        option_b TEXT NOT NULL,
        option_c TEXT NOT NULL,
        option_d TEXT NOT NULL,
        correct_option CHAR(1) NOT NULL,
        category VARCHAR(50) NOT NULL,
        difficulty ENUM('easy', 'medium', 'hard') NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (admin_id) REFERENCES users(id)
    )";

    // Create tests table
    $sql3 = "CREATE TABLE IF NOT EXISTS tests (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(100) NOT NULL,
        description TEXT,
        duration INT NOT NULL, -- in minutes
        passing_score FLOAT NOT NULL,
        active BOOLEAN DEFAULT TRUE,
        created_by INT NOT NULL,
        type VARCHAR(15) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES users(id)
    )";

    // Create test_questions junction table
    $sql4 = "CREATE TABLE IF NOT EXISTS test_questions (
        id INT PRIMARY KEY AUTO_INCREMENT,
        test_id INT NOT NULL,
        question_id INT NOT NULL,
        FOREIGN KEY (test_id) REFERENCES tests(id),
        FOREIGN KEY (question_id) REFERENCES questions(id)
    )";

    // Create test_attempts table
    $sql5 = "CREATE TABLE IF NOT EXISTS test_attempts (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        test_id INT NOT NULL,
        score FLOAT NOT NULL,
        total_questions INT NOT NULL,
        correct_answers INT NOT NULL,
        time_taken INT NOT NULL, -- in seconds
        completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (test_id) REFERENCES tests(id)
    )";

    // Create test_answers table
    $sql6 = "CREATE TABLE IF NOT EXISTS user_answers (
        id INT PRIMARY KEY AUTO_INCREMENT,
        attempt_id INT NOT NULL,
        question_id INT NOT NULL,
        selected_option CHAR(1),
        FOREIGN KEY (attempt_id) REFERENCES test_attempts(id),
        FOREIGN KEY (question_id) REFERENCES questions(id)
    )";

    $pdo->exec($sql2);
    $pdo->exec($sql3);
    $pdo->exec($sql4);
    $pdo->exec($sql5);
    $pdo->exec($sql6);

} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to redirect with message
function redirectWith($url, $message, $type = 'error') {
    $separator = (strpos($url, '?') !== false) ? '&' : '?';
    header("Location: $url$separator$type=" . urlencode($message));
    exit();
}
?>