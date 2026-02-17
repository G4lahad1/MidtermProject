<?php
session_start();
require 'assets/includes/db_connection.php';

// 1. Check Login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit;
}

// 2. Fetch User's History
$user_id = $_SESSION['user_id'];

// We want ALL reservations, ordered by date (newest first)
$sql = "SELECT r.*, rm.room_name 
        FROM reservations r 
        JOIN rooms rm ON r.room_id = rm.id 
        WHERE r.user_id = ? 
        ORDER BY r.reservation_date DESC, r.start_time DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HAU Library | My History</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    
    <style>
                /* Modal Overlay (Background) */
        .modal-overlay {
            display: none; /* Hidden by default */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5); /* Semi-transparent black */
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        /* Modal Box (The actual window) */
        .modal-box {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            width: 400px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            animation: fadeIn 0.3s;
        }

        .modal-box h3 {
            margin-top: 0;
            color: #333;
            font-size: 20px;
        }

        .modal-box p {
            color: #666;
            margin: 15px 0 25px;
        }

        /* Modal Buttons */
        .modal-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .btn-confirm {
            background: #e74c3c;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-close {
            background: #ccc;
            color: #333;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
        }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .btn-cancel {
            background-color: #e74c3c; /* Nice Red */
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-family: 'Poppins', sans-serif;
            transition: background 0.3s ease;
        }

        .btn-cancel:hover {
            background-color: #c0392b; /* Darker Red on Hover */
        }

        /* Ensure the form doesn't mess up table alignment */
        .action-form {
            margin: 0;
            display: inline-block;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        th {
            font-weight: 500;
            color: #0A2558;
        }
        /* Status Badges */
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        .badge.pending { background: #fff3cd; color: #856404; }
        .badge.approved { background: #d4edda; color: #155724; }
        .badge.completed { background: #cce5ff; color: #004085; }
        .badge.cancelled { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <?php include 'assets/includes/sidebar.php'; ?>
     <?php include 'assets/includes/topbar.php'; ?>


    
        <div class="home-content">
            <div class="sales-boxes">
                <div class="recent-sales box" style="width: 100%;">
                    <div class="title">All Reservations</div>
                    
                    <?php if ($result->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Room</th>
                                <th>Time</th>
                                <th>Status</th>
                                <th>Action</th> </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo date("M d, Y", strtotime($row['reservation_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['room_name']); ?></td>
                                    <td>
                                        <?php 
                                            echo date("g:i A", strtotime($row['start_time'])) . " - " . date("g:i A", strtotime($row['end_time'])); 
                                        ?>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo strtolower($row['status']); ?>">
                                            <?php echo ucfirst($row['status']); ?>
                                        </span>
                                             </td>
                                    <td>
                                        <?php if($row['status'] == 'pending'): ?>
                                            
                                            <button type="button" class="btn-cancel" onclick="openModal(<?php echo $row['id']; ?>)">
                                                Cancel
                                            </button>

                                        <?php else: ?>
                                            <span style="color: #ccc; font-size: 20px;">-</span>
                                        <?php endif; ?>
                                    </td>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                        <p style="padding: 20px; color: #666; text-align: center;">You haven't made any reservations yet.</p>
                        <div class="button" style="text-align: center;">
                            <a href="reserve.php">Make a Reservation</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
                <div id="cancelModal" class="modal-overlay">
                <div class="modal-box">
                    <i class='bx bx-error-circle' style="font-size: 50px; color: #e74c3c;"></i>
                    <h3>Cancel Reservation?</h3>
                    <p>Are you sure you want to cancel this booking? This action cannot be undone.</p>
                    
                    <form action="assets/actions/cancel_reservation.php" method="POST">
                        <input type="hidden" name="reservation_id" id="modal_reservation_id" value="">
                        
                        <div class="modal-buttons">
                            <button type="button" class="btn-close" onclick="closeModal()">No, Keep it</button>
                            <button type="submit" class="btn-confirm">Yes, Cancel it</button>
                        </div>
                    </form>
                </div>
            </div>

    <script src="assets/js/main.js"></script>
    <script>
    // Open the modal and set the ID
    function openModal(id) {
        // Find the hidden input inside the modal and set its value to the reservation ID
        document.getElementById('modal_reservation_id').value = id;
        
        // Show the modal
        document.getElementById('cancelModal').style.display = 'flex';
    }

    // Close the modal
    function closeModal() {
        document.getElementById('cancelModal').style.display = 'none';
    }

    // Close modal if user clicks outside the box
    window.onclick = function(event) {
        var modal = document.getElementById('cancelModal');
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
</script>
</body>
</html>