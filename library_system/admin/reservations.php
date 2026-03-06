<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../index.php");
    exit;
}

require '../assets/includes/db_connection.php';
$current_page = 'reservations';

// 1. FETCH PENDING REQUESTS
$sql_pending = "SELECT r.*, u.username, u.full_name, rm.room_name 
                FROM reservations r 
                JOIN users u ON r.user_id = u.id 
                JOIN rooms rm ON r.room_id = rm.id
                WHERE r.status = 'pending' 
                ORDER BY r.reservation_date ASC";
$res_pending = $conn->query($sql_pending);

// 2. FETCH HISTORY
$sql_all = "SELECT r.*, u.username, rm.room_name 
            FROM reservations r 
            JOIN users u ON r.user_id = u.id 
            JOIN rooms rm ON r.room_id = rm.id
            WHERE r.status != 'pending' 
            ORDER BY r.reservation_date DESC LIMIT 50";
$res_all = $conn->query($sql_all);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Reservations | LibSpace Admin</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .btn { padding: 5px 10px; border-radius: 4px; text-decoration: none; color: white; font-size: 12px; margin-right: 5px; cursor: pointer; border: none; }
        .btn-approve { background: #2ecc71; }
        .btn-reject { background: #e74c3c; }
        .status-badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500; }
        .status-approved { background: #d5f5e3; color: #2ecc71; }
        .status-rejected { background: #fadbd8; color: #e74c3c; }
        
        /* Modal Styles (Same as Student Side) */
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center; }
        .modal-box { background: #fff; padding: 30px; border-radius: 12px; width: 400px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.3); animation: fadeIn 0.3s; }
        .modal-buttons { display: flex; gap: 10px; justify-content: center; margin-top: 20px; }
        .btn-close { background: #ccc; color: #333; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        .btn-confirm { background: #e74c3c; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        @keyframes fadeIn { from {opacity: 0; transform: translateY(-20px);} to {opacity: 1; transform: translateY(0);} }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <section class="home-section">
        <nav>
            <div class="sidebar-button">
                <i class='bx bx-menu sidebarBtn'></i>
                <span class="dashboard">Reservations</span>
            </div>
            <div class="profile-details">
                <span class="admin_name"><?php echo $_SESSION['name']; ?></span>
            </div>
        </nav>

        <div class="home-content">
            
            <div class="sales-boxes">
                <div class="recent-sales box" style="width:100%;">
                    <div class="title" style="color: #e67e22;">Needs Action (Pending)</div>
                    
                    <?php if ($res_pending->num_rows > 0): ?>
                    <div class="sales-details">
                        <table style="width:100%; border-collapse: collapse; margin-top:15px;">
                            <thead>
                                <tr style="text-align:left; border-bottom: 2px solid #eee;">
                                    <th style="padding:10px;">Date</th>
                                    <th>Student</th>
                                    <th>Room</th>
                                    <th>Time</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $res_pending->fetch_assoc()): ?>
                                <tr style="border-bottom: 1px solid #f0f0f0;">
                                    <td style="padding:10px;"><?php echo date("M d", strtotime($row['reservation_date'])); ?></td>
                                    <td>
                                        <strong><?php echo $row['full_name']; ?></strong><br>
                                        <small style="color:#888;"><?php echo $row['username']; ?></small>
                                    </td>
                                    <td style="color:#0A2558; font-weight:500;"><?php echo $row['room_name']; ?></td>
                                    <td><?php echo date("g:i A", strtotime($row['start_time'])) . ' - ' . date("g:i A", strtotime($row['end_time'])); ?></td>
                                    <td>
                                        <a href="../assets/actions/admin_update_res.php?action=approve&id=<?php echo $row['id']; ?>" class="btn btn-approve">Approve</a>
                                        
                                        <button class="btn btn-reject" onclick="openRejectModal(<?php echo $row['id']; ?>)">Reject</button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                        <p style="padding:20px; color:#666; font-style:italic;">No pending requests.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="sales-boxes" style="margin-top: 20px;">
                <div class="recent-sales box" style="width:100%;">
                    <div class="title">History Log</div>
                    <div class="sales-details">
                        <table style="width:100%; border-collapse: collapse; margin-top:15px;">
                            <tbody>
                                <?php while($row = $res_all->fetch_assoc()): ?>
                                <tr style="border-bottom: 1px solid #f0f0f0;">
                                    <td style="padding:10px;"><?php echo date("M d", strtotime($row['reservation_date'])); ?></td>
                                    <td><?php echo $row['username']; ?></td>
                                    <td><?php echo $row['room_name']; ?></td>
                                    <td><span class="status-badge status-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <div id="rejectModal" class="modal-overlay">
        <div class="modal-box">
            <i class='bx bx-error-circle' style="font-size: 50px; color: #e74c3c;"></i>
            <h3>Reject Reservation?</h3>
            <p>Are you sure you want to reject this student's request? This cannot be undone.</p>
            
            <form action="../assets/actions/admin_update_res.php" method="GET">
                <input type="hidden" name="id" id="modal_reject_id" value="">
                <input type="hidden" name="action" value="reject"> 
                
                <div class="modal-buttons">
                    <button type="button" class="btn-close" onclick="closeRejectModal()">Cancel</button>
                    <button type="submit" class="btn-confirm">Yes, Reject</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        // Open Modal
        function openRejectModal(id) {
            document.getElementById('modal_reject_id').value = id;
            document.getElementById('rejectModal').style.display = 'flex';
        }

        // Close Modal
        function closeRejectModal() {
            document.getElementById('rejectModal').style.display = 'none';
        }

        // Close if clicked outside
        window.onclick = function(event) {
            var modal = document.getElementById('rejectModal');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>