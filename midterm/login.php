<?php
session_start();

// 1. Include the database connection
include 'assets/includes/db_connection.php'; // Adjust path if needed


if ($_SERVER["REQUEST_METHOD"] == "POST") {
   
    
    // Sanitize input to prevent basic SQL injection
    $user = $conn->real_escape_string($_POST['username']);
    $pass = $_POST['password'];


$sql = "SELECT id, username, password, full_name, profile_image FROM users WHERE username = '$user'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        // User found, now check password
        $row = $result->fetch_assoc();
        
        // NOTE: For better security, use password_verify($pass, $row['password']) 
        // if you hashed passwords during registration. 
        // For this simple example, we are comparing plain text.
        if ($pass === $row['password']) {
            
            // Password Correct: Start Session
            $_SESSION['profile_image'] = $row['profile_image'];
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $row['username'];
            $_SESSION['name'] = $row['full_name'];
            $_SESSION['user_id'] = $row['id'];

            header("Location: dashboard.php");
            exit;
            
        } else {
            // Password Incorrect
            header("Location: index.php?error=invalid_password");
            exit;
        }
    } else {
        // User not found
        header("Location: index.php?error=user_not_found");
        exit;
    }
    
    $conn->close();
}
?>