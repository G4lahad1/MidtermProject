<?php
session_start();
require 'assets/includes/db_connection.php'; 

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit;
}

// --- CONFIG FOR SIDEBAR OUTPUT AND ACTIVE BAR---
$current_page = 'dashboard'; 
$page_title = 'Student Dashboard';
// --------------------------

// 1. GET DATA FROM DATABASE
$user_id = $_SESSION['user_id'];

// Get Reservations
$sql = "SELECT r.*, rm.room_name 
        FROM reservations r 
        JOIN rooms rm ON r.room_id = rm.id 
        WHERE r.user_id = ? 
        ORDER BY r.reservation_date DESC, r.start_time ASC 
        LIMIT 5";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$my_reservations = [];
while ($row = $result->fetch_assoc()) {
    $my_reservations[] = $row;
}

// 2. GET TOTAL BOOKINGS
$count_sql = "SELECT COUNT(*) as total FROM reservations WHERE user_id = ?";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param("i", $user_id);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_bookings = $count_result->fetch_assoc()['total'];

// 3. GET UPCOMING RESERVATION
$upcoming_sql = "SELECT r.*, rm.room_name 
                 FROM reservations r
                 JOIN rooms rm ON r.room_id = rm.id
                 WHERE r.user_id = ? 
                 AND r.status IN ('approved', 'pending')
                 AND (r.reservation_date > CURDATE() 
                      OR (r.reservation_date = CURDATE() AND r.start_time > CURTIME()))
                 ORDER BY r.reservation_date ASC, r.start_time ASC
                 LIMIT 1";

$up_stmt = $conn->prepare($upcoming_sql);
$up_stmt->bind_param("i", $user_id);
$up_stmt->execute();
$up_result = $up_stmt->get_result();
$upcoming = $up_result->fetch_assoc(); 

// 4. CHECK PENALTY STATUS
$penalty_sql = "SELECT COUNT(*) as count FROM violations WHERE user_id = ? AND status = 'Active'";
$penalty_stmt = $conn->prepare($penalty_sql);
$penalty_stmt->bind_param("i", $user_id);
$penalty_stmt->execute();
$penalty_res = $penalty_stmt->get_result();
$active_violations = $penalty_res->fetch_assoc()['count'];

if ($active_violations > 0) {
    $p_status = "Suspended";
    $p_text = "$active_violations Active Violation(s)";
    $p_icon = "bx-error-circle";
    $p_style = "color: #e74c3c;"; 
    $p_icon_bg = "background: #fdedec; color: #e74c3c;"; 
} 

else {
    $p_status = "Clear";
    $p_text = "Good Standing";
    $p_icon = "bx-check-shield";
    $p_style = ""; 
    $p_icon_bg = ""; 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HAU Library | Student Dashboard</title>
    
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
            <div class="overview-boxes">
                <div class="box">
                    <div class="right-side">
                        <div class="box-topic">Total Bookings</div>
                        <div class="number"><?php echo $total_bookings; ?></div>
                        <div class="indicator">
                            <i class='bx bx-up-arrow-alt'></i>
                            <span class="text">All Time</span>
                        </div>
                    </div>
                </div>
                
                <div class="box">
                    <div class="right-side">
                        <div class="box-topic">Upcoming</div>
                        <?php if ($upcoming): ?>
                            <div class="number">
                                <?php echo ($upcoming['reservation_date'] == date('Y-m-d')) ? "Today" : date("M d", strtotime($upcoming['reservation_date'])); ?>
                            </div>
                            <div class="indicator">
                                <i class='bx bx-time-five'></i>
                                <span class="text">
                                    <?php echo date("g:i A", strtotime($upcoming['start_time'])) . " - " . $upcoming['room_name']; ?>
                                </span>
                            </div>
                        <?php else: ?>
                            <div class="number">--</div>
                            <div class="indicator">
                                <span class="text">No upcoming bookings</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <i class='bx bxs-calendar-check cart two'></i>
                </div>
                
                <div class="box">
                    <div class="right-side">
                        <div class="box-topic">Penalty Status</div>
                        <div class="number" style="<?php echo $p_style; ?>"><?php echo $p_status; ?></div>
                        <div class="indicator">
                            <span class="text" style="<?php echo $p_style; ?>"><?php echo $p_text; ?></span>
                        </div>
                    </div>
                    <i class='bx <?php echo $p_icon; ?> cart three' style="<?php echo $p_icon_bg; ?>"></i>
                </div>
            </div>

            <div class="sales-boxes">
                <div class="recent-sales box">
                    <div class="title">Current & Recent Reservations</div>
                    
                    <?php if (empty($my_reservations)): ?>
                        <div style="padding: 20px; color: #666;">No reservations found. <a href="reserve.php" style="color:#c22727; font-weight:bold;">Book a room now.</a></div>
                    
                    <?php else: ?>
                    <div class="sales-details">

                        <ul class="details">
                            <li class="topic">Date</li>
                            <?php foreach($my_reservations as $res): ?>
                                <li><a href="#"><?php echo date("d M Y", strtotime($res['reservation_date'])); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                        <ul class="details">
                            <li class="topic">Room</li>
                            <?php foreach($my_reservations as $res): ?>
                                <li><a href="#"><?php echo htmlspecialchars($res['room_name']); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                        <ul class="details">
                            <li class="topic">Time</li>
                            <?php foreach($my_reservations as $res): ?>
                                <li><a href="#"><?php echo date("g:i A", strtotime($res['start_time'])) . " - " . date("g:i A", strtotime($res['end_time'])); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                        <ul class="details">
                            <li class="topic">Status</li>
                            <?php foreach($my_reservations as $res): 
                                $status_class = ($res['status'] == 'approved' || $res['status'] == 'completed') ? 'return' : 'pending';
                                if($res['status'] == 'cancelled') $status_class = 'return'; 
                            ?>
                                <li><span class="status <?php echo $status_class; ?>"><?php echo ucfirst($res['status']); ?></span></li>
                            <?php endforeach; ?>
                        </ul>

                    </div>
                    
                    <?php endif; ?>

                    <div class="button">
                        <a href="history.php">See All</a>
                    </div>
                </div>
                
                <div class="top-sales box">
                    <div class="title">Quick Actions</div>
                    <ul class="top-sales-details">
                        <li><a href="reserve.php"><span class="product">Reserve Discussion Room</span></a><span class="price"><i class='bx bx-chevron-right'></i></span></li>
                        <li><a href="reserve.php"><span class="product">Reserve Multimedia Room</span></a><span class="price"><i class='bx bx-chevron-right'></i></span></li>
                        <li><a href="#"><span class="product">Report an Issue</span></a><span class="price"><i class='bx bx-chevron-right'></i></span></li>
                        <li><a href="#"><span class="product">View Library Rules</span></a><span class="price"><i class='bx bx-chevron-right'></i></span></li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <script src="assets/js/main.js"></script>
</body>
</html>

