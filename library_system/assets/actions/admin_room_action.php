<?php
session_start();

// 1. SECURITY: Only Admins allowed
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../../index.php");
    exit;
}

require '../includes/db_connection.php';

// --- ACTION: ADD ROOM ---
if (isset($_POST['add_room'])) {
    $name = $_POST['room_name'];
    $type = $_POST['room_type']; // e.g., Discussion, Multimedia
    $capacity = $_POST['capacity'];

    $sql = "INSERT INTO rooms (room_name, type, capacity) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $name, $type, $capacity);
    
    if ($stmt->execute()) {
        header("Location: ../../admin/rooms.php?msg=added");
    } else {
        header("Location: ../../admin/rooms.php?error=insert_failed");
    }
}

// --- ACTION: DELETE ROOM ---
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Check if there are active reservations for this room first!
    // (Optional safety check: Don't delete a room if people are booked in it)
    
    $sql = "DELETE FROM rooms WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: ../../admin/rooms.php?msg=deleted");
    } else {
        header("Location: ../../admin/rooms.php?error=delete_failed");
    }
}
?>