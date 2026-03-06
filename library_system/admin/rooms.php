<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../index.php");
    exit;
}

require '../assets/includes/db_connection.php';
$current_page = 'rooms';

// Fetch all rooms
$sql = "SELECT * FROM rooms ORDER BY room_name ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Rooms | LibSpace Admin</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        /* Form Styles */
        .form-container { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 5px 10px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: 500; margin-bottom: 5px; color: #333; }
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; }
        
        /* Submit Button */
        .btn-submit { background: #0A2558; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; width: 100%; font-size: 15px; }
        .btn-submit:hover { background: #081D45; }

        /* Delete Button */
        .btn-delete { background: #fadbd8; color: #e74c3c; padding: 5px 10px; border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: 500; border: none; cursor: pointer; }
        .btn-delete:hover { background: #e74c3c; color: white; }

        /* --- YOUR CUSTOM MODAL CSS --- */
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
                <span class="dashboard">Manage Rooms</span>
            </div>
            <div class="profile-details">
                <span class="admin_name"><?php echo $_SESSION['name']; ?></span>
            </div>
        </nav>

        <div class="home-content">
            <div class="sales-boxes" style="display: flex; gap: 20px; flex-wrap: wrap;">
                
                <div class="recent-sales box" style="flex: 1; min-width: 300px;">
                    <div class="title">Add New Room</div>
                    <form action="../assets/actions/admin_room_action.php" method="POST" style="margin-top: 20px;">
                        <div class="form-group">
                            <label>Room Name</label>
                            <input type="text" name="room_name" placeholder="e.g. Discussion Room A" required>
                        </div>
                        <div class="form-group">
                            <label>Room Type</label>
                            <select name="room_type">
                                <option value="Discussion">Discussion Room</option>
                                <option value="Multimedia">Multimedia Room</option>
                                <option value="Quiet Area">Quiet Area</option>
                                <option value="Lab">Computer Lab</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Capacity (People)</label>
                            <input type="number" name="capacity" min="1" value="6" required>
                        </div>
                        <button type="submit" name="add_room" class="btn-submit">
                            <i class='bx bx-plus-circle'></i> Add Room
                        </button>
                    </form>
                </div>

                <div class="recent-sales box" style="flex: 2; min-width: 400px;">
                    <div class="title">Existing Rooms</div>
                    <div class="sales-details">
                        <table style="width:100%; border-collapse: collapse; margin-top:15px;">
                            <thead>
                                <tr style="text-align:left; border-bottom: 2px solid #eee;">
                                    <th style="padding:10px;">ID</th>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Capacity</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result->num_rows > 0): ?>
                                    <?php while($row = $result->fetch_assoc()): ?>
                                    <tr style="border-bottom: 1px solid #f0f0f0;">
                                        <td style="padding:10px; color:#888;">#<?php echo $row['id']; ?></td>
                                        <td style="font-weight: 500; color: #0A2558;"><?php echo $row['room_name']; ?></td>
                                        <td><?php echo $row['type']; ?></td>
                                        <td><?php echo $row['capacity']; ?> pax</td>
                                        <td>
                                            <button class="btn-delete" onclick="openDeleteModal(<?php echo $row['id']; ?>)">
                                               <i class='bx bx-trash'></i> Delete
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" style="padding:20px;">No rooms found. Add one!</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <div id="deleteModal" class="modal-overlay">
        <div class="modal-box">
            <i class='bx bx-error-circle' style="font-size: 50px; color: #e74c3c;"></i>
            <h3>Delete Room?</h3>
            <p>Are you sure you want to delete this room? This action cannot be undone.</p>
                
            <form action="../assets/actions/admin_room_action.php" method="GET">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="modal_room_id" value=""> 
                
                <div class="modal-buttons">
                    <button type="button" class="btn-close" onclick="closeDeleteModal()">No, Keep it</button>
                    <button type="submit" class="btn-confirm">Yes, Delete it</button>
                </div>
            </form>
        </div>
    </div>
    <script src="../assets/js/main.js"></script>
    <script>
        // Open Modal and set ID
        function openDeleteModal(id) {
            document.getElementById('modal_room_id').value = id;
            document.getElementById('deleteModal').style.display = 'flex';
        }

        // Close Modal
        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        // Close if clicked outside
        window.onclick = function(event) {
            var modal = document.getElementById('deleteModal');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>