<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../index.php");
    exit;
}

require '../assets/includes/db_connection.php';
$current_page = 'users';

// Fetch students and count active violations
$sql = "SELECT u.id, u.username, u.full_name, 
        (SELECT COUNT(*) FROM violations v WHERE v.user_id = u.id AND v.status = 'Active') as active_violations
        FROM users u 
        WHERE u.role = 'student' 
        ORDER BY u.full_name ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Students | LibSpace Admin</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        /* Status Badges */
        .status-badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500; }
        .status-clear { background: #d5f5e3; color: #2ecc71; }
        .status-suspended { background: #fadbd8; color: #e74c3c; }

        /* Action Buttons */
        .btn { padding: 5px 10px; border-radius: 4px; text-decoration: none; color: white; font-size: 12px; border: none; cursor: pointer; }
        .btn-sanction { background: #e74c3c; } 
        .btn-resolve { background: #2ecc71; } 

        /* --- YOUR CUSTOM MODAL STYLES --- */
        .modal-overlay { 
            display: none; 
            position: fixed; top: 0; left: 0; 
            width: 100%; height: 100%; 
            background: rgba(0,0,0,0.5); 
            z-index: 1000; 
            justify-content: center; align-items: center; 
        }
        .modal-box { 
            background: #fff; padding: 30px; border-radius: 12px; 
            width: 400px; text-align: center; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.3); 
            animation: fadeIn 0.3s; 
        }
        .modal-buttons { display: flex; gap: 10px; justify-content: center; margin-top: 20px; }
        .btn-close { background: #ccc; color: #333; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        
        /* Green Confirm Button for Resolving */
        .btn-confirm-green { background: #2ecc71; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        .btn-confirm-red { background: #e74c3c; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        
        /* Form inputs for the Sanction Modal */
        .form-group { margin-bottom: 15px; text-align: left; }
        .form-group label { display: block; margin-bottom: 5px; font-size: 14px; color: #555; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px; }

        @keyframes fadeIn { from {opacity: 0; transform: translateY(-20px);} to {opacity: 1; transform: translateY(0);} }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <section class="home-section">
        <nav>
            <div class="sidebar-button">
                <i class='bx bx-menu sidebarBtn'></i>
                <span class="dashboard">Students & Violations</span>
            </div>
            <div class="profile-details">
                <span class="admin_name"><?php echo $_SESSION['name']; ?></span>
            </div>
        </nav>

        <div class="home-content">
            <div class="sales-boxes">
                <div class="recent-sales box" style="width:100%;">
                    <div class="title">Student Directory</div>
                    <div class="sales-details">
                        <table style="width:100%; border-collapse: collapse; margin-top:15px;">
                            <thead>
                                <tr style="text-align:left; border-bottom: 2px solid #eee;">
                                    <th style="padding:10px;">ID</th>
                                    <th>Full Name</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $result->fetch_assoc()): 
                                    $is_suspended = $row['active_violations'] > 0;
                                ?>
                                <tr style="border-bottom: 1px solid #f0f0f0;">
                                    <td style="padding:10px; color:#888;"><?php echo $row['username']; ?></td>
                                    <td style="font-weight: 500; color: #0A2558;"><?php echo $row['full_name']; ?></td>
                                    <td>
                                        <?php if($is_suspended): ?>
                                            <span class="status-badge status-suspended">Suspended</span>
                                        <?php else: ?>
                                            <span class="status-badge status-clear">Good Standing</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($is_suspended): ?>
                                            <button class="btn btn-resolve" onclick="openResolveModal(<?php echo $row['id']; ?>)">
                                               Resolve
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-sanction" onclick="openSanctionModal(<?php echo $row['id']; ?>, '<?php echo $row['full_name']; ?>')">
                                                Sanction
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div id="resolveModal" class="modal-overlay">
        <div class="modal-box">
            <i class='bx bx-check-circle' style="font-size: 50px; color: #2ecc71;"></i>
            <h3>Lift Suspension?</h3>
            <p>Are you sure you want to resolve all violations for this student? They will be allowed to book rooms again.</p>
                
            <form action="../assets/actions/admin_violation_action.php" method="GET">
                <input type="hidden" name="action" value="resolve">
                <input type="hidden" name="user_id" id="modal_resolve_id" value=""> 
                
                <div class="modal-buttons">
                    <button type="button" class="btn-close" onclick="closeResolveModal()">Cancel</button>
                    <button type="submit" class="btn-confirm-green">Yes, Lift Ban</button>
                </div>
            </form>
        </div>
    </div>

    <div id="sanctionModal" class="modal-overlay">
        <div class="modal-box" style="text-align: left;">
            <h3 style="margin-bottom: 15px; text-align: center;">Sanction Student</h3>
            <p id="modalStudentName" style="margin-bottom: 15px; color: #666; text-align: center;"></p>
            
            <form action="../assets/actions/admin_violation_action.php" method="POST">
                <input type="hidden" name="user_id" id="modalSanctionId">
                <input type="hidden" name="sanction_student" value="true">

                <div class="form-group">
                    <label>Violation Type</label>
                    <select name="violation_type" required>
                        <option value="Late Return">Late Return</option>
                        <option value="Noise/Disruption">Noise/Disruption</option>
                        <option value="Lost Item">Lost Item</option>
                        <option value="Damaged Equipment">Damaged Equipment</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Penalty Duration</label>
                    <select name="penalty" required>
                        <option value="1 Day">1 Day Suspension</option>
                        <option value="3 Days">3 Days Suspension</option>
                        <option value="1 Week">1 Week Suspension</option>
                        <option value="Indefinite">Indefinite (Requires Admin Removal)</option>
                    </select>
                </div>

                <div class="modal-buttons">
                    <button type="button" class="btn-close" onclick="closeSanctionModal()">Cancel</button>
                    <button type="submit" class="btn-confirm-red">Apply Sanction</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        // --- LOGIC FOR RESOLVE MODAL ---
        function openResolveModal(id) {
            document.getElementById('modal_resolve_id').value = id;
            document.getElementById('resolveModal').style.display = 'flex';
        }
        function closeResolveModal() {
            document.getElementById('resolveModal').style.display = 'none';
        }

        // --- LOGIC FOR SANCTION MODAL ---
        function openSanctionModal(id, name) {
            document.getElementById('modalSanctionId').value = id;
            document.getElementById('modalStudentName').innerText = "Student: " + name;
            document.getElementById('sanctionModal').style.display = 'flex';
        }
        function closeSanctionModal() {
            document.getElementById('sanctionModal').style.display = 'none';
        }

        // --- CLOSE ON CLICK OUTSIDE ---
        window.onclick = function(event) {
            var m1 = document.getElementById('resolveModal');
            var m2 = document.getElementById('sanctionModal');
            if (event.target == m1) m1.style.display = "none";
            if (event.target == m2) m2.style.display = "none";
        }
    </script>
</body>
</html>