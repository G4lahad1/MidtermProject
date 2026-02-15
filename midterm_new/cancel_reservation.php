<?php
session_start();
require 'assets/includes/db_connection.php';

// 1. Check Login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit;
}

// 2. Process Cancellation
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $reservation_id = $_POST['reservation_id'];
    $user_id = $_SESSION['user_id']; // Get current user ID for security

    // 3. SECURITY CHECK: 
    // Only update if the reservation belongs to THIS user AND is currently pending.
    // This prevents users from cancelling other people's bookings.
    
    $sql = "UPDATE reservations 
            SET status = 'cancelled' 
            WHERE id = ? AND user_id = ? AND status = 'pending'";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $reservation_id, $user_id);
    
    if ($stmt->execute()) {
        // Success
        header("Location: history.php?msg=cancelled");
    } else {
        // Error
        header("Location: history.php?msg=error");
    }
    
    $stmt->close();
    $conn->close();

} else {
    header("Location: history.php");
    exit;
}
?>