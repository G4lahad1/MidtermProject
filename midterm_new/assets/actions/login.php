<?php
session_start();

include '../includes/db_connection.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
   
    // Sanitize input to prevent basic SQL injection
    $user = $conn->real_escape_string($_POST['username']);
    $pass = $_POST['password'];

    // Prepare execute method
    $sql = "SELECT id, username, password, full_name, profile_image FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        // User found, now check password
        $row = $result->fetch_assoc();
        
        if ($pass === $row['password']) {
            // Password Correct: Start Session
            $_SESSION['profile_image'] = $row['profile_image'];
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $row['username'];
            $_SESSION['name'] = $row['full_name'];
            $_SESSION['user_id'] = $row['id'];

            header("Location: ../../dashboard.php");
            exit;
            
        } 

        else {
            // Password Incorrect
            header("Location: ../../index.php?error=invalid_password");
            exit;
        }
    } 

    else {
        // User not found
        header("Location: ../../index.php?error=user_not_found");
        exit;
    }
    
    $stmt->close();
    $conn->close();
}

?>
