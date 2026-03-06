<?php
session_start();

include '../includes/db_connection.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $user = $_POST['username']; // No need for real_escape_string with prepare()
    $pass = $_POST['password'];

    // 1. Fetch the user AND THE ROLE
    // Changed 'i' to 's' in bind_param so it works for both Student IDs (numbers) and "admin" (text)
    $sql = "SELECT id, username, password, full_name, profile_image, role FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $user); 
    $stmt->execute();
    $result = $stmt->get_result();

    // 2. Check if user exists
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        
        // 3. Verify the password hash
        if (password_verify($pass, $row['password'])) {
            
            // --- SUCCESS: Set Session Variables ---
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['name'] = $row['full_name'];
            $_SESSION['profile_image'] = $row['profile_image'];
            $_SESSION['role'] = $row['role']; // Save the role for later use

            // --- 4. REDIRECT BASED ON ROLE ---
            if ($row['role'] == 'admin') {
                
                // Set the specific flag your Admin Dashboard looks for
                $_SESSION['admin_logged_in'] = true;
                
                // Go to Admin Dashboard (inside admin folder)
                header("Location: ../../admin/dashboard.php");
                exit;

            } else {
                
                // Set the specific flag your Student Dashboard looks for
                $_SESSION['loggedin'] = true;
                
                // Go to Student Dashboard (main folder)
                header("Location: ../../dashboard.php");
                exit;
            }

        } else {
            // Password Incorrect
            header("Location: ../../index.php?error=invalid_credentials");
            exit;
        }
    } else {
        // User Not Found
        header("Location: ../../index.php?error=invalid_credentials");
        exit;
    }

    $stmt->close();
    $conn->close();
}
?>
