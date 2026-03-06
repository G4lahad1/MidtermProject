<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../../index.php");
    exit;
}
require '../includes/db_connection.php';

// --- 1. ADD VIOLATION (Sanction) ---
if (isset($_POST['sanction_student'])) {
    $user_id = $_POST['user_id'];
    $type = $_POST['violation_type']; // e.g., "Late Return"
    $desc = $_POST['description'];
    $penalty = $_POST['penalty']; // e.g., "3 Days"
    
    // Insert new active violation
    $sql = "INSERT INTO violations (user_id, violation_type, description, penalty, status) 
            VALUES (?, ?, ?, ?, 'Active')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $user_id, $type, $desc, $penalty);
    
    if ($stmt->execute()) {
        header("Location: ../../admin/users.php?msg=sanctioned");
    } else {
        header("Location: ../../admin/users.php?error=failed");
    }
}

// --- 2. RESOLVE VIOLATION (Lift Ban) ---
if (isset($_GET['action']) && $_GET['action'] == 'resolve' && isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
    
    // Mark all active violations for this user as Resolved
    $sql = "UPDATE violations SET status = 'Resolved' WHERE user_id = ? AND status = 'Active'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        header("Location: ../../admin/users.php?msg=resolved");
    } else {
        header("Location: ../../admin/users.php?error=failed");
    }
}
?>