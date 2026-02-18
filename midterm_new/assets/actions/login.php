<?php
session_start();

include '../includes/db_connection.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Sanitize input
    $user = $conn->real_escape_string($_POST['username']);
    $pass = $_POST['password'];

    // 1. Fetch the user
    $sql = "SELECT id, username, password, full_name, profile_image FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user);
    $stmt->execute();
    $result = $stmt->get_result();

    // 2. Check if user exists
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        
        // 3. Verify the password hash
        if (password_verify($pass, $row['password'])) {
            
            // --- SUCCESS ---
            $_SESSION['profile_image'] = $row['profile_image'];
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $row['username'];
            $_SESSION['name'] = $row['full_name'];
            $_SESSION['user_id'] = $row['id'];

            header("Location: ../../dashboard.php");
            exit;
        }
    }

    
    header("Location: ../../index.php?error=invalid_credentials");
    exit;

    $stmt->close();
    $conn->close();
}
?>
