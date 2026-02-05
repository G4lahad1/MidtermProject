<?php
session_start();

// SECURITY CHECK:
// If the user hasn't logged in, or the flag is false, redirect them back to login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit;
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

    <nav class="sidebar">
        <div class="logo-details">
            <img src="assets/css/photos/HAU logo.png" alt="HAU Logo">
            <span class="logo_name">HAU Library</span>
        </div>
        
        <ul class="nav-links">
            <li>
                <a href="#" class="active">
                    <i class='bx bx-grid-alt'></i>
                    <span class="link_name">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="#">
                    <i class='bx bx-calendar-plus'></i>
                    <span class="link_name">Reserve Room</span>
                </a>
            </li>
            <li>
                <a href="#">
                    <i class='bx bx-history'></i>
                    <span class="link_name">My History</span>
                </a>
            </li>
            <li>
                <a href="#">
                    <i class='bx bx-error-circle'></i>
                    <span class="link_name">Violations</span>
                </a>
            </li>
            <li>
                <a href="#">
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
                <span class="dashboard">Student Dashboard</span>
            </div>
            
            <div class="profile-details">
                <?php
                    // Default image
                    $profilePic = "https://via.placeholder.com/40";
                    
                    // Check if the user has a profile image in the session
                    if (isset($_SESSION['profile_image']) && !empty($_SESSION['profile_image'])) {
                        $profilePic = 'data:image/jpeg;base64,' . base64_encode($_SESSION['profile_image']);
                    }
                    
                    // Fallback for name if it's missing
                    $displayName = isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : 'Student';
                ?>
                <img src="<?php echo $profilePic; ?>" alt="profile">
                
                <span class="admin_name"><?php echo $displayName; ?></span>
                <i class='bx bx-chevron-down'></i>
            </div>
        </nav>

        <div class="home-content">
            <div class="overview-boxes">
                <div class="box">
                    <div class="right-side">
                        <div class="box-topic">Total Bookings</div>
                        <div class="number">12</div>
                        <div class="indicator">
                            <i class='bx bx-up-arrow-alt'></i>
                            <span class="text">This Semester</span>
                        </div>
                    </div>
                    <i class='bx bx-cart-alt cart'></i>
                </div>
                <div class="box">
                    <div class="right-side">
                        <div class="box-topic">Upcoming</div>
                        <div class="number">Today</div>
                        <div class="indicator">
                            <span class="text">2:00 PM - Room A</span>
                        </div>
                    </div>
                    <i class='bx bxs-calendar-check cart two'></i>
                </div>
                <div class="box">
                    <div class="right-side">
                        <div class="box-topic">Penalty Status</div>
                        <div class="number">Clear</div>
                        <div class="indicator">
                            <span class="text">Good Standing</span>
                        </div>
                    </div>
                    <i class='bx bx-check-shield cart three'></i>
                </div>
            </div>

            <div class="sales-boxes">
                <div class="recent-sales box">
                    <div class="title">Current & Recent Reservations</div>
                    <div class="sales-details">
                        <ul class="details">
                            <li class="topic">Date</li>
                            <li><a href="#">02 Oct 2023</a></li>
                            <li><a href="#">28 Sep 2023</a></li>
                            <li><a href="#">15 Sep 2023</a></li>
                        </ul>
                        <ul class="details">
                            <li class="topic">Room</li>
                            <li><a href="#">Discussion Room A</a></li>
                            <li><a href="#">Multimedia Room 2</a></li>
                            <li><a href="#">Discussion Room B</a></li>
                        </ul>
                        <ul class="details">
                            <li class="topic">Time</li>
                            <li><a href="#">2:00 PM - 4:00 PM</a></li>
                            <li><a href="#">9:00 AM - 11:00 AM</a></li>
                            <li><a href="#">1:00 PM - 3:00 PM</a></li>
                        </ul>
                        <ul class="details">
                            <li class="topic">Status</li>
                            <li><span class="status pending">Upcoming</span></li>
                            <li><span class="status return">Completed</span></li>
                            <li><span class="status return">Completed</span></li>
                        </ul>
                    </div>
                    <div class="button">
                        <a href="#">See All</a>
                    </div>
                </div>
                
                <div class="top-sales box">
                    <div class="title">Quick Actions</div>
                    <ul class="top-sales-details">
                        <li>
                            <a href="#">
                                <span class="product">Reserve Discussion Room</span>
                            </a>
                            <span class="price"><i class='bx bx-chevron-right'></i></span>
                        </li>
                        <li>
                            <a href="#">
                                <span class="product">Reserve Multimedia Room</span>
                            </a>
                            <span class="price"><i class='bx bx-chevron-right'></i></span>
                        </li>
                        <li>
                            <a href="#">
                                <span class="product">Report an Issue</span>
                            </a>
                            <span class="price"><i class='bx bx-chevron-right'></i></span>
                        </li>
                         <li>
                            <a href="#">
                                <span class="product">View Library Rules</span>
                            </a>
                            <span class="price"><i class='bx bx-chevron-right'></i></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <script src="assets/js/main.js"></script>
</body>
</html>