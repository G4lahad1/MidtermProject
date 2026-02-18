  <section class="home-section">
        <nav class="top-navbar">
            <div class="sidebar-button">
                <i class='bx bx-menu sidebarBtn'></i>
                <span class="dashboard"><?= $page_title ?></span>
            </div>
            
            <div class="profile-details">
                <?php
                    $profilePic = "assets/css/photos/profile.png";
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
