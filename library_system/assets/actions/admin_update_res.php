<?php
session_start();

// 1. SECURITY: Only Admins allowed
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../../index.php");
    exit;
}

require '../includes/db_connection.php';

// 2. CHECK INPUTS
if (isset($_GET['id']) && isset($_GET['action'])) {
    
    $id = $_GET['id'];
    $action = $_GET['action'];
    
    // Determine new status
    if ($action == 'approve') {
        $new_status = 'approved';
    } elseif ($action == 'reject') {
        $new_status = 'rejected';
    } else {
        // Invalid action
        header("Location: ../../admin/reservations.php");
        exit;
    }

    // 3. UPDATE DATABASE
    $sql = "UPDATE reservations SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $new_status, $id);
    
    if ($stmt->execute()) {
        header("Location: ../../admin/reservations.php?msg=" . $action);
    } else {
        echo "Error updating record.";
    }
} else {
    header("Location: ../../admin/reservations.php");
}
?>