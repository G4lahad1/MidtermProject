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

   <?php include 'assets/includes/sidebar.php'; ?>
     <?php include 'assets/includes/topbar.php'; ?>


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

                    <form action="assets/actions/process_reservation.php" method="POST" style="margin-top: 10px;">
                        
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
                            <button type="submit" style="background: #119939; color: #fff; padding: 10px 25px; border: none; border-radius: 4px; font-size: 15px; cursor: pointer;">
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