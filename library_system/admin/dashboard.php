<?php
session_start();

// 1. SECURITY CHECK: Kick out anyone who isn't an admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../index.php"); // Go back to login
    exit;
}

// 2. CONNECT TO DATABASE (Go up one level to find the file)
require '../assets/includes/db_connection.php';

// --- CONFIG FOR SIDEBAR ---
$current_page = 'dashboard';
$page_title = 'Admin Dashboard';

// 3. FETCH ADMIN STATS
// Count Pending Requests (Needs Approval)
$pending_sql = "SELECT COUNT(*) as total FROM reservations WHERE status = 'pending'";
$pending_res = $conn->query($pending_sql);
$pending_count = $pending_res->fetch_assoc()['total'];

// Count Today's Bookings (Active Now)
$today_sql = "SELECT COUNT(*) as total FROM reservations WHERE reservation_date = CURDATE() AND status = 'approved'";
$today_res = $conn->query($today_sql);
$today_count = $today_res->fetch_assoc()['total'];

// Count Active Violations
$violation_sql = "SELECT COUNT(*) as total FROM violations WHERE status = 'Active'";
$violation_res = $conn->query($violation_sql);
$violation_count = $violation_res->fetch_assoc()['total'];

// 4. FETCH RECENT PENDING REQUESTS (For the table)
$recent_sql = "SELECT r.id, r.reservation_date, r.start_time, r.end_time, u.username, rm.room_name 
               FROM reservations r
               JOIN users u ON r.user_id = u.id
               JOIN rooms rm ON r.room_id = rm.id
               WHERE r.status = 'pending'
               ORDER BY r.reservation_date ASC
               LIMIT 5";
$recent_res = $conn->query($recent_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | LibSpace HAU</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    
    <style>
        /* Admin-specific tweaks */
        .box .number { font-size: 30px; font-weight: 600; }
        .box .indicator { display: flex; align-items: center; margin-top: 10px; }
        .box .indicator i { height: 20px; width: 20px; background: #e0f7fa; line-height: 20px; text-align: center; border-radius: 50%; color: #00838f; font-size: 14px; margin-right: 5px; }
        .box .indicator .text { font-size: 14px; color: #333; }
        /* Green Approve Button */
        .btn-approve { padding: 4px 8px; background: #2ecc71; color: white; border-radius: 4px; text-decoration: none; font-size: 12px; }
        .btn-approve:hover { background: #27ae60; }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <section class="home-section">
        <nav>
            <div class="sidebar-button">
                <i class='bx bx-menu sidebarBtn'></i>
                <span class="dashboard">Admin Dashboard</span>
            </div>
            
            <div class="profile-details">
                <span class="admin_name"><?php echo isset($_SESSION['name']) ? $_SESSION['name'] : 'Admin'; ?></span>
            </div>
        </nav>

        <div class="home-content">
            <div class="overview-boxes">
                <div class="box">
                    <div class="right-side">
                        <div class="box-topic">Pending Requests</div>
                        <div class="number"><?php echo $pending_count; ?></div>
                        <div class="indicator">
                            <i class='bx bx-time'></i>
                            <span class="text">Needs Approval</span>
                        </div>
                    </div>
                    <i class='bx bx-hourglass cart'></i> </div>
                
                <div class="box">
                    <div class="right-side">
                        <div class="box-topic">Bookings Today</div>
                        <div class="number"><?php echo $today_count; ?></div>
                        <div class="indicator">
                            <i class='bx bx-calendar-check'></i>
                            <span class="text">Scheduled Now</span>
                        </div>
                    </div>
                    <i class='bx bxs-calendar cart two'></i> </div>
                
                <div class="box">
                    <div class="right-side">
                        <div class="box-topic">Active Violations</div>
                        <div class="number"><?php echo $violation_count; ?></div>
                        <div class="indicator">
                            <i class='bx bx-error'></i>
                            <span class="text">Suspended Users</span>
                        </div>
                    </div>
                    <i class='bx bx-error-circle cart three'></i> </div>
            </div>

            <div class="sales-boxes">
                <div class="recent-sales box" style="width: 100%;">
                    <div class="title">Latest Pending Requests</div>
                    
                    <?php if ($recent_res->num_rows > 0): ?>
                    <div class="sales-details">
                        <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                            <thead>
                                <tr style="text-align: left; border-bottom: 2px solid #eee;">
                                    <th style="padding: 10px;">Date</th>
                                    <th style="padding: 10px;">Student</th>
                                    <th style="padding: 10px;">Room</th>
                                    <th style="padding: 10px;">Time</th>
                                    <th style="padding: 10px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $recent_res->fetch_assoc()): ?>
                                <tr style="border-bottom: 1px solid #eee;">
                                    <td style="padding: 10px;"><?php echo date("M d", strtotime($row['reservation_date'])); ?></td>
                                    <td style="padding: 10px; font-weight: 500;"><?php echo $row['username']; ?></td>
                                    <td style="padding: 10px; color: #0A2558;"><?php echo $row['room_name']; ?></td>
                                    <td style="padding: 10px;"><?php echo date("g:i A", strtotime($row['start_time'])) . ' - ' . date("g:i A", strtotime($row['end_time'])); ?></td>
                                    <td style="padding: 10px;">
                                        <a href="reservations.php?action=approve&id=<?php echo $row['id']; ?>" class="btn-approve">Review</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                        <p style="padding: 20px; color: #666;">No pending requests at the moment.</p>
                    <?php endif; ?>

                    <div class="button">
                        <a href="reservations.php">View All Reservations</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="../assets/js/main.js"></script>
</body>
</html>