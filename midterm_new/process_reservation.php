<?php
session_start();
require 'assets/includes/db_connection.php';

// 1. Security Check: Ensure user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit;
}

// 2. Process the Form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Get and sanitize inputs
    $user_id = $_SESSION['user_id'];
    $room_id = $_POST['room_id'];
    $date = $_POST['date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    // 3. Validation: End time must be after Start time
    if ($start_time >= $end_time) {
        header("Location: reserve.php?msg=invalid_time");
        exit;
    }

    // 4. CRITICAL: Check for Conflicts (Double Booking Prevention)
    // Logic: Look for any existing reservation for the SAME room, on the SAME date,
    // where the time slots overlap. We exclude 'cancelled' bookings.
    // Overlap Formula: (StartA < EndB) and (EndA > StartB)
    
    $check_sql = "SELECT id FROM reservations 
                  WHERE room_id = ? 
                  AND reservation_date = ? 
                  AND status != 'cancelled'
                  AND (start_time < ? AND end_time > ?)";
                  
    $stmt = $conn->prepare($check_sql);
    
    // Bind params: i = integer, s = string. (room_id, date, end_time, start_time)
    // Note: We compare Request Start vs Existing End, and Request End vs Existing Start
    $stmt->bind_param("isss", $room_id, $date, $end_time, $start_time);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // CONFLICT FOUND: Room is already booked
        header("Location: reserve.php?msg=collision");
        exit;   
    } else {
        // 5. NO CONFLICT: Save the Reservation
        // Default status is 'pending'
        $insert_sql = "INSERT INTO reservations (user_id, room_id, reservation_date, start_time, end_time, status) 
                       VALUES (?, ?, ?, ?, ?, 'pending')";
        
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("iisss", $user_id, $room_id, $date, $start_time, $end_time);
        
        if ($insert_stmt->execute()) {
            // Success!
            header("Location: reserve.php?msg=success");
            exit;
        } else {
            // Database Error
            header("Location: reserve.php?msg=error");
            exit;
        }
    }
    
    $stmt->close();
    $conn->close();
    
} else {
    // If someone tries to access this file directly without submitting the form
    header("Location: dashboard.php");
    exit;
}
?>