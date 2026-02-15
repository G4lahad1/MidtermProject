<?php
session_start();
require 'assets/includes/db_connection.php';

// 1. Check Login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit;
}

// 2. Fetch Rooms for the Dropdown
$rooms_sql = "SELECT * FROM rooms ORDER BY room_name ASC";
$rooms_result = $conn->query($rooms_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HAU Library | Reserve Room</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>

    <nav class="sidebar">
        <div class="logo-details">
            <img src="assets/css/photos/HAU logo.png" alt="HAU Logo">
            <span class="logo_name">HAU Library</span>
        </div>
        <ul class="nav-links">
            <li>
                <a href="dashboard.php">
                    <i class='bx bx-grid-alt'></i>
                    <span class="link_name">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="reserve.php" class="active">
                    <i class='bx bx-calendar-plus'></i>
                    <span class="link_name">Reserve Room</span>
                </a>
            </li>
            <li>
                <a href="history.php">
                    <i class='bx bx-history'></i>
                    <span class="link_name">My History</span>
                </a>
            </li>
            <li>
                <a href="violations.php">
                    <i class='bx bx-error-circle'></i>
                    <span class="link_name">Violations</span>
                </a>
            </li>
            <li>
                <a href="profile.php">
                    <i class='bx bx-user'></i>
                    <span class="link_name">Profile</span>
                </a>
            </li>
            <li class="log_out">
                <a href="index.php">
                    <i class='bx bx-log-out'></i>
                    <span class="link_name">Log Out</span>
                </a>
            </li>
        </ul>
    </nav>

    <section class="home-section">
        <nav class="top-navbar">
            <div class="sidebar-button">
                <i class='bx bx-menu sidebarBtn'></i>
                <span class="dashboard">Reserve a Room</span>
            </div>
            
            <div class="profile-details">
                <?php
                    // Profile Logic
                    $profilePic = "https://via.placeholder.com/40";
                    if (isset($_SESSION['profile_image']) && !empty($_SESSION['profile_image'])) {
                        $profilePic = 'data:image/jpeg;base64,' . base64_encode($_SESSION['profile_image']);
                    }
                    $displayName = isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : 'Student';
                ?>
                <img src="<?php echo $profilePic; ?>" alt="profile">
                <span class="admin_name"><?php echo $displayName; ?></span>
                <i class='bx bx-chevron-down'></i>
            </div>
        </nav>

        <div class="home-content">
            <div class="sales-boxes">
                
                <div class="recent-sales box" style="width: 100%;">
                    <div class="title">New Reservation Details</div>
                    
                    <div style="margin-top: 15px; margin-bottom: 15px;">
                        <?php
                        if (isset($_GET['msg'])) {
                            if ($_GET['msg'] == 'success') echo "<p style='color: green;'>Reservation submitted successfully! Status: Pending.</p>";
                            if ($_GET['msg'] == 'collision') echo "<p style='color: red;'>Error: This room is already booked for that time slot.</p>";
                            if ($_GET['msg'] == 'invalid_time') echo "<p style='color: red;'>Error: End time must be after start time.</p>";
                            if ($_GET['msg'] == 'error') echo "<p style='color: red;'>Database error. Please try again.</p>";
                        }
                        ?>
                    </div>

                    <form action="process_reservation.php" method="POST" style="margin-top: 10px;">
                        
                        <div style="margin-bottom: 15px;">
                            <label style="font-weight: 500; display: block; margin-bottom: 5px;">Select Room:</label>
                            <select name="room_id" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
                                <option value="" disabled selected>-- Choose a Room --</option>
                                <?php 
                                if ($rooms_result->num_rows > 0) {
                                    while($row = $rooms_result->fetch_assoc()) {
                                        echo '<option value="'.$row['id'].'">'.$row['room_name'].' ('.$row['type'].')</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <div style="margin-bottom: 15px;">
                            <label style="font-weight: 500; display: block; margin-bottom: 5px;">Date:</label>
                            <input type="date" name="date" required min="<?php echo date('Y-m-d'); ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
                        </div>

                        <div style="margin-bottom: 15px; display: flex; gap: 20px;">
                            <div style="flex: 1;">
                                <label style="font-weight: 500; display: block; margin-bottom: 5px;">Start Time:</label>
                                <input type="time" name="start_time" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
                            </div>
                            <div style="flex: 1;">
                                <label style="font-weight: 500; display: block; margin-bottom: 5px;">End Time:</label>
                                <input type="time" name="end_time" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
                            </div>
                        </div>

                        <div class="button" style="text-align: left;">
                            <button type="submit" style="background: #0A2558; color: #fff; padding: 10px 25px; border: none; border-radius: 4px; font-size: 15px; cursor: pointer;">
                                Confirm Reservation
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </section>

    <script src="assets/js/main.js"></script>
</body>
</html>